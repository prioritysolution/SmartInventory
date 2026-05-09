<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class AgentCustomer extends Controller
{
    public function index()
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');

        $customers = DB::connection('coops')->select('CALL USP_GET_PARTY_LIST(?, ?)', [2, session('branch_id')]);
        return view('Agent.agent-custmer', [
            'customers'  => $customers,
            'year_start' => session('year_start'),
            'year_end'   => session('year_end'),
        ]);
    }

    public function getItemByBarcode(Request $request)
    {
        try {
            Config::set('database.connections.coops.database', session('org_schema'));
            DB::purge('coops');

            $result = DB::connection('coops')->select('CALL USP_GET_PROD_INFO_AGENT(?, ?, ?)', [
                $request->input('barcode'),
                session('agent_id'),
                $request->input('sale_date'),
            ]);
            Log::channel('trading')->info('product info:', ['result' => $result]);

            if (!empty($result)) {
                return response()->json($result[0]);
            }
            return response()->json(['error' => 'Invalid Code Entered !!'], 404);
        } catch (\Exception $e) {
            Log::error('Agent Sale Barcode Error: ' . $e->getMessage());
            return response()->json(['error' => 'Error fetching product details'], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'sale_date'  => 'required|date',
            'party_id'   => 'required|integer',
            'trans_mode' => 'required|in:1,2,3',
            'items'      => 'required|array|min:1',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.rate'     => 'required|numeric|min:0',
        ]);

        $yearStart = session('year_start');
        $yearEnd   = session('year_end');
        if ($request->sale_date < $yearStart || $request->sale_date > $yearEnd) {
            return response()->json(['error' => "Sale Date must be between {$yearStart} and {$yearEnd}"], 400);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');

        $conn = DB::connection('coops');
        $conn->beginTransaction();

        try {
            $conn->statement('DROP TEMPORARY TABLE IF EXISTS temppurchase');
            $conn->statement('CREATE TEMPORARY TABLE temppurchase (
                Id        INT PRIMARY KEY AUTO_INCREMENT,
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

            foreach ($request->items as $item) {
                $conn->insert('INSERT INTO temppurchase
                    (item_id,hsn_code,qnty,rate,unit,tot_amt,sale_mrp,disc_perc,disc_amt,tax_amt,sgst_perc,sgst_amt,cgst_perc,cgst_amt,net_amt,Pack_Date)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', [
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
                    $item['sgst_rate'] ,
                    $item['sgst_amount'] ,
                    $item['cgst_rate'] ,
                    $item['cgst_amount'] ,
                    $item['net_amount'],
                    !empty($item['item_sale_date']) ? $item['item_sale_date'] : null,
                ]);
            }

            $items       = collect($request->items);
            $totAmt      = $items->sum(fn($i) => floatval($i['total_amount']));
            $taxableAmt  = $items->sum(fn($i) => floatval($i['taxable_amount']));
            $totGst      = $items->sum(fn($i) => floatval($i['total_gst']));
            $hasItemDisc = $items->contains(fn($i) => floatval($i['discount_percent'] ?? 0) > 0);
            $discAmt     = $hasItemDisc
                ? $items->sum(fn($i) => floatval($i['discount_amount'] ?? 0))
                : floatval($request->input('disc_amt', 0));
            $roundOff    = floatval($request->input('round_off', 0));
            $netAmt      = floatval($request->input('net_amt', 0));
            $saleId      = intval($request->input('sale_id', 0));

            $result = $conn->select('CALL USP_ADD_EDIT_SALE_RETURN(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
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
                session('agent_id'),
                session('year_id'),
                $saleId > 0 ? 2 : 1,
            ]);

            if (!empty($result) && $result[0]->Error_No < 0) {
                $conn->rollBack();
                return response()->json(['error' => $result[0]->Message ?? 'Save failed'], 400);
            }

            $conn->commit();

            $billData = null;
            if (!empty($result[0]->Bill_Data)) {
                $billData = json_decode($result[0]->Bill_Data, true);
            }

            return response()->json([
                'success'   => true,
                'message'   => $result[0]->Message ?? 'Sale saved successfully',
                'bill_data' => $billData,
                'show_bill' => !empty($billData),
            ]);

        } catch (\Exception $e) {
            $conn->rollBack();
            Log::error('Agent Sale Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to save sale'], 500);
        }
    }
}
