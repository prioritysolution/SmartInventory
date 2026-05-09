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
        $agents = DB::connection('coops')->select('CALL USP_GET_AGENT_LIST(?)', [$branchId]);
        return view('Admin.agent-indent', compact('agents'));
    }

    public function getProductInfo(Request $request)
    {
        try {
            $barcode = $request->input('barcode');
            $date = $request->input('date');

            Config::set('database.connections.coops.database', session('org_schema'));

            $result = DB::connection('coops')->select('CALL USP_GET_PROD_INFO(?, ?)', [$barcode, $date]);

            if (!empty($result)) {
                return response()->json([
                    'success' => true,
                    'data' => $result[0]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Code Entered !!'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Agent Indent Product Info Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching product details'
            ], 500);
        }
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
        $agents = DB::connection('coops')->select('CALL USP_GET_AGENT_LIST(?)', [$branchId]);
        return view('Admin.agent-return', compact('agents'));
    }

    public function AgentReturngetProductInfo(Request $request)
    {
        try {
            $barcode = $request->input('barcode');
            $date = $request->input('date');

            Config::set('database.connections.coops.database', session('org_schema'));

            $result = DB::connection('coops')->select('CALL USP_GET_PROD_INFO(?, ?)', [$barcode, $date]);

            if (!empty($result)) {
                return response()->json([
                    'success' => true,
                    'data' => $result[0]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Code Entered !!'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Agent Indent Product Info Error: ' . $e->getMessage());
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
            // Create temporary table
            $conn->statement('DROP TEMPORARY TABLE IF EXISTS tempitem');
            $conn->statement('CREATE TEMPORARY TABLE tempitem (
                id INT PRIMARY KEY AUTO_INCREMENT,
                prod_id INT,
                unit_id INT,
                qnty SMALLINT
            )');

            // Insert items into temporary table
            foreach ($request->items as $item) {
                $conn->insert('INSERT INTO tempitem (prod_id, unit_id, qnty) VALUES (?, ?, ?)', [
                    $item['prod_id'],
                    $item['unit_id'],
                    $item['quantity']
                ]);
            }

            // Call stored procedure to save agent indent
            $result = $conn->select('CALL USP_ADD_EDIT_AGENT_INDENT(?, ?, ?, ?)', [
                $request->input('agent_id'),
                $request->input('indent_date'),
                session('branch_id'),
                1 // Mode 1 for insert
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


    //counter sale

    public function indexCounterSale()
    {
        $branchId = session('branch_id');
        Config::set('database.connections.coops.database', session('org_schema'));
        $customers  = DB::connection('coops')->select('CALL USP_GET_PARTY_LIST(?, ?)', [2, $branchId]);
        return view('Admin.counter-sale', compact('customers'))
            ->with('pageTitle', 'Counter Sale');
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
                    $item['hsn_code'],   // hsn_code
                    $item['quantity'],                 // qnty
                    $item['rate'],                     // rate
                    $item['unit_id'],                  // unit
                    $item['total_amount'],             // tot_amt
                    $item['sale_mrp'],    // sale_mrp
                    $item['discount_percent'],   // disc_perc
                    $item['discount_amount'],   // disc_amt
                    $item['taxable_amount'],           // tax_amt
                    $item['sgst_rate'],   // sgst_perc
                    $item['sgst_amount'],   // sgst_amt
                    $item['cgst_rate'],   // cgst_perc
                    $item['cgst_amount'],   // cgst_amt
                    $item['net_amount'],               // net_amt
                    !empty($item['item_sale_date']) ? $item['item_sale_date'] : null, // Pack_Date
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

            $result = $conn->select('CALL USP_ADD_EDIT_SALE(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $saleId,                                     // pSale_Id (0 = new)
                session('branch_id'),                        // pBranch_Id
                $request->input('sale_date'),                // pSale_Date
                $request->input('sale_no', null),            // pSale_No (manual sale number or null)
                $request->input('party_id'),                 // pParty_Id
                $totAmt,                                     // pTot_Amt
                $discAmt,                                    // pDisc_Amt
                $taxableAmt,                                 // pTaxble_Amt
                $totGst,                                     // pTot_Gst
                $roundOff,                                   // pRound_Amt
                $netAmt,                                     // pNet_Amt
                session('user_id'),                          // pUser_Id
                session('year_id'),                          // pFin_Id
                $mode,                                       // pMode (1=Insert, 2=Update)
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
        return view('Admin.sale-return', compact('customers'))
            ->with('pageTitle', ' Sale Return');
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

            // Call USP_ADD_EDIT_SALE_RETURN with the exact parameters it expects (13 parameters)
            $result = $conn->select('CALL USP_ADD_EDIT_SALE_RETURN(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $saleId,                                     // pSale_Id (0 = new)
                session('branch_id'),                        // pBranch_Id
                $request->input('sale_date'),                // pSale_Date
                $request->input('party_id'),                 // pParty_Id
                $totAmt,                                     // pTot_Amt
                $discAmt,                                    // pDisc_Amt
                $taxableAmt,                                 // pTaxble_Amt
                $totGst,                                     // pTot_Gst
                $roundOff,                                   // pRound_Amt
                $netAmt,                                     // pNet_Amt
                session('user_id'),                          // pUser_Id
                session('year_id'),                          // pFin_Id
                $mode,                                       // pMode (1=Insert, 2=Update)
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
}
