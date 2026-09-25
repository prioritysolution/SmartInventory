<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class AccountsReports extends Controller
{
    public function userScroll()
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $users = DB::connection('coops')->select('CALL USP_GET_USER_LIST(?)', [session('branch_id')]);

        return view('Admin.user-scroll', [
            'users'           => $users,
            'user_id'         => session('user_id'),
            'year_start'      => session('year_start'),
            'year_end'        => session('year_end'),
            'org_name'        => session('org_name'),
            'branch_name'     => session('branch_name'),
            'searchUrl'       => route('account-user-scroll.search'),
            'docLabel'        => 'Voucher No',
            'leftAmtLabel'    => 'Receipt',
            'rightAmtLabel'   => 'Payment',
            'showCashBalance' => true,
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

        $rows = DB::connection('coops')->select('CALL USP_RPT_USER_SCROLL(?, ?, ?, ?)', [
            (int) session('year_id'),
            $asOn,
            $userId,
            (int) (session('branch_id') ?: 0),
        ]);

        $openingRow = DB::connection('coops')->selectOne(
            "SELECT IFNULL(SUM(b.balance), 0) AS opening
             FROM trn_counter_balance b
             INNER JOIN (
                 SELECT t.counter_id, MAX(t.id) AS id
                 FROM trn_counter_balance t
                 INNER JOIN (
                     SELECT counter_id, MAX(`date`) AS d
                     FROM trn_counter_balance
                     WHERE `date` <= ?
                       AND (? = 0 OR counter_id = ?)
                     GROUP BY counter_id
                 ) d ON d.counter_id = t.counter_id AND t.`date` = d.d
                 GROUP BY t.counter_id
             ) x ON x.id = b.id",
            [$asOn, $userId, $userId]
        );
        $opening = (float) ($openingRow->opening ?? 0);

        $cashHead = DB::connection('coops')->selectOne(
            "SELECT IFNULL(
                (SELECT Cash_Ledg FROM mst_default_ledger WHERE Cash_Ledg IS NOT NULL LIMIT 1),
                (SELECT Account_Id FROM mst_acct_glhead WHERE Account_For = 'C' LIMIT 1)
             ) AS Cash_Id"
        );
        $cashId = (int) ($cashHead->Cash_Id ?? 0);

        $cashIn = 0.0;
        $cashOut = 0.0;
        if ($cashId) {
            $cashMove = DB::connection('coops')->selectOne(
                "SELECT
                    IFNULL(SUM(CASE WHEN cash.Trans_Type = 'D' THEN cash.Vou_Amount ELSE 0 END), 0) AS cash_in,
                    IFNULL(SUM(CASE WHEN cash.Trans_Type = 'C' THEN cash.Vou_Amount ELSE 0 END), 0) AS cash_out
                 FROM trans_voucher_master m
                 INNER JOIN trans_voucher_details cash
                    ON cash.Voucher_Id = m.Voucher_Id
                   AND cash.GlHead_Id = ?
                 WHERE m.Year_Id = ?
                   AND IFNULL(m.Status, 2) = 2
                   AND m.Vou_Date = ?
                   AND (? = 0 OR m.Created_By = ?)
                   AND cash.Trans_Type IN ('D', 'C')",
                [$cashId, (int) session('year_id'), $asOn, $userId, $userId]
            );
            $cashIn = (float) ($cashMove->cash_in ?? 0);
            $cashOut = (float) ($cashMove->cash_out ?? 0);
        }

        return response()->json([
            'rows'    => $rows,
            'opening' => $opening,
            'closing' => $opening + $cashIn - $cashOut,
        ]);
    }

    public function cashBook()
    {
        return view('Admin.cash-book', [
            'year_start'  => session('year_start'),
            'year_end'    => session('year_end'),
            'org_name'    => session('org_name'),
            'branch_name' => session('branch_name'),
        ])->with('pageTitle', 'Cash Book');
    }

    public function cashBookSearch(Request $request)
    {
        $frm = $request->input('frm_date') ?: $request->input('as_on_date');
        $to = $request->input('to_date') ?: $request->input('as_on_date');
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
        $yearId = (int) session('year_id');

        $cashHead = DB::connection('coops')->selectOne(
            "SELECT IFNULL(
                (SELECT Cash_Ledg FROM mst_default_ledger WHERE Cash_Ledg IS NOT NULL LIMIT 1),
                (SELECT Account_Id FROM mst_acct_glhead WHERE Account_For = 'C' LIMIT 1)
             ) AS Cash_Id"
        );
        $cashId = (int) ($cashHead->Cash_Id ?? 0);

        $opening = 0.0;
        $rows = [];
        if ($cashId) {
            $opening = (float) (DB::connection('coops')->selectOne(
                "SELECT IFNULL(SUM(CASE WHEN d.Trans_Type = 'D' THEN d.Vou_Amount ELSE -d.Vou_Amount END), 0) AS Amt
                 FROM trans_voucher_details d
                 INNER JOIN trans_voucher_master m ON m.Voucher_Id = d.Voucher_Id
                 WHERE d.GlHead_Id = ?
                   AND m.Year_Id = ?
                   AND IFNULL(m.Status, 2) = 2
                   AND m.Vou_Date < ?",
                [$cashId, $yearId, $frm]
            )->Amt ?? 0);

            $rows = DB::connection('coops')->select(
                "SELECT
                    m.Vou_Date,
                    IFNULL(m.Vou_No, '') AS Vou_No,
                    CASE WHEN cash.Trans_Type = 'D' THEN 'R' ELSE 'P' END AS Side,
                    IFNULL((
                        SELECT g.Account_Desc
                        FROM trans_voucher_details d
                        LEFT JOIN mst_acct_glhead g ON g.Account_Id = d.GlHead_Id
                        WHERE d.Voucher_Id = m.Voucher_Id
                          AND d.GlHead_Id <> ?
                        ORDER BY d.Vou_Amount DESC, d.VouDtls_Id
                        LIMIT 1
                    ), '') AS Gl_Head,
                    IFNULL(NULLIF(TRIM(m.Particulars), ''), IFNULL(m.Ref_Vou_No, '')) AS Particulars,
                    IFNULL(cash.Vou_Amount, 0) AS Amount
                 FROM trans_voucher_master m
                 INNER JOIN trans_voucher_details cash
                    ON cash.Voucher_Id = m.Voucher_Id
                   AND cash.GlHead_Id = ?
                 WHERE m.Year_Id = ?
                   AND IFNULL(m.Status, 2) = 2
                   AND m.Vou_Date BETWEEN ? AND ?
                 ORDER BY m.Vou_Date, m.Voucher_Id",
                [$cashId, $cashId, $yearId, $frm, $to]
            );
        }

        $receiptTotal = 0.0;
        $paymentTotal = 0.0;
        foreach ($rows as $row) {
            $amt = (float) ($row->Amount ?? 0);
            if (($row->Side ?? '') === 'R') {
                $receiptTotal += $amt;
            } else {
                $paymentTotal += $amt;
            }
        }

        return response()->json([
            'opening' => round($opening, 2),
            'closing' => round($opening + $receiptTotal - $paymentTotal, 2),
            'receipt_total' => round($receiptTotal, 2),
            'payment_total' => round($paymentTotal, 2),
            'rows' => $rows,
        ]);
    }

    public function journalBook()
    {
        return view('Admin.journal-book', [
            'year_start'  => session('year_start'),
            'year_end'    => session('year_end'),
            'org_name'    => session('org_name'),
            'branch_name' => session('branch_name'),
        ])->with('pageTitle', 'Journal Book');
    }

    public function journalBookSearch(Request $request)
    {
        $asOn = $request->input('as_on_date');
        if (!$asOn) {
            return response()->json(['message' => 'Select date'], 422);
        }
        if ($asOn < session('year_start') || $asOn > session('year_end')) {
            return response()->json(['message' => 'Date must be within the accounting year'], 422);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        $rows = DB::connection('coops')->select('CALL USP_RPT_JOURNAL_BOOK(?, ?, ?)', [
            (int) session('year_id'),
            $asOn,
            $asOn,
        ]);

        return response()->json($rows);
    }

    public function cashAccount()
    {
        return view('Admin.cash-account', [
            'year_start'  => session('year_start'),
            'year_end'    => session('year_end'),
            'org_name'    => session('org_name'),
            'branch_name' => session('branch_name'),
        ])->with('pageTitle', 'Cash A/c');
    }

    public function cashAccountSearch(Request $request)
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
        $rows = DB::connection('coops')->select('CALL USP_RPT_CASH_ACCOUNT(?, ?, ?)', [
            (int) session('year_id'),
            $frm,
            $to,
        ]);

        return response()->json($rows);
    }

    public function ledgerBook()
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $ledgers = DB::connection('coops')->select('CALL USP_GET_ACCT_GLHEAD()');

        return view('Admin.ledger-book', [
            'ledgers'     => $ledgers,
            'year_start'  => session('year_start'),
            'year_end'    => session('year_end'),
            'org_name'    => session('org_name'),
            'branch_name' => session('branch_name'),
        ])->with('pageTitle', 'Ledger Book');
    }

    public function ledgerBookSearch(Request $request)
    {
        $frm = $request->input('frm_date');
        $to = $request->input('to_date');
        $ledgerId = (int) $request->input('ledger_id', 0);
        if (!$frm || !$to) {
            return response()->json(['message' => 'Select from date and to date'], 422);
        }
        if ($frm > $to) {
            return response()->json(['message' => 'From date cannot be after to date'], 422);
        }
        if ($frm < session('year_start') || $to > session('year_end')) {
            return response()->json(['message' => 'Date must be within the accounting year'], 422);
        }
        if (!$ledgerId) {
            return response()->json(['message' => 'Select a ledger'], 422);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        $rows = DB::connection('coops')->select('CALL USP_RPT_LEDGER_BOOK(?, ?, ?, ?)', [
            (int) session('year_id'),
            $frm,
            $to,
            $ledgerId,
        ]);

        return response()->json($rows);
    }

    public function receiptPayment()
    {
        return view('Admin.receipt-payment', [
            'year_start'  => session('year_start'),
            'year_end'    => session('year_end'),
            'org_name'    => session('org_name'),
            'branch_name' => session('branch_name'),
        ])->with('pageTitle', 'Receipt & Payment');
    }

    public function receiptPaymentSearch(Request $request)
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
        $rows = DB::connection('coops')->select('CALL USP_RPT_RECEIPT_PAYMENT(?, ?, ?)', [
            (int) session('year_id'),
            $frm,
            $to,
        ]);

        return response()->json($rows);
    }

    public function trialBalance()
    {
        return view('Admin.trial-balance', [
            'year_start'  => session('year_start'),
            'year_end'    => session('year_end'),
            'org_name'    => session('org_name'),
            'branch_name' => session('branch_name'),
        ])->with('pageTitle', 'Trial Balance');
    }

    public function trialBalanceSearch(Request $request)
    {
        $asOn = $request->input('as_on_date');
        if (!$asOn) {
            return response()->json(['message' => 'Select as on date'], 422);
        }
        if ($asOn < session('year_start') || $asOn > session('year_end')) {
            return response()->json(['message' => 'Date must be within the accounting year'], 422);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        $rows = DB::connection('coops')->select('CALL USP_RPT_TRIAL_BALANCE(?, ?)', [
            (int) session('year_id'),
            $asOn,
        ]);

        return response()->json($rows);
    }

    public function tradingPl()
    {
        return view('Admin.trading-pl', [
            'year_start'  => session('year_start'),
            'year_end'    => session('year_end'),
            'org_name'    => session('org_name'),
            'branch_name' => session('branch_name'),
        ])->with('pageTitle', 'Trading & P/L A/c');
    }

    public function tradingPlSearch(Request $request)
    {
        $asOn = $request->input('as_on_date');
        if (!$asOn) {
            return response()->json(['message' => 'Select as on date'], 422);
        }
        if ($asOn < session('year_start') || $asOn > session('year_end')) {
            return response()->json(['message' => 'Date must be within the accounting year'], 422);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        $rows = DB::connection('coops')->select('CALL USP_RPT_TRADING_PL(?, ?)', [
            (int) session('year_id'),
            $asOn,
        ]);

        return response()->json($rows);
    }

    public function plAppropriation()
    {
        return view('Admin.pl-appropriation', [
            'year_start'  => session('year_start'),
            'year_end'    => session('year_end'),
            'org_name'    => session('org_name'),
            'branch_name' => session('branch_name'),
        ])->with('pageTitle', 'P/L Appropriation A/C');
    }

    public function plAppropriationSearch(Request $request)
    {
        $asOn = $request->input('as_on_date');
        if (!$asOn) {
            return response()->json(['message' => 'Select as on date'], 422);
        }
        if ($asOn < session('year_start') || $asOn > session('year_end')) {
            return response()->json(['message' => 'Date must be within the accounting year'], 422);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        $rows = DB::connection('coops')->select('CALL USP_RPT_PL_APPROPRIATION(?, ?)', [
            (int) session('year_id'),
            $asOn,
        ]);

        return response()->json($rows);
    }

    public function balanceSheet()
    {
        return view('Admin.balance-sheet', [
            'year_start'  => session('year_start'),
            'year_end'    => session('year_end'),
            'org_name'    => session('org_name'),
            'branch_name' => session('branch_name'),
        ])->with('pageTitle', 'Balance Sheet');
    }

    public function balanceSheetSearch(Request $request)
    {
        $asOn = $request->input('as_on_date');
        if (!$asOn) {
            return response()->json(['message' => 'Select as on date'], 422);
        }
        if ($asOn < session('year_start') || $asOn > session('year_end')) {
            return response()->json(['message' => 'Date must be within the accounting year'], 422);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        $rows = DB::connection('coops')->select('CALL USP_RPT_BALANCE_SHEET(?, ?)', [
            (int) session('year_id'),
            $asOn,
        ]);

        return response()->json($rows);
    }
}
