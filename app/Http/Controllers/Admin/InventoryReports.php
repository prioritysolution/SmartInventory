<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class InventoryReports extends Controller
{
    public function stockSummary()
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $categories = DB::connection('coops')->select('CALL USP_GET_ITEM_CAT(?)', [session('org_id')]);

        return view('Admin.stock-summary', [
            'categories'  => $categories,
            'year_start'  => session('year_start'),
            'year_end'    => session('year_end'),
            'org_name'    => session('org_name'),
            'branch_name' => session('branch_name'),
        ])->with('pageTitle', 'Stock Summary Report');
    }

    public function stockSummarySearch(Request $request)
    {
        $asOn = $request->input('as_on_date');
        if (!$asOn) {
            return response()->json(['message' => 'Select as on date'], 422);
        }
        if ($asOn < session('year_start') || $asOn > session('year_end')) {
            return response()->json(['message' => 'Date must be within the accounting year'], 422);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        $rows = DB::connection('coops')->select('CALL USP_GET_STOCK_SUMMARY(?, ?, ?)', [
            $asOn,
            (int) $request->input('cat_id', 0),
            (int) $request->input('sub_cat_id', 0),
        ]);

        $grouped = [];
        foreach ($rows as $row) {
            $id = (int) $row->Prod_Id;
            if (!isset($grouped[$id])) {
                $grouped[$id] = [
                    'Prod_Id'       => $id,
                    'Prod_Code'     => $row->Prod_Code,
                    'Prod_ShortNm'  => $row->Prod_ShortNm,
                    'Cate_Name'     => $row->Cate_Name,
                    'Unit_Name'     => $row->Unit_Name,
                    'Counter_Qty'   => 0,
                    'Agent_Qty'     => 0,
                ];
            }
            $grouped[$id]['Counter_Qty'] = round($grouped[$id]['Counter_Qty'] + (float) $row->Qty, 2);
        }

        $catId = (int) $request->input('cat_id', 0);
        $subCatId = (int) $request->input('sub_cat_id', 0);
        $agentRows = DB::connection('coops')->select(
            "SELECT
                p.Prod_Id,
                p.Prod_Code,
                p.Prod_ShortNm,
                IFNULL(UDF_GET_CAT_SUB_NAME(p.Cate_Id, 1), '') AS Cate_Name,
                IFNULL(UDF_GET_UNIT_NAME(p.Unit_Id), '') AS Unit_Name,
                IFNULL(SUM(
                    CASE
                        WHEN s.Stock_Type = 4 THEN s.Quantity
                        WHEN s.Stock_Type = 6 THEN -s.Quantity
                        WHEN s.Stock_Type = 5 AND CAST(IFNULL(t.Is_Settle, 0) AS UNSIGNED) = 1 THEN -s.Quantity
                        ELSE 0
                    END
                ), 0) AS Agent_Qty
             FROM trns_stockinout s
             INNER JOIN mst_product p ON p.Prod_Id = s.Prod_Id
             LEFT JOIN trans_trading t
                ON t.Trading_Id = s.Type_Id
               AND s.Stock_Type = 5
             WHERE s.InOut_Date <= ?
               AND s.Stock_Type IN (4, 5, 6)
               AND (? = 0 OR p.Cate_Id = ?)
               AND (? = 0 OR p.SubCate_Id = ?)
             GROUP BY p.Prod_Id, p.Prod_Code, p.Prod_ShortNm, p.Cate_Id, p.Unit_Id",
            [$asOn, $catId, $catId, $subCatId, $subCatId]
        );

        foreach ($agentRows as $row) {
            $id = (int) $row->Prod_Id;
            $agentQty = round((float) $row->Agent_Qty, 2);
            if (!isset($grouped[$id])) {
                $grouped[$id] = [
                    'Prod_Id'      => $id,
                    'Prod_Code'    => $row->Prod_Code,
                    'Prod_ShortNm' => $row->Prod_ShortNm,
                    'Cate_Name'    => $row->Cate_Name,
                    'Unit_Name'    => $row->Unit_Name,
                    'Counter_Qty'  => 0,
                    'Agent_Qty'    => 0,
                ];
            }
            $grouped[$id]['Agent_Qty'] = $agentQty;
        }

        $products = array_values(array_filter($grouped, function ($row) {
            return abs($row['Counter_Qty']) > 0.0001 || abs($row['Agent_Qty']) > 0.0001;
        }));
        usort($products, function ($a, $b) {
            return strcmp((string) $a['Prod_ShortNm'], (string) $b['Prod_ShortNm']);
        });

        return response()->json($products);
    }

    public function stockSummaryDetails(Request $request)
    {
        $asOn = $request->input('as_on_date');
        $prodId = (int) $request->input('prod_id', 0);
        if (!$asOn || $prodId <= 0) {
            return response()->json(['message' => 'Product and date are required'], 422);
        }
        if ($asOn < session('year_start') || $asOn > session('year_end')) {
            return response()->json(['message' => 'Date must be within the accounting year'], 422);
        }

        Config::set('database.connections.coops.database', session('org_schema'));

        return response()->json($this->productBarcodeStock($prodId, $asOn));
    }

    /**
     * Remaining office stock by barcode. A movement saved without a barcode
     * is added to that MRP when only one barcode exists for it.
     */
    private function productBarcodeStock(int $prodId, string $date): array
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
                    IFNULL(SUM($signed), 0) AS Avil_Qnty
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
            ORDER BY b.MRP DESC, b.Barcode DESC
        ", [$prodId, $date, $prodId, $date, $prodId, $date]);

        $leftover = DB::connection('coops')->select("
            SELECT
                0 AS Barcode,
                u.MRP,
                u.Unbar_Qnty AS Avil_Qnty
            FROM (
                SELECT
                    CAST(IFNULL(s.MRP, 0) AS DECIMAL(10,2)) AS MRP,
                    IFNULL(SUM($signed), 0) AS Unbar_Qnty
                FROM trns_stockinout s
                WHERE s.Prod_Id = ?
                  AND s.InOut_Date <= ?
                  AND IFNULL(s.Barcode, 0) = 0
                GROUP BY CAST(IFNULL(s.MRP, 0) AS DECIMAL(10,2))
            ) u
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
            ) c ON c.MRP = u.MRP
            WHERE IFNULL(c.Barcode_Count, 0) <> 1
            ORDER BY u.MRP DESC
        ", [$prodId, $date, $prodId, $date]);

        $all = array_merge($rows, $leftover);

        return array_values(array_filter($all, function ($row) {
            return (float) $row->Avil_Qnty > 0.0001;
        }));
    }

    public function stockSummarySubCats(Request $request)
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $subs = DB::connection('coops')->select('CALL USP_GET_ITEM_SUB_CAT(?, ?)', [
            session('org_id'),
            (int) $request->input('cat_id', 0),
        ]);

        return response()->json($subs);
    }

    public function purchaseRegister()
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $parties = DB::connection('coops')->select('CALL USP_GET_PARTY_LIST(?, ?, ?)', [
            1,
            session('branch_id') ?: 0,
            0,
        ]);

        return view('Admin.purchase-register', [
            'parties'     => $parties,
            'year_start'  => session('year_start'),
            'year_end'    => session('year_end'),
            'org_name'    => session('org_name'),
            'branch_name' => session('branch_name'),
        ])->with('pageTitle', 'Purchase Register');
    }

    public function purchaseRegisterSearch(Request $request)
    {
        $frm = $request->input('frm_date');
        $to = $request->input('to_date');
        if (!$frm || !$to) {
            return response()->json(['message' => 'Select from date and to date'], 422);
        }
        if ($frm > $to) {
            return response()->json(['message' => 'From date cannot be after to date'], 422);
        }
        if ($frm < session('year_start') || $to > session('year_end')) {
            return response()->json(['message' => 'Date must be within the accounting year'], 422);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        $partyId = (int) $request->input('party_id', 0);
        $branchId = (int) (session('branch_id') ?: 0);
        $gstFilter = (int) $request->input('gst_filter', 0);

        $rows = DB::connection('coops')->select(
            "SELECT
                m.Trading_Id,
                m.Invoice_No,
                m.Invoice_Date,
                CASE IFNULL(m.Pay_Mode, 1)
                    WHEN 1 THEN 'Cash'
                    WHEN 2 THEN 'Bank'
                    WHEN 3 THEN 'Credit'
                    ELSE ''
                END AS Pay_Mode,
                IFNULL(pt.Party_Name, '') AS Party_Name,
                '' AS Agent_Name,
                IFNULL(i.Prod_ShortNm, '') AS Item_Name,
                IFNULL(tp.Gst_Code, '') AS HSN_No,
                IFNULL(tp.Item_Qty, 0) AS Item_Qty,
                IFNULL(UDF_GET_UNIT_NAME(tp.Unit_Id), '') AS Unit_Name,
                IFNULL(tp.Item_Rate, 0) AS Item_Rate,
                IFNULL(tp.Item_Total, 0) AS Item_Total,
                (IFNULL(tp.SGST_Prcnt, 0) + IFNULL(tp.CGST_Prcnt, 0)) AS GST_Prcnt,
                (IFNULL(tp.SGST_Amt, 0) + IFNULL(tp.CGST_Amt, 0)) AS GST_Amt,
                IFNULL(m.Round_Off, 0) AS Round_Off,
                IFNULL(tp.Net_Amt, 0) AS Net_Amt
             FROM trans_trading m
             INNER JOIN trans_trading_products tp ON tp.Trading_Id = m.Trading_Id
             INNER JOIN mst_product i ON i.Prod_Id = tp.Prod_Id
             LEFT JOIN mst_party pt ON pt.Party_Id = m.Party_Id
             WHERE m.Tranding_Type = 2
               AND IFNULL(m.Status_Cd, 1) = 1
               AND m.Invoice_Date BETWEEN ? AND ?
               AND (? = 0 OR m.Party_Id = ?)
               AND (? = 0 OR m.Branch_Id = ?)
               AND (
                    ? = 0
                    OR (? = 1 AND IFNULL(TRIM(pt.GstIn), '') <> '')
                    OR (? = 2 AND IFNULL(TRIM(pt.GstIn), '') = '')
               )
             ORDER BY m.Invoice_Date, m.Trading_Id, tp.Sl",
            [$frm, $to, $partyId, $partyId, $branchId, $branchId, $gstFilter, $gstFilter, $gstFilter]
        );

        return response()->json($rows);
    }

    public function itemwisePurchase()
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $parties = DB::connection('coops')->select('CALL USP_GET_PARTY_LIST(?, ?, ?)', [
            1,
            session('branch_id') ?: 0,
            0,
        ]);
        $categories = DB::connection('coops')->select('CALL USP_GET_ITEM_CAT(?)', [session('org_id')]);

        return view('Admin.itemwise-purchase', [
            'parties'     => $parties,
            'categories'  => $categories,
            'year_start'  => session('year_start'),
            'year_end'    => session('year_end'),
            'org_name'    => session('org_name'),
            'branch_name' => session('branch_name'),
        ])->with('pageTitle', 'Item Wise Purchase Report');
    }

    public function itemwisePurchaseSearch(Request $request)
    {
        $frm = $request->input('frm_date');
        $to = $request->input('to_date');
        if (!$frm || !$to) {
            return response()->json(['message' => 'Select from date and to date'], 422);
        }
        if ($frm > $to) {
            return response()->json(['message' => 'From date cannot be after to date'], 422);
        }
        if ($frm < session('year_start') || $to > session('year_end')) {
            return response()->json(['message' => 'Date must be within the accounting year'], 422);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        $partyId = (int) $request->input('party_id', 0);
        $branchId = (int) (session('branch_id') ?: 0);
        $catId = (int) $request->input('cat_id', 0);
        $subCatId = (int) $request->input('sub_cat_id', 0);

        $rows = DB::connection('coops')->select(
            "SELECT
                i.Prod_Code,
                i.Prod_ShortNm,
                IFNULL(c.Prd_CateNm, '') AS Cate_Name,
                IFNULL(UDF_GET_UNIT_NAME(p.Unit_Id), '') AS Unit_Name,
                SUM(IFNULL(p.Item_Qty, 0)) AS Qty,
                CASE
                    WHEN SUM(IFNULL(p.Item_Qty, 0)) = 0 THEN 0
                    ELSE ROUND(SUM(IFNULL(p.Item_Total, 0)) / SUM(p.Item_Qty), 2)
                END AS Item_Rate,
                SUM(IFNULL(p.Taxable_Amt, 0)) AS Taxable_Amt,
                SUM(IFNULL(p.SGST_Amt, 0) + IFNULL(p.CGST_Amt, 0)) AS GST_Amt,
                SUM(IFNULL(p.Net_Amt, 0)) AS Net_Amt
            FROM trans_trading m
            INNER JOIN trans_trading_products p ON p.Trading_Id = m.Trading_Id
            INNER JOIN mst_product i ON i.Prod_Id = p.Prod_Id
            LEFT JOIN mst_prod_category c ON c.Prd_CateId = i.Cate_Id
            WHERE m.Tranding_Type = 2
              AND IFNULL(m.Status_Cd, 1) = 1
              AND m.Invoice_Date BETWEEN ? AND ?
              AND (? = 0 OR m.Party_Id = ?)
              AND (? = 0 OR m.Branch_Id = ?)
              AND (? = 0 OR i.Cate_Id = ?)
              AND (? = 0 OR i.SubCate_Id = ?)
            GROUP BY i.Prod_Id, i.Prod_Code, i.Prod_ShortNm, c.Prd_CateNm, p.Unit_Id
            ORDER BY i.Prod_ShortNm, i.Prod_Code",
            [$frm, $to, $partyId, $partyId, $branchId, $branchId, $catId, $catId, $subCatId, $subCatId]
        );

        return response()->json($rows);
    }

    public function salesRegister()
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $categories = DB::connection('coops')->select('CALL USP_GET_ITEM_CAT(?)', [session('org_id')]);
        return view('Admin.sales-register', [
            'categories'  => $categories,
            'year_start'  => session('year_start'),
            'year_end'    => session('year_end'),
            'org_name'    => session('org_name'),
            'branch_name' => session('branch_name'),
        ])->with('pageTitle', 'Sales Register');
    }

    public function salesRegisterSearch(Request $request)
    {
        $frm = $request->input('frm_date');
        $to = $request->input('to_date');
        if (!$frm || !$to) {
            return response()->json(['message' => 'Select from date and to date'], 422);
        }
        if ($frm > $to) {
            return response()->json(['message' => 'From date cannot be after to date'], 422);
        }
        if ($frm < session('year_start') || $to > session('year_end')) {
            return response()->json(['message' => 'Date must be within the accounting year'], 422);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        $mode = (int) $request->input('mode', 0);
        $branchId = (int) (session('branch_id') ?: 0);
        $catId = (int) $request->input('cat_id', 0);

        $rows = DB::connection('coops')->select(
            "SELECT
                m.Trading_Id,
                m.Invoice_No,
                m.Invoice_Date,
                CASE IFNULL(m.Pay_Mode, 1)
                    WHEN 1 THEN 'Cash'
                    WHEN 2 THEN 'Bank'
                    WHEN 3 THEN 'Credit'
                    ELSE ''
                END AS Pay_Mode,
                CASE WHEN m.Agent_Id IS NULL THEN 'Counter' ELSE 'Agent' END AS Sale_Type,
                IFNULL(p.Party_Name, '') AS Party_Name,
                IFNULL(a.Agent_Name, '') AS Agent_Name,
                IFNULL(i.Prod_ShortNm, '') AS Item_Name,
                IFNULL(tp.Gst_Code, '') AS HSN_No,
                IFNULL(tp.Item_Qty, 0) AS Item_Qty,
                IFNULL(UDF_GET_UNIT_NAME(tp.Unit_Id), '') AS Unit_Name,
                IFNULL(tp.Item_Rate, 0) AS Item_Rate,
                IFNULL(tp.Item_Total, 0) AS Item_Total,
                IFNULL(tp.Taxable_Amt, 0) AS Taxable_Amt,
                (IFNULL(tp.SGST_Prcnt, 0) + IFNULL(tp.CGST_Prcnt, 0)) AS GST_Prcnt,
                (IFNULL(tp.SGST_Amt, 0) + IFNULL(tp.CGST_Amt, 0)) AS GST_Amt,
                IFNULL(m.Round_Off, 0) AS Round_Off,
                IFNULL(tp.Net_Amt, 0) AS Net_Amt
             FROM trans_trading m
             INNER JOIN trans_trading_products tp ON tp.Trading_Id = m.Trading_Id
             INNER JOIN mst_product i ON i.Prod_Id = tp.Prod_Id
             LEFT JOIN mst_party p ON p.Party_Id = m.Party_Id
             LEFT JOIN mst_agent a ON a.Agent_Id = m.Agent_Id
             WHERE m.Tranding_Type = 3
               AND IFNULL(m.Status_Cd, 1) = 1
               AND m.Invoice_Date BETWEEN ? AND ?
               AND (? = 0 OR m.Branch_Id = ?)
               AND (
                    ? = 0
                    OR (? = 1 AND m.Agent_Id IS NULL)
                    OR (? = 2 AND m.Agent_Id IS NOT NULL)
               )
               AND (? = 0 OR i.Cate_Id = ?)
             ORDER BY m.Invoice_Date, m.Trading_Id, tp.Sl",
            [$frm, $to, $branchId, $branchId, $mode, $mode, $mode, $catId, $catId]
        );

        return response()->json($rows);
    }

    public function agentRegister()
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $pdo = DB::connection('coops')->getPdo();
        $stmt = $pdo->prepare('CALL USP_GET_AGENT_LIST(?, ?, ?, ?)');
        $stmt->execute([session('branch_id'), '', 1, 0]);
        $agents = $stmt->fetchAll(\PDO::FETCH_OBJ);
        $stmt->closeCursor();

        return view('Admin.agent-register', [
            'agents'      => $agents,
            'year_start'  => session('year_start'),
            'year_end'    => session('year_end'),
            'org_name'    => session('org_name'),
            'branch_name' => session('branch_name'),
        ])->with('pageTitle', 'Agent Register');
    }

    public function agentRegisterSearch(Request $request)
    {
        $mode = (int) $request->input('mode', 1);
        Config::set('database.connections.coops.database', session('org_schema'));

        if ($mode === 2) {
            $asOn = $request->input('as_on_date');
            if (!$asOn) {
                return response()->json(['message' => 'Select as on date'], 422);
            }
            if ($asOn < session('year_start') || $asOn > session('year_end')) {
                return response()->json(['message' => 'Date must be within the accounting year'], 422);
            }
            $rows = DB::connection('coops')->select('CALL USP_RPT_AGENT_STOCK(?, ?)', [
                $asOn,
                (int) $request->input('agent_id', 0),
            ]);
            return response()->json($rows);
        }

        $frm = $request->input('frm_date');
        $to = $request->input('to_date');
        if (!$frm || !$to) {
            return response()->json(['message' => 'Select from date and to date'], 422);
        }
        if ($frm > $to) {
            return response()->json(['message' => 'From date cannot be after to date'], 422);
        }
        if ($frm < session('year_start') || $to > session('year_end')) {
            return response()->json(['message' => 'Date must be within the accounting year'], 422);
        }

        $rows = DB::connection('coops')->select('CALL USP_RPT_AGENT_INDENT(?, ?, ?)', [
            $frm,
            $to,
            (int) $request->input('agent_id', 0),
        ]);

        return response()->json($rows);
    }

    public function returnRegister()
    {
        return view('Admin.return-register', [
            'year_start'  => session('year_start'),
            'year_end'    => session('year_end'),
            'org_name'    => session('org_name'),
            'branch_name' => session('branch_name'),
        ])->with('pageTitle', 'Return Register');
    }

    public function returnRegisterSearch(Request $request)
    {
        $frm = $request->input('frm_date');
        $to = $request->input('to_date');
        if (!$frm || !$to) {
            return response()->json(['message' => 'Select from date and to date'], 422);
        }
        if ($frm > $to) {
            return response()->json(['message' => 'From date cannot be after to date'], 422);
        }
        if ($frm < session('year_start') || $to > session('year_end')) {
            return response()->json(['message' => 'Date must be within the accounting year'], 422);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        $rows = DB::connection('coops')->select('CALL USP_RPT_RETURN_REGISTER(?, ?, ?, ?)', [
            $frm,
            $to,
            (int) $request->input('mode', 0),
            (int) (session('branch_id') ?: 0),
        ]);

        return response()->json($rows);
    }

    public function supplierRegister()
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $branchId = (int) (session('branch_id') ?: 0);
        $parties = DB::connection('coops')->select('CALL USP_GET_PARTY_LIST(?, ?, ?)', [1, $branchId, 0]);

        return view('Admin.supplier-register', [
            'year_start'  => session('year_start'),
            'year_end'    => session('year_end'),
            'org_name'    => session('org_name'),
            'branch_name' => session('branch_name'),
            'parties'     => $parties,
        ])->with('pageTitle', 'Supplier Register');
    }

    public function supplierRegisterSearch(Request $request)
    {
        $mode = (int) $request->input('mode', 1);
        $frm = $request->input('frm_date');
        $to = $request->input('to_date');
        if (!$frm || !$to) {
            return response()->json(['message' => 'Select from date and to date'], 422);
        }
        if ($frm > $to) {
            return response()->json(['message' => 'From date cannot be after to date'], 422);
        }
        if ($frm < session('year_start') || $to > session('year_end')) {
            return response()->json(['message' => 'Date must be within the accounting year'], 422);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        $branchId = (int) (session('branch_id') ?: 0);

        if ($mode === 2) {
            $partyId = (int) $request->input('party_id', 0);
            if ($partyId <= 0) {
                return response()->json(['message' => 'Select a supplier for ledger report'], 422);
            }
            $rows = DB::connection('coops')->select('CALL USP_RPT_PARTY_LEDGER(?, ?, ?, ?, ?)', [
                $frm,
                $to,
                $branchId,
                1,
                $partyId,
            ]);
            return response()->json($rows);
        }

        $rows = DB::connection('coops')->select('CALL USP_RPT_SUPPLIER_REGISTER(?, ?, ?)', [
            $frm,
            $to,
            $branchId,
        ]);

        return response()->json($rows);
    }

    public function customerRegister()
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $branchId = (int) (session('branch_id') ?: 0);
        $parties = DB::connection('coops')->select('CALL USP_GET_PARTY_LIST(?, ?, ?)', [2, $branchId, 0]);

        return view('Admin.customer-register', [
            'year_start'  => session('year_start'),
            'year_end'    => session('year_end'),
            'org_name'    => session('org_name'),
            'branch_name' => session('branch_name'),
            'parties'     => $parties,
        ])->with('pageTitle', 'Customer Register');
    }

    public function customerRegisterSearch(Request $request)
    {
        $mode = (int) $request->input('mode', 1);
        $frm = $request->input('frm_date');
        $to = $request->input('to_date');
        if (!$frm || !$to) {
            return response()->json(['message' => 'Select from date and to date'], 422);
        }
        if ($frm > $to) {
            return response()->json(['message' => 'From date cannot be after to date'], 422);
        }
        if ($frm < session('year_start') || $to > session('year_end')) {
            return response()->json(['message' => 'Date must be within the accounting year'], 422);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        $branchId = (int) (session('branch_id') ?: 0);

        if ($mode === 2) {
            $partyId = (int) $request->input('party_id', 0);
            if ($partyId <= 0) {
                return response()->json(['message' => 'Select a customer for ledger report'], 422);
            }
            $rows = DB::connection('coops')->select('CALL USP_RPT_PARTY_LEDGER(?, ?, ?, ?, ?)', [
                $frm,
                $to,
                $branchId,
                2,
                $partyId,
            ]);
            return response()->json($rows);
        }

        $rows = DB::connection('coops')->select('CALL USP_RPT_CUSTOMER_REGISTER(?, ?, ?)', [
            $frm,
            $to,
            $branchId,
        ]);

        return response()->json($rows);
    }

    public function shareRegister()
    {
        return view('Admin.share-register', [
            'year_start'  => session('year_start'),
            'year_end'    => session('year_end'),
            'org_name'    => session('org_name'),
            'branch_name' => session('branch_name'),
        ])->with('pageTitle', 'Share Register');
    }

    public function shareRegisterSearch(Request $request)
    {
        $frm = $request->input('frm_date');
        $to = $request->input('to_date');
        if (!$frm || !$to) {
            return response()->json(['message' => 'Select from date and to date'], 422);
        }
        if ($frm > $to) {
            return response()->json(['message' => 'From date cannot be after to date'], 422);
        }
        if ($frm < session('year_start') || $to > session('year_end')) {
            return response()->json(['message' => 'Date must be within the accounting year'], 422);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        $rows = DB::connection('coops')->select('CALL USP_RPT_SHARE_REGISTER(?, ?)', [$frm, $to]);

        return response()->json($rows);
    }

    public function gstRegister()
    {
        return view('Admin.gst-register', [
            'year_start'  => session('year_start'),
            'year_end'    => session('year_end'),
            'org_name'    => session('org_name'),
            'branch_name' => session('branch_name'),
        ])->with('pageTitle', 'GST Register');
    }

    public function gstRegisterSearch(Request $request)
    {
        $frm = $request->input('frm_date');
        $to = $request->input('to_date');
        if (!$frm || !$to) {
            return response()->json(['message' => 'Select from date and to date'], 422);
        }
        if ($frm > $to) {
            return response()->json(['message' => 'From date cannot be after to date'], 422);
        }
        if ($frm < session('year_start') || $to > session('year_end')) {
            return response()->json(['message' => 'Date must be within the accounting year'], 422);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        $rows = DB::connection('coops')->select('CALL USP_RPT_GST_REGISTER(?, ?, ?)', [
            $frm,
            $to,
            (int) (session('branch_id') ?: 0),
        ]);

        return response()->json($rows);
    }

    public function stockStatement()
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $categories = DB::connection('coops')->select('CALL USP_GET_ITEM_CAT(?)', [session('org_id')]);

        return view('Admin.stock-statement', [
            'categories'  => $categories,
            'year_start'  => session('year_start'),
            'year_end'    => session('year_end'),
            'org_name'    => session('org_name'),
            'branch_name' => session('branch_name'),
        ])->with('pageTitle', 'Stock Statement & Register');
    }

    public function stockStatementSearch(Request $request)
    {
        $frm = $request->input('frm_date');
        $to = $request->input('to_date');
        if (!$frm || !$to) {
            return response()->json(['message' => 'Select from date and to date'], 422);
        }
        if ($frm > $to) {
            return response()->json(['message' => 'From date cannot be after to date'], 422);
        }
        if ($frm < session('year_start') || $to > session('year_end')) {
            return response()->json(['message' => 'Date must be within the accounting year'], 422);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        $catId = (int) $request->input('cat_id', 0);
        $subCatId = (int) $request->input('sub_cat_id', 0);
        $signed = $this->officeSignedQtySql();
        $lineValue = 's.Quantity * (CASE WHEN IFNULL(s.MRP, 0) > 0 THEN s.MRP ELSE IFNULL(s.Rate, 0) END)';
        $soldInPeriod = "
            s.InOut_Date BETWEEN ? AND ?
            AND (
                s.Stock_Type = 3
                OR (
                    s.Stock_Type = 5
                    AND CAST(IFNULL(t.Is_Settle, 0) AS UNSIGNED) = 1
                    AND (t.Settle_Date IS NULL OR t.Settle_Date <= ?)
                )
            )
        ";

        $rows = DB::connection('coops')->select("
            SELECT
                p.Prod_Id,
                p.Prod_Code,
                p.Prod_ShortNm,
                IFNULL(UDF_GET_CAT_SUB_NAME(p.Cate_Id, 1), '') AS Cate_Name,
                IFNULL(UDF_GET_UNIT_NAME(p.Unit_Id), '') AS Unit_Name,
                x.Opening_Qty,
                x.Purchase_Qty,
                x.Purchase_Value,
                x.Purchase_Return_Qty,
                x.Purchase_Return_Value,
                x.Sold_Qty,
                x.Sold_Value,
                x.Sold_Return_Qty,
                x.Sold_Return_Value,
                x.Damage_Qty,
                x.Damage_Value
            FROM (
                SELECT
                    s.Prod_Id,
                    IFNULL(SUM(CASE
                        WHEN s.InOut_Date < ? THEN $signed
                        WHEN s.Stock_Type = 1 AND s.InOut_Date BETWEEN ? AND ? THEN s.Quantity
                        ELSE 0
                    END), 0) AS Opening_Qty,
                    IFNULL(SUM(CASE
                        WHEN s.InOut_Date BETWEEN ? AND ? AND s.Stock_Type = 2 THEN s.Quantity
                        ELSE 0
                    END), 0) AS Purchase_Qty,
                    IFNULL(SUM(CASE
                        WHEN s.InOut_Date BETWEEN ? AND ? AND s.Stock_Type = 2 THEN $lineValue
                        ELSE 0
                    END), 0) AS Purchase_Value,
                    IFNULL(SUM(CASE
                        WHEN s.InOut_Date BETWEEN ? AND ? AND s.Stock_Type = 7 THEN s.Quantity
                        ELSE 0
                    END), 0) AS Purchase_Return_Qty,
                    IFNULL(SUM(CASE
                        WHEN s.InOut_Date BETWEEN ? AND ? AND s.Stock_Type = 7 THEN $lineValue
                        ELSE 0
                    END), 0) AS Purchase_Return_Value,
                    IFNULL(SUM(CASE
                        WHEN $soldInPeriod THEN s.Quantity
                        ELSE 0
                    END), 0) AS Sold_Qty,
                    IFNULL(SUM(CASE
                        WHEN $soldInPeriod THEN $lineValue
                        ELSE 0
                    END), 0) AS Sold_Value,
                    IFNULL(SUM(CASE
                        WHEN s.InOut_Date BETWEEN ? AND ? AND s.Stock_Type = 8 THEN s.Quantity
                        ELSE 0
                    END), 0) AS Sold_Return_Qty,
                    IFNULL(SUM(CASE
                        WHEN s.InOut_Date BETWEEN ? AND ? AND s.Stock_Type = 8 THEN $lineValue
                        ELSE 0
                    END), 0) AS Sold_Return_Value,
                    IFNULL(SUM(CASE
                        WHEN s.InOut_Date BETWEEN ? AND ? AND s.Stock_Type = 9 THEN s.Quantity
                        ELSE 0
                    END), 0) AS Damage_Qty,
                    IFNULL(SUM(CASE
                        WHEN s.InOut_Date BETWEEN ? AND ? AND s.Stock_Type = 9 THEN $lineValue
                        ELSE 0
                    END), 0) AS Damage_Value
                FROM trns_stockinout s
                LEFT JOIN trans_trading t
                    ON t.Trading_Id = s.Type_Id
                   AND s.Stock_Type = 5
                WHERE s.InOut_Date <= ?
                GROUP BY s.Prod_Id
            ) x
            INNER JOIN mst_product p ON p.Prod_Id = x.Prod_Id
            WHERE (? = 0 OR p.Cate_Id = ?)
              AND (? = 0 OR p.SubCate_Id = ?)
            ORDER BY p.Prod_ShortNm, p.Prod_Code
        ", [
            $frm, $frm, $to,
            $frm, $to, $frm, $to,
            $frm, $to, $frm, $to,
            $frm, $to, $to, $frm, $to, $to,
            $frm, $to, $frm, $to,
            $frm, $to, $frm, $to,
            $to,
            $catId, $catId, $subCatId, $subCatId,
        ]);

        $closing = [];
        foreach ($this->closingBarcodeStock($to, 0) as $line) {
            $id = (int) $line->Prod_Id;
            if (!isset($closing[$id])) {
                $closing[$id] = ['qty' => 0, 'value' => 0];
            }
            $qty = (float) $line->Avil_Qnty;
            $closing[$id]['qty'] = round($closing[$id]['qty'] + $qty, 2);
            $closing[$id]['value'] = round($closing[$id]['value'] + round($qty * (float) $line->MRP, 2), 2);
        }

        $agentValue = 's.Quantity * (CASE WHEN IFNULL(s.MRP, 0) > 0 THEN s.MRP ELSE IFNULL(s.Rate, 0) END)';
        $agentRows = DB::connection('coops')->select("
            SELECT
                s.Prod_Id,
                IFNULL(SUM(CASE
                    WHEN s.Stock_Type = 4 THEN s.Quantity
                    WHEN s.Stock_Type = 6 THEN -s.Quantity
                    WHEN s.Stock_Type = 5
                     AND CAST(IFNULL(t.Is_Settle, 0) AS UNSIGNED) = 1
                     AND (t.Settle_Date IS NULL OR t.Settle_Date <= ?) THEN -s.Quantity
                    ELSE 0
                END), 0) AS Agent_Qty,
                IFNULL(SUM(CASE
                    WHEN s.Stock_Type = 4 THEN $agentValue
                    WHEN s.Stock_Type = 6 THEN -$agentValue
                    WHEN s.Stock_Type = 5
                     AND CAST(IFNULL(t.Is_Settle, 0) AS UNSIGNED) = 1
                     AND (t.Settle_Date IS NULL OR t.Settle_Date <= ?) THEN -$agentValue
                    ELSE 0
                END), 0) AS Agent_Value
            FROM trns_stockinout s
            INNER JOIN mst_product p ON p.Prod_Id = s.Prod_Id
            LEFT JOIN trans_trading t
                ON t.Trading_Id = s.Type_Id
               AND s.Stock_Type = 5
            WHERE s.InOut_Date <= ?
              AND s.Stock_Type IN (4, 5, 6)
              AND (? = 0 OR p.Cate_Id = ?)
              AND (? = 0 OR p.SubCate_Id = ?)
            GROUP BY s.Prod_Id
        ", [$to, $to, $to, $catId, $catId, $subCatId, $subCatId]);
        $agents = [];
        foreach ($agentRows as $agent) {
            $agents[(int) $agent->Prod_Id] = [
                'qty'   => round((float) $agent->Agent_Qty, 2),
                'value' => round((float) $agent->Agent_Value, 2),
            ];
        }

        $products = [];
        foreach ($rows as $row) {
            $id = (int) $row->Prod_Id;
            $opening = round((float) $row->Opening_Qty, 2);
            $purchaseQty = round((float) $row->Purchase_Qty, 2);
            $purchaseValue = round((float) $row->Purchase_Value, 2);
            $purchaseReturnQty = round((float) $row->Purchase_Return_Qty, 2);
            $purchaseReturnValue = round((float) $row->Purchase_Return_Value, 2);
            $soldQty = round((float) $row->Sold_Qty, 2);
            $soldValue = round((float) $row->Sold_Value, 2);
            $soldReturnQty = round((float) $row->Sold_Return_Qty, 2);
            $soldReturnValue = round((float) $row->Sold_Return_Value, 2);
            $damageQty = round((float) $row->Damage_Qty, 2);
            $damageValue = round((float) $row->Damage_Value, 2);
            $agentQty = $agents[$id]['qty'] ?? 0;
            $agentValueAmt = $agents[$id]['value'] ?? 0;
            $available = $closing[$id]['qty'] ?? 0;
            $value = $closing[$id]['value'] ?? 0;
            if (
                abs($opening) < 0.0001 && abs($purchaseQty) < 0.0001 && abs($purchaseReturnQty) < 0.0001
                && abs($soldQty) < 0.0001 && abs($soldReturnQty) < 0.0001 && abs($damageQty) < 0.0001
                && abs($agentQty) < 0.0001 && abs($available) < 0.0001
            ) {
                continue;
            }
            $products[] = [
                'Prod_Id'                 => $id,
                'Prod_ShortNm'            => $row->Prod_ShortNm,
                'Opening_Qty'             => $opening,
                'Purchase_Qty'            => $purchaseQty,
                'Purchase_Value'          => $purchaseValue,
                'Purchase_Return_Qty'     => $purchaseReturnQty,
                'Purchase_Return_Value'   => $purchaseReturnValue,
                'Sold_Qty'                => $soldQty,
                'Sold_Value'              => $soldValue,
                'Sold_Return_Qty'         => $soldReturnQty,
                'Sold_Return_Value'       => $soldReturnValue,
                'Damage_Qty'              => $damageQty,
                'Damage_Value'            => $damageValue,
                'Agent_Qty'               => $agentQty,
                'Agent_Value'             => $agentValueAmt,
                'Available'               => $available,
                'Value'                   => $value,
            ];
        }

        return response()->json($products);
    }

    public function stockStatementDetails(Request $request)
    {
        $to = $request->input('to_date');
        $prodId = (int) $request->input('prod_id', 0);
        if (!$to || $prodId <= 0) {
            return response()->json(['message' => 'Product and to date are required'], 422);
        }
        if ($to < session('year_start') || $to > session('year_end')) {
            return response()->json(['message' => 'Date must be within the accounting year'], 422);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        $product = DB::connection('coops')->selectOne(
            'SELECT Prod_ShortNm FROM mst_product WHERE Prod_Id = ?',
            [$prodId]
        );
        $name = $product->Prod_ShortNm ?? '';

        $lines = [];
        foreach ($this->closingBarcodeStock($to, $prodId) as $line) {
            $qty = round((float) $line->Avil_Qnty, 2);
            $lines[] = [
                'Barcode'      => $line->Barcode,
                'Prod_ShortNm' => $name,
                'MRP'          => $line->MRP,
                'Avil_Qnty'    => $qty,
                'Value'        => round($qty * (float) $line->MRP, 2),
            ];
        }

        return response()->json($lines);
    }

    private function officeSignedQtySql(): string
    {
        return "
            CASE
                WHEN s.Stock_Type IN (1, 2, 6) THEN s.Quantity
                WHEN s.Stock_Type = 8 AND NOT EXISTS (
                    SELECT 1 FROM trans_agent_stock a WHERE a.TrnsStock_Id = s.Stock_Id
                ) THEN s.Quantity
                WHEN s.Stock_Type IN (3, 4, 7, 9) THEN -s.Quantity
                ELSE 0
            END
        ";
    }

    /**
     * Office stock left on each barcode as on a date. $prodId 0 returns every product.
     */
    private function closingBarcodeStock(string $date, int $prodId): array
    {
        $signed = $this->officeSignedQtySql();
        $rows = DB::connection('coops')->select("
            SELECT
                b.Prod_Id,
                b.Barcode,
                b.MRP,
                (b.Avil_Qnty + CASE WHEN c.Barcode_Count = 1 THEN IFNULL(u.Unbar_Qnty, 0) ELSE 0 END) AS Avil_Qnty
            FROM (
                SELECT
                    s.Prod_Id,
                    s.Barcode AS Barcode,
                    CAST(IFNULL(MAX(CASE WHEN s.Stock_Type IN (1, 2) THEN s.MRP END), MAX(s.MRP)) AS DECIMAL(10,2)) AS MRP,
                    IFNULL(SUM($signed), 0) AS Avil_Qnty
                FROM trns_stockinout s
                WHERE s.InOut_Date <= ?
                  AND (? = 0 OR s.Prod_Id = ?)
                  AND IFNULL(s.Barcode, 0) <> 0
                GROUP BY s.Prod_Id, s.Barcode
            ) b
            LEFT JOIN (
                SELECT Prod_Id, MRP, COUNT(*) AS Barcode_Count
                FROM (
                    SELECT
                        s.Prod_Id,
                        s.Barcode,
                        CAST(IFNULL(MAX(CASE WHEN s.Stock_Type IN (1, 2) THEN s.MRP END), MAX(s.MRP)) AS DECIMAL(10,2)) AS MRP
                    FROM trns_stockinout s
                    WHERE s.InOut_Date <= ?
                      AND (? = 0 OR s.Prod_Id = ?)
                      AND IFNULL(s.Barcode, 0) <> 0
                    GROUP BY s.Prod_Id, s.Barcode
                ) m
                GROUP BY Prod_Id, MRP
            ) c ON c.Prod_Id = b.Prod_Id AND c.MRP = b.MRP
            LEFT JOIN (
                SELECT
                    s.Prod_Id,
                    CAST(IFNULL(s.MRP, 0) AS DECIMAL(10,2)) AS MRP,
                    IFNULL(SUM($signed), 0) AS Unbar_Qnty
                FROM trns_stockinout s
                WHERE s.InOut_Date <= ?
                  AND (? = 0 OR s.Prod_Id = ?)
                  AND IFNULL(s.Barcode, 0) = 0
                GROUP BY s.Prod_Id, CAST(IFNULL(s.MRP, 0) AS DECIMAL(10,2))
            ) u ON u.Prod_Id = b.Prod_Id AND u.MRP = b.MRP
            ORDER BY b.MRP DESC, b.Barcode DESC
        ", [$date, $prodId, $prodId, $date, $prodId, $prodId, $date, $prodId, $prodId]);

        $leftover = DB::connection('coops')->select("
            SELECT
                u.Prod_Id,
                0 AS Barcode,
                u.MRP,
                u.Unbar_Qnty AS Avil_Qnty
            FROM (
                SELECT
                    s.Prod_Id,
                    CAST(IFNULL(s.MRP, 0) AS DECIMAL(10,2)) AS MRP,
                    IFNULL(SUM($signed), 0) AS Unbar_Qnty
                FROM trns_stockinout s
                WHERE s.InOut_Date <= ?
                  AND (? = 0 OR s.Prod_Id = ?)
                  AND IFNULL(s.Barcode, 0) = 0
                GROUP BY s.Prod_Id, CAST(IFNULL(s.MRP, 0) AS DECIMAL(10,2))
            ) u
            LEFT JOIN (
                SELECT Prod_Id, MRP, COUNT(*) AS Barcode_Count
                FROM (
                    SELECT
                        s.Prod_Id,
                        s.Barcode,
                        CAST(IFNULL(MAX(CASE WHEN s.Stock_Type IN (1, 2) THEN s.MRP END), MAX(s.MRP)) AS DECIMAL(10,2)) AS MRP
                    FROM trns_stockinout s
                    WHERE s.InOut_Date <= ?
                      AND (? = 0 OR s.Prod_Id = ?)
                      AND IFNULL(s.Barcode, 0) <> 0
                    GROUP BY s.Prod_Id, s.Barcode
                ) m
                GROUP BY Prod_Id, MRP
            ) c ON c.Prod_Id = u.Prod_Id AND c.MRP = u.MRP
            WHERE IFNULL(c.Barcode_Count, 0) <> 1
            ORDER BY u.MRP DESC
        ", [$date, $prodId, $prodId, $date, $prodId, $prodId]);

        $all = array_merge($rows, $leftover);

        return array_values(array_filter($all, function ($row) {
            return (float) $row->Avil_Qnty > 0.0001;
        }));
    }

    public function expiryReport()
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $categories = DB::connection('coops')->select('CALL USP_GET_ITEM_CAT(?)', [session('org_id')]);

        return view('Admin.expiry-report', [
            'categories'  => $categories,
            'year_start'  => session('year_start'),
            'year_end'    => session('year_end'),
            'org_name'    => session('org_name'),
            'branch_name' => session('branch_name'),
        ])->with('pageTitle', 'Expiry Report');
    }

    public function expiryReportSearch(Request $request)
    {
        $asOn = $request->input('as_on_date');
        if (!$asOn) {
            return response()->json(['message' => 'Select as on date'], 422);
        }
        if ($asOn < session('year_start') || $asOn > session('year_end')) {
            return response()->json(['message' => 'Date must be within the accounting year'], 422);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        $rows = DB::connection('coops')->select('CALL USP_RPT_EXPIRY(?, ?, ?)', [
            $asOn,
            (int) $request->input('cat_id', 0),
            (int) $request->input('sub_cat_id', 0),
        ]);

        return response()->json($rows);
    }

    public function reorderReport()
    {
        return view('Admin.reorder-report', [
            'year_start'  => session('year_start'),
            'year_end'    => session('year_end'),
            'org_name'    => session('org_name'),
            'branch_name' => session('branch_name'),
        ])->with('pageTitle', 'Reorder Level Report');
    }

    public function reorderReportSearch(Request $request)
    {
        $asOn = $request->input('as_on_date');
        if (!$asOn) {
            return response()->json(['message' => 'Select as on date'], 422);
        }
        if ($asOn < session('year_start') || $asOn > session('year_end')) {
            return response()->json(['message' => 'Date must be within the accounting year'], 422);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        $rows = DB::connection('coops')->select('CALL USP_RPT_REORDER(?)', [$asOn]);

        return response()->json($rows);
    }

    public function movementReport()
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $categories = DB::connection('coops')->select('CALL USP_GET_ITEM_CAT(?)', [session('org_id')]);

        return view('Admin.movement-report', [
            'categories'  => $categories,
            'year_start'  => session('year_start'),
            'year_end'    => session('year_end'),
            'org_name'    => session('org_name'),
            'branch_name' => session('branch_name'),
        ])->with('pageTitle', 'Slow / Fast Moving Items');
    }

    public function movementReportSearch(Request $request)
    {
        $frm = $request->input('frm_date');
        $to = $request->input('to_date');
        if (!$frm || !$to) {
            return response()->json(['message' => 'Select from date and to date'], 422);
        }
        if ($frm > $to) {
            return response()->json(['message' => 'From date cannot be after to date'], 422);
        }
        if ($frm < session('year_start') || $to > session('year_end')) {
            return response()->json(['message' => 'Date must be within the accounting year'], 422);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        $rows = DB::connection('coops')->select('CALL USP_RPT_MOVEMENT(?, ?, ?, ?)', [
            $frm,
            $to,
            (int) $request->input('cat_id', 0),
            (int) $request->input('sub_cat_id', 0),
        ]);

        return response()->json($rows);
    }
}
