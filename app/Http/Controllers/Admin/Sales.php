<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class Sales extends Controller
{
    public function indexAgentIndent()
    {
        $branchId = session('branch_id');
        Config::set('database.connections.coops.database', session('org_schema'));
        DB::connection('coops')->reconnect();
        $pdo  = DB::connection('coops')->getPdo();
        $stmt = $pdo->prepare('CALL USP_GET_AGENT_LIST(?, ?, ?, ?)');
        $stmt->execute([$branchId, '', 1, 0]);
        $agents = $stmt->fetchAll(\PDO::FETCH_OBJ);
        $stmt->closeCursor();
        $categories = DB::connection('coops')->select('CALL USP_GET_ITEM_CAT(?)', [session('org_id')]);
        return view('Admin.agent-indent', compact('agents', 'categories'))
            ->with('pageTitle', 'Agent Indent');
    }

    public function getProductInfo(Request $request)
    {
        try {
            $result = $this->resolveProductInfo($request->input('barcode'), $request->input('date'));
            Log::channel('trading')->info('Product Info Result: ', ['data' => $result]);

            if (!empty($result)) {
                return response()->json([
                    'success' => true,
                    'data' => $result[0]
                ]);
            }
            return response()->json([
                'success' => false,
                'message' => 'Invalid Code Entered !!'
            ]);
        } catch (\Exception $e) {
            Log::error('Agent Indent Product Info Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching product details'
            ], 500);
        }
    }

    private function resolveProductInfo($code, $date)
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $code = trim((string) $code);
        $result = [];

        if ($code !== '' && ctype_digit($code)) {
            $result = DB::connection('coops')->select('CALL USP_GET_PROD_INFO(?, ?)', [$code, $date]);
        }

        if (empty($result) && $code !== '') {
            $items = DB::connection('coops')->select('CALL USP_GET_ITEM_LIST(?, ?, ?)', [0, 0, $code]);
            if (!empty($items)) {
                $match = collect($items)->first(function ($item) use ($code) {
                    return strcasecmp((string) $item->Prod_Code, $code) === 0;
                }) ?? $items[0];
                $result = DB::connection('coops')->select('CALL USP_GET_PROD_INFO_BY_ITEM(?, ?)', [
                    $match->Prod_Id,
                    $date
                ]);
            }
        }

        return $result;
    }
    public function getPendingIndents(Request $request)
    {
        try {
            $agentId = $request->input('agent_id');
            Config::set('database.connections.coops.database', session('org_schema'));
            $result = DB::connection('coops')->select('CALL USP_GET_PENDING_INDENT(?)', [$agentId]);
            Log::channel('trading')->info('Pending Indent Result: ' . json_encode($result));

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Pending Indent Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to load pending indents'], 500);
        }
    }


    public function storeAgentIndent(Request $request)
    {
        $request->validate([
            'indent_date'      => 'required|date',
            'agent_id'         => 'required|integer',
            'indent_type'      => 'required|in:1,2',
            'items'            => 'required|array|min:1',
            'items.*.prod_id'  => 'required|integer',
            'items.*.unit_id'  => 'required|integer',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        Config::set('database.connections.coops.database', session('org_schema'));
        $conn = DB::connection('coops');
        $conn->beginTransaction();

        try {
            $conn->statement('DROP TEMPORARY TABLE IF EXISTS tempitem');
            $conn->statement('CREATE TEMPORARY TABLE tempitem (
                    id        INT PRIMARY KEY AUTO_INCREMENT,
                    indent_id INT,
                    item_id   INT,
                    prod_id   INT,
                    unit_id   INT,
                    qnty      DECIMAL(10,2)
                  )');

            $indentId = $request->input('indent_id') ?: 0;

            foreach ($request->items as $item) {
                $conn->insert('INSERT INTO tempitem (indent_id, item_id, prod_id, unit_id, qnty) VALUES (?, ?, ?, ?, ?)', [
                    $indentId,
                    $item['prod_id'],
                    $item['prod_id'],
                    $item['unit_id'],
                    $item['quantity']
                ]);
            }


            $result = $conn->select('CALL USP_ADD_EDIT_AGENT_INDENT(?, ?, ?, ?, ?, ?)', [
                $request->input('agent_id'),
                $request->input('indent_date'),
                session('branch_id'),
                session('year_id'),
                $request->input('indent_type'),
                1 // pMode
            ]);

            if (!empty($result) && $result[0]->Error_No < 0) {
                $conn->rollBack();
                return response()->json(['error' => $result[0]->Message ?? 'Save failed'], 400);
            }

            $conn->commit();
            return response()->json(['message' => $result[0]->Message ?? 'Agent indent saved successfully']);
        } catch (\Exception $e) {
            $conn->rollBack();
            Log::error('Agent Indent Save Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to save agent indent'], 500);
        }
    }

    //agent return
    public function indexAgentReturn()
    {
        $branchId = session('branch_id');
        Config::set('database.connections.coops.database', session('org_schema'));
        DB::connection('coops')->reconnect();
        $pdo  = DB::connection('coops')->getPdo();
        $stmt = $pdo->prepare('CALL USP_GET_AGENT_LIST(?, ?, ?, ?)');
        $stmt->execute([$branchId, '', 1, 0]);
        $agents = $stmt->fetchAll(\PDO::FETCH_OBJ);
        $stmt->closeCursor();
        $categories = DB::connection('coops')->select('CALL USP_GET_ITEM_CAT(?)', [session('org_id')]);
        return view('Admin.agent-return', compact('agents', 'categories'))
            ->with('pageTitle', 'Agent Return');
    }



    public function AgentReturngetProductInfo(Request $request)
    {
        try {
            $result = $this->resolveProductInfo($request->input('barcode'), $request->input('date'));

            if (!empty($result)) {
                return response()->json([
                    'success' => true,
                    'data' => $result[0]
                ]);
            }
            return response()->json([
                'success' => false,
                'message' => 'Invalid Code Entered !!'
            ]);
        } catch (\Exception $e) {
            Log::error('Agent Return Product Info Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching product details'
            ], 500);
        }
    }

    public function storeAgentReturn(Request $request)
    {
        $request->validate([
            'indent_date' => 'required|date',
            'agent_id' => 'required|integer',
            'items' => 'required|array|min:1',
            'items.*.prod_id' => 'required|integer',
            'items.*.unit_id' => 'required|integer',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        Config::set('database.connections.coops.database', session('org_schema'));
        $conn = DB::connection('coops');
        $conn->beginTransaction();

        try {
            $conn->statement('DROP TEMPORARY TABLE IF EXISTS tempitem');
            $conn->statement('CREATE TEMPORARY TABLE tempitem (
                id INT PRIMARY KEY AUTO_INCREMENT,
                indent_id INT,
                item_id INT,
                prod_id INT,
                unit_id INT,
                qnty DECIMAL(10,2)
            )');

            foreach ($request->items as $item) {
                $conn->insert('INSERT INTO tempitem (indent_id, item_id, prod_id, unit_id, qnty) VALUES (?, ?, ?, ?, ?)', [
                    0,
                    $item['prod_id'],
                    $item['prod_id'],
                    $item['unit_id'],
                    $item['quantity']
                ]);
            }

            $result = $conn->select('CALL USP_ADD_EDIT_AGENT_INDENT(?, ?, ?, ?, ?, ?)', [
                $request->input('agent_id'),
                $request->input('indent_date'),
                session('branch_id'),
                session('year_id'),
                2,
                1
            ]);

            if (!empty($result) && $result[0]->Error_No < 0) {
                $conn->rollBack();
                return response()->json(['error' => $result[0]->Message ?? 'Save failed'], 400);
            }

            $conn->commit();
            return response()->json(['message' => $result[0]->Message ?? 'Agent return saved successfully']);
        } catch (\Exception $e) {
            $conn->rollBack();
            Log::error('Agent Return Save Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to save agent return'], 500);
        }
    }


    //counter sale

    public function indexCounterSale()
    {
        $branchId = session('branch_id');
        Config::set('database.connections.coops.database', session('org_schema'));
        $customers  = DB::connection('coops')->select('CALL USP_GET_PARTY_LIST(?, ?)', [2, $branchId]);
        $banks      = DB::connection('coops')->select('CALL USP_GET_BANK_LEDGER()');
        $categories = DB::connection('coops')->select('CALL USP_GET_ITEM_CAT(?)', [session('org_id')]);
        return view('Admin.counter-sale', compact('customers', 'banks', 'categories'))
            ->with('pageTitle', 'Counter Sale');
    }

    public function getSaleSubCats(Request $request)
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $subs = DB::connection('coops')->select('CALL USP_GET_ITEM_SUB_CAT(?, ?)', [
            session('org_id'),
            $request->input('cat_id', 0)
        ]);
        return response()->json($subs);
    }

    public function getSaleItems(Request $request)
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $items = DB::connection('coops')->select('CALL USP_GET_ITEM_LIST(?, ?, ?)', [
            (int) $request->input('cat_id', 0),
            (int) $request->input('sub_cat_id', 0),
            (string) ($request->input('code') ?? '')
        ]);
        return response()->json($items);
    }

    public function getSaleItemByProd(Request $request)
    {
        try {
            $prodId = (int) $request->input('prod_id', 0);
            $date = $request->input('sale_date');
            if ($prodId <= 0 || !$date) {
                return response()->json(['error' => 'Product and sale date are required'], 400);
            }
            Config::set('database.connections.coops.database', session('org_schema'));
            $result = DB::connection('coops')->select('CALL USP_GET_PROD_INFO_BY_ITEM(?, ?)', [$prodId, $date]);
            if (!empty($result)) {
                return response()->json($result[0]);
            }
            return response()->json(['error' => 'Item not found'], 404);
        } catch (\Exception $e) {
            Log::error('Counter Sale Item Info Error: ' . $e->getMessage());
            return response()->json(['error' => 'Error fetching product details'], 500);
        }
    }

    public function getItemByBarcode(Request $request)
    {
        try {
            $barcode = $request->input('barcode');
            $date = $request->input('sale_date');

            Config::set('database.connections.coops.database', session('org_schema'));

            $result = DB::connection('coops')->select('CALL USP_GET_PROD_INFO(?, ?)', [$barcode, $date]);
            if (!empty($result)) {
                return response()->json($result[0]);
            } else {
                return response()->json(['error' => 'Invalid Code Entered !!'], 404);
            }
        } catch (\Exception $e) {
            Log::error('Counter Sale Barcode Error: ' . $e->getMessage());
            return response()->json(['error' => 'Error fetching product details'], 500);
        }
    }

    public function countersalesearch(Request $request)
    {
        try {
            $fromDate = $request->input('from_date');
            $toDate = $request->input('to_date');

            if (!$fromDate || !$toDate) {
                return response()->json(['error' => 'From Date and To Date are required'], 400);
            }

            Config::set('database.connections.coops.database', session('org_schema'));

            $sales = DB::connection('coops')->select('CALL USP_SEARCH_SALE(?, ?)', [
                $fromDate,
                $toDate
            ]);

            return response()->json($sales);
        } catch (\Exception $e) {
            Log::error('Counter Sale Search Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to search sales'], 500);
        }
    }


    public function storeCounterSale(Request $request)
    {
        $request->validate([
            'sale_date'    => 'required|date',
            'party_id'     => 'required|integer',
            'trans_mode'   => 'required|in:1,2,3',
            'items'        => 'required|array|min:1',
            'bank_id'      => 'nullable|integer',
            'bank_remarks' => 'nullable|string|max:100',
            'ref_vouch_no' => 'nullable|string|max:20',
            'disc_percent' => 'nullable|numeric',
            'round_off'    => 'nullable|numeric',
            'net_amt'      => 'nullable|numeric',
            'items.*.quantity' => 'required|numeric|max:99999999.99|min:0.01',
            'items.*.rate'     => 'required|numeric|max:99999999.99|min:0',
            'vouch_id' => 'nullable|integer',
        ]);

        $yearStart = session('year_start');
        $yearEnd   = session('year_end');

        if ($request->sale_date < $yearStart || $request->sale_date > $yearEnd) {
            return response()->json(['error' => "Sale Date must be between {$yearStart} and {$yearEnd}"], 400);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        $conn = DB::connection('coops');
        $conn->beginTransaction();

        try {
            $conn->statement('DROP TEMPORARY TABLE IF EXISTS temppurchase');
            $conn->statement('CREATE TEMPORARY TABLE temppurchase (
            item_id   INT,
            hsn_code  VARCHAR(20),
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
                $conn->insert('INSERT INTO temppurchase VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
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
                    !empty($item['item_sale_date']) ? $item['item_sale_date'] : null,
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

            $saleId = intval($request->input('sale_id', 0));
            $mode = $saleId > 0 ? 2 : 1;

            $result = $conn->select('CALL USP_ADD_EDIT_SALE(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $saleId,
                intval($request->input('vouch_id', 0)),
                session('branch_id'),
                $request->input('sale_date'),
                $request->input('sale_no', null),
                $request->input('party_id'),
                $totAmt,
                $discAmt,
                $taxableAmt,
                $totGst,
                $roundOff,
                $netAmt,
                intval($request->input('trans_mode')),
                intval($request->input('bank_id', 0)),
                session('user_id'),
                session('year_id'),
                $mode,
            ]);

            if (!empty($result) && $result[0]->Error_No < 0) {
                $conn->rollBack();
                return response()->json(['error' => $result[0]->Message ?? 'Save failed'], 400);
            }

            $conn->commit();

            // Check if bill data is available
            $billData = null;
            if (!empty($result[0]->Bill_Data)) {
                $billData = json_decode($result[0]->Bill_Data, true);
            }

            return response()->json([
                'success' => true,
                'message' => $result[0]->Message ?? 'Sale saved successfully',
                'bill_data' => $billData,
                'show_bill' => true
            ]);
        } catch (\Exception $e) {
            $conn->rollBack();
            Log::channel('trading')->error('Counter sale error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to save sale'], 500);
        }
    }
    public function details($id)
    {
        try {
            Config::set('database.connections.coops.database', session('org_schema'));

            $result = DB::connection('coops')->select('CALL USP_GET_SALE_DTLS(?)', [$id]);

            if (empty($result)) {
                return response()->json(['error' => 'Sale not found'], 404);
            }

            $row = $result[0];
            $row->Item_Details = json_decode($row->Item_Details, true);

            return response()->json($row);
        } catch (\Exception $e) {
            Log::error('Counter Sale Details Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load sale details'], 500);
        }
    }


    //sale return
    public function indexSaleReturn()
    {
        $branchId = session('branch_id');
        Config::set('database.connections.coops.database', session('org_schema'));
        $customers  = DB::connection('coops')->select('CALL USP_GET_PARTY_LIST(?, ?)', [2, $branchId]);
        $banks      = DB::connection('coops')->select('CALL USP_GET_BANK_LEDGER()');
        $categories = DB::connection('coops')->select('CALL USP_GET_ITEM_CAT(?)', [session('org_id')]);
        return view('Admin.sale-return', compact('customers', 'banks', 'categories'))
            ->with('pageTitle', ' Counter Sale Return');
    }

    public function getItemByBarcodeReturn(Request $request)
    {
        try {
            $barcode = $request->input('barcode');
            $date = $request->input('sale_date');

            Config::set('database.connections.coops.database', session('org_schema'));

            $result = DB::connection('coops')->select('CALL USP_GET_PROD_INFO(?, ?)', [$barcode, $date]);
            if (!empty($result)) {
                return response()->json($result[0]);
            } else {
                return response()->json(['error' => 'Invalid Code Entered !!'], 404);
            }
        } catch (\Exception $e) {
            Log::error('Counter Sale Barcode Error: ' . $e->getMessage());
            return response()->json(['error' => 'Error fetching product details'], 500);
        }
    }

    public function storeSaleReturn(Request $request)
    {
        $request->validate([
            'sale_date'    => 'required|date',
            'party_id'     => 'required|integer',
            'trans_mode'   => 'required|in:1,2,3',
            'items'        => 'required|array|min:1',
            'bank_id'      => 'nullable|integer',
            'bank_remarks' => 'nullable|string|max:100',
            'ref_vouch_no' => 'nullable|string|max:20',
            'disc_percent' => 'nullable|numeric',
            'round_off'    => 'nullable|numeric',
            'net_amt'      => 'nullable|numeric',
            'items.*.quantity' => 'required|numeric|max:99999999.99|min:0.01',
            'items.*.rate'     => 'required|numeric|max:99999999.99|min:0',
        ]);

        $yearStart = session('year_start');
        $yearEnd   = session('year_end');

        if ($request->sale_date < $yearStart || $request->sale_date > $yearEnd) {
            return response()->json(['error' => "Sale Date must be between {$yearStart} and {$yearEnd}"], 400);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        $conn = DB::connection('coops');
        $conn->beginTransaction();

        try {
            $conn->statement('DROP TEMPORARY TABLE IF EXISTS temppurchase');
            $conn->statement('CREATE TEMPORARY TABLE temppurchase (
            item_id   INT,
            hsn_code  VARCHAR(20),
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
                $conn->insert('INSERT INTO temppurchase VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
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
                    !empty($item['item_sale_date']) ? $item['item_sale_date'] : null,
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

            $saleId = intval($request->input('sale_id', 0));
            $mode = $saleId > 0 ? 2 : 1;

            $result = $conn->select('CALL USP_ADD_EDIT_SALE_RETURN(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $saleId,
                session('branch_id'),
                $request->input('sale_date'),
                $request->input('party_id'),
                $totAmt,
                $discAmt,
                $taxableAmt,
                $totGst,
                $roundOff,
                $netAmt,
                session('user_id'),
                session('year_id'),
                $mode,
                $request->input('sale_no'),
            ]);

            if (!empty($result) && $result[0]->Error_No < 0) {
                $conn->rollBack();
                return response()->json(['error' => $result[0]->Message ?? 'Save failed'], 400);
            }

            $conn->commit();

            return response()->json([
                'success' => true,
                'message' => $result[0]->Message ?? 'Sale return saved successfully'
            ]);
        } catch (\Exception $e) {
            $conn->rollBack();
            Log::channel('trading')->error('Sale return error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to save sale return'], 500);
        }
    }


    public function saleReturnsearch(Request $request)
    {
        try {
            $fromDate = $request->input('from_date');
            $toDate = $request->input('to_date');

            if (!$fromDate || !$toDate) {
                return response()->json(['error' => 'From Date and To Date are required'], 400);
            }

            Config::set('database.connections.coops.database', session('org_schema'));

            $saleReturns = DB::connection('coops')->select('CALL USP_SEARCH_SALE_RETURN(?, ?)', [
                $fromDate,
                $toDate
            ]);

            return response()->json($saleReturns);
        } catch (\Exception $e) {
            Log::error('Sale Return Search Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to search sale returns'], 500);
        }
    }

    public function saleReturnDetails($id)
    {
        try {
            Config::set('database.connections.coops.database', session('org_schema'));

            $result = DB::connection('coops')->select('CALL USP_GET_SALE_DTLS(?)', [$id]);

            if (empty($result)) {
                return response()->json(['error' => 'Sale return not found'], 404);
            }

            $row = $result[0];
            $row->Item_Details = json_decode($row->Item_Details, true);

            return response()->json($row);
        } catch (\Exception $e) {
            Log::error('Sale Return Details Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load sale return details'], 500);
        }
    }

    public function searchForReturn(Request $request)
    {
        Config::set('database.connections.coops.database', session('org_schema'));

        $results = DB::connection('coops')->select('CALL USP_SEARCH_SALE_FOR_RETURN(?, ?, ?, ?)', [
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

        $result = DB::connection('coops')->select('CALL USP_GET_SALE_RETURNABLE_DTLS(?)', [$id]);

        if (empty($result)) {
            return response()->json(['error' => 'Record not found'], 404);
        }

        $row = $result[0];
        $row->Item_Details = json_decode($row->Item_Details, true);

        return response()->json($row);
    }
}
