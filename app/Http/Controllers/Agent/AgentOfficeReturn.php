<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Concerns\SearchesItemsByCode;
use App\Http\Controllers\Controller;
use App\Support\AgentIndentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AgentOfficeReturn extends Controller
{
    use SearchesItemsByCode;

    private function bindOrg(): void
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');
    }

    private function toIsoDate(?string $date): ?string
    {
        if (!$date) {
            return null;
        }
        $date = trim($date);
        if (preg_match('/^(\d{2})[\/\-](\d{2})[\/\-](\d{4})$/', $date, $m)) {
            return "{$m[3]}-{$m[2]}-{$m[1]}";
        }
        return substr($date, 0, 10);
    }

    private function agentAvailableQty(int $prodId, string $date): float
    {
        $row = DB::connection('coops')->selectOne(
            'SELECT UDF_CAL_AGENT_STOCK(?, ?, ?) AS Avil_Qnty',
            [session('agent_id'), $prodId, $date]
        );
        return (float) ($row->Avil_Qnty ?? 0);
    }

    public function index()
    {
        $this->bindOrg();
        $categories = DB::connection('coops')->select('CALL USP_GET_ITEM_CAT(?)', [session('org_id')]);
        return view('Agent.office-return', [
            'categories' => $categories,
            'year_start' => session('year_start'),
            'year_end'   => session('year_end'),
        ]);
    }

    public function getSubCats(Request $request)
    {
        $this->bindOrg();
        $subs = DB::connection('coops')->select('CALL USP_GET_ITEM_SUB_CAT(?, ?)', [
            session('org_id'),
            $request->input('cat_id', 0),
        ]);
        return response()->json($subs);
    }

    public function getItems(Request $request)
    {
        $this->bindOrg();
        $items = $this->searchItemsByCode(
            (int) $request->input('cat_id', 0),
            (int) $request->input('sub_cat_id', 0),
            (string) ($request->input('code') ?? '')
        );
        return response()->json($items);
    }

    public function getItemByBarcode(Request $request)
    {
        try {
            $this->bindOrg();
            $date = $this->toIsoDate($request->input('return_date'));
            $code = trim((string) $request->input('barcode'));
            $result = $this->prodInfoByAgentBarcode($code, (int) session('agent_id'), (string) $date);
            if (empty($result)) {
                $result = $this->resolveUniqueProductInfo($code, (string) $date);
            }
            if (!empty($result)) {
                $item = $result[0];
                $item->Avil_Qnty = $this->agentAvailableQty((int) $item->Prod_Id, $date);
                return response()->json($item);
            }
            return response()->json(['error' => 'Invalid Code Entered !!'], 404);
        } catch (\Exception $e) {
            Log::error('Office Return Barcode Error: ' . $e->getMessage());
            return response()->json(['error' => 'Error fetching product details'], 500);
        }
    }

    public function getItemByProd(Request $request)
    {
        try {
            $prodId = (int) $request->input('prod_id', 0);
            $date = $this->toIsoDate($request->input('return_date'));
            if ($prodId <= 0 || !$date) {
                return response()->json(['error' => 'Product and date are required'], 400);
            }
            $this->bindOrg();
            $agentQty = $this->agentAvailableQty($prodId, $date);
            $result = DB::connection('coops')->select('CALL USP_GET_PROD_INFO_BY_ITEM(?, ?)', [$prodId, $date]);
            if (!empty($result)) {
                $item = $result[0];
                $item->Avil_Qnty = $agentQty;
                return response()->json($item);
            }
            return response()->json(['error' => 'Item not found'], 404);
        } catch (\Exception $e) {
            Log::error('Office Return Item Info Error: ' . $e->getMessage());
            return response()->json(['error' => 'Error fetching product details'], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'return_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer',
            'items.*.unit_id' => 'required|integer',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        $yearStart = session('year_start');
        $yearEnd   = session('year_end');
        $date = $this->toIsoDate($request->return_date);
        if ($date < $yearStart || $date > $yearEnd) {
            return response()->json(['error' => "Return date must be between {$yearStart} and {$yearEnd}"], 400);
        }

        $this->bindOrg();
        $conn = DB::connection('coops');
        $conn->beginTransaction();

        try {
            foreach ($request->items as $item) {
                $avail = $this->agentAvailableQty((int) $item['item_id'], $date);
                if ((float) $item['quantity'] > $avail) {
                    $conn->rollBack();
                    return response()->json([
                        'error' => 'Return qty cannot exceed available stock for an item.',
                    ], 400);
                }
            }

            $conn->statement('DROP TEMPORARY TABLE IF EXISTS tempitem');
            $conn->statement('CREATE TEMPORARY TABLE tempitem (
                item_id INT,
                qnty SMALLINT,
                unit_id SMALLINT
            )');

            foreach ($request->items as $item) {
                $conn->insert(
                    'INSERT INTO tempitem (item_id, qnty, unit_id) VALUES (?, ?, ?)',
                    [$item['item_id'], $item['quantity'], $item['unit_id']]
                );
            }

            $result = $conn->select('CALL USP_AGENT_REQUISITION(?, ?, ?, ?, ?, ?, ?, ?)', [
                null,
                session('agent_id'),
                $date,
                $request->input('remarks', 'Office Return'),
                session('year_id'),
                session('branch_id'),
                1,
                AgentIndentType::COUNTER_RETURN,
            ]);

            if (!empty($result) && ($result[0]->Error_No ?? 0) < 0) {
                $conn->rollBack();
                return response()->json(['error' => $result[0]->Message ?? 'Save failed'], 400);
            }

            $conn->commit();
            return response()->json([
                'success' => true,
                'message' => $result[0]->Message ?? 'Return requisition submitted successfully',
            ]);
        } catch (\Exception $e) {
            $conn->rollBack();
            Log::error('Office Return Save Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to save return requisition.'], 500);
        }
    }
}
