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

        return response()->json($rows);
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
        $rows = DB::connection('coops')->select('CALL USP_RPT_PURCHASE_REGISTER(?, ?, ?, ?, ?)', [
            $frm,
            $to,
            (int) $request->input('party_id', 0),
            (int) (session('branch_id') ?: 0),
            (int) $request->input('gst_filter', 0),
        ]);

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
        $rows = DB::connection('coops')->select('CALL USP_RPT_SALES_REGISTER(?, ?, ?, ?, ?)', [
            $frm,
            $to,
            (int) $request->input('mode', 0),
            (int) (session('branch_id') ?: 0),
            (int) $request->input('cat_id', 0),
        ]);

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

    public function userScroll()
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $users = DB::connection('coops')->select('CALL USP_GET_USER_LIST(?)', [session('branch_id')]);

        return view('Admin.user-scroll', [
            'users'          => $users,
            'user_id'        => session('user_id'),
            'year_start'     => session('year_start'),
            'year_end'       => session('year_end'),
            'org_name'       => session('org_name'),
            'branch_name'    => session('branch_name'),
            'searchUrl'      => route('user-scroll.search'),
            'docLabel'       => 'Invoice No',
            'leftAmtLabel'   => 'Sale',
            'rightAmtLabel'  => 'Sale Return',
        ])->with('pageTitle', 'User Scroll');
    }

    public function userScrollSearch(Request $request)
    {
        $asOn = $request->input('as_on_date');
        if (!$asOn) {
            return response()->json(['message' => 'Select date'], 422);
        }
        if ($asOn < session('year_start') || $asOn > session('year_end')) {
            return response()->json(['message' => 'Date must be within the accounting year'], 422);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        $userId = (int) $request->input('user_id', 0);
        $branchId = (int) (session('branch_id') ?: 0);

        $rows = DB::connection('coops')->select(
            "SELECT
                CASE WHEN m.Tranding_Type = 3 THEN 'Sale' ELSE 'Sale Return' END AS Ledger_Name,
                IFNULL(m.Invoice_No, '') AS Doc_No,
                TRIM(CONCAT(
                    IFNULL(p.Party_Name, ''),
                    CASE
                        WHEN m.Agent_Id IS NULL THEN ' (Counter)'
                        ELSE CONCAT(' (Agent: ', IFNULL(a.Agent_Name, ''), ')')
                    END
                )) AS Particulars,
                CASE WHEN m.Tranding_Type = 3 THEN IFNULL(m.Net_Amt, 0) ELSE 0 END AS Receipt_Amt,
                CASE WHEN m.Tranding_Type = 5 THEN IFNULL(m.Net_Amt, 0) ELSE 0 END AS Payment_Amt,
                m.Trading_Id AS Sort_Id
            FROM trans_trading m
            LEFT JOIN mst_party p ON p.Party_Id = m.Party_Id
            LEFT JOIN mst_agent a ON a.Agent_Id = m.Agent_Id
            WHERE m.Tranding_Type IN (3, 5)
              AND IFNULL(m.Status_Cd, 1) = 1
              AND m.Invoice_Date = ?
              AND (? = 0 OR m.Branch_Id = ?)
              AND (? = 0 OR m.Created_By = ?)
            ORDER BY m.Tranding_Type, m.Trading_Id",
            [$asOn, $branchId, $branchId, $userId, $userId]
        );

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
        ])->with('pageTitle', 'Stock Statement');
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
        $rows = DB::connection('coops')->select('CALL USP_RPT_STOCK_STATEMENT(?, ?, ?, ?)', [
            $frm,
            $to,
            (int) $request->input('cat_id', 0),
            (int) $request->input('sub_cat_id', 0),
        ]);

        return response()->json($rows);
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
