<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Support\AgentIndentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class AgentRequisition extends Controller
{
    public function index()
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');
        $categories = DB::connection('coops')->select('CALL USP_GET_ITEM_CAT(?)', [session('org_id')]);
        return view('Agent.requisition', [
            'year_start' => session('year_start'),
            'year_end'   => session('year_end'),
            'categories' => $categories,
        ]);
    }

    public function getSubCats(Request $request)
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');
        $subs = DB::connection('coops')->select('CALL USP_GET_ITEM_SUB_CAT(?, ?)', [
            session('org_id'),
            $request->input('cat_id', 0)
        ]);
        return response()->json($subs);
    }

    public function getItems(Request $request)
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');
        $code = trim((string) ($request->input('code') ?? ''));
        $catId = (int) $request->input('cat_id', 0);
        $subCatId = (int) $request->input('sub_cat_id', 0);

        $items = $this->searchItems($catId, $subCatId, $code);

        if (empty($items) && $code !== '' && ctype_digit($code)) {
            $items = $this->itemsFromBarcode($code);
        }

        if ($code !== '' && count($items) > 1) {
            $exact = array_values(array_filter($items, function ($item) use ($code) {
                return strcasecmp(trim((string) ($item->Prod_Code ?? '')), $code) === 0
                    || (string) ($item->Barcode_Label ?? '') === $code;
            }));
            if (count($exact) === 1) {
                $items = $exact;
            }
        }

        return response()->json(array_values($items));
    }

    private function searchItems(int $catId, int $subCatId, string $code): array
    {
        try {
            $pdo = DB::connection('coops')->getPdo();
            $stmt = $pdo->prepare('CALL USP_SEARCH_ITEM_BY_CODE(?, ?, ?)');
            $stmt->execute([$catId, $subCatId, $code]);
            $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
            $stmt->closeCursor();
            return $rows ?: [];
        } catch (\Exception $e) {
            Log::error('Requisition item search error: ' . $e->getMessage());
            try {
                DB::purge('coops');
                $rows = DB::connection('coops')->select('CALL USP_GET_ITEM_LIST(?, ?, ?)', [
                    $catId,
                    $subCatId,
                    $code,
                ]);
                return $rows ?: [];
            } catch (\Exception $fallback) {
                Log::error('Requisition item list fallback error: ' . $fallback->getMessage());
                return [];
            }
        }
    }

    private function itemsFromBarcode(string $code): array
    {
        try {
            DB::purge('coops');
            $pdo = DB::connection('coops')->getPdo();
            $stmt = $pdo->prepare('CALL USP_GET_PROD_INFO(?, ?)');
            $stmt->execute([$code, date('Y-m-d')]);
            $info = $stmt->fetchAll(\PDO::FETCH_OBJ);
            $stmt->closeCursor();
        } catch (\Exception $e) {
            Log::error('Requisition barcode lookup error: ' . $e->getMessage());
            return [];
        }

        $items = [];
        $seen = [];
        foreach ($info as $row) {
            $prodId = (int) ($row->Prod_Id ?? 0);
            if ($prodId <= 0 || isset($seen[$prodId])) {
                continue;
            }
            $seen[$prodId] = true;
            $items[] = (object) [
                'Prod_Id'      => $prodId,
                'Prod_Code'    => $row->Prod_Code ?? $code,
                'Prod_ShortNm' => $row->Prod_ShortNm ?? '',
                'Unit_Id'      => $row->Unit_Id ?? '',
                'Unit_Name'    => $row->Unit_Name ?? '',
            ];
        }
        return $items;
    }

    public function searchItem(Request $request)
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');
        $items = DB::connection('coops')->select('CALL USP_SEARCH_ITEM(?)', [$request->keyword]);
        return response()->json($items);
    }

    public function search(Request $request)
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');
        $result = DB::connection('coops')->select('CALL USP_SEARCH_AGENT_REQ(?, ?, ?, ?)', [
            session('agent_id'),
            $request->frm_date,
            $request->to_date,
            AgentIndentType::ISSUE,
        ]);
        return response()->json($result);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date'    => 'required|date',
            'items'   => 'required|array|min:1',
            'items.*.item_id' => 'required|integer',
            'items.*.qnty'    => 'required|numeric|min:1',
            'items.*.unit_id' => 'required|integer',
        ]);

        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');

        try {
            DB::connection('coops')->beginTransaction();

            DB::connection('coops')->statement('DROP TEMPORARY TABLE IF EXISTS tempitem');
            DB::connection('coops')->statement('CREATE TEMPORARY TABLE tempitem (item_id INT, qnty SMALLINT, unit_id SMALLINT)');

            foreach ($request->items as $item) {
                DB::connection('coops')->statement(
                    'INSERT INTO tempitem (item_id, qnty, unit_id) VALUES (?, ?, ?)',
                    [$item['item_id'], $item['qnty'], $item['unit_id']]
                );
            }

            $result = DB::connection('coops')->select('CALL USP_AGENT_REQUISITION(?, ?, ?, ?, ?, ?, ?, ?)', [
                $request->indent_id,
                session('agent_id'),
                $request->date,
                $request->remarks,
                session('year_id'),
                session('branch_id'),
                $request->indent_id ? 2 : 1,
                AgentIndentType::ISSUE,
            ]);

            if ($result[0]->Error_No < 0) {
                DB::connection('coops')->rollBack();
                return response()->json(['success' => false, 'message' => $result[0]->Message], 400);
            }

            DB::connection('coops')->commit();
            return response()->json(['success' => true, 'message' => $result[0]->Message]);
        } catch (\Exception $e) {
            DB::connection('coops')->rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
