<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\SearchesItemsByCode;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Wastage extends Controller
{
    use SearchesItemsByCode;

    public function index()
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $categories = DB::connection('coops')->select('CALL USP_GET_ITEM_CAT(?)', [session('org_id')]);
        return view('Admin.wastage-damage', compact('categories'))->with('pageTitle', 'Wastage / Damage Entry');
    }

    public function getSubCats(Request $request)
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $subs = DB::connection('coops')->select('CALL USP_GET_ITEM_SUB_CAT(?, ?)', [
            session('org_id'),
            $request->input('cat_id', 0),
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

    public function itemInfo(Request $request)
    {
        $prodId = (int) $request->input('prod_id', 0);
        $date = $request->input('date') ?: date('Y-m-d');
        if (!$prodId) {
            return response()->json(['error' => 'Select a product'], 422);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        $row = DB::connection('coops')->select('CALL USP_GET_LAST_PURCHASE_RATE(?, ?)', [$prodId, $date]);
        if (empty($row)) {
            return response()->json(['error' => 'Product not found'], 404);
        }
        return response()->json($row[0]);
    }

    public function barcode(Request $request)
    {
        $barcode = trim((string) $request->input('barcode', ''));
        $date = (string) $request->input('date', '');
        $damageId = (int) $request->input('damage_id', 0);
        if ($barcode === '' || !ctype_digit($barcode) || $date === '') {
            return response()->json(['error' => 'Invalid Code Entered !!'], 404);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');

        $result = $this->prodInfoByBarcode($barcode, $date);
        if ($result === []) {
            return response()->json(['error' => 'Invalid Code Entered !!'], 404);
        }

        $item = $result[0];
        $matched = null;
        foreach ($this->officeBarcodes((int) $item->Prod_Id, $date, $damageId, false) as $row) {
            if ((string) $row->Barcode === $barcode) {
                $matched = $row;
                break;
            }
        }
        if ($matched === null) {
            return response()->json(['error' => 'Invalid Code Entered !!'], 404);
        }

        $available = max(0, round((float) $matched->Avil_Qnty, 2));
        if ($available <= 0) {
            return response()->json(['error' => 'No stock is left on this barcode'], 400);
        }

        $code = DB::connection('coops')->selectOne(
            'SELECT Prod_Code FROM mst_product WHERE Prod_Id = ?',
            [(int) $item->Prod_Id]
        );
        $rates = $this->purchaseRatesForBarcodes([$barcode]);
        $rateRow = $rates[$barcode] ?? null;

        $item->Prod_Code = $code->Prod_Code ?? '';
        $item->Barcode = $barcode;
        $item->MRP = $matched->MRP;
        $item->Avil_Qnty = $available;
        $item->Purchase_Rate = round((float) ($rateRow->Rate ?? 0), 2);

        return response()->json($item);
    }

    public function barcodes(Request $request)
    {
        $prodId = (int) $request->input('prod_id', 0);
        $date = (string) $request->input('date', '');
        $damageId = (int) $request->input('damage_id', 0);
        if ($prodId <= 0 || $date === '') {
            return response()->json(['error' => 'Product and date are required'], 400);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');

        $rows = $this->officeBarcodes($prodId, $date, $damageId, true);
        $rates = $this->purchaseRatesForBarcodes(array_map(function ($row) {
            return (string) $row->Barcode;
        }, $rows));
        foreach ($rows as $row) {
            $rateRow = $rates[(string) $row->Barcode] ?? null;
            $row->Purchase_Rate = round((float) ($rateRow->Rate ?? 0), 2);
            $row->Barcode = (string) $row->Barcode;
        }

        return response()->json($rows);
    }

    public function store(Request $request)
    {
        $request->validate([
            'entry_date'   => 'required|date',
            'particulars'  => 'required|string|max:200',
            'items'        => 'required|array|min:1',
            'items.*.item_id'   => 'required|integer',
            'items.*.barcode'   => ['required', 'regex:/^[0-9]+$/'],
            'items.*.quantity'  => 'required|integer|min:1|max:32767',
            'items.*.rate'      => 'required|numeric|min:0|max:99999999.99',
            'items.*.unit_id'   => 'required|integer',
            'items.*.mrp'       => 'nullable|numeric|min:0',
            'damage_id'    => 'nullable|integer',
            'vouch_id'     => 'nullable|integer',
        ]);

        $yearStart = session('year_start');
        $yearEnd   = session('year_end');
        if ($request->entry_date < $yearStart || $request->entry_date > $yearEnd) {
            return response()->json(['error' => "Date must be between {$yearStart} and {$yearEnd}"], 400);
        }
        if ($request->entry_date > date('Y-m-d')) {
            return response()->json(['error' => 'Date cannot be after today'], 400);
        }

        $damageId = intval($request->input('damage_id', 0));
        $stockError = $this->damageStockError($request->items, $request->entry_date, $damageId);
        if ($stockError !== null) {
            return response()->json(['error' => $stockError], 400);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        $conn = DB::connection('coops');
        $conn->beginTransaction();

        try {
            $conn->statement('DROP TEMPORARY TABLE IF EXISTS tempdamage');
            $conn->statement('CREATE TEMPORARY TABLE tempdamage (
                item_id INT,
                unit    SMALLINT,
                qnty    NUMERIC(10,2),
                rate    NUMERIC(12,2),
                tot_amt NUMERIC(18,2)
            )');

            foreach ($request->items as $item) {
                $qty = floatval($item['quantity']);
                $rate = floatval($item['rate']);
                $conn->insert('INSERT INTO tempdamage VALUES (?, ?, ?, ?, ?)', [
                    intval($item['item_id']),
                    intval($item['unit_id']),
                    $qty,
                    $rate,
                    round($qty * $rate, 2),
                ]);
            }

            $mode = $damageId > 0 ? 2 : 1;

            $result = $conn->select('CALL USP_ADD_EDIT_DAMAGE(?, ?, ?, ?, ?, ?, ?, ?)', [
                $damageId,
                intval($request->input('vouch_id', 0)),
                session('branch_id'),
                $request->input('entry_date'),
                $request->input('particulars'),
                session('user_id'),
                session('year_id'),
                $mode,
            ]);

            if (!empty($result) && $result[0]->Error_No < 0) {
                $conn->rollBack();
                return response()->json(['error' => $result[0]->Message ?? 'Save failed'], 400);
            }

            $this->assignDamageBarcodes($conn, $request->items, $request->entry_date, $damageId);

            $conn->commit();
            return response()->json(['message' => $result[0]->Message ?? 'Saved successfully']);
        } catch (\Exception $e) {
            $conn->rollBack();
            Log::channel('trading')->error('Damage store error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function search(Request $request)
    {
        $frm = $request->input('from_date');
        $to = $request->input('to_date');
        if (!$frm || !$to) {
            return response()->json(['error' => 'Select from date and to date'], 422);
        }
        if ($frm > $to) {
            return response()->json(['error' => 'From date cannot be after to date'], 422);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        $rows = DB::connection('coops')->select('CALL USP_SEARCH_DAMAGE(?, ?, ?)', [
            $frm,
            $to,
            (int) (session('branch_id') ?: 0),
        ]);
        return response()->json($rows);
    }

    public function details($id)
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $result = DB::connection('coops')->select('CALL USP_GET_DAMAGE_DTLS(?)', [intval($id)]);
        if (empty($result)) {
            return response()->json(['error' => 'Record not found'], 404);
        }
        $row = $result[0];
        $row->Item_Details = json_decode($row->Item_Details, true) ?: [];
        $this->attachDamageBarcodes($row->Item_Details, (int) $id);
        return response()->json($row);
    }

    /**
     * Office stock left on each barcode. Same movement rule as counter sale.
     * The open damage document is left out so its own quantity can be saved again.
     */
    private function officeBarcodes(int $prodId, string $date, int $excludeDamageId, bool $onlyInStock): array
    {
        if ($prodId <= 0 || $date === '') {
            return [];
        }

        $signed = "
            CASE
                WHEN s.Stock_Type = 9 AND ? > 0 AND IFNULL(s.Type_Id, 0) = ? THEN 0
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
        ", [
            $excludeDamageId, $excludeDamageId, $prodId, $date,
            $prodId, $date,
            $excludeDamageId, $excludeDamageId, $prodId, $date,
        ]);

        $this->applyUnbarcodedPurchaseReturns($rows, $prodId, $date);

        if (!$onlyInStock) {
            return $rows;
        }

        return array_values(array_filter($rows, function ($row) {
            return (float) $row->Avil_Qnty > 0;
        }));
    }

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

    private function purchaseRatesForBarcodes(array $barcodes): array
    {
        $barcodes = array_values(array_filter($barcodes, function ($barcode) {
            return $barcode !== '' && ctype_digit((string) $barcode);
        }));
        if ($barcodes === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($barcodes), '?'));
        $rows = DB::connection('coops')->select("
            SELECT Barcode, Rate
            FROM trns_stockinout
            WHERE Stock_Type IN (1, 2)
              AND Barcode IN ($placeholders)
            ORDER BY Stock_Type DESC, Stock_Id DESC
        ", $barcodes);

        $map = [];
        foreach ($rows as $row) {
            $key = (string) $row->Barcode;
            if (!isset($map[$key])) {
                $map[$key] = $row;
            }
        }
        return $map;
    }

    private function damageStockError(array $items, string $date, int $damageId): ?string
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');

        $used = [];
        foreach ($items as $item) {
            $prodId = (int) ($item['item_id'] ?? 0);
            $barcode = trim((string) ($item['barcode'] ?? ''));
            $qty = (int) ($item['quantity'] ?? 0);
            $name = trim((string) ($item['item_name'] ?? 'Item'));
            if ($barcode === '' || !ctype_digit($barcode)) {
                return 'Scan a barcode before saving damage / wastage';
            }

            $left = 0.0;
            foreach ($this->officeBarcodes($prodId, $date, $damageId, false) as $row) {
                if ((string) $row->Barcode === $barcode) {
                    $left = (float) $row->Avil_Qnty;
                    break;
                }
            }
            $left -= $used[$barcode] ?? 0;
            if ($qty > $left + 0.0001) {
                return $name . ': quantity ' . $qty . ' is more than the stock left on barcode ' . $barcode . ' (' . max(0, $left) . ').';
            }
            $used[$barcode] = ($used[$barcode] ?? 0) + $qty;
        }

        return null;
    }

    /**
     * The damage procedure does not store a barcode. Write the barcode the user selected.
     */
    private function assignDamageBarcodes($conn, array $items, string $date, int $damageId): void
    {
        if ($damageId <= 0) {
            $row = $conn->selectOne(
                'SELECT Trading_Id
                   FROM trans_trading
                  WHERE Tranding_Type = 6
                    AND Invoice_Date = ?
                    AND Created_By = ?
                  ORDER BY Trading_Id DESC
                  LIMIT 1',
                [$date, (int) session('user_id')]
            );
            $damageId = (int) ($row->Trading_Id ?? 0);
        }
        if ($damageId <= 0) {
            throw new \RuntimeException('Damage entry was saved without a document number');
        }

        foreach ($items as $item) {
            $prodId = (int) ($item['item_id'] ?? 0);
            $barcode = trim((string) ($item['barcode'] ?? ''));
            if ($prodId <= 0 || $barcode === '' || !ctype_digit($barcode)) {
                throw new \RuntimeException('Scan a barcode before saving damage / wastage');
            }
            $mrp = round((float) ($item['mrp'] ?? 0), 2);
            if ($mrp <= 0) {
                $inward = $conn->selectOne(
                    'SELECT IFNULL(MAX(MRP), 0) AS MRP
                       FROM trns_stockinout
                      WHERE Barcode = ?
                        AND Stock_Type IN (1, 2)',
                    [$barcode]
                );
                $mrp = round((float) ($inward->MRP ?? 0), 2);
            }
            $updated = $conn->update("
                UPDATE trns_stockinout
                   SET Barcode = ?,
                       MRP = CASE WHEN IFNULL(MRP, 0) = 0 THEN ? ELSE MRP END
                 WHERE Stock_Type = 9
                   AND Type_Id = ?
                   AND Prod_Id = ?
                   AND Quantity = ?
                   AND ROUND(IFNULL(Rate, 0), 2) = ?
                   AND IFNULL(Barcode, 0) = 0
                 ORDER BY Stock_Id ASC
                 LIMIT 1
            ", [
                $barcode,
                $mrp,
                $damageId,
                $prodId,
                (int) $item['quantity'],
                round((float) $item['rate'], 2),
            ]);
            if ($updated !== 1) {
                throw new \RuntimeException('Could not store the barcode on the damage stock row');
            }
        }
    }

    private function attachDamageBarcodes(array &$items, int $damageId): void
    {
        if ($damageId <= 0 || $items === []) {
            return;
        }

        $stocks = DB::connection('coops')->select("
            SELECT Prod_Id, Barcode, Quantity, Rate, MRP
              FROM trns_stockinout
             WHERE Stock_Type = 9
               AND Type_Id = ?
             ORDER BY Stock_Id
        ", [$damageId]);

        foreach ($items as &$item) {
            $item['Barcode'] = '';
            $item['MRP'] = 0;
            foreach ($stocks as $index => $stock) {
                $sameProduct = (int) $stock->Prod_Id === (int) ($item['Prod_Id'] ?? 0);
                $sameQty = (int) $stock->Quantity === (int) round((float) ($item['qnty'] ?? 0));
                $sameRate = round((float) $stock->Rate, 2) === round((float) ($item['Item_Rate'] ?? 0), 2);
                if ($sameProduct && $sameQty && $sameRate) {
                    $item['Barcode'] = (string) ($stock->Barcode ?? '');
                    $item['MRP'] = $stock->MRP;
                    unset($stocks[$index]);
                    break;
                }
            }
        }
        unset($item);
    }
}
