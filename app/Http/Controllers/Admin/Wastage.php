<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\SearchesItemsByCode;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Wastage extends Controller
{
    use SearchesItemsByCode;

    public function index()
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $categories = DB::connection('coops')->select('CALL USP_GET_ITEM_CAT(?)', [session('org_id')]);
        return view('Admin.wastage-damage', compact('categories'))->with('pageTitle', 'Wastage / Damage Entry');
    }

    public function getSubCats(Request $request)
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $subs = DB::connection('coops')->select('CALL USP_GET_ITEM_SUB_CAT(?, ?)', [
            session('org_id'),
            $request->input('cat_id', 0),
        ]);
        return response()->json($subs);
    }

    public function getItems(Request $request)
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');
        $items = $this->searchItemsByCode(
            (int) $request->input('cat_id', 0),
            (int) $request->input('sub_cat_id', 0),
            (string) ($request->input('code') ?? '')
        );
        return response()->json($items);
    }

    public function itemInfo(Request $request)
    {
        $prodId = (int) $request->input('prod_id', 0);
        $date = $request->input('date') ?: date('Y-m-d');
        if (!$prodId) {
            return response()->json(['error' => 'Select a product'], 422);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        $row = DB::connection('coops')->select('CALL USP_GET_LAST_PURCHASE_RATE(?, ?)', [$prodId, $date]);
        if (empty($row)) {
            return response()->json(['error' => 'Product not found'], 404);
        }
        return response()->json($row[0]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'entry_date'   => 'required|date',
            'particulars'  => 'required|string|max:200',
            'items'        => 'required|array|min:1',
            'items.*.item_id'   => 'required|integer',
            'items.*.quantity'  => 'required|integer|min:1|max:32767',
            'items.*.rate'      => 'required|numeric|min:0|max:99999999.99',
            'items.*.unit_id'   => 'required|integer',
            'damage_id'    => 'nullable|integer',
            'vouch_id'     => 'nullable|integer',
        ]);

        $yearStart = session('year_start');
        $yearEnd   = session('year_end');
        if ($request->entry_date < $yearStart || $request->entry_date > $yearEnd) {
            return response()->json(['error' => "Date must be between {$yearStart} and {$yearEnd}"], 400);
        }
        if ($request->entry_date > date('Y-m-d')) {
            return response()->json(['error' => 'Date cannot be after today'], 400);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        $conn = DB::connection('coops');
        $conn->beginTransaction();

        try {
            $conn->statement('DROP TEMPORARY TABLE IF EXISTS tempdamage');
            $conn->statement('CREATE TEMPORARY TABLE tempdamage (
                item_id INT,
                unit    SMALLINT,
                qnty    NUMERIC(10,2),
                rate    NUMERIC(12,2),
                tot_amt NUMERIC(18,2)
            )');

            foreach ($request->items as $item) {
                $qty = floatval($item['quantity']);
                $rate = floatval($item['rate']);
                $conn->insert('INSERT INTO tempdamage VALUES (?, ?, ?, ?, ?)', [
                    intval($item['item_id']),
                    intval($item['unit_id']),
                    $qty,
                    $rate,
                    round($qty * $rate, 2),
                ]);
            }

            $damageId = intval($request->input('damage_id', 0));
            $mode = $damageId > 0 ? 2 : 1;

            $result = $conn->select('CALL USP_ADD_EDIT_DAMAGE(?, ?, ?, ?, ?, ?, ?, ?)', [
                $damageId,
                intval($request->input('vouch_id', 0)),
                session('branch_id'),
                $request->input('entry_date'),
                $request->input('particulars'),
                session('user_id'),
                session('year_id'),
                $mode,
            ]);

            if (!empty($result) && $result[0]->Error_No < 0) {
                $conn->rollBack();
                return response()->json(['error' => $result[0]->Message ?? 'Save failed'], 400);
            }

            $conn->commit();
            return response()->json(['message' => $result[0]->Message ?? 'Saved successfully']);
        } catch (\Exception $e) {
            $conn->rollBack();
            Log::channel('trading')->error('Damage store error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function search(Request $request)
    {
        $frm = $request->input('from_date');
        $to = $request->input('to_date');
        if (!$frm || !$to) {
            return response()->json(['error' => 'Select from date and to date'], 422);
        }
        if ($frm > $to) {
            return response()->json(['error' => 'From date cannot be after to date'], 422);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        $rows = DB::connection('coops')->select('CALL USP_SEARCH_DAMAGE(?, ?, ?)', [
            $frm,
            $to,
            (int) (session('branch_id') ?: 0),
        ]);
        return response()->json($rows);
    }

    public function details($id)
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $result = DB::connection('coops')->select('CALL USP_GET_DAMAGE_DTLS(?)', [intval($id)]);
        if (empty($result)) {
            return response()->json(['error' => 'Record not found'], 404);
        }
        $row = $result[0];
        $row->Item_Details = json_decode($row->Item_Details, true) ?: [];
        return response()->json($row);
    }
}
