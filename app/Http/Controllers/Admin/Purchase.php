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

    private function getAllSuppliers(int $branchId): array
    {
        $stmt = DB::connection('coops')->getPdo()->prepare('CALL USP_GET_PARTY_LIST(?, ?, ?, ?, ?, ?)');
        try {
            $stmt->execute([1, $branchId, 0, 1, 0, '']);
            $suppliers = $stmt->fetchAll(\PDO::FETCH_OBJ);
            if (!$stmt->nextRowset()) {
                throw new \RuntimeException('USP_GET_PARTY_LIST did not return its total-count result set.');
            }
            if (!$stmt->fetch(\PDO::FETCH_OBJ)) {
                throw new \RuntimeException('USP_GET_PARTY_LIST did not return a total count.');
            }

            return $suppliers;
        } finally {
            $stmt->closeCursor();
        }
    }

    public function index()
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $categories = DB::connection('coops')->select('CALL USP_GET_ITEM_CAT()');
        $banks      = DB::connection('coops')->select('CALL USP_GET_BANK_LEDGER()');
        return view('Admin.good-received', compact('categories', 'banks'))->with('pageTitle', 'Good Received Entry');
    }

    public function getSubCategories($catId)
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $subCategories = DB::connection('coops')->select('CALL USP_GET_ITEM_SUB_CAT(?)', [$catId]);
        return response()->json($subCategories);
    }
    public function getItems(Request $request)
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');
        if ($request->has('page')) {
            return response()->json($this->searchItemsByCodePage(
                (int) $request->input('cat_id', 0),
                (int) $request->input('sub_cat_id', 0),
                (string) ($request->input('code') ?? ''),
                (int) $request->input('page', 1),
                (int) $request->input('page_size', 25)
            ));
        }
        $items = $this->searchItemsByCode(
            (int) $request->input('cat_id', 0),
            (int) $request->input('sub_cat_id', 0),
            (string) ($request->input('code') ?? '')
        );
        return response()->json($items);
    }




    public function store(Request $request)
    {
        $items = $request->input('items', []);
        if (is_array($items)) {
            foreach ($items as $i => $item) {
                if (isset($item['quantity']) && is_numeric($item['quantity'])) {
                    $qty = (float) $item['quantity'];
                    if (abs($qty - round($qty)) < 0.0001) {
                        $items[$i]['quantity'] = (int) round($qty);
                    }
                }
                if (isset($item['item_purchase_date']) && is_string($item['item_purchase_date'])) {
                    $date = $item['item_purchase_date'];
                    foreach (['Y-m-d', 'd/m/Y'] as $format) {
                        $parsedDate = \DateTimeImmutable::createFromFormat('!' . $format, $date);
                        $dateErrors = \DateTimeImmutable::getLastErrors();
                        if (
                            $parsedDate &&
                            ($dateErrors === false || ($dateErrors['warning_count'] === 0 && $dateErrors['error_count'] === 0)) &&
                            $parsedDate->format($format) === $date
                        ) {
                            $items[$i]['item_purchase_date'] = $parsedDate->format('Y-m-d');
                            break;
                        }
                    }
                }
            }
            $request->merge(['items' => $items]);
        }

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
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.rate'     => 'required|numeric|max:99999999.99|min:0',
            'items.*.sale_mrp' => 'required|numeric|min:0.01',
            'items.*.item_purchase_date' => 'nullable|date_format:Y-m-d',
            'vouch_id' => 'nullable|integer',
        ], [
            'items.*.quantity.numeric' => 'Quantity must be a valid number.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'items.*.item_purchase_date.date_format' => 'Pack Date must be a valid date in DD/MM/YYYY format.',
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
            return response()->json(['error' => 'Failed to save purchase'], 500);
        }
    }

    public function getSubCats(Request $request)
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        // amazonq-ignore-next-line
        $subs = DB::connection('coops')->select('CALL USP_GET_ITEM_SUB_CAT(?)', [
            $request->input('cat_id', 0)
        ]);
        return response()->json($subs);
    }


    public function search(Request $request)
    {
        $filters = $this->purchaseSearchFilters($request, false);
        if ($filters instanceof \Illuminate\Http\JsonResponse) {
            return $filters;
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        $limit = min(100, max(10, (int) $request->input('limit', 30)));
        $offset = max(0, (int) $request->input('offset', 0));

        $results = DB::connection('coops')->select('CALL USP_SEARCH_PURCHASE_PAGE(?, ?, ?, ?, ?, ?)', [
            $filters['from_date'],
            $filters['to_date'],
            $filters['party_id'],
            $filters['bill_like'],
            $limit,
            $offset,
        ]);
        $total = (int) ($results[0]->Total_Count ?? count($results));

        return response()->json([
            'results' => $results,
            'recordsTotal' => $total,
            'has_more' => ($offset + count($results)) < $total,
        ]);
    }

    private function purchaseSearchFilters(Request $request, bool $partyRequired): array|\Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'from_date' => 'nullable|date_format:Y-m-d',
            'to_date' => 'nullable|date_format:Y-m-d',
            'party_id' => 'nullable|integer|min:0',
            'bill_no' => 'nullable|string|max:120',
        ]);

        $fromDate = $validated['from_date'] ?? null;
        $toDate = $validated['to_date'] ?? null;
        $partyId = (int) ($validated['party_id'] ?? 0);
        $billNo = trim($validated['bill_no'] ?? '');

        if ($partyRequired && $partyId <= 0) {
            return response()->json(['error' => 'Please select a Party'], 422);
        }
        if ($billNo !== '' && $partyId <= 0) {
            return response()->json(['error' => 'Please select a Party when searching by Bill No or Ref Bill No'], 422);
        }
        if (($fromDate === null) !== ($toDate === null)) {
            return response()->json(['error' => 'Please select both From Date and To Date'], 422);
        }
        if ($billNo === '' && ($fromDate === null || $toDate === null)) {
            return response()->json(['error' => 'Enter Bill No or Ref Bill No, or select From Date and To Date'], 422);
        }

        return [
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'party_id' => $partyId,
            'bill_like' => $billNo === '' ? null : '%' . $billNo . '%',
        ];
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
        $this->decodePurchaseItemDetails($row);
        Log::channel('trading')->info('Purchase Details', [
    'Voucher_Id' => $row->Voucher_Id ?? null,
    'Pur_Id'     => $row->Pur_Id ?? null,
]);


        return response()->json($row);
    }

    private function decodePurchaseItemDetails(object $row): void
    {
        $items = json_decode($row->Item_Details ?? '[]', true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($items)) {
            throw new \UnexpectedValueException('Purchase item details must be a JSON array.');
        }

        $row->Item_Details = array_values($items);
    }

    public function searchForReturn(Request $request)
    {
        $filters = $this->purchaseSearchFilters($request, false);
        if ($filters instanceof \Illuminate\Http\JsonResponse) {
            return $filters;
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        $limit = min(100, max(10, (int) $request->input('limit', 30)));
        $offset = max(0, (int) $request->input('offset', 0));
        $results = DB::connection('coops')->select(
            'CALL USP_SEARCH_PURCHASE_PAGE(?, ?, ?, ?, ?, ?)',
            [
                $filters['from_date'],
                $filters['to_date'],
                $filters['party_id'],
                $filters['bill_like'],
                $limit,
                $offset,
            ]
        );

        foreach ($results as $row) {
            $this->decodePurchaseItemDetails($row);
        }
        $total = (int) ($results[0]->Total_Count ?? count($results));

        return response()->json([
            'results' => $results,
            'recordsTotal' => $total,
            'has_more' => ($offset + count($results)) < $total,
        ]);
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
            $returnRows = DB::connection('coops')->select(
                'CALL USP_GET_PURCHASE_RETURN_POOL(?, ?, ?)',
                [$partyId, $invoiceNo, $refNo]
            );
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
        $branchId = session('branch_id');
        Config::set('database.connections.coops.database', session('org_schema'));
        $suppliers  = $this->getAllSuppliers((int) $branchId);
        $categories = DB::connection('coops')->select('CALL USP_GET_ITEM_CAT()');
        $banks      = DB::connection('coops')->select('CALL USP_GET_BANK_LEDGER()');
        return view('Admin.purchase-return', compact('suppliers', 'categories', 'banks'))
            ->with('pageTitle', 'Purchase Return ');
    }

    public function purchaseReturnGetSubCats(Request $request)
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        // amazonq-ignore-next-line
        $subs = DB::connection('coops')->select('CALL USP_GET_ITEM_SUB_CAT(?)', [
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
        if ($request->has('page')) {
            return response()->json($this->searchItemsByCodePage(
                (int) $request->input('cat_id', 0),
                (int) $request->input('sub_cat_id', 0),
                (string) ($request->input('code') ?? ''),
                (int) $request->input('page', 1),
                (int) $request->input('page_size', 25)
            ));
        }
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
            'disc_amt'      => 'nullable|numeric|min:0|max:99999999.99',
            'round_off'     => 'nullable|numeric',
            'net_amt'       => 'nullable|numeric',
            'items.*.item_id' => 'required|integer|min:1',
            'items.*.quantity' => 'required|numeric|max:99999999.99|min:0.01',
            'items.*.rate'     => 'required|numeric|max:99999999.99|min:0',
            'items.*.barcode'  => 'nullable|string|max:50',
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
                Pack_Date DATE NULL,
                Barcode   VARCHAR(50) NULL
            )');

            // Insert each item into temp table
            foreach ($request->items as $item) {
                $conn->insert('INSERT INTO temppurchase (
                    item_id, hsn_code, qnty, rate, unit, tot_amt, sale_mrp, disc_perc,
                    disc_amt, tax_amt, sgst_perc, sgst_amt, cgst_perc, cgst_amt, net_amt, Pack_Date, Barcode
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
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
                    trim((string) ($item['barcode'] ?? '')) ?: null,
                ]);
            }


            $totAmt     = collect($request->items)->sum(fn($i) => floatval($i['total_amount']));
            $discAmt    = floatval($request->input('disc_amt', 0));
            if ($discAmt > $totAmt) {
                $conn->rollBack();
                return response()->json(['error' => 'Discount amount cannot exceed total amount'], 422);
            }
            $taxableAmt = $totAmt - $discAmt;
            $totGst     = collect($request->items)->sum(fn($i) => floatval($i['total_gst']));
            $discPerc = $discAmt > 0 && $totAmt > 0 ? ($discAmt / $totAmt) * 100 : 0;
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
        $filters = $this->purchaseSearchFilters($request, false);
        if ($filters instanceof \Illuminate\Http\JsonResponse) {
            return $filters;
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        $limit = min(100, max(10, (int) $request->input('limit', 30)));
        $offset = max(0, (int) $request->input('offset', 0));

        $results = DB::connection('coops')->select('CALL USP_SEARCH_PURCHASE_RETURN_PAGE(?, ?, ?, ?, ?, ?)', [
            $filters['from_date'],
            $filters['to_date'],
            $filters['party_id'],
            $filters['bill_like'],
            $limit,
            $offset,
        ]);
        $total = (int) ($results[0]->Total_Count ?? count($results));

        return response()->json([
            'results' => $results,
            'recordsTotal' => $total,
            'has_more' => ($offset + count($results)) < $total,
        ]);
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
        $this->decodePurchaseItemDetails($row);

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

    /**
     * Office quantity still on barcodes purchased from this supplier.
     * Sold, issued to an agent, damaged, and earlier purchase returns are already taken out.
     */
    private function officeQtyForPartyProduct(int $partyId, int $prodId, string $date, int $excludeId): float
    {
        $row = DB::connection('coops')->selectOne(
            'CALL USP_GET_OFFICE_QTY_PARTY_PRODUCT(?, ?, ?, ?)',
            [$excludeId, $date, $prodId, $partyId]
        );

        return (float) ($row->Office_Qty ?? 0);
    }

    private function officeQtyByBarcode(array $barcodes, string $date, int $excludeId): array
    {
        if ($barcodes === []) {
            return [];
        }

        $rows = DB::connection('coops')->select(
            'CALL USP_GET_OFFICE_QTY_BY_BARCODES(?, ?, ?)',
            [$excludeId, $date, implode(',', $barcodes)]
        );

        $qty = [];
        foreach ($rows as $row) {
            $qty[(string) $row->Barcode] = (float) $row->Qty;
        }

        return $qty;
    }

    /**
     * Barcodes purchased from this supplier that still have quantity to return.
     */
    private function purchaseBarcodesForReturn(int $partyId, string $date, int $prodId, string $barcode, int $excludeId): array
    {
        $rows = DB::connection('coops')->select(
            'CALL USP_GET_PURCHASE_RETURN_BARCODES(?, ?, ?, ?, ?)',
            [$date, $excludeId, $partyId, $prodId, $barcode]
        );

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

}
