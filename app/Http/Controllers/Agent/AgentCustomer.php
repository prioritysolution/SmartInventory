<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Concerns\SearchesItemsByCode;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class AgentCustomer extends Controller
{
    use SearchesItemsByCode;

    public function index()
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');

        $customers = DB::connection('coops')->select('CALL USP_GET_PARTY_LIST(?, ?, ?)', [
            2,
            session('branch_id'),
            session('agent_id'),
        ]);
        $categories = DB::connection('coops')->select('CALL USP_GET_ITEM_CAT(?)', [session('org_id')]);
        return view('Agent.agent-custmer', [
            'customers'  => $customers,
            'categories' => $categories,
            'year_start' => session('year_start'),
            'year_end'   => session('year_end'),
        ]);
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

    private function saleReturnableQty(int $partyId, int $prodId, int $excludeId = 0): object
    {
        $pdo = DB::connection('coops')->getPdo();
        $stmt = $pdo->prepare('CALL USP_GET_AGENT_SALE_RETURNABLE_QTY(?, ?, ?, ?)');
        $stmt->execute([
            session('agent_id'),
            $partyId,
            $prodId,
            $excludeId,
        ]);
        $row = $stmt->fetch(\PDO::FETCH_OBJ);
        $stmt->closeCursor();
        return $row ?: (object) [
            'Sold_Qty' => 0,
            'Returned_Qty' => 0,
            'Remaining_Qty' => 0,
        ];
    }

    public function getReturnableQty(Request $request)
    {
        $partyId = (int) $request->input('party_id', 0);
        $prodId  = (int) $request->input('prod_id', 0);
        $exclude = (int) $request->input('exclude_id', 0);
        if ($partyId <= 0 || $prodId <= 0) {
            return response()->json(['error' => 'Select customer and item first'], 422);
        }
        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');
        try {
            return response()->json($this->saleReturnableQty($partyId, $prodId, $exclude));
        } catch (\Exception $e) {
            Log::error('Agent customer returnable qty lookup failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load returnable quantity'], 500);
        }
    }

    public function getItemByBarcode(Request $request)
    {
        try {
            Config::set('database.connections.coops.database', session('org_schema'));
            DB::purge('coops');

            $date = $this->toIsoDate($request->input('sale_date'));
            $code = trim((string) $request->input('barcode'));
            $result = $this->prodInfoByAgentBarcode($code, (int) session('agent_id'), (string) $date);
            if (empty($result)) {
                $result = $this->resolveUniqueProductInfo($code, (string) $date);
            }
            if (!empty($result)) {
                $item = $result[0];
                return response()->json($this->attachSaleRates($item, (int) $item->Prod_Id));
            }
            return response()->json(['error' => 'Invalid Code Entered !!'], 404);
        } catch (\Exception $e) {
            Log::error('Agent Customer Return Barcode Error: ' . $e->getMessage());
            return response()->json(['error' => 'Error fetching product details'], 500);
        }
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
        $items = $this->searchItemsByCode(
            (int) $request->input('cat_id', 0),
            (int) $request->input('sub_cat_id', 0),
            (string) ($request->input('code') ?? '')
        );
        return response()->json($items);
    }

    public function getItemByProd(Request $request)
    {
        try {
            $prodId = (int) $request->input('prod_id', 0);
            $date = $this->toIsoDate($request->input('sale_date'));
            if ($prodId <= 0 || !$date) {
                return response()->json(['error' => 'Product and date are required'], 400);
            }
            Config::set('database.connections.coops.database', session('org_schema'));
            DB::purge('coops');
            $result = DB::connection('coops')->select('CALL USP_GET_PROD_INFO_BY_ITEM(?, ?)', [
                $prodId,
                $date,
            ]);
            if (!empty($result)) {
                $item = $result[0];
                return response()->json($this->attachSaleRates($item, $prodId));
            }
            return response()->json(['error' => 'Item not found'], 404);
        } catch (\Exception $e) {
            Log::error('Agent Customer Return Item Info Error: ' . $e->getMessage());
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

        $party = DB::connection('coops')->selectOne(
            'SELECT Party_Id FROM mst_party WHERE Party_Id = ? AND Party_Type = 2 AND Cust_Agent_Id = ? LIMIT 1',
            [$request->party_id, session('agent_id')]
        );
        if (!$party) {
            return response()->json(['error' => 'Selected customer is not assigned to you'], 400);
        }

        try {
            $excludeId = intval($request->input('sale_id', 0));
            $usedQty   = [];
            foreach ($request->items as $item) {
                $prodId = (int) ($item['item_id'] ?? 0);
                if ($prodId <= 0) {
                    return response()->json(['error' => 'Invalid item in return list'], 400);
                }
                $row       = $this->saleReturnableQty((int) $request->party_id, $prodId, $excludeId);
                $remaining = (float) ($row->Remaining_Qty ?? 0);
                $already   = $usedQty[$prodId] ?? 0;
                $qty       = (float) $item['quantity'];
                $allowed   = $remaining - $already;
                if ($qty > $allowed + 0.0001) {
                    $name = $item['item_name'] ?? ('Item ' . $prodId);
                    return response()->json([
                        'error' => "{$name}: return qty {$qty} exceeds remaining sold qty ({$allowed}). Sold: {$row->Sold_Qty}, already returned: {$row->Returned_Qty}.",
                    ], 400);
                }
                $usedQty[$prodId] = $already + $qty;
            }
        } catch (\Exception $e) {
            Log::error('Agent customer return qty check failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to validate returnable quantity'], 500);
        }

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

            $result = $conn->select('CALL USP_ADD_EDIT_AGENT_SALE_RET(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $saleId,
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
            Log::error('Agent customer return error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to save customer return'], 500);
        }
    }

    public function getSaleRates(Request $request)
    {
        return response()->json($this->fetchSaleRates((int) $request->input('prod_id', 0)));
    }

    private function fetchSaleRates(int $prodId): array
    {
        if ($prodId <= 0) {
            return [];
        }
        try {
            Config::set('database.connections.coops.database', session('org_schema'));
            DB::purge('coops');
            $pdo = DB::connection('coops')->getPdo();
            $stmt = $pdo->prepare('CALL USP_GET_PROD_SALE_RATES(?)');
            $stmt->execute([$prodId]);
            $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
            $stmt->closeCursor();
            $rates = [];
            foreach ($rows as $row) {
                $mrp = round((float) ($row->MRP ?? $row->mrp ?? 0), 2);
                if ($mrp > 0 && !in_array($mrp, $rates, true)) {
                    $rates[] = $mrp;
                }
            }
            return $rates;
        } catch (\Exception $e) {
            Log::error('Sale rates lookup error: ' . $e->getMessage());
            return [];
        }
    }

    private function attachSaleRates(object $item, int $prodId): object
    {
        $item->Sale_Rates = $this->fetchSaleRates($prodId);
        return $item;
    }
}
