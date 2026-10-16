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
            'items.*.barcode'  => 'nullable|string|max:20',
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
        foreach ($issueItems as $item) {
            $barcode = trim((string) ($item['barcode'] ?? ''));
            if ($barcode === '' || !ctype_digit($barcode)) {
                return response()->json(['error' => 'Scan a barcode before issuing'], 422);
            }
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

            foreach (array_reverse($request->items) as $item) {
                $qty = round((float) ($item['quantity'] ?? 0), 2);
                $barcode = trim((string) ($item['barcode'] ?? ''));
                if ($qty <= 0 || $barcode === '' || !ctype_digit($barcode)) {
                    continue;
                }
                $conn->statement(
                    'UPDATE trns_stockinout
                     SET Barcode = ?,
                         Created_By = IFNULL(Created_By, ?)
                     WHERE Stock_Type = 4
                       AND Prod_Id = ?
                       AND InOut_Date = ?
                       AND Quantity = ?
                       AND ROUND(IFNULL(MRP, 0), 2) = ?
                       AND IFNULL(Barcode, 0) = 0
                     ORDER BY Stock_Id DESC
                     LIMIT 1',
                    [
                        $barcode,
                        (int) session('user_id'),
                        (int) $item['prod_id'],
                        $request->input('indent_date'),
                        $qty,
                        round((float) ($item['mrp'] ?? 0), 2),
                    ]
                );
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

    public function getProductBarcodes(Request $request)
    {
        $prodId = (int) $request->input('prod_id', 0);
        $date = $request->input('sale_date');
        if ($prodId <= 0 || !$date) {
            return response()->json(['error' => 'Product and sale date are required'], 400);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');

        return response()->json($this->officeBarcodeStock($prodId, $date, true));
    }

    /**
     * Office stock left on each barcode. A movement saved without a barcode
     * is applied to that MRP when only one barcode exists for it, so the
     * quantity matches the indent MRP stock.
     */
    private function officeBarcodeStock(int $prodId, string $date, bool $onlyInStock): array
    {
        $signed = "
            CASE
                WHEN s.Stock_Type IN (1, 2, 6) THEN s.Quantity
                WHEN s.Stock_Type = 8 AND NOT EXISTS (
                    SELECT 1 FROM trans_agent_stock a WHERE a.TrnsStock_Id = s.Stock_Id
                ) THEN s.Quantity
                WHEN s.Stock_Type IN (3, 4, 7, 9) THEN -s.Quantity
                ELSE 0
            END
        ";

        $rows = DB::connection('coops')->select("
            SELECT
                b.Barcode,
                b.MRP,
                (b.Avil_Qnty + CASE WHEN c.Barcode_Count = 1 THEN IFNULL(u.Unbar_Qnty, 0) ELSE 0 END) AS Avil_Qnty
            FROM (
                SELECT
                    s.Barcode AS Barcode,
                    CAST(IFNULL(MAX(CASE WHEN s.Stock_Type IN (1, 2) THEN s.MRP END), MAX(s.MRP)) AS DECIMAL(10,2)) AS MRP,
                    IFNULL(SUM($signed), 0) AS Avil_Qnty,
                    MAX(CASE WHEN s.Stock_Type = 2 THEN s.Stock_Id END) AS Purchase_Id,
                    MAX(s.Stock_Id) AS Last_Id
                FROM trns_stockinout s
                WHERE s.Prod_Id = ?
                  AND s.InOut_Date <= ?
                  AND IFNULL(s.Barcode, 0) <> 0
                GROUP BY s.Barcode
            ) b
            LEFT JOIN (
                SELECT MRP, COUNT(*) AS Barcode_Count
                FROM (
                    SELECT
                        s.Barcode,
                        CAST(IFNULL(MAX(CASE WHEN s.Stock_Type IN (1, 2) THEN s.MRP END), MAX(s.MRP)) AS DECIMAL(10,2)) AS MRP
                    FROM trns_stockinout s
                    WHERE s.Prod_Id = ?
                      AND s.InOut_Date <= ?
                      AND IFNULL(s.Barcode, 0) <> 0
                    GROUP BY s.Barcode
                ) m
                GROUP BY MRP
            ) c ON c.MRP = b.MRP
            LEFT JOIN (
                SELECT
                    CAST(IFNULL(s.MRP, 0) AS DECIMAL(10,2)) AS MRP,
                    IFNULL(SUM($signed), 0) AS Unbar_Qnty
                FROM trns_stockinout s
                WHERE s.Prod_Id = ?
                  AND s.InOut_Date <= ?
                  AND IFNULL(s.Barcode, 0) = 0
                GROUP BY CAST(IFNULL(s.MRP, 0) AS DECIMAL(10,2))
            ) u ON u.MRP = b.MRP
            ORDER BY b.Purchase_Id DESC, b.Last_Id DESC
        ", [$prodId, $date, $prodId, $date, $prodId, $date]);

        $this->applyUnbarcodedPurchaseReturns($rows, $prodId, $date);

        if (!$onlyInStock) {
            return $rows;
        }

        return array_values(array_filter($rows, function ($row) {
            return (float) $row->Avil_Qnty > 0;
        }));
    }

    /**
     * Barcodes sold on counter sale, with the quantity still available to return.
     * A sale saved without a barcode is not included.
     */
    private function soldBarcodesForReturn(int $prodId, string $date, int $excludeReturnId = 0, string $barcode = '', int $saleId = 0): array
    {
        if ($date === '') {
            return [];
        }

        $rows = DB::connection('coops')->select("
            SELECT
                s.Barcode AS Barcode,
                MAX(s.Prod_Id) AS Prod_Id,
                CAST(IFNULL(MAX(CASE WHEN s.Stock_Type = 3 THEN s.MRP END), MAX(s.MRP)) AS DECIMAL(10,2)) AS MRP,
                CAST(IFNULL(MAX(CASE WHEN s.Stock_Type = 3 THEN s.Rate END), 0) AS DECIMAL(10,2)) AS Rate,
                IFNULL(SUM(CASE WHEN s.Stock_Type = 3 THEN s.Quantity ELSE 0 END), 0) AS Sold_Qty,
                IFNULL(SUM(CASE
                    WHEN s.Stock_Type = 8
                     AND NOT EXISTS (
                        SELECT 1 FROM trans_agent_stock a WHERE a.TrnsStock_Id = s.Stock_Id
                     )
                     AND (? = 0 OR IFNULL(s.Type_Id, 0) <> ?)
                    THEN s.Quantity ELSE 0 END), 0) AS Returned_Qty
            FROM trns_stockinout s
            WHERE s.InOut_Date <= ?
              AND IFNULL(s.Barcode, 0) <> 0
              AND (? = 0 OR s.Prod_Id = ?)
              AND (? = '' OR s.Barcode = ?)
              AND (
                    s.Stock_Type = 8
                    OR (s.Stock_Type = 3 AND (? = 0 OR s.Type_Id = ?))
              )
            GROUP BY s.Barcode
            HAVING Sold_Qty > 0
            ORDER BY s.Barcode
        ", [
            $excludeReturnId,
            $excludeReturnId,
            $date,
            $prodId,
            $prodId,
            $barcode,
            $barcode,
            $saleId,
            $saleId,
        ]);

        foreach ($rows as $row) {
            $row->Returnable_Qty = max(0, round((float) $row->Sold_Qty - (float) $row->Returned_Qty, 2));
        }

        return $rows;
    }

    private function barcodeReturnableQty(int $prodId, string $barcode, string $date, int $excludeReturnId = 0): float
    {
        $rows = $this->soldBarcodesForReturn($prodId, $date, $excludeReturnId, $barcode);
        if ($rows === []) {
            return 0.0;
        }

        return (float) $rows[0]->Returnable_Qty;
    }

    /**
     * The sale-return procedure does not store a barcode. Write the barcode the user selected.
     */
    private function assignSaleReturnBarcodes($conn, array $items, string $date): void
    {
        foreach ($items as $item) {
            $prodId = (int) ($item['item_id'] ?? 0);
            $barcode = trim((string) ($item['barcode'] ?? ''));
            if ($prodId <= 0 || $barcode === '' || !ctype_digit($barcode) || $date === '') {
                continue;
            }
            $mrp = round((float) ($item['sale_mrp'] ?? 0), 2);
            if ($mrp <= 0) {
                $sold = $conn->selectOne(
                    'SELECT IFNULL(MAX(MRP), 0) AS MRP FROM trns_stockinout WHERE Barcode = ? AND Stock_Type = 3',
                    [$barcode]
                );
                $mrp = round((float) ($sold->MRP ?? 0), 2);
            }
            $conn->statement("
                UPDATE trns_stockinout
                SET Barcode = ?,
                    MRP = CASE WHEN IFNULL(MRP, 0) = 0 THEN ? ELSE MRP END
                WHERE Stock_Type = 8
                  AND Prod_Id = ?
                  AND IFNULL(Barcode, 0) = 0
                  AND InOut_Date = ?
                ORDER BY Stock_Id ASC
                LIMIT 1
            ", [$barcode, $mrp, $prodId, $date]);
        }
    }

    /**
     * A purchase return saved without a barcode (often MRP 0) still reduces
     * that product's barcode. A matching MRP with one barcode is already included.
     */
    private function applyUnbarcodedPurchaseReturns(array $rows, int $prodId, string $date): void
    {
        if ($rows === []) {
            return;
        }

        $returns = DB::connection('coops')->select("
            SELECT
                CAST(IFNULL(s.MRP, 0) AS DECIMAL(10,2)) AS MRP,
                IFNULL(SUM(s.Quantity), 0) AS Qty
            FROM trns_stockinout s
            WHERE s.Prod_Id = ?
              AND s.InOut_Date <= ?
              AND s.Stock_Type = 7
              AND IFNULL(s.Barcode, 0) = 0
            GROUP BY CAST(IFNULL(s.MRP, 0) AS DECIMAL(10,2))
        ", [$prodId, $date]);

        $countByMrp = [];
        foreach ($rows as $row) {
            $key = number_format((float) $row->MRP, 2, '.', '');
            $countByMrp[$key] = ($countByMrp[$key] ?? 0) + 1;
        }

        foreach ($returns as $ret) {
            $qty = (float) $ret->Qty;
            if ($qty == 0.0) {
                continue;
            }
            $key = number_format((float) $ret->MRP, 2, '.', '');
            if ((float) $ret->MRP > 0 && ($countByMrp[$key] ?? 0) === 1) {
                continue;
            }

            $target = null;
            if ((float) $ret->MRP > 0) {
                foreach ($rows as $row) {
                    if (number_format((float) $row->MRP, 2, '.', '') === $key) {
                        $target = $row;
                        break;
                    }
                }
            }
            if ($target === null) {
                $target = $rows[0];
            }
            $target->Avil_Qnty = round((float) $target->Avil_Qnty - $qty, 2);
        }
    }

    public function getItemByBarcode(Request $request)
    {
        try {
            $barcode = trim((string) $request->input('barcode'));
            $date = $request->input('sale_date');

            Config::set('database.connections.coops.database', session('org_schema'));
            DB::purge('coops');

            if ($barcode === '' || !ctype_digit($barcode)) {
                return response()->json(['error' => 'Invalid Code Entered !!'], 404);
            }

            $result = $this->prodInfoByBarcode($barcode, (string) $date);
            if (empty($result)) {
                return response()->json(['error' => 'Invalid Code Entered !!'], 404);
            }

            $item = $result[0];
            $matched = false;
            foreach ($this->officeBarcodeStock((int) $item->Prod_Id, (string) $date, false) as $row) {
                if ((string) $row->Barcode === $barcode) {
                    $item->Avil_Qnty = max(0, round((float) $row->Avil_Qnty, 2));
                    $item->MRP = $row->MRP;
                    $matched = true;
                    break;
                }
            }
            if (!$matched) {
                return response()->json(['error' => 'Invalid Code Entered !!'], 404);
            }

            $item->Barcode = $barcode;
            $mrp = round((float) ($item->MRP ?? 0), 2);
            $item->Sale_Rates = $mrp > 0 ? [$mrp] : [];
            return response()->json($item);
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
            'items.*.quantity' => 'required|integer|min:1|max:99999999',
            'items.*.rate'     => 'required|numeric|max:99999999.99|min:0',
            'items.*.barcode'  => ['required', 'regex:/^[0-9]+$/'],
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
        DB::purge('coops');
        $conn = DB::connection('coops');

        foreach ($request->items as $item) {
            $barcode = trim((string) ($item['barcode'] ?? ''));
            $available = null;
            foreach ($this->officeBarcodeStock((int) $item['item_id'], (string) $request->sale_date, false) as $row) {
                if ((string) $row->Barcode === $barcode) {
                    $available = (float) $row->Avil_Qnty;
                    break;
                }
            }
            $qty = (float) $item['quantity'];
            if ($available === null || $qty > $available + 0.0001) {
                $left = max(0, (float) $available);
                return response()->json([
                    'error' => 'You cannot sell this product as available quantity = ' . $left,
                ], 400);
            }
        }

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
            Pack_Date DATE NULL,
            barcode   BIGINT NULL
        )');

            // Insert each item into temp table
            foreach ($request->items as $item) {
                $conn->insert('INSERT INTO temppurchase VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
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
                    !empty($item['barcode']) ? $item['barcode'] : null,
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

            // Edit mode writes the item lines with an unset sale id, so Trading_Id is NULL.
            if ($mode === 2 && $saleId > 0) {
                $conn->update(
                    'UPDATE trans_trading_products SET Trading_Id = ? WHERE Trading_Id IS NULL',
                    [$saleId]
                );
                $conn->update(
                    'UPDATE trans_trading_gst SET Trading_Id = ? WHERE Trading_Id IS NULL',
                    [$saleId]
                );
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
            $row->Item_Details = $this->saleLineItems((int) $id);

            return response()->json($row);
        } catch (\Exception $e) {
            Log::error('Counter Sale Details Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load sale details'], 500);
        }
    }

    /**
     * One row per sale line. The details procedure joins every stock row of the
     * same product, and returns no items at all when a line has no stock row.
     */
    private function saleLineItems(int $tradingId): array
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
                p.Disc_Prcnt,
                p.Disc_Amt,
                p.Taxable_Amt,
                p.SGST_Prcnt,
                p.CGST_Prcnt,
                p.SGST_Amt,
                p.CGST_Amt,
                p.Net_Amt,
                s.Pack_Date,
                s.Barcode
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
                    st.Barcode,
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
            $date = (string) $request->input('sale_date');

            Config::set('database.connections.coops.database', session('org_schema'));
            DB::purge('coops');

            if ($barcode === '' || !ctype_digit($barcode)) {
                return response()->json(['error' => 'Invalid Code Entered !!'], 404);
            }

            $sold = $this->soldBarcodesForReturn(0, $date, 0, $barcode);
            if ($sold === []) {
                return response()->json(['error' => 'This barcode was not sold'], 404);
            }
            if ((float) $sold[0]->Returnable_Qty <= 0) {
                return response()->json(['error' => 'Nothing is left to return for this barcode'], 400);
            }

            $result = $this->prodInfoByItem((int) $sold[0]->Prod_Id, $date);
            if (empty($result)) {
                return response()->json(['error' => 'Invalid Code Entered !!'], 404);
            }

            $item = $result[0];
            $item->Barcode = $barcode;
            $item->MRP = $sold[0]->MRP;
            $item->Rate = $sold[0]->Rate;
            $item->Returnable_Qty = $sold[0]->Returnable_Qty;
            return response()->json($item);
        } catch (\Exception $e) {
            Log::error('Sale Return Barcode Error: ' . $e->getMessage());
            return response()->json(['error' => 'Error fetching product details'], 500);
        }
    }

    public function saleReturnBarcodes(Request $request)
    {
        $prodId = (int) $request->input('prod_id', 0);
        $date = (string) $request->input('sale_date');
        $saleId = (int) $request->input('sale_id', 0);
        if ($prodId <= 0 || $date === '') {
            return response()->json(['error' => 'Product and sale date are required'], 400);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');

        $rows = array_values(array_filter(
            $this->soldBarcodesForReturn($prodId, $date, 0, '', $saleId),
            fn ($row) => (float) $row->Returnable_Qty > 0
        ));

        return response()->json($rows);
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
            'items.*.barcode'  => ['required', 'regex:/^[0-9]+$/'],
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
        DB::purge('coops');
        $conn = DB::connection('coops');

        $excludeReturnId = (int) $request->input('sale_id', 0);
        $returnQtyByBarcode = [];
        foreach ($request->items as $item) {
            $barcode = trim((string) ($item['barcode'] ?? ''));
            if ($barcode === '' || !ctype_digit($barcode)) {
                return response()->json(['error' => 'Select a barcode before returning'], 400);
            }
            $key = (int) ($item['item_id'] ?? 0) . '|' . $barcode;
            $returnQtyByBarcode[$key] = ($returnQtyByBarcode[$key] ?? 0) + (float) $item['quantity'];
        }
        foreach ($returnQtyByBarcode as $key => $qty) {
            [$prodId, $barcode] = explode('|', $key, 2);
            $allowed = $this->barcodeReturnableQty((int) $prodId, $barcode, (string) $request->sale_date, $excludeReturnId);
            if ($qty > $allowed + 0.0001) {
                $left = rtrim(rtrim(number_format($allowed, 2, '.', ''), '0'), '.');
                return response()->json([
                    'error' => 'Return quantity cannot exceed the sold quantity of this barcode. Quantity available to return = ' . $left,
                ], 400);
            }
        }

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

            $this->assignSaleReturnBarcodes($conn, $request->items, (string) $request->input('sale_date'));

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
            $row->Item_Details = $this->saleLineItems((int) $id);

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
