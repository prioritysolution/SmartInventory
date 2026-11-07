<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\PaginatesReportRows;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class AccountsReports extends Controller
{
    use PaginatesReportRows;

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
        ])->with('pageTitle', 'User Scroll');
    }

    public function userScrollSearch(Request $request)
    {
        $asOn = $request->input('as_on_date');
        if (!$asOn) {
            return response()->json(['message' => 'Select date'], 422);
        }
Config::set('database.connections.coops.database', session('org_schema'));
        $userId = (int) $request->input('user_id', 0);
        $records = DB::connection('coops')->select(
            'CALL USP_RPT_USER_SCROLL(?, ?, ?)',
            [(int) session('year_id'), $asOn, $userId]
        );

        $typeNames = [
            2 => 'Purchase',
            3 => 'Sale',
            4 => 'Purchase Return',
            5 => 'Sale Return',
        ];

        $rows = [];
        foreach ($records as $record) {
            $voucherId = (int) $record->Voucher_Id;
            if (!isset($rows[$voucherId])) {
                $type = (int) ($record->Tranding_Type ?? 0);
                $who = trim((string) ($record->Party_Name ?? ''));
                if ($who === '' && trim((string) ($record->Agent_Name ?? '')) !== '') {
                    $who = trim((string) $record->Agent_Name);
                }

                if (isset($typeNames[$type])) {
                    $particulars = $who !== '' ? $typeNames[$type] . ' - ' . $who : $typeNames[$type];
                } else {
                    $particulars = trim((string) ($record->Particulars ?? ''));
                    if ($particulars === '') {
                        $particulars = trim((string) ($record->Ledger_Name ?? '')) ?: 'Voucher';
                    }
                    if ((int) ($record->Vou_Type ?? 0) === 3 && stripos($particulars, 'Contra') !== 0) {
                        $particulars = 'Contra - ' . $particulars;
                    }
                }

                $invoice = trim((string) ($record->Invoice_No ?: $record->Ref_Vou_No));
                $vouNo = trim((string) ($record->Vou_No ?? ''));
                $docNo = $invoice !== '' && $vouNo !== '' && strcasecmp($invoice, $vouNo) !== 0
                    ? $invoice . ' / ' . $vouNo
                    : ($invoice !== '' ? $invoice : $vouNo);

                $rows[$voucherId] = [
                    'Kind' => $type === 2 ? 'purchase'
                        : ($type === 3 ? 'sale'
                        : ($type === 4 ? 'purchase_return'
                        : ($type === 5 ? 'sale_return' : 'other'))),
                    'Particulars' => $particulars,
                    'Doc_No' => $docNo,
                    'Receipt_Cash' => round((float) $record->Receipt_Cash, 2),
                    'Receipt_Bank' => round((float) $record->Receipt_Bank, 2),
                    'Payment_Cash' => round((float) $record->Payment_Cash, 2),
                    'Payment_Bank' => round((float) $record->Payment_Bank, 2),
                    'Credit' => round((float) $record->Credit, 2),
                    'Items' => [],
                ];
            }

            if (isset($typeNames[(int) ($record->Tranding_Type ?? 0)]) && $record->Item_Trading_Id !== null) {
                $rows[$voucherId]['Items'][] = [
                    'Sl' => count($rows[$voucherId]['Items']) + 1,
                    'Item' => $record->Item_Name,
                    'HSN' => $record->HSN_No,
                    'Unit' => $record->Unit_Name,
                    'Qty' => round((float) $record->Item_Qty, 2),
                    'Rate' => round((float) $record->Item_Rate, 2),
                    'MRP' => round((float) $record->MRP, 2),
                    'Disc' => round((float) $record->Disc_Prcnt, 2),
                    'Taxable' => round((float) $record->Taxable_Amt, 2),
                    'GST_Prcnt' => round((float) $record->GST_Prcnt, 2),
                    'GST' => round((float) $record->GST_Amt, 2),
                    'Amount' => round((float) $record->Item_Net_Amt, 2),
                ];
            }
        }

        return response()->json(['rows' => array_values($rows)]);
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
Config::set('database.connections.coops.database', session('org_schema'));
        $records = DB::connection('coops')->select(
            'CALL USP_RPT_CASH_BOOK(?, ?, ?)',
            [(int) session('year_id'), $frm, $to]
        );

        $opening = 0.0;
        $rows = [];
        $receiptTotal = 0.0;
        $paymentTotal = 0.0;
        foreach ($records as $row) {
            if ((int) $row->Is_Summary === 1) {
                $opening = (float) $row->Opening;
                continue;
            }

            $rows[] = $row;
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
Config::set('database.connections.coops.database', session('org_schema'));
        $rows = DB::connection('coops')->select('CALL USP_RPT_JOURNAL_BOOK(?, ?, ?)', [
            (int) session('year_id'),
            $asOn,
            $asOn,
        ]);

        return $this->pagedReport($request, $rows);
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
Config::set('database.connections.coops.database', session('org_schema'));
        $yearId = (int) session('year_id');

        $cashHead = DB::connection('coops')->selectOne('CALL USP_GET_DEFAULT_CASH_LEDGER()');
        $cashId = (int) ($cashHead->Cash_Id ?? 0);

        $opening = 0.0;
        $lines = [];
        if ($cashId) {
            $opening = (float) (DB::connection('coops')->selectOne(
                'CALL USP_RPT_CASH_BOOK_OPENING(?, ?, ?)',
                [$cashId, $yearId, $frm]
            )->Amt ?? 0);

            $raw = DB::connection('coops')->select(
                'CALL USP_RPT_CASH_ACCOUNT_LINES(?, ?, ?)',
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

        return $this->pagedReport($request, $rows);
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
Config::set('database.connections.coops.database', session('org_schema'));
        $rows = DB::connection('coops')->select('CALL USP_RPT_RECEIPT_PAYMENT(?, ?, ?)', [
            (int) session('year_id'),
            $frm,
            $to,
        ]);

        return $this->pagedReport($request, $rows);
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
Config::set('database.connections.coops.database', session('org_schema'));
        $rows = DB::connection('coops')->select('CALL USP_RPT_TRIAL_BALANCE(?, ?)', [
            (int) session('year_id'),
            $asOn,
        ]);

        return $this->pagedReport($request, $rows);
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
Config::set('database.connections.coops.database', session('org_schema'));
        $rows = DB::connection('coops')->select('CALL USP_RPT_TRADING_PL(?, ?)', [
            (int) session('year_id'),
            $asOn,
        ]);

        return $this->pagedReport($request, $rows);
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
Config::set('database.connections.coops.database', session('org_schema'));
        $rows = DB::connection('coops')->select('CALL USP_RPT_PL_APPROPRIATION(?, ?)', [
            (int) session('year_id'),
            $asOn,
        ]);

        return $this->pagedReport($request, $rows);
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
Config::set('database.connections.coops.database', session('org_schema'));
        $rows = DB::connection('coops')->select('CALL USP_RPT_BALANCE_SHEET(?, ?)', [
            (int) session('year_id'),
            $asOn,
        ]);

        return $this->pagedReport($request, $rows);
    }
}
