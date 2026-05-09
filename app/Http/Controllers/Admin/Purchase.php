<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class Purchase extends Controller
{
    public function index()
    {
        $orgId    = session('org_id');
        $branchId = session('branch_id');
        Config::set('database.connections.coops.database', session('org_schema'));
        $suppliers  = DB::connection('coops')->select('CALL USP_GET_PARTY_LIST(?, ?)', [1, $branchId]);
        $categories = DB::select('CALL USP_GET_ITEM_CAT(?)', [$orgId]);
        return view('Admin.good-received', compact('suppliers', 'categories'))
            ->with('pageTitle', 'Good Received Entry');
    }

    public function getSubCategories($catId)
    {
        $orgId = session('org_id');
        $subCategories = DB::select('CALL USP_GET_ITEM_SUB_CAT(?, ?)', [$orgId, $catId]);
        return response()->json($subCategories);
    }

    public function getItems($catId, $subCatId)
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $items = DB::connection('coops')->select('CALL USP_GET_ITEM_LIST(?, ?)', [$catId, $subCatId]);
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
            'bank_remarks'  => 'nullable|string|max:100',
            'ref_vouch_no'  => 'nullable|string|max:20',
            'disc_percent'  => 'nullable|numeric',
            'round_off'     => 'nullable|numeric',
            'net_amt'       => 'nullable|numeric',
            'items.*.quantity' => 'required|numeric|max:99999999.99|min:0.01',
            'items.*.rate'     => 'required|numeric|max:99999999.99|min:0',
        ]);
        $yearStart = session('year_start');
        $yearEnd   = session('year_end');

        if ($request->purchase_date < $yearStart || $request->purchase_date > $yearEnd) {
            return response()->json(['error' => "Purchase Date must be between {$yearStart} and {$yearEnd}"], 400);
        }
        Config::set('database.connections.coops.database', session('org_schema'));
        // amazonq-ignore-next-line
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
                $conn->insert('INSERT INTO temppurchase VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)', [
                    $item['item_id'],
                    $item['hsn_code'] ,   
                    $item['quantity'],                
                    $item['rate'],                    
                    $item['unit_id'],                  
                    $item['total_amount'],             
                    $item['sale_mrp']  ,    
                    $item['discount_percent'] ,   
                    $item['discount_amount'] ,  
                    $item['taxable_amount'],           
                    $item['sgst_rate']   ,   
                    $item['sgst_amount']  ,  
                    $item['cgst_rate']  ,   
                    $item['cgst_amount'] ,   
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


            $purchaseId = intval($request->input('purchase_id', 0));
            $mode = $purchaseId > 0 ? 2 : 1;
            $result = $conn->select('CALL USP_ADD_EDIT_PURCHASE(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)', [
                $purchaseId,                                   
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
                session('user_id'),                  
                session('year_id'),
                $mode,                                
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
        $row->Item_Details = json_decode($row->Item_Details, true);

        return response()->json($row);
    }



    //purchase return
      public function purchaseReturnIndex()
    {
        $orgId    = session('org_id');
        $branchId = session('branch_id');
        Config::set('database.connections.coops.database', session('org_schema'));
        $suppliers  = DB::connection('coops')->select('CALL USP_GET_PARTY_LIST(?, ?)', [1, $branchId]);
        $categories = DB::select('CALL USP_GET_ITEM_CAT(?)', [$orgId]);
        return view('Admin.purchase-return', compact('suppliers', 'categories'))
            ->with('pageTitle', 'Purchase Return ');
    }

    public function purchasegetSubCategories($catId)
    {
        $orgId = session('org_id');
        $subCategories = DB::select('CALL USP_GET_ITEM_SUB_CAT(?, ?)', [$orgId, $catId]);
        return response()->json($subCategories);
    }

    public function purchasegetItems($catId, $subCatId)
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $items = DB::connection('coops')->select('CALL USP_GET_ITEM_LIST(?, ?)', [$catId, $subCatId]);
        return response()->json($items);
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
            'bank_remarks'  => 'nullable|string|max:100',
            'ref_vouch_no'  => 'nullable|string|max:20',
            'disc_percent'  => 'nullable|numeric',
            'round_off'     => 'nullable|numeric',
            'net_amt'       => 'nullable|numeric',
            'items.*.quantity' => 'required|numeric|max:99999999.99|min:0.01',
            'items.*.rate'     => 'required|numeric|max:99999999.99|min:0',
        ]);
        $yearStart = session('year_start');
        $yearEnd   = session('year_end');

        if ($request->purchase_date < $yearStart || $request->purchase_date > $yearEnd) {
            return response()->json(['error' => "Purchase Date must be between {$yearStart} and {$yearEnd}"], 400);
        }
        Config::set('database.connections.coops.database', session('org_schema'));
        // amazonq-ignore-next-line
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
                $conn->insert('INSERT INTO temppurchase VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)', [
                    $item['item_id'],
                    $item['hsn_code'],        
                    $item['quantity'],            
                    $item['rate'],                     
                    $item['unit_id'],               
                    $item['total_amount'],             
                    $item['sale_mrp'] ,      
                    $item['discount_percent'],
                    $item['discount_amount'] ,   
                    $item['taxable_amount'],          
                    $item['sgst_rate']    ,   
                    $item['sgst_amount']   ,  
                    $item['cgst_rate']    ,   
                    $item['cgst_amount']   ,  
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
            $result = $conn->select('CALL USP_ADD_EDIT_PURCHASE_RETURN(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)', [
                $purchaseId,                                  
                session('branch_id'),                 // pBranch_Id
                $request->input('purchase_no'),       // pPur_No
                $request->input('purchase_date'),     // pPur_Date
                $request->input('party_id'),          // pParty_Id
                $totAmt,                              // pTot_Amt
                $discPerc,                            // pDisc_Perc
                $discAmt,                             // pDisc_Amt
                0,                                    // pFreg_Amt (freight)
                $taxableAmt,                          // pTaxble_Amt
                $totGst,                              // pTot_Gst
                $roundOff,                            // pRound_Amt
                $netAmt,                              // pNet_Amt
                session('user_id'),                   // pUser_Id
                session('year_id'),
                $mode,                                 // pMode (1=Insert)
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
        Config::set('database.connections.coops.database', session('org_schema'));

     Log::channel('trading')->info('Purchase Return Search Params', [
        'from_date' => $request->input('from_date'),
        'to_date'   => $request->input('to_date'),
        'party_id'  => $request->input('party_id'),
        'branch_id' => session('branch_id'),
        'schema'    => session('org_schema'),
    ]);
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
        $row->Item_Details = json_decode($row->Item_Details, true);

        return response()->json($row);
    }
}
