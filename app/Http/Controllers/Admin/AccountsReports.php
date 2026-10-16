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
        $yearId = (int) session('year_id');
        $scroll = $this->userScrollDocuments($yearId, $asOn, $userId);

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
                [$cashId, $yearId, $asOn, $userId, $userId]
            );
            $cashIn = (float) ($cashMove->cash_in ?? 0);
            $cashOut = (float) ($cashMove->cash_out ?? 0);
        }

        return response()->json([
            'rows'            => $scroll['rows'],
            'credit_summary'  => $scroll['credit_summary'],
            'opening'         => $opening,
            'closing'         => $opening + $cashIn - $cashOut,
        ]);
    }

    /**
     * One row per voucher. Sales and purchases carry their item lines.
     * Cash and bank are split. Anything left on sundry debtor or creditor is credit.
     */
    private function userScrollDocuments(int $yearId, string $asOn, int $userId): array
    {
        $ledgers = DB::connection('coops')->selectOne(
            'SELECT IFNULL(Debit_Ledger, 0) AS Debit_Ledger, IFNULL(Credit_Ledger, 0) AS Credit_Ledger
             FROM mst_default_ledger LIMIT 1'
        );
        $debtorId = (int) (data_get($ledgers, 'Debit_Ledger') ?? 0);
        $creditorId = (int) (data_get($ledgers, 'Credit_Ledger') ?? 0);

        $vouchers = DB::connection('coops')->select(
            "SELECT
                m.Voucher_Id,
                IFNULL(m.Vou_No, '') AS Vou_No,
                IFNULL(m.Particulars, '') AS Particulars,
                IFNULL(m.Ref_Vou_No, '') AS Ref_Vou_No,
                MIN(t.Trading_Id) AS Trading_Id,
                MIN(t.Tranding_Type) AS Tranding_Type,
                MIN(t.Invoice_No) AS Invoice_No,
                MIN(t.Net_Amt) AS Net_Amt,
                MIN(IFNULL(p.Party_Name, '')) AS Party_Name,
                MIN(IFNULL(a.Agent_Name, '')) AS Agent_Name,
                IFNULL(SUM(CASE WHEN g.Account_For = 'C' AND d.Trans_Type = 'D' THEN d.Vou_Amount ELSE 0 END), 0) AS Receipt_Cash,
                IFNULL(SUM(CASE WHEN g.Account_For = 'B' AND d.Trans_Type = 'D' THEN d.Vou_Amount ELSE 0 END), 0) AS Receipt_Bank,
                IFNULL(SUM(CASE WHEN g.Account_For = 'C' AND d.Trans_Type = 'C' THEN d.Vou_Amount ELSE 0 END), 0) AS Payment_Cash,
                IFNULL(SUM(CASE WHEN g.Account_For = 'B' AND d.Trans_Type = 'C' THEN d.Vou_Amount ELSE 0 END), 0) AS Payment_Bank,
                IFNULL(SUM(CASE WHEN d.GlHead_Id = ? AND d.Trans_Type = 'D' THEN d.Vou_Amount ELSE 0 END), 0) AS Debtor_Dr,
                IFNULL(SUM(CASE WHEN d.GlHead_Id = ? AND d.Trans_Type = 'C' THEN d.Vou_Amount ELSE 0 END), 0) AS Creditor_Cr,
                IFNULL((
                    SELECT g2.Account_Desc
                    FROM trans_voucher_details d2
                    INNER JOIN mst_acct_glhead g2 ON g2.Account_Id = d2.GlHead_Id
                    WHERE d2.Voucher_Id = m.Voucher_Id
                      AND IFNULL(g2.Account_For, 'O') NOT IN ('C', 'B')
                    ORDER BY d2.Vou_Amount DESC, d2.VouDtls_Id
                    LIMIT 1
                ), '') AS Ledger_Name
             FROM trans_voucher_master m
             LEFT JOIN trans_voucher_details d ON d.Voucher_Id = m.Voucher_Id
             LEFT JOIN mst_acct_glhead g ON g.Account_Id = d.GlHead_Id
             LEFT JOIN trans_trading t ON t.Voucher_Id = m.Voucher_Id
             LEFT JOIN mst_party p ON p.Party_Id = t.Party_Id
             LEFT JOIN mst_agent a ON a.Agent_Id = t.Agent_Id
             WHERE m.Year_Id = ?
               AND IFNULL(m.Status, 2) = 2
               AND m.Vou_Date = ?
               AND (? = 0 OR m.Created_By = ?)
             GROUP BY m.Voucher_Id, m.Vou_No, m.Particulars, m.Ref_Vou_No
             ORDER BY m.Voucher_Id",
            [$debtorId, $creditorId, $yearId, $asOn, $userId, $userId]
        );

        $tradingIds = [];
        foreach ($vouchers as $voucher) {
            $id = (int) ($voucher->Trading_Id ?? 0);
            if ($id > 0) {
                $tradingIds[] = $id;
            }
        }

        $itemsByTrading = [];
        if ($tradingIds) {
            $placeholders = implode(',', array_fill(0, count($tradingIds), '?'));
            $itemRows = DB::connection('coops')->select(
                "SELECT tp.Trading_Id, IFNULL(p.Prod_ShortNm, '') AS Item_Name,
                        IFNULL(tp.Gst_Code, '') AS HSN_No,
                        IFNULL(UDF_GET_UNIT_NAME(tp.Unit_Id), '') AS Unit_Name,
                        IFNULL(tp.Item_Qty, 0) AS Item_Qty,
                        IFNULL(tp.Item_Rate, 0) AS Item_Rate,
                        IFNULL(tp.MRP, 0) AS MRP,
                        IFNULL(tp.Disc_Prcnt, 0) AS Disc_Prcnt,
                        IFNULL(tp.Taxable_Amt, 0) AS Taxable_Amt,
                        (IFNULL(tp.SGST_Prcnt, 0) + IFNULL(tp.CGST_Prcnt, 0)) AS GST_Prcnt,
                        (IFNULL(tp.SGST_Amt, 0) + IFNULL(tp.CGST_Amt, 0)) AS GST_Amt,
                        IFNULL(tp.Net_Amt, 0) AS Net_Amt
                 FROM trans_trading_products tp
                 LEFT JOIN mst_product p ON p.Prod_Id = tp.Prod_Id
                 WHERE tp.Trading_Id IN ($placeholders)
                 ORDER BY tp.Trading_Id, tp.Sl",
                $tradingIds
            );
            foreach ($itemRows as $item) {
                $itemsByTrading[(int) $item->Trading_Id][] = $item;
            }
        }

        $typeNames = [
            2 => 'Purchase',
            3 => 'Sale',
            4 => 'Purchase Return',
            5 => 'Sale Return',
        ];
        $rows = [];
        $credit = [
            'sale' => 0.0,
            'purchase' => 0.0,
            'sale_return' => 0.0,
            'purchase_return' => 0.0,
            'other' => 0.0,
            'bills' => [],
        ];

        foreach ($vouchers as $voucher) {
            $type = (int) ($voucher->Tranding_Type ?? 0);
            $receiptCash = round((float) $voucher->Receipt_Cash, 2);
            $receiptBank = round((float) $voucher->Receipt_Bank, 2);
            $paymentCash = round((float) $voucher->Payment_Cash, 2);
            $paymentBank = round((float) $voucher->Payment_Bank, 2);
            $creditAmt = round((float) $voucher->Debtor_Dr + (float) $voucher->Creditor_Cr, 2);
            $settled = $receiptCash + $receiptBank + $paymentCash + $paymentBank;
            $net = round((float) ($voucher->Net_Amt ?? 0), 2);
            if ($creditAmt < 0.0001 && $settled < 0.0001 && $net > 0 && isset($typeNames[$type])) {
                $creditAmt = $net;
            }

            $who = trim((string) $voucher->Party_Name);
            if ($who === '' && trim((string) $voucher->Agent_Name) !== '') {
                $who = trim((string) $voucher->Agent_Name);
            }
            if (isset($typeNames[$type])) {
                $particulars = $who !== '' ? $typeNames[$type] . ' - ' . $who : $typeNames[$type];
            } else {
                $particulars = trim((string) $voucher->Particulars);
                if ($particulars === '') {
                    $particulars = trim((string) $voucher->Ledger_Name) ?: 'Voucher';
                }
            }

            $invoice = trim((string) ($voucher->Invoice_No ?: $voucher->Ref_Vou_No));
            $vouNo = trim((string) $voucher->Vou_No);
            if ($invoice !== '' && $vouNo !== '' && strcasecmp($invoice, $vouNo) !== 0) {
                $docNo = $invoice . ' / ' . $vouNo;
            } else {
                $docNo = $invoice !== '' ? $invoice : $vouNo;
            }

            $items = [];
            $tradingId = (int) ($voucher->Trading_Id ?? 0);
            if (isset($typeNames[$type])) {
                $sl = 1;
                foreach ($itemsByTrading[$tradingId] ?? [] as $item) {
                    $items[] = [
                        'Sl'       => $sl++,
                        'Item'     => $item->Item_Name,
                        'HSN'      => $item->HSN_No,
                        'Unit'     => $item->Unit_Name,
                        'Qty'      => round((float) $item->Item_Qty, 2),
                        'Rate'     => round((float) $item->Item_Rate, 2),
                        'MRP'      => round((float) $item->MRP, 2),
                        'Disc'     => round((float) $item->Disc_Prcnt, 2),
                        'Taxable'  => round((float) $item->Taxable_Amt, 2),
                        'GST_Prcnt'=> round((float) $item->GST_Prcnt, 2),
                        'GST'      => round((float) $item->GST_Amt, 2),
                        'Amount'   => round((float) $item->Net_Amt, 2),
                    ];
                }
            }

            $kind = $type === 2 ? 'purchase' : ($type === 3 ? 'sale' : ($type === 4 ? 'purchase_return' : ($type === 5 ? 'sale_return' : 'other')));
            $rows[] = [
                'Kind'          => $kind,
                'Particulars'   => $particulars,
                'Doc_No'        => $docNo,
                'Receipt_Cash'  => $receiptCash,
                'Receipt_Bank'  => $receiptBank,
                'Payment_Cash'  => $paymentCash,
                'Payment_Bank'  => $paymentBank,
                'Credit'        => $creditAmt,
                'Items'         => $items,
            ];

            if ($creditAmt > 0.0001) {
                if ($type === 3) {
                    $credit['sale'] += $creditAmt;
                } elseif ($type === 2) {
                    $credit['purchase'] += $creditAmt;
                } elseif ($type === 5) {
                    $credit['sale_return'] += $creditAmt;
                } elseif ($type === 4) {
                    $credit['purchase_return'] += $creditAmt;
                } else {
                    $credit['other'] += $creditAmt;
                }
                $credit['bills'][] = [
                    'Particulars' => $particulars,
                    'Doc_No'      => $docNo,
                    'Amount'      => $creditAmt,
                    'Group'       => $type === 3 ? 'sale'
                        : ($type === 2 ? 'purchase'
                        : ($type === 5 ? 'sale_return'
                        : ($type === 4 ? 'purchase_return' : 'other'))),
                ];
            }
        }

        foreach (['sale', 'purchase', 'sale_return', 'purchase_return', 'other'] as $key) {
            $credit[$key] = round($credit[$key], 2);
        }
        $credit['total'] = round(
            $credit['sale'] + $credit['purchase'] + $credit['sale_return'] + $credit['purchase_return'] + $credit['other'],
            2
        );

        return ['rows' => $rows, 'credit_summary' => $credit];
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
                   AND IFNULL(m.Vou_Mode, 0) = 1
                   AND m.Vou_Date < ?",
                [$cashId, $yearId, $frm]
            )->Amt ?? 0);

            $raw = DB::connection('coops')->select(
                "SELECT
                    m.Voucher_Id,
                    m.Vou_Date,
                    IFNULL(m.Vou_No, '') AS Vou_No,
                    IFNULL(m.Trans_Type, 0) AS Trans_Type,
                    IFNULL(m.Type_Id, 0) AS Type_Id,
                    CASE WHEN cash.Trans_Type = 'D' THEN 'R' ELSE 'P' END AS Side,
                    IFNULL(tr.Tranding_Type, 0) AS Tranding_Type,
                    IFNULL(pty.Party_Type, 0) AS Party_Type,
                    IFNULL(pty.Party_Name, '') AS Party_Name,
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
                 LEFT JOIN (
                    SELECT Voucher_Id, MIN(Tranding_Type) AS Tranding_Type
                    FROM trans_trading
                    WHERE Voucher_Id IS NOT NULL
                    GROUP BY Voucher_Id
                 ) tr ON tr.Voucher_Id = m.Voucher_Id
                 LEFT JOIN mst_party pty
                    ON pty.Party_Id = m.Type_Id
                   AND m.Trans_Type = 9
                   AND IFNULL(m.Type_Id, 0) > 0
                 WHERE m.Year_Id = ?
                   AND IFNULL(m.Status, 2) = 2
                   AND IFNULL(m.Vou_Mode, 0) = 1
                   AND m.Vou_Date BETWEEN ? AND ?
                 ORDER BY m.Vou_Date, m.Voucher_Id",
                [$cashId, $cashId, $yearId, $frm, $to]
            );
            $rows = $this->cashBookLines($raw);
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

    /**
     * Cash purchases and cash sales are one total per date.
     * General vouchers, supplier payments and customer collections stay one line each.
     */
    private function cashBookLines(array $raw): array
    {
        $groups = [
            2 => ['key' => 'purchase', 'side' => 'P', 'gl' => 'Purchase', 'text' => 'Cash Purchase', 'sort' => 10],
            3 => ['key' => 'sale', 'side' => 'R', 'gl' => 'Sale', 'text' => 'Cash Sale', 'sort' => 1],
            4 => ['key' => 'purchase_return', 'side' => 'R', 'gl' => 'Purchase Return', 'text' => 'Cash Purchase Return', 'sort' => 2],
            5 => ['key' => 'sale_return', 'side' => 'P', 'gl' => 'Sale Return', 'text' => 'Cash Sale Return', 'sort' => 11],
        ];

        $totals = [];
        $singles = [];

        foreach ($raw as $row) {
            $date = substr((string) $row->Vou_Date, 0, 10);
            $trading = (int) ($row->Tranding_Type ?? 0);
            if (isset($groups[$trading])) {
                $spec = $groups[$trading];
                $key = $date . '|' . $spec['key'];
                if (!isset($totals[$key])) {
                    $totals[$key] = (object) [
                        'Vou_Date' => $date,
                        'Side' => $spec['side'],
                        'Gl_Head' => $spec['gl'],
                        'Particulars' => $spec['text'],
                        'Amount' => 0,
                        'Sort' => $spec['sort'],
                    ];
                }
                $totals[$key]->Amount = round($totals[$key]->Amount + (float) $row->Amount, 2);
                continue;
            }

            $transType = (int) ($row->Trans_Type ?? 0);
            $partyType = (int) ($row->Party_Type ?? 0);
            $typeId = (int) ($row->Type_Id ?? 0);
            $gl = (string) ($row->Gl_Head ?? '');
            $text = (string) ($row->Particulars ?? '');
            $sort = ($row->Side ?? '') === 'R' ? 40 : 50;

            if ($transType === 9 && $partyType === 1) {
                $gl = 'Supplier Payment';
                $text = trim(($row->Party_Name ?? '') . ($text !== '' ? ' - ' . $text : ''));
                $sort = 20;
            } elseif ($transType === 9 && $partyType === 2) {
                $gl = 'Customer Collection';
                $text = trim(($row->Party_Name ?? '') . ($text !== '' ? ' - ' . $text : ''));
                $sort = 3;
            } elseif ($transType === 9 && $typeId === 0) {
                $gl = $gl !== '' ? $gl : 'General Voucher';
                $text = $text !== '' ? $text : 'General Voucher';
                $sort = ($row->Side ?? '') === 'R' ? 4 : 21;
            }

            $singles[] = (object) [
                'Vou_Date' => $date,
                'Side' => $row->Side,
                'Gl_Head' => $gl,
                'Particulars' => $text,
                'Amount' => round((float) $row->Amount, 2),
                'Sort' => $sort,
                'Voucher_Id' => (int) $row->Voucher_Id,
            ];
        }

        $lines = array_values($totals);
        foreach ($singles as $line) {
            $lines[] = $line;
        }

        usort($lines, function ($a, $b) {
            $date = strcmp((string) $a->Vou_Date, (string) $b->Vou_Date);
            if ($date !== 0) {
                return $date;
            }
            $sort = ((int) $a->Sort) <=> ((int) $b->Sort);
            if ($sort !== 0) {
                return $sort;
            }
            return ((int) ($a->Voucher_Id ?? 0)) <=> ((int) ($b->Voucher_Id ?? 0));
        });

        return $lines;
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
        $yearId = (int) session('year_id');

        $cashHead = DB::connection('coops')->selectOne(
            "SELECT IFNULL(
                (SELECT Cash_Ledg FROM mst_default_ledger WHERE Cash_Ledg IS NOT NULL LIMIT 1),
                (SELECT Account_Id FROM mst_acct_glhead WHERE Account_For = 'C' LIMIT 1)
             ) AS Cash_Id"
        );
        $cashId = (int) ($cashHead->Cash_Id ?? 0);

        $opening = 0.0;
        $lines = [];
        if ($cashId) {
            $opening = (float) (DB::connection('coops')->selectOne(
                "SELECT IFNULL(SUM(CASE WHEN d.Trans_Type = 'D' THEN d.Vou_Amount ELSE -d.Vou_Amount END), 0) AS Amt
                 FROM trans_voucher_details d
                 INNER JOIN trans_voucher_master m ON m.Voucher_Id = d.Voucher_Id
                 WHERE d.GlHead_Id = ?
                   AND m.Year_Id = ?
                   AND IFNULL(m.Status, 2) = 2
                   AND IFNULL(m.Vou_Mode, 0) = 1
                   AND m.Vou_Date < ?",
                [$cashId, $yearId, $frm]
            )->Amt ?? 0);

            $raw = DB::connection('coops')->select(
                "SELECT
                    IFNULL(m.Vou_Mode, 0) AS Vou_Mode,
                    d.Trans_Type AS DrCr,
                    IFNULL(d.GlHead_Id, 0) AS GlHead_Id,
                    IFNULL(g.Account_Code, '') AS Account_Code,
                    IFNULL(g.Account_Desc, '') AS Gl_Head,
                    IFNULL(d.Vou_Amount, 0) AS Amount
                 FROM trans_voucher_master m
                 INNER JOIN trans_voucher_details d ON d.Voucher_Id = m.Voucher_Id
                 LEFT JOIN mst_acct_glhead g ON g.Account_Id = d.GlHead_Id
                 WHERE m.Year_Id = ?
                   AND IFNULL(m.Status, 2) = 2
                   AND m.Vou_Date BETWEEN ? AND ?",
                [$yearId, $frm, $to]
            );
            $lines = $this->cashAccountLines($raw, $cashId);
        }

        $receiptCash = 0.0;
        $receiptTransfer = 0.0;
        $paymentCash = 0.0;
        $paymentTransfer = 0.0;
        foreach ($lines as $line) {
            $receiptCash += (float) $line->receipt_cash;
            $receiptTransfer += (float) $line->receipt_transfer;
            $paymentCash += (float) $line->payment_cash;
            $paymentTransfer += (float) $line->payment_transfer;
        }

        return response()->json([
            'opening' => round($opening, 2),
            'closing' => round($opening + $receiptCash + $receiptTransfer - $paymentCash - $paymentTransfer, 2),
            'receipt_cash' => round($receiptCash, 2),
            'receipt_transfer' => round($receiptTransfer, 2),
            'payment_cash' => round($paymentCash, 2),
            'payment_transfer' => round($paymentTransfer, 2),
            'ledgers' => $lines,
        ]);
    }

    /**
     * One row per ledger for the whole period. Credit is receipt, debit is payment.
     * Cash vouchers go in Cash. Journal, credit and bank go in Transfer.
     */
    private function cashAccountLines(array $raw, int $cashId): array
    {
        $bucket = [];
        foreach ($raw as $row) {
            $glId = (int) $row->GlHead_Id;
            $amount = round((float) $row->Amount, 2);
            if ($glId === 0 || $glId === $cashId || $amount == 0.0) {
                continue;
            }
            if (!isset($bucket[$glId])) {
                $name = trim((string) $row->Gl_Head);
                $bucket[$glId] = (object) [
                    'Code' => (string) $row->Account_Code,
                    'Ledger' => $name !== '' ? $name : 'Ledger',
                    'receipt_cash' => 0,
                    'receipt_transfer' => 0,
                    'payment_cash' => 0,
                    'payment_transfer' => 0,
                ];
            }
            $side = $row->DrCr === 'C' ? 'receipt' : 'payment';
            $column = ((int) $row->Vou_Mode === 1) ? 'cash' : 'transfer';
            $field = $side . '_' . $column;
            $bucket[$glId]->{$field} = round($bucket[$glId]->{$field} + $amount, 2);
        }

        $lines = array_values($bucket);
        usort($lines, function ($a, $b) {
            return strcmp((string) $a->Code, (string) $b->Code);
        });

        return $lines;
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
