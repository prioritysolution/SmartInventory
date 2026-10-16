<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\SearchesItemsByCode;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class Purchase extends Controller
{
    use SearchesItemsByCode;

    public function index()
    {
        $branchId = session('branch_id');
        $orgId    = session('org_id');
        Config::set('database.connections.coops.database', session('org_schema'));
        $suppliers  = DB::connection('coops')->select('CALL USP_GET_PARTY_LIST(?, ?, ?)', [1, $branchId, 0]);
        $categories = DB::connection('coops')->select('CALL USP_GET_ITEM_CAT(?)', [$orgId]);
        $banks      = DB::connection('coops')->select('CALL USP_GET_BANK_LEDGER()');
        return view('Admin.good-received', compact('suppliers', 'categories', 'banks'))->with('pageTitle', 'Good Received Entry');
    }

    public function getSubCategories($catId)
    {
        $orgId = session('org_id');
        Config::set('database.connections.coops.database', session('org_schema'));
        $subCategories = DB::connection('coops')->select('CALL USP_GET_ITEM_SUB_CAT(?, ?)', [$orgId, $catId]);
        return response()->json($subCategories);
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




    public function store(Request $request)
    {
        $request->validate([
            'purchase_date' => 'required|date',
            'purchase_no'   => 'nullable|string|max:20',
            'party_id'      => 'required|integer',
            'trans_mode'    => 'required|in:1,2,3',
            'items'         => 'required|array|min:1',
            'bank_id'       => 'nullable|integer',
            'instrument_no' => 'nullable|string|max:50',
            'ref_vouch_no'  => 'nullable|string|max:20',
            'disc_percent'  => 'nullable|numeric',
            'freight_amt'   => 'nullable|numeric|min:0|max:999999.99',
            'round_off'     => 'nullable|numeric',
            'net_amt'       => 'nullable|numeric',
            'items.*.quantity' => 'required|numeric|max:99999999.99|min:0.01',
            'items.*.rate'     => 'required|numeric|max:99999999.99|min:0',
            'items.*.sale_mrp' => 'required|numeric|min:0.01',
            'vouch_id' => 'nullable|integer',
        ]);
        $yearStart = session('year_start');
        $yearEnd   = session('year_end');

        if ($request->purchase_date < $yearStart || $request->purchase_date > $yearEnd) {
            return response()->json(['error' => "Purchase Date must be between {$yearStart} and {$yearEnd}"], 400);
        }
        if ((int) $request->input('trans_mode') === 2) {
            if ((int) $request->input('bank_id', 0) <= 0) {
                return response()->json(['error' => 'Please select a Bank'], 400);
            }
            if (!trim((string) $request->input('instrument_no', ''))) {
                return response()->json(['error' => 'Instrument No is required for Bank'], 400);
            }
        }
        Config::set('database.connections.coops.database', session('org_schema'));
        
        // amazonq-ignore-next-line
        $conn = DB::connection('coops');

        $conn->beginTransaction();

    
        try {
            $conn->statement('DROP TEMPORARY TABLE IF EXISTS temppurchase');
            $conn->statement('CREATE TEMPORARY TABLE temppurchase (
                item_id   INT,
                hsn_code  INT,
                qnty      NUMERIC(10,2),
                rate      NUMERIC(10,2),
                unit      SMALLINT,
                tot_amt   NUMERIC(10,2),
                sale_mrp  NUMERIC(10,2),
                disc_perc NUMERIC(5,2),
                disc_amt  NUMERIC(8,2),
                tax_amt   NUMERIC(10,2),
                sgst_perc NUMERIC(5,2),
                sgst_amt  NUMERIC(10,2),
                cgst_perc NUMERIC(5,2),
                cgst_amt  NUMERIC(10,2),
                net_amt   NUMERIC(10,2),
                Pack_Date DATE NULL
            )');

            // Insert each item into temp table
            foreach ($request->items as $item) {
                $conn->insert('INSERT INTO temppurchase VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)', [
                    $item['item_id'],
                    $item['hsn_code'],
                    $item['quantity'],
                    $item['rate'],
                    $item['unit_id'],
                    $item['total_amount'],
                    $item['sale_mrp'],
                    $item['discount_percent'],
                    $item['discount_amount'],
                    $item['taxable_amount'],
                    $item['sgst_rate'],
                    $item['sgst_amount'],
                    $item['cgst_rate'],
                    $item['cgst_amount'],
                    $item['net_amount'],
                    !empty($item['item_purchase_date']) ? $item['item_purchase_date'] : null,

                ]);
            }

            $totAmt     = collect($request->items)->sum(fn($i) => floatval($i['total_amount']));
            $discAmt    = collect($request->items)->sum(fn($i) => floatval($i['discount_amount'] ?? 0));
            $taxableAmt = collect($request->items)->sum(fn($i) => floatval($i['taxable_amount']));
            $totGst     = collect($request->items)->sum(fn($i) => floatval($i['total_gst']));
            $hasItemDiscount = collect($request->items)->contains(fn($i) => floatval($i['discount_percent'] ?? 0) > 0);
            $discPerc = $hasItemDiscount ? 0 : floatval($request->input('disc_percent', 0));
            $discAmt = $hasItemDiscount
                ? collect($request->items)->sum(fn($i) => floatval($i['discount_amount'] ?? 0))
                : floatval($request->input('disc_amt', 0));
            $roundOff   = floatval($request->input('round_off', 0));
            $netAmt = floatval($request->input('net_amt', 0));
            $fregAmt = floatval($request->input('freight_amt', 0));


            $purchaseId = intval($request->input('purchase_id', 0));
            $mode = $purchaseId > 0 ? 2 : 1;

            $result = $conn->select('CALL USP_ADD_EDIT_PURCHASE(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?,?,?,?)', [
                $purchaseId,
                intval($request->input('vouch_id', 0)),
                session('branch_id'),
                $request->input('purchase_no'),
                $request->input('purchase_date'),
                $request->input('party_id'),
                $totAmt,
                $discPerc,
                $discAmt,
                $fregAmt,
                $taxableAmt,
                $totGst,
                $roundOff,
                $netAmt,
                intval($request->input('trans_mode')),           
                intval($request->input('bank_id', 0)),
                session('user_id'),
                session('year_id'),
                $mode,
                (int) $request->input('trans_mode') === 2 ? trim((string) $request->input('instrument_no', '')) : '',
            ]);

            if (!empty($result) && $result[0]->Error_No < 0) {
                $conn->rollBack();
                return response()->json(['error' => $result[0]->Message ?? 'Save failed'], 400);
            }

            $conn->commit();
            return response()->json(['message' => $result[0]->Message ?? 'Purchase saved successfully']);
        } catch (\Exception $e) {
            $conn->rollBack();
            Log::channel('trading')->error('Purchase store error: ' . $e->getMessage());
            return response()->json([
                'error'   => 'Failed to save purchase',
                'message' => $e->getMessage(),  
                'line'    => $e->getLine(),     
                'file'    => $e->getFile(),    
            ], 500);
        }
    }

    public function getSubCats(Request $request)
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        // amazonq-ignore-next-line
        $subs = DB::connection('coops')->select('CALL USP_GET_ITEM_SUB_CAT(?, ?)', [
            session('org_id'),
            $request->input('cat_id', 0)
        ]);
        return response()->json($subs);
    }


    public function search(Request $request)
    {
        Config::set('database.connections.coops.database', session('org_schema'));

        // amazonq-ignore-next-line
        $results = DB::connection('coops')->select('CALL USP_SEARCH_PURCHASE(?, ?, ?, ?)', [
            $request->input('from_date'),
            $request->input('to_date'),
            $request->input('party_id') ?: 0,
            session('branch_id'),
        ]);

        return response()->json($results);
    }

    public function details($id)
    {
        Config::set('database.connections.coops.database', session('org_schema'));

        // amazonq-ignore-next-line
        $result = DB::connection('coops')->select('CALL USP_GET_PURCHASE_DTLS(?)', [$id]);

        if (empty($result)) {
            return response()->json(['error' => 'Record not found'], 404);
        }

        $row = $result[0];
        $row->Item_Details = $this->purchaseLineItems((int) $id);
        Log::channel('trading')->info('Purchase Details', [
    'Voucher_Id' => $row->Voucher_Id ?? null,
    'Pur_Id'     => $row->Pur_Id ?? null,
]);


        return response()->json($row);
    }

    /**
     * One row per purchase line. The details procedure joins every stock row of
     * the same product, so two rates of one item are returned twice.
     */
    private function purchaseLineItems(int $tradingId): array
    {
        $rows = DB::connection('coops')->select("
            SELECT
                p.Prod_Id,
                i.Cate_Id AS Cat_Id,
                i.Prod_ShortNm AS Prod_Name,
                p.Gst_Code AS hsn_code,
                p.Item_Qty AS qnty,
                p.Item_Rate,
                p.Unit_Id,
                UDF_GET_UNIT_NAME(p.Unit_Id) AS Unit_Name,
                p.Item_Total,
                p.MRP,
                IFNULL(i.Sale_Margin, 0) AS Sale_Margin,
                p.Disc_Prcnt,
                p.Disc_Amt,
                p.Taxable_Amt,
                p.SGST_Prcnt,
                p.CGST_Prcnt,
                p.SGST_Amt,
                p.CGST_Amt,
                p.Net_Amt,
                s.Pack_Date
            FROM (
                SELECT tp.*,
                       ROW_NUMBER() OVER (
                           PARTITION BY tp.Prod_Id, tp.Item_Qty, ROUND(tp.Item_Rate, 2), ROUND(IFNULL(tp.MRP, 0), 2)
                           ORDER BY tp.Sl
                       ) AS line_no
                FROM trans_trading_products tp
                WHERE tp.Trading_Id = ?
            ) p
            JOIN mst_product i ON i.Prod_Id = p.Prod_Id
            LEFT JOIN (
                SELECT
                    st.Prod_Id,
                    st.Quantity,
                    ROUND(st.Rate, 2) AS Rate,
                    ROUND(IFNULL(st.MRP, 0), 2) AS MRP,
                    st.Pack_Date,
                    ROW_NUMBER() OVER (
                        PARTITION BY st.Prod_Id, st.Quantity, ROUND(st.Rate, 2), ROUND(IFNULL(st.MRP, 0), 2)
                        ORDER BY st.Stock_Id
                    ) AS line_no
                FROM trns_stockinout st
                WHERE st.Type_Id = ?
            ) s ON s.Prod_Id = p.Prod_Id
               AND s.Quantity = p.Item_Qty
               AND s.Rate = ROUND(p.Item_Rate, 2)
               AND s.MRP = ROUND(IFNULL(p.MRP, 0), 2)
               AND s.line_no = p.line_no
            ORDER BY p.Sl
        ", [$tradingId, $tradingId]);

        return array_map(static fn ($row) => (array) $row, $rows);
    }

    public function searchForReturn(Request $request)
    {
        Config::set('database.connections.coops.database', session('org_schema'));

        $results = DB::connection('coops')->select('CALL USP_SEARCH_PURCHASE_FOR_RETURN(?, ?, ?, ?)', [
            $request->input('from_date'),
            $request->input('to_date'),
            $request->input('party_id') ?: 0,
            session('branch_id'),
        ]);

        return response()->json($results);
    }

    public function returnableDetails($id)
    {
        Config::set('database.connections.coops.database', session('org_schema'));

        $result = DB::connection('coops')->select('CALL USP_GET_PURCHASE_RETURNABLE_DTLS(?)', [$id]);

        if (empty($result)) {
            return response()->json(['error' => 'Record not found'], 404);
        }

        $row = $result[0];
        $items = json_decode($row->Item_Details, true);
        if (!is_array($items)) {
            $items = [];
        }

        $partyId = (int) ($row->Party_Id ?? 0);
        $invoiceNo = trim((string) ($row->Invoice_No ?? ''));
        $refNo = trim((string) ($row->Ref_No ?? ''));
        $returnPool = [];
        if ($partyId > 0) {
            $returnRows = DB::connection('coops')->select("
                SELECT rp.Prod_Id,
                       ROUND(rp.Item_Rate, 2) AS Item_Rate,
                       SUM(rp.Item_Qty) AS Returned_Qty
                FROM trans_trading r
                INNER JOIN trans_trading_products rp ON rp.Trading_Id = r.Trading_Id
                WHERE r.Tranding_Type = 4
                  AND IFNULL(r.Status_Cd, 1) = 1
                  AND r.Party_Id = ?
                  AND (
                        IFNULL(TRIM(r.Ref_No), '') = ''
                     OR r.Ref_No = ?
                     OR r.Ref_No = ?
                  )
                GROUP BY rp.Prod_Id, ROUND(rp.Item_Rate, 2)
            ", [$partyId, $invoiceNo, $refNo]);
            foreach ($returnRows as $returnRow) {
                $key = ((int) $returnRow->Prod_Id) . '|' . number_format((float) $returnRow->Item_Rate, 2, '.', '');
                $returnPool[$key] = (float) $returnRow->Returned_Qty;
            }
        }

        foreach ($items as &$item) {
            $prodId    = (int) ($item['Prod_Id'] ?? 0);
            $purchased = (float) ($item['Purchased_Qty'] ?? 0);
            $rateKey   = number_format((float) ($item['Item_Rate'] ?? 0), 2, '.', '');
            $key       = $prodId . '|' . $rateKey;
            $available = $returnPool[$key] ?? 0;
            $returned  = min($purchased, max($available, 0));
            $returnPool[$key] = round($available - $returned, 2);
            $item['Returned_Qty']  = round($returned, 2);
            $item['Remaining_Qty'] = round(max($purchased - $returned, 0), 2);
            $item['qnty']          = $item['Remaining_Qty'];
        }
        unset($item);

        $row->Item_Details = array_values($items);

        return response()->json($row);
    }

    //purchase return
    public function purchaseReturnIndex()
    {
        $orgId    = session('org_id');
        $branchId = session('branch_id');
        Config::set('database.connections.coops.database', session('org_schema'));
        $suppliers  = DB::connection('coops')->select('CALL USP_GET_PARTY_LIST(?, ?, ?)', [1, $branchId, 0]);
        $categories = DB::connection('coops')->select('CALL USP_GET_ITEM_CAT(?)', [$orgId]);
        $banks      = DB::connection('coops')->select('CALL USP_GET_BANK_LEDGER()');
        return view('Admin.purchase-return', compact('suppliers', 'categories', 'banks'))
            ->with('pageTitle', 'Purchase Return ');
    }

    public function purchaseReturnGetSubCats(Request $request)
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        // amazonq-ignore-next-line
        $subs = DB::connection('coops')->select('CALL USP_GET_ITEM_SUB_CAT(?, ?)', [
            session('org_id'),
            $request->input('cat_id', 0)
        ]);
        return response()->json($subs);
    }

    public function purchaseReturnBarcodes(Request $request)
    {
        $partyId = (int) $request->input('party_id', 0);
        $date = (string) $request->input('return_date', '');
        $prodId = (int) $request->input('prod_id', 0);
        $barcode = trim((string) $request->input('barcode', ''));
        if ($partyId <= 0 || $date === '') {
            return response()->json(['error' => 'Select party and return date'], 400);
        }
        if ($date < session('year_start') || $date > session('year_end')) {
            return response()->json(['error' => 'Date must be within the accounting year'], 400);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');

        $rows = $this->purchaseBarcodesForReturn(
            $partyId,
            $date,
            $prodId,
            $barcode,
            (int) $request->input('exclude_id', 0)
        );

        return response()->json($rows);
    }

    public function purchaseReturnGetItems(Request $request)
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

    public function getPurchaseReturnableQty(Request $request)
    {
        $partyId = (int) $request->input('party_id', 0);
        $prodId  = (int) $request->input('prod_id', 0);
        $exclude = (int) $request->input('exclude_id', 0);

        if ($partyId <= 0 || $prodId <= 0) {
            return response()->json(['error' => 'Select party and item first'], 422);
        }

        Config::set('database.connections.coops.database', session('org_schema'));

        try {
            $row = $this->purchaseReturnableQty($partyId, $prodId, $exclude, (string) $request->input('return_date', ''));
        } catch (\Exception $e) {
            Log::channel('trading')->error('Returnable qty lookup failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load returnable quantity'], 500);
        }

        return response()->json($row);
    }

    public function storePurchaseReturn(Request $request)
    {
        $request->validate([
            'purchase_date' => 'required|date',
            'purchase_no'   => 'nullable|string|max:20',
            'party_id'      => 'required|integer',
            'trans_mode'    => 'required|in:1,2,3',
            'items'         => 'required|array|min:1',
            'bank_id'       => 'nullable|integer',
            'instrument_no' => 'nullable|string|max:50',
            'ref_vouch_no'  => 'nullable|string|max:20',
            'disc_percent'  => 'nullable|numeric',
            'round_off'     => 'nullable|numeric',
            'net_amt'       => 'nullable|numeric',
            'items.*.quantity' => 'required|numeric|max:99999999.99|min:0.01',
            'items.*.rate'     => 'required|numeric|max:99999999.99|min:0',
            'items.*.barcode'  => ['required', 'regex:/^[0-9]+$/'],
            'items.*.sale_mrp' => 'nullable|numeric|min:0',
            'vouch_id' => 'nullable|integer',
        ]);
        $yearStart = session('year_start');
        $yearEnd   = session('year_end');

        if ($request->purchase_date < $yearStart || $request->purchase_date > $yearEnd) {
            return response()->json(['error' => "Purchase Date must be between {$yearStart} and {$yearEnd}"], 400);
        }
        if ((int) $request->input('trans_mode') === 2) {
            if ((int) $request->input('bank_id', 0) <= 0) {
                return response()->json(['error' => 'Please select a Bank'], 400);
            }
            if (!trim((string) $request->input('instrument_no', ''))) {
                return response()->json(['error' => 'Instrument No is required for Bank'], 400);
            }
        }
        Config::set('database.connections.coops.database', session('org_schema'));
        // amazonq-ignore-next-line
        $conn = DB::connection('coops');

        try {
            $excludeId = intval($request->input('purchase_id', 0));
            $usedQty   = [];
            $usedBarcode = [];
            foreach ($request->items as $item) {
                $prodId = (int) ($item['item_id'] ?? 0);
                $barcode = trim((string) ($item['barcode'] ?? ''));
                if ($prodId <= 0) {
                    return response()->json(['error' => 'Invalid item in return list'], 400);
                }
                if ($barcode === '' || !ctype_digit($barcode)) {
                    return response()->json(['error' => 'Select a barcode for each return item'], 400);
                }
                $row      = $this->purchaseReturnableQty((int) $request->party_id, $prodId, $excludeId, (string) $request->purchase_date);
                $remaining = (float) ($row->Remaining_Qty ?? 0);
                $already   = $usedQty[$prodId] ?? 0;
                $qty       = (float) $item['quantity'];
                $allowed   = $remaining - $already;
                if ($qty > $allowed + 0.0001) {
                    $name = $item['item_name'] ?? ('Item ' . $prodId);
                    return response()->json([
                        'error' => "{$name}: only " . $this->qtyText(max(0, $allowed)) . " can be returned. That is the quantity still in office. Purchased: {$row->Purchased_Qty}, already returned: {$row->Returned_Qty}.",
                    ], 400);
                }
                $barcodeRows = $this->purchaseBarcodesForReturn(
                    (int) $request->party_id,
                    (string) $request->purchase_date,
                    $prodId,
                    $barcode,
                    $excludeId
                );
                $barcodeLeft = (float) (($barcodeRows[0]->Remaining_Qty ?? 0)) - ($usedBarcode[$barcode] ?? 0);
                if ($barcodeRows === [] || $qty > $barcodeLeft + 0.0001) {
                    $name = $item['item_name'] ?? ('Item ' . $prodId);
                    return response()->json([
                        'error' => "{$name}: only " . $this->qtyText(max(0, $barcodeLeft)) . " of barcode {$barcode} is still in office. Sold, issued, or damaged quantity cannot be returned.",
                    ], 400);
                }
                $usedQty[$prodId] = $already + $qty;
                $usedBarcode[$barcode] = ($usedBarcode[$barcode] ?? 0) + $qty;
            }
        } catch (\Exception $e) {
            Log::channel('trading')->error('Purchase return qty check failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to validate returnable quantity'], 500);
        }

        $conn->beginTransaction();

        try {
            $conn->statement('DROP TEMPORARY TABLE IF EXISTS temppurchase');
            $conn->statement('CREATE TEMPORARY TABLE temppurchase (
                item_id   INT,
                hsn_code  INT,
                qnty      NUMERIC(10,2),
                rate      NUMERIC(10,2),
                unit      SMALLINT,
                tot_amt   NUMERIC(10,2),
                sale_mrp  NUMERIC(10,2),
                disc_perc NUMERIC(5,2),
                disc_amt  NUMERIC(8,2),
                tax_amt   NUMERIC(10,2),
                sgst_perc NUMERIC(5,2),
                sgst_amt  NUMERIC(10,2),
                cgst_perc NUMERIC(5,2),
                cgst_amt  NUMERIC(10,2),
                net_amt   NUMERIC(10,2),
                Pack_Date DATE NULL
            )');

            // Insert each item into temp table
            foreach ($request->items as $item) {
                $conn->insert('INSERT INTO temppurchase VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)', [
                    $item['item_id'],
                    $item['hsn_code'],
                    $item['quantity'],
                    $item['rate'],
                    $item['unit_id'],
                    $item['total_amount'],
                    $item['sale_mrp'],
                    $item['discount_percent'],
                    $item['discount_amount'],
                    $item['taxable_amount'],
                    $item['sgst_rate'],
                    $item['sgst_amount'],
                    $item['cgst_rate'],
                    $item['cgst_amount'],
                    $item['net_amount'],               // net_amt
                    !empty($item['item_purchase_date']) ? $item['item_purchase_date'] : null,

                ]);
            }


            $totAmt     = collect($request->items)->sum(fn($i) => floatval($i['total_amount']));
            $discAmt    = collect($request->items)->sum(fn($i) => floatval($i['discount_amount'] ?? 0));
            $taxableAmt = collect($request->items)->sum(fn($i) => floatval($i['taxable_amount']));
            $totGst     = collect($request->items)->sum(fn($i) => floatval($i['total_gst']));
            $hasItemDiscount = collect($request->items)->contains(fn($i) => floatval($i['discount_percent'] ?? 0) > 0);
            $discPerc = $hasItemDiscount ? 0 : floatval($request->input('disc_percent', 0));
            $discAmt = $hasItemDiscount
                ? collect($request->items)->sum(fn($i) => floatval($i['discount_amount'] ?? 0))
                : floatval($request->input('disc_amt', 0));
            $roundOff   = floatval($request->input('round_off', 0));
            $netAmt = floatval($request->input('net_amt', 0));


            $purchaseId = intval($request->input('purchase_id', 0));
            $mode = $purchaseId > 0 ? 2 : 1;
            $result = $conn->select('CALL USP_ADD_EDIT_PURCHASE_RETURN(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?,?,?,?)', [
                $purchaseId,
                intval($request->input('vouch_id', 0)),
                session('branch_id'),
                $request->input('purchase_no'),
                $request->input('purchase_date'),
                $request->input('party_id'),
                $totAmt,
                $discPerc,
                $discAmt,
                0,
                $taxableAmt,
                $totGst,
                $roundOff,
                $netAmt,
                intval($request->input('trans_mode')),
                intval($request->input('bank_id', 0)),
                session('user_id'),
                session('year_id'),
                $mode,
                (int) $request->input('trans_mode') === 2 ? trim((string) $request->input('instrument_no', '')) : '',
            ]);

            if (!empty($result) && $result[0]->Error_No < 0) {
                $conn->rollBack();
                return response()->json(['error' => $result[0]->Message ?? 'Save failed'], 400);
            }

            $this->assignPurchaseReturnBarcodes($conn, $request->items, (string) $request->input('purchase_date'));

            $conn->commit();
            return response()->json(['message' => $result[0]->Message ?? 'Purchase saved successfully']);
        } catch (\Exception $e) {
            $conn->rollBack();
            Log::channel('trading')->error('Purchase store error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to save purchase'], 500);
        }
    }



    public function searchPurchaseReturn(Request $request)
    {
        Config::set('database.connections.coops.database', session('org_schema'));

        Log::channel('trading')->info('Purchase Return Search Params', [
            'from_date' => $request->input('from_date'),
            'to_date'   => $request->input('to_date'),
            'party_id'  => $request->input('party_id'),
            'branch_id' => session('branch_id'),
            'schema'    => session('org_schema'),
        ]);
        // amazonq-ignore-next-line
        $results = DB::connection('coops')->select('CALL USP_SEARCH_PURCHASE_RETURN(?, ?, ?, ?)', [
            $request->input('from_date'),
            $request->input('to_date'),
            $request->input('party_id'),
            session('branch_id'),
        ]);

        return response()->json($results);
    }

    public function purchaseReturnDetails($id)
    {
        Config::set('database.connections.coops.database', session('org_schema'));

        // amazonq-ignore-next-line
        $result = DB::connection('coops')->select('CALL USP_GET_PURCHASE_DTLS(?)', [$id]);

        if (empty($result)) {
            return response()->json(['error' => 'Record not found'], 404);
        }

        $row = $result[0];
        $row->Item_Details = $this->purchaseLineItems((int) $id);

        return response()->json($row);
    }

    private function purchaseReturnableQty(int $partyId, int $prodId, int $excludeId = 0, string $date = ''): object
    {
        $rows = DB::connection('coops')->select(
            'CALL USP_GET_PURCHASE_RETURNABLE_QTY(?, ?, ?, ?)',
            [$partyId, $prodId, (int) session('branch_id'), $excludeId]
        );

        $row = $rows[0] ?? (object) [
            'Purchased_Qty'  => 0,
            'Returned_Qty'   => 0,
            'Remaining_Qty'  => 0,
        ];

        $purchasedLeft = max(0, (float) ($row->Purchased_Qty ?? 0) - (float) ($row->Returned_Qty ?? 0));
        $office = $date !== '' ? $this->officeQtyForPartyProduct($partyId, $prodId, $date, $excludeId) : $purchasedLeft;
        $row->Office_Qty = round(max(0, $office), 2);
        $row->Remaining_Qty = round(min($purchasedLeft, $row->Office_Qty), 2);

        return $row;
    }

    private function qtyText(float $qty): string
    {
        $qty = round($qty, 2);
        return abs($qty - round($qty)) < 0.001 ? (string) (int) round($qty) : number_format($qty, 2, '.', '');
    }

    /**
     * Office quantity still on barcodes purchased from this supplier.
     * Sold, issued to an agent, damaged, and earlier purchase returns are already taken out.
     */
    private function officeQtyForPartyProduct(int $partyId, int $prodId, string $date, int $excludeId): float
    {
        $signed = $this->officeSignedQtySql();
        $row = DB::connection('coops')->selectOne("
            SELECT IFNULL(SUM(CASE WHEN q.Qty > 0 THEN q.Qty ELSE 0 END), 0) AS Office_Qty
            FROM (
                SELECT IFNULL(SUM({$signed}), 0) AS Qty
                FROM trns_stockinout s
                WHERE s.InOut_Date <= ?
                  AND s.Barcode IN (
                      SELECT DISTINCT p.Barcode
                      FROM trns_stockinout p
                      INNER JOIN trans_trading t
                          ON t.Trading_Id = p.Type_Id
                         AND t.Tranding_Type = 2
                      WHERE p.Stock_Type = 2
                        AND p.Prod_Id = ?
                        AND t.Party_Id = ?
                        AND IFNULL(p.Barcode, 0) <> 0
                        AND p.InOut_Date <= ?
                  )
                GROUP BY s.Barcode
            ) q
        ", [$excludeId, $excludeId, $date, $prodId, $partyId, $date]);

        return (float) ($row->Office_Qty ?? 0);
    }

    private function officeQtyByBarcode(array $barcodes, string $date, int $excludeId): array
    {
        if ($barcodes === []) {
            return [];
        }
        $signed = $this->officeSignedQtySql();
        $placeholders = implode(',', array_fill(0, count($barcodes), '?'));
        $rows = DB::connection('coops')->select("
            SELECT s.Barcode, IFNULL(SUM({$signed}), 0) AS Qty
            FROM trns_stockinout s
            WHERE s.InOut_Date <= ?
              AND s.Barcode IN ({$placeholders})
            GROUP BY s.Barcode
        ", array_merge([$excludeId, $excludeId, $date], $barcodes));

        $qty = [];
        foreach ($rows as $row) {
            $qty[(string) $row->Barcode] = (float) $row->Qty;
        }

        return $qty;
    }

    private function officeSignedQtySql(): string
    {
        return "
            CASE
                WHEN s.Stock_Type IN (1, 2, 6) THEN s.Quantity
                WHEN s.Stock_Type = 8 AND NOT EXISTS (
                    SELECT 1 FROM trans_agent_stock a WHERE a.TrnsStock_Id = s.Stock_Id
                ) THEN s.Quantity
                WHEN s.Stock_Type = 7 AND ? > 0 AND IFNULL(s.Type_Id, 0) = ? THEN 0
                WHEN s.Stock_Type IN (3, 4, 7, 9) THEN -s.Quantity
                ELSE 0
            END
        ";
    }

    /**
     * Barcodes purchased from this supplier that still have quantity to return.
     */
    private function purchaseBarcodesForReturn(int $partyId, string $date, int $prodId, string $barcode, int $excludeId): array
    {
        $sql = "
            SELECT
                s.Barcode,
                CAST(IFNULL(MAX(s.MRP), 0) AS DECIMAL(10,2)) AS MRP,
                p.Prod_Id,
                IFNULL(p.Prod_Code, '') AS Prod_Code,
                p.Prod_ShortNm,
                p.Unit_Id,
                UDF_GET_UNIT_NAME(p.Unit_Id) AS Unit_Name,
                p.Gst_Id,
                IFNULL(c.Is_Fmcg, 0) + 0 AS Is_Fmcg,
                UDF_GET_ITEM_GST_DETAILS(p.Gst_Id) AS GST_Data,
                SUM(s.Quantity) AS Purchased_Qty,
                IFNULL(MAX(ret.Returned_Qty), 0) AS Returned_Qty,
                SUM(s.Quantity) - IFNULL(MAX(ret.Returned_Qty), 0) AS Remaining_Qty
            FROM trns_stockinout s
            INNER JOIN trans_trading t
                ON t.Trading_Id = s.Type_Id
               AND t.Tranding_Type = 2
            INNER JOIN mst_product p ON p.Prod_Id = s.Prod_Id
            LEFT JOIN mst_prod_category c ON c.Prd_CateId = p.Cate_Id
            LEFT JOIN (
                SELECT Barcode, SUM(Quantity) AS Returned_Qty
                FROM trns_stockinout
                WHERE Stock_Type = 7
                  AND IFNULL(Barcode, 0) <> 0
                  AND InOut_Date <= ?
                  AND (? = 0 OR IFNULL(Type_Id, 0) <> ?)
                GROUP BY Barcode
            ) ret ON ret.Barcode = s.Barcode
            WHERE s.Stock_Type = 2
              AND IFNULL(s.Barcode, 0) <> 0
              AND t.Party_Id = ?
              AND s.InOut_Date <= ?
              AND (? = 0 OR s.Prod_Id = ?)
              AND (? = '' OR s.Barcode = ?)
            GROUP BY s.Barcode, p.Prod_Id, p.Prod_Code, p.Prod_ShortNm, p.Unit_Id, p.Gst_Id, c.Is_Fmcg
            HAVING SUM(s.Quantity) - IFNULL(MAX(ret.Returned_Qty), 0) > 0
            ORDER BY MAX(s.Stock_Id) DESC
        ";

        $rows = DB::connection('coops')->select($sql, [
            $date,
            $excludeId,
            $excludeId,
            $partyId,
            $date,
            $prodId,
            $prodId,
            $barcode,
            $barcode,
        ]);

        $office = $this->officeQtyByBarcode(array_map(function ($row) {
            return (string) $row->Barcode;
        }, $rows), $date, $excludeId);

        $kept = [];
        foreach ($rows as $row) {
            $purchasedLeft = max(0, (float) $row->Purchased_Qty - (float) $row->Returned_Qty);
            $officeQty = max(0, $office[(string) $row->Barcode] ?? 0);
            $row->Office_Qty = round($officeQty, 2);
            $row->Remaining_Qty = round(min($purchasedLeft, $officeQty), 2);
            if ($row->Remaining_Qty > 0.0001) {
                $kept[] = $row;
            }
        }

        return $kept;
    }

    /**
     * The return procedure does not store a barcode. Write the barcode the user selected.
     */
    private function assignPurchaseReturnBarcodes($conn, array $items, string $date): void
    {
        foreach ($items as $item) {
            $prodId = (int) ($item['item_id'] ?? 0);
            $barcode = trim((string) ($item['barcode'] ?? ''));
            if ($prodId <= 0 || $barcode === '' || !ctype_digit($barcode) || $date === '') {
                continue;
            }
            $mrp = round((float) ($item['sale_mrp'] ?? 0), 2);
            if ($mrp <= 0) {
                $purchase = $conn->selectOne(
                    'SELECT IFNULL(MAX(MRP), 0) AS MRP FROM trns_stockinout WHERE Barcode = ? AND Stock_Type IN (1, 2)',
                    [$barcode]
                );
                $mrp = round((float) ($purchase->MRP ?? 0), 2);
            }
            $conn->statement("
                UPDATE trns_stockinout
                SET Barcode = ?,
                    MRP = CASE WHEN IFNULL(MRP, 0) = 0 THEN ? ELSE MRP END
                WHERE Stock_Type = 7
                  AND Prod_Id = ?
                  AND IFNULL(Barcode, 0) = 0
                  AND InOut_Date = ?
                ORDER BY Stock_Id ASC
                LIMIT 1
            ", [$barcode, $mrp, $prodId, $date]);
        }
    }
}
