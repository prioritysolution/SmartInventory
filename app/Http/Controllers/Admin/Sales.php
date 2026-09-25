<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\SearchesItemsByCode;
use App\Http\Controllers\Controller;
use App\Support\AgentIndentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class Sales extends Controller
{
    use SearchesItemsByCode;

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
                $item = $this->attachIndentRates($result[0], (int) $result[0]->Prod_Id, (string) $request->input('date'));
                return response()->json([
                    'success' => true,
                    'data' => $item
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
        DB::purge('coops');
        return $this->resolveUniqueProductInfo((string) $code, (string) $date);
    }
    public function getPendingIndents(Request $request)
    {
        try {
            $agentId = $request->input('agent_id');
            $indentTypeId = (int) $request->input('indent_type_id', AgentIndentType::ISSUE);
            Config::set('database.connections.coops.database', session('org_schema'));
            $result = DB::connection('coops')->select(
                'CALL USP_GET_PENDING_INDENT(?, ?)',
                [$agentId, $indentTypeId]
            );
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

    public function cancelAgentIndentItem(Request $request)
    {
        $request->validate([
            'indent_sl' => 'required|integer|min:1',
            'indent_id' => 'required|integer|min:1',
            'agent_id'  => 'required|integer|min:1',
        ]);

        Config::set('database.connections.coops.database', session('org_schema'));
        try {
            $result = DB::connection('coops')->select(
                'CALL USP_CANCEL_AGENT_INDENT_ITEM(?, ?, ?)',
                [
                    (int) $request->input('indent_sl'),
                    (int) $request->input('indent_id'),
                    (int) $request->input('agent_id'),
                ]
            );
            $row = $result[0] ?? null;
            if (!$row || (int) ($row->Error_No ?? -1) < 0) {
                return response()->json(['message' => $row->Message ?? 'Cancel failed'], 422);
            }
            return response()->json(['message' => $row->Message ?? 'Item cancelled']);
        } catch (\Exception $e) {
            Log::error('Cancel Indent Item Error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to cancel item'], 500);
        }
    }

    public function deleteAgentIndentItem(Request $request)
    {
        $request->validate([
            'indent_sl' => 'required|integer|min:1',
            'indent_id' => 'required|integer|min:1',
            'agent_id'  => 'required|integer|min:1',
        ]);

        Config::set('database.connections.coops.database', session('org_schema'));
        try {
            $result = DB::connection('coops')->select(
                'CALL USP_DELETE_AGENT_INDENT_ITEM(?, ?, ?)',
                [
                    (int) $request->input('indent_sl'),
                    (int) $request->input('indent_id'),
                    (int) $request->input('agent_id'),
                ]
            );
            $row = $result[0] ?? null;
            if (!$row || (int) ($row->Error_No ?? -1) < 0) {
                return response()->json(['message' => $row->Message ?? 'Delete failed'], 422);
            }
            return response()->json([
                'message' => $row->Message ?? 'Item deleted',
                'remaining_count' => (int) ($row->Remaining_Count ?? 0),
            ]);
        } catch (\Exception $e) {
            Log::error('Delete Indent Item Error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete item'], 500);
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
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.mrp'      => 'nullable|numeric|min:0',
            'items.*.rejected' => 'nullable|boolean',
        ]);

        $issueItems = collect($request->items)->filter(function ($item) {
            return (float) ($item['quantity'] ?? 0) > 0;
        });
        $indentId = (int) ($request->input('indent_id') ?: 0);
        $allRejected = $issueItems->isEmpty()
            && collect($request->items)->isNotEmpty()
            && $indentId > 0
            && (int) $request->input('indent_type') === 1;

        if ($issueItems->isEmpty() && !$allRejected) {
            return response()->json(['error' => 'At least one product must be issued (qty > 0)'], 422);
        }

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
                    qnty      DECIMAL(10,2),
                    mrp       DECIMAL(10,2) DEFAULT 0
                  )');

            $indentId = $request->input('indent_id') ?: 0;

            foreach ($request->items as $item) {
                // Soft-delete / reject: quantity 0 stays on indent with full Reject_Qty
                $conn->insert('INSERT INTO tempitem (indent_id, item_id, prod_id, unit_id, qnty, mrp) VALUES (?, ?, ?, ?, ?, ?)', [
                    $indentId,
                    $item['prod_id'],
                    $item['prod_id'],
                    $item['unit_id'],
                    round((float) ($item['quantity'] ?? 0), 2),
                    round((float) ($item['mrp'] ?? 0), 2),
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
            $agentId = (int) $request->input('agent_id', 0);
            $date = $request->input('date');
            if ($agentId <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select an agent first',
                ], 400);
            }
            if (!$date) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select return date first',
                ], 400);
            }

            Config::set('database.connections.coops.database', session('org_schema'));
            DB::purge('coops');

            $code = trim((string) $request->input('barcode'));
            $result = $this->prodInfoByAgentBarcode($code, $agentId, $date);
            if (empty($result)) {
                $result = $this->resolveUniqueProductInfo($code, $date);
            }
            if (!empty($result)) {
                $item = $result[0];
                $item->Avil_Qnty = $this->agentAvailableQty($agentId, (int) $item->Prod_Id, $date);
                $item->Sale_Rates = $this->fetchSaleRates((int) $item->Prod_Id);
                return response()->json([
                    'success' => true,
                    'data' => $item,
                ]);
            }
            return response()->json([
                'success' => false,
                'message' => 'Invalid Code Entered !!',
            ]);
        } catch (\Exception $e) {
            Log::error('Agent Return Product Info Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching product details',
            ], 500);
        }
    }

    public function getAgentReturnItemByProd(Request $request)
    {
        try {
            $prodId = (int) $request->input('prod_id', 0);
            $agentId = (int) $request->input('agent_id', 0);
            $date = $request->input('sale_date');
            if ($agentId <= 0) {
                return response()->json(['error' => 'Please select an agent first'], 400);
            }
            if ($prodId <= 0 || !$date) {
                return response()->json(['error' => 'Product and return date are required'], 400);
            }
            Config::set('database.connections.coops.database', session('org_schema'));
            DB::purge('coops');
            $result = DB::connection('coops')->select('CALL USP_GET_PROD_INFO_BY_ITEM(?, ?)', [$prodId, $date]);
            if (!empty($result)) {
                $item = $result[0];
                $item->Avil_Qnty = $this->agentAvailableQty($agentId, $prodId, $date);
                $item->Sale_Rates = $this->fetchSaleRates($prodId);
                return response()->json($item);
            }
            return response()->json(['error' => 'Item not found'], 404);
        } catch (\Exception $e) {
            Log::error('Agent Return Item Info Error: ' . $e->getMessage());
            return response()->json(['error' => 'Error fetching product details'], 500);
        }
    }

    private function agentAvailableQty(int $agentId, int $prodId, string $date): float
    {
        $row = DB::connection('coops')->selectOne(
            'SELECT UDF_CAL_AGENT_STOCK(?, ?, ?) AS Avil_Qnty',
            [$agentId, $prodId, $date]
        );
        return (float) ($row->Avil_Qnty ?? 0);
    }

    public function storeAgentReturn(Request $request)
    {
        $request->validate([
            'indent_date' => 'required|date',
            'agent_id' => 'required|integer',
            'return_type' => 'required|in:1,2',
            'items' => 'required|array|min:1',
            'items.*.prod_id' => 'required|integer',
            'items.*.unit_id' => 'required|integer',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.mrp' => 'nullable|numeric|min:0',
            'indent_id' => 'nullable|integer',
        ]);

        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');
        $conn = DB::connection('coops');
        $conn->beginTransaction();

        try {
            $date = $request->input('indent_date');
            $agentId = (int) $request->input('agent_id');
            $returnType = (int) $request->input('return_type');
            $indentId = (int) $request->input('indent_id', 0);

            if ($returnType === 1 && $indentId <= 0) {
                $conn->rollBack();
                return response()->json(['error' => 'Please attach a return requisition first.'], 400);
            }

            $usedQty = [];
            foreach ($request->items as $item) {
                $prodId = (int) $item['prod_id'];
                $already = $usedQty[$prodId] ?? 0;
                $avail = $this->agentAvailableQty($agentId, $prodId, $date) - $already;
                if ((float) $item['quantity'] > $avail + 0.0001) {
                    $conn->rollBack();
                    return response()->json([
                        'error' => 'Return qty cannot exceed this agent\'s available stock.',
                    ], 400);
                }
                $usedQty[$prodId] = $already + (float) $item['quantity'];
            }

            $conn->statement('DROP TEMPORARY TABLE IF EXISTS tempitem');
            $conn->statement('CREATE TEMPORARY TABLE tempitem (
                id INT PRIMARY KEY AUTO_INCREMENT,
                indent_id INT,
                item_id INT,
                prod_id INT,
                unit_id INT,
                qnty DECIMAL(10,2),
                mrp DECIMAL(10,2) DEFAULT 0
            )');

            foreach ($request->items as $item) {
                $conn->insert(
                    'INSERT INTO tempitem (indent_id, item_id, prod_id, unit_id, qnty, mrp) VALUES (?, ?, ?, ?, ?, ?)',
                    [
                        $returnType === 1 ? $indentId : 0,
                        $item['prod_id'],
                        $item['prod_id'],
                        $item['unit_id'],
                        $item['quantity'],
                        round((float) ($item['mrp'] ?? 0), 2),
                    ]
                );
            }

            $result = $conn->select('CALL USP_ADD_EDIT_AGENT_OFFICE_RETURN(?, ?, ?, ?)', [
                $agentId,
                $date,
                session('branch_id'),
                $request->input('remarks', 'From Office'),
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
        $customers  = DB::connection('coops')->select('CALL USP_GET_PARTY_LIST(?, ?, ?)', [2, $branchId, 0]);
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
        DB::purge('coops');
        $items = $this->searchItemsByCode(
            (int) $request->input('cat_id', 0),
            (int) $request->input('sub_cat_id', 0),
            (string) ($request->input('code') ?? '')
        );
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
                if ($request->routeIs('agent-indent.item-info')) {
                    return response()->json($this->attachIndentRates($result[0], $prodId, (string) $date));
                }
                return response()->json($this->attachSaleRates($result[0], $prodId));
            }
            return response()->json(['error' => 'Item not found'], 404);
        } catch (\Exception $e) {
            Log::error('Counter Sale Item Info Error: ' . $e->getMessage());
            return response()->json(['error' => 'Error fetching product details'], 500);
        }
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

    private function fetchMrpStock(int $prodId, string $date): array
    {
        if ($prodId <= 0 || $date === '') {
            return [];
        }
        try {
            Config::set('database.connections.coops.database', session('org_schema'));
            DB::purge('coops');
            $pdo = DB::connection('coops')->getPdo();
            $stmt = $pdo->prepare('CALL USP_GET_PROD_MRP_STOCK(?, ?)');
            $stmt->execute([$prodId, $date]);
            $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
            $stmt->closeCursor();
            $stock = [];
            foreach ($rows ?: [] as $row) {
                $mrp = round((float) ($row->MRP ?? 0), 2);
                if ($mrp <= 0) {
                    continue;
                }
                $stock[] = (object) [
                    'MRP' => $mrp,
                    'Avil_Qnty' => (float) ($row->Avil_Qnty ?? 0),
                ];
            }
            return $stock;
        } catch (\Exception $e) {
            Log::error('MRP stock lookup error: ' . $e->getMessage());
            $fallback = [];
            foreach ($this->fetchSaleRates($prodId) as $mrp) {
                $fallback[] = (object) ['MRP' => $mrp, 'Avil_Qnty' => null];
            }
            return $fallback;
        }
    }

    private function attachIndentRates(object $item, int $prodId, string $date): object
    {
        $item->Sale_Rates = $this->fetchSaleRates($prodId);
        $item->Mrp_Stock = $this->fetchMrpStock($prodId, $date);
        return $item;
    }

    public function getSaleRates(Request $request)
    {
        return response()->json($this->fetchSaleRates((int) $request->input('prod_id', 0)));
    }

    public function getItemByBarcode(Request $request)
    {
        try {
            $barcode = trim((string) $request->input('barcode'));
            $date = $request->input('sale_date');

            Config::set('database.connections.coops.database', session('org_schema'));
            DB::purge('coops');

            $result = $this->resolveUniqueProductInfo($barcode, (string) $date);
            if (!empty($result)) {
                return response()->json($this->attachSaleRates($result[0], (int) $result[0]->Prod_Id));
            }
            return response()->json(['error' => 'Invalid Code Entered !!'], 404);
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
            'instrument_no' => 'nullable|string|max:50',
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
        if ((int) $request->input('trans_mode') === 2) {
            if ((int) $request->input('bank_id', 0) <= 0) {
                return response()->json(['error' => 'Please select a Bank'], 400);
            }
            if (!trim((string) $request->input('instrument_no', ''))) {
                return response()->json(['error' => 'Instrument No is required for Bank'], 400);
            }
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

            $result = $conn->select('CALL USP_ADD_EDIT_SALE(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
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
                (int) $request->input('trans_mode') === 2 ? trim((string) $request->input('instrument_no', '')) : '',
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
        $customers  = DB::connection('coops')->select('CALL USP_GET_PARTY_LIST(?, ?, ?)', [2, $branchId, 0]);
        $banks      = DB::connection('coops')->select('CALL USP_GET_BANK_LEDGER()');
        $categories = DB::connection('coops')->select('CALL USP_GET_ITEM_CAT(?)', [session('org_id')]);
        return view('Admin.sale-return', compact('customers', 'banks', 'categories'))
            ->with('pageTitle', ' Counter Sale Return');
    }

    public function getItemByBarcodeReturn(Request $request)
    {
        try {
            $barcode = trim((string) $request->input('barcode'));
            $date = $request->input('sale_date');

            Config::set('database.connections.coops.database', session('org_schema'));
            DB::purge('coops');

            $result = $this->resolveUniqueProductInfo($barcode, (string) $date);
            if (!empty($result)) {
                return response()->json($this->attachSaleRates($result[0], (int) $result[0]->Prod_Id));
            }
            return response()->json(['error' => 'Invalid Code Entered !!'], 404);
        } catch (\Exception $e) {
            Log::error('Sale Return Barcode Error: ' . $e->getMessage());
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
            'instrument_no' => 'nullable|string|max:50',
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
        if ((int) $request->input('trans_mode') === 2) {
            if ((int) $request->input('bank_id', 0) <= 0) {
                return response()->json(['error' => 'Please select a Bank'], 400);
            }
            if (!trim((string) $request->input('instrument_no', ''))) {
                return response()->json(['error' => 'Instrument No is required for Bank'], 400);
            }
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

            $result = $conn->select('CALL USP_ADD_EDIT_SALE_RETURN(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
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
                intval($request->input('trans_mode')),
                intval($request->input('bank_id', 0)),
                (int) $request->input('trans_mode') === 2 ? trim((string) $request->input('instrument_no', '')) : '',
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

    public function indexAgentSettlement()
    {
        return view('Admin.agent-settlement', [
            'year_start' => session('year_start'),
            'year_end'   => session('year_end'),
        ])->with('pageTitle', 'Agent Settlement');
    }

    public function agentSettlementSearch(Request $request)
    {
        $token = trim((string) $request->input('token', ''));
        if ($token === '') {
            return response()->json(['message' => 'Enter settlement token'], 422);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        $rows = DB::connection('coops')->select('CALL USP_SEARCH_AGENT_SETTLEMENT_BY_TOKEN(?, ?)', [
            $token,
            (int) session('branch_id'),
        ]);

        if (empty($rows)) {
            return response()->json(['message' => 'Invalid settlement token'], 422);
        }

        $first = $rows[0];
        if ((int) ($first->Error_No ?? 0) < 0) {
            return response()->json(['message' => $first->Message ?? 'Invalid settlement token'], 422);
        }

        $sales = array_values(array_filter($rows, fn ($row) => (int) ($row->Trading_Id ?? 0) > 0));

        $denomRows = DB::connection('coops')->select('CALL USP_GET_AGENT_SETTLE_DENOM(?)', [$token]);
        $denom = $denomRows[0] ?? null;

        return response()->json([
            'agent_name' => $first->Agent_Name ?? '',
            'agent_code' => $first->Agent_Code ?? '',
            'rows'         => $sales,
            'denomination' => $denom ? [
                'agent_id'    => (int) ($denom->Agent_Id ?? 0),
                'settle_date' => $denom->Settle_Date ?? null,
                'coin'        => (int) ($denom->Coin ?? 0),
                'rs_5'        => (int) ($denom->Rs_5 ?? 0),
                'rs_10'       => (int) ($denom->Rs_10 ?? 0),
                'rs_20'       => (int) ($denom->Rs_20 ?? 0),
                'rs_50'       => (int) ($denom->Rs_50 ?? 0),
                'rs_100'      => (int) ($denom->Rs_100 ?? 0),
                'rs_200'      => (int) ($denom->Rs_200 ?? 0),
                'rs_500'      => (int) ($denom->Rs_500 ?? 0),
                'token'       => $denom->Token ?? $token,
                'denom_total' => (float) ($denom->Denom_Total ?? 0),
            ] : null,
        ]);
    }

    public function storeAgentSettlement(Request $request)
    {
        $request->validate([
            'settle_date' => 'required|date',
            'token'       => 'required|string|max:20',
        ]);

        if ($request->input('settle_date') < session('year_start') || $request->input('settle_date') > session('year_end')) {
            return response()->json(['message' => 'Settlement date must be within the accounting year'], 422);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        DB::connection('coops')->beginTransaction();

        try {
            $result = DB::connection('coops')->select('CALL USP_SETTLE_AGENT_SALES(?, ?, ?, ?, ?)', [
                $request->input('settle_date'),
                trim((string) $request->input('token')),
                (int) session('branch_id'),
                (int) session('year_id'),
                (int) session('user_id'),
            ]);

            $row = $result[0] ?? null;
            if (!$row || (int) ($row->Error_No ?? -1) < 0) {
                DB::connection('coops')->rollBack();
                return response()->json([
                    'message' => $row->Message ?? 'Settlement failed',
                ], 422);
            }

            DB::connection('coops')->commit();
            return response()->json([
                'message'        => $row->Message ?? 'Settlement completed',
                'settled_count'  => (int) ($row->Settled_Count ?? 0),
                'voucher_count'  => (int) ($row->Voucher_Count ?? 0),
            ]);
        } catch (\Exception $e) {
            DB::connection('coops')->rollBack();
            Log::error('Agent settlement error: ' . $e->getMessage());
            return response()->json(['message' => 'Settlement failed'], 500);
        }
    }
}
