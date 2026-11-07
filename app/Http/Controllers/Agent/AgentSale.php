<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Concerns\ResolvesOrganisationAddress;
use App\Http\Controllers\Concerns\SearchesItemsByCode;
use App\Http\Controllers\Concerns\SearchesParties;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class AgentSale extends Controller
{
    use ResolvesOrganisationAddress;
    use SearchesItemsByCode;
    use SearchesParties;

    public function index()
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');

        // Customers load via AJAX Select2 filtered by logged-in agent.
        $customers = [];
        $categories = DB::connection('coops')->select('CALL USP_GET_ITEM_CAT(?)', [session('org_id')]);
        $this->organisationAddress();
        return view('Agent.sale', [
            'customers'  => $customers,
            'categories' => $categories,
            'year_start' => session('year_start'),
            'year_end'   => session('year_end'),
        ]);
    }

    /**
     * Agent portal party search — always scoped to the logged-in agent.
     */
    public function searchMyParties(Request $request)
    {
        $request->merge([
            'party_type' => 2,
            'agent_id' => (int) session('agent_id'),
        ]);
        return $this->searchParties($request);
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
                $held = $this->agentBarcodeRow((int) $item->Prod_Id, (string) $date, $code);
                if (!$held) {
                    return response()->json(['error' => 'Invalid Code Entered !!'], 404);
                }
                $item->Avil_Qnty = $held->Avil_Qnty;
                if ((float) $held->MRP > 0) {
                    $item->MRP = $held->MRP;
                    $item->Rate = $held->MRP;
                }
                $item->Barcode = $code;
                $mrp = round((float) ($item->MRP ?? 0), 2);
                $item->Sale_Rates = $mrp > 0 ? [$mrp] : [];
                return response()->json($item);
            }
            return response()->json(['error' => 'Invalid Code Entered !!'], 404);
        } catch (\Exception $e) {
            Log::error('Agent Sale Barcode Error: ' . $e->getMessage());
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

    public function productBarcodes(Request $request)
    {
        $prodId = (int) $request->input('prod_id', 0);
        $date = $this->toIsoDate($request->input('sale_date'));
        if ($prodId <= 0 || !$date) {
            return response()->json(['error' => 'Product and sale date are required'], 400);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');

        return response()->json($this->agentProductBarcodes($prodId, $date));
    }

    /**
     * Barcodes held by this agent. Quantity is indent stock issued to the agent
     * (issue minus office return, agent sale, plus customer return), not office stock.
     */
    private function agentProductBarcodes(int $prodId, string $date): array
    {
        $rows = DB::connection('coops')->select(
            'CALL USP_GET_AGENT_PRODUCT_BARCODES(?, ?, ?)',
            [session('agent_id'), $prodId, $date]
        );
        $availTotal = $this->agentAvailableQty($prodId, $date);
        $held = 0.0;
        foreach ($rows as $row) {
            $held += (float) ($row->Avil_Qnty ?? 0);
        }
        $cut = max(0, round($held - $availTotal, 2));
        foreach ($rows as $row) {
            if ($cut <= 0) {
                break;
            }
            $qty = (float) ($row->Avil_Qnty ?? 0);
            $take = min($qty, $cut);
            $row->Avil_Qnty = round($qty - $take, 2);
            $cut = round($cut - $take, 2);
        }

        return array_values(array_filter($rows, function ($row) {
            return (float) ($row->Avil_Qnty ?? 0) > 0.0001;
        }));
    }

    private function agentBarcodeRow(int $prodId, string $date, string $barcode): ?object
    {
        foreach ($this->agentProductBarcodes($prodId, $date) as $row) {
            if ((string) $row->Barcode === (string) $barcode) {
                return $row;
            }
        }
        return null;
    }

    public function getItemByProd(Request $request)
    {
        try {
            $prodId = (int) $request->input('prod_id', 0);
            $date = $this->toIsoDate($request->input('sale_date'));
            if ($prodId <= 0 || !$date) {
                return response()->json(['error' => 'Product and sale date are required'], 400);
            }
            Config::set('database.connections.coops.database', session('org_schema'));
            DB::purge('coops');
            $agentQty = $this->agentAvailableQty($prodId, $date);
            $result = DB::connection('coops')->select('CALL USP_GET_PROD_INFO_BY_ITEM(?, ?)', [
                $prodId,
                $date,
            ]);
            if (!empty($result)) {
                $item = $result[0];
                $item->Avil_Qnty = $agentQty;
                $item = $this->attachSaleRates($item, $prodId);
                Log::channel('trading')->info('agent sale item-info', [
                    'prod_id' => $prodId,
                    'agent_qty' => $agentQty,
                ]);
                return response()->json($item);
            }
            return response()->json(['error' => 'Item not found'], 404);
        } catch (\Exception $e) {
            Log::error('Agent Sale Item Info Error: ' . $e->getMessage());
            return response()->json(['error' => 'Error fetching product details'], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'sale_date'  => 'required|date',
            'party_id'   => 'required|integer|min:0',
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

        $partyId = (int) $request->input('party_id', 0);
        $transMode = (int) $request->input('trans_mode', 1);

        if ($transMode === 3 && $partyId <= 0) {
            return response()->json(['error' => 'Customer is required for credit sale'], 400);
        }

        if ($partyId > 0) {
            $party = DB::connection('coops')->selectOne(
                'CALL USP_VALIDATE_AGENT_CUSTOMER(?, ?)',
                [$partyId, session('agent_id')]
            );
            if (!$party) {
                return response()->json(['error' => 'Selected customer is not assigned to you'], 400);
            }
        }

        $needed = [];
        foreach ($request->items as $item) {
            $prodId = (int) ($item['item_id'] ?? 0);
            if ($prodId <= 0) {
                continue;
            }
            $needed[$prodId] = ($needed[$prodId] ?? 0) + (float) ($item['quantity'] ?? 0);
        }
        foreach ($needed as $prodId => $qty) {
            $avail = $this->agentAvailableQty($prodId, (string) $request->input('sale_date'));
            if ($qty > $avail + 0.0001) {
                return response()->json([
                    'error' => 'Cannot sell more than available stock. Pending office return is held. Available qty: '
                        . rtrim(rtrim(number_format($avail, 2, '.', ''), '0'), '.'),
                ], 400);
            }
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
            $warning     = $this->creditSellLimitWarning(
                (int) session('agent_id'),
                $netAmt,
                $saleId
            );

            $result = $conn->select('CALL USP_ADD_EDIT_AGENT_SALE(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $saleId,
                session('branch_id'),
                $request->input('sale_date'),
                $request->input('sale_no', null),
                $partyId,
                $totAmt,
                $discAmt,
                $taxableAmt,
                $totGst,
                $roundOff,
                $netAmt,
                $transMode,
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
                'warning'   => $warning,
                'bill_data' => $billData,
                'show_bill' => true,
            ]);

        } catch (\Exception $e) {
            $conn->rollBack();
            Log::error('Agent Sale Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to save sale'], 500);
        }
    }

    /**
     * Warn when the agent's unsettled sales (cash, bank, credit) plus this sale
     * exceed the credit sell limit. Does not block the sale.
     */
    private function creditSellLimitWarning(int $agentId, float $saleAmt, int $saleId): ?string
    {
        try {
            $pdo = DB::connection('coops')->getPdo();
            $stmt = $pdo->prepare('CALL USP_CHK_AGENT_CREDIT_SELL_LIMIT(?, ?, ?)');
            $stmt->execute([$agentId, $saleAmt, $saleId]);
            $row = $stmt->fetch(\PDO::FETCH_OBJ);
            $stmt->closeCursor();

            $limit = (float) ($row->Credit_Sell_Limit ?? 0);
            if ($limit <= 0) {
                return null;
            }

            $unsettled = (float) ($row->Unsettled_Amt ?? 0);
            $sale = (float) ($row->Sale_Amt ?? $saleAmt);
            $total = $unsettled + $sale;
            if ($total <= $limit) {
                return null;
            }

            if ($unsettled > $limit) {
                return 'You have crossed your credit sell limit. Unsettled amount ₹'
                    . number_format($unsettled, 2)
                    . '. Credit sell limit is ₹' . number_format($limit, 2) . '.';
            }

            return 'You have crossed your credit sell limit. Unsettled amount ₹'
                . number_format($unsettled, 2)
                . ' + this sale ₹' . number_format($sale, 2)
                . ' = ₹' . number_format($total, 2)
                . '. Credit sell limit is ₹' . number_format($limit, 2) . '.';
        } catch (\Exception $e) {
            Log::error('Agent credit sell limit warning error: ' . $e->getMessage());
            return null;
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
