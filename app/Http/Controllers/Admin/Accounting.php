<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class Accounting extends Controller
{
    public function indexGeneralVoucher()
    {
        Config::set('database.connections.coops.database', session('org_schema'));

        $allTypes = DB::select('CALL USP_GET_APPLICATION_OPTION(?)', [11]);
        $voucherTypes = array_values(array_filter($allTypes, function ($row) {
            return in_array((int) $row->Value_Id, [1, 2, 3, 4], true);
        }));
        $have = array_map(function ($row) {
            return (int) $row->Value_Id;
        }, $voucherTypes);
        foreach ([3 => 'Contra', 4 => 'Journal'] as $id => $name) {
            if (!in_array($id, $have, true)) {
                $voucherTypes[] = (object) ['Value_Id' => $id, 'Value_Name' => $name];
            }
        }

        $allLedgers = DB::connection('coops')->select('CALL USP_GET_ACCT_GLHEAD()');
        $active = array_values(array_filter($allLedgers, function ($row) {
            return !in_array($row->Is_Active ?? 1, [0, '0', false, "\x00"], true);
        }));
        $ledgers = array_values(array_filter($active, function ($row) {
            return strtoupper((string) ($row->Account_For ?? '')) !== 'C';
        }));
        $pairLedgers = array_map(function ($ledger) {
            return [
                'id' => (int) $ledger->Account_Id,
                'label' => $ledger->Account_Code . ' - ' . $ledger->Account_Desc,
            ];
        }, $active);

        $banks    = DB::connection('coops')->select('CALL USP_GET_BANK_LEDGER()');
        $vouchers = DB::connection('coops')->select('CALL USP_GET_GENERAL_VOUCHER_LIST(?)', [session('year_id')]);
        foreach ($vouchers as $voucher) {
            $voucher->Vou_Type_Name = $this->voucherTypeName((int) $voucher->Vou_Type, $voucher->Vou_Type_Name ?? '');
        }

        return view('Admin.general-voucher', compact('voucherTypes', 'ledgers', 'pairLedgers', 'banks', 'vouchers'))
            ->with('pageTitle', 'General Voucher');
    }

    public function listGeneralVoucher()
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $vouchers = DB::connection('coops')->select('CALL USP_GET_GENERAL_VOUCHER_LIST(?)', [session('year_id')]);
        return response()->json($vouchers);
    }

    public function detailsGeneralVoucher($id)
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $row = DB::connection('coops')->selectOne("
            SELECT m.Voucher_Id,
                   DATE_FORMAT(m.Vou_Date, '%Y-%m-%d') AS Vou_Date,
                   m.Vou_Type,
                   m.Vou_Mode,
                   m.Vou_No,
                   m.Ref_Vou_No,
                   m.Particulars,
                   m.Instrument_No,
                   dr.GlHead_Id AS Debit_Ledger,
                   cr.GlHead_Id AS Credit_Ledger,
                   dr.Vou_Amount AS Amount
            FROM trans_voucher_master m
            INNER JOIN trans_voucher_details dr
                ON dr.Voucher_Id = m.Voucher_Id AND dr.Trans_Type = 'D'
            INNER JOIN trans_voucher_details cr
                ON cr.Voucher_Id = m.Voucher_Id AND cr.Trans_Type = 'C'
            WHERE m.Voucher_Id = ?
              AND m.Trans_Type = 9
            LIMIT 1
        ", [intval($id)]);
        if (!$row) {
            return response()->json(['error' => 'Voucher not found'], 404);
        }
        $type = (int) $row->Vou_Type;
        $row->Ledger_Id = $type === 1 ? $row->Credit_Ledger : $row->Debit_Ledger;
        $row->Bank_Ledg = (int) $row->Vou_Mode === 2
            ? ($type === 1 ? $row->Debit_Ledger : $row->Credit_Ledger)
            : 0;
        return response()->json($row);
    }

    public function storeGeneralVoucher(Request $request)
    {
        $request->validate([
            'voucher_date' => 'required|date',
            'voucher_type' => 'required|in:1,2,3,4',
            'trans_mode'   => 'nullable|in:1,2',
            'ledger_id'    => 'nullable|integer',
            'debit_ledger' => 'nullable|integer',
            'credit_ledger' => 'nullable|integer',
            'amount'       => 'required|numeric|min:0.01|max:99999999.99',
            'particulars'  => 'required|string|max:200',
            'ref_vouch_no' => 'nullable|string|max:50',
            'bank_id'      => 'nullable|integer',
            'instrument_no' => 'nullable|string|max:50',
            'vouch_id'     => 'nullable|integer',
        ]);

        $voucherType = (int) $request->input('voucher_type');
        $isPair = in_array($voucherType, [3, 4], true);
        $debitLedger = (int) $request->input('debit_ledger', 0);
        $creditLedger = (int) $request->input('credit_ledger', 0);
        if ($isPair) {
            if ($debitLedger <= 0 || $creditLedger <= 0) {
                return response()->json(['error' => 'Select debit ledger and credit ledger'], 400);
            }
            if ($debitLedger === $creditLedger) {
                return response()->json(['error' => 'Debit ledger and credit ledger must be different'], 400);
            }
        } else {
            if ((int) $request->input('ledger_id', 0) <= 0) {
                return response()->json(['error' => 'Please select a Ledger'], 400);
            }
            if (!in_array((int) $request->input('trans_mode'), [1, 2], true)) {
                return response()->json(['error' => 'Select Cash or Bank'], 400);
            }
            if ((int) $request->trans_mode === 2 && !$request->bank_id) {
                return response()->json(['error' => 'Please select a Bank'], 400);
            }
            if ((int) $request->trans_mode === 2 && !trim((string) $request->input('instrument_no', ''))) {
                return response()->json(['error' => 'Instrument No is required for Bank'], 400);
            }
        }

        $yearStart = session('year_start');
        $yearEnd   = session('year_end');
        if ($request->voucher_date < $yearStart || $request->voucher_date > $yearEnd) {
            return response()->json(['error' => "Voucher Date must be between {$yearStart} and {$yearEnd}"], 400);
        }
        if ($request->voucher_date > date('Y-m-d')) {
            return response()->json(['error' => 'Voucher Date cannot be after today'], 400);
        }

        $vouchId = intval($request->input('vouch_id', 0));
        $mode    = $vouchId > 0 ? 2 : 1;

        Config::set('database.connections.coops.database', session('org_schema'));
        $conn = DB::connection('coops');
        $conn->beginTransaction();

        try {
            $spParams = [
                'pVouch_Id'     => $vouchId,
                'pVou_Date'     => $request->input('voucher_date'),
                'pVou_Type'     => intval($request->input('voucher_type')),
                'pVou_Mode'     => intval($request->input('trans_mode')),
                'pLedger_Id'    => intval($request->input('ledger_id')),
                'pAmount'       => floatval($request->input('amount')),
                'pParticulars'  => $request->input('particulars'),
                'pRef_Vou_No'   => $request->input('ref_vouch_no', ''),
                'pBank_Ledg'    => intval($request->input('bank_id', 0)),
                'pUser_Id'      => session('user_id'),
                'pFin_Id'       => session('year_id'),
                'pBranch_Id'    => session('branch_id'),
                'pMode'         => $mode,
                'pInstrument_No' => (int) $request->input('trans_mode') === 2
                    ? trim((string) $request->input('instrument_no', ''))
                    : '',
            ];

            Log::channel('trading')->info('USP_ADD_EDIT_GENERAL_VOUCHER params', $spParams);

            if ($isPair) {
                $result = $this->savePairVoucher(
                    $conn,
                    $vouchId,
                    $mode,
                    $voucherType,
                    $request->input('voucher_date'),
                    $debitLedger,
                    $creditLedger,
                    floatval($request->input('amount')),
                    $request->input('particulars'),
                    (string) $request->input('ref_vouch_no', '')
                );
            } else {
                $result = $conn->select('CALL USP_ADD_EDIT_GENERAL_VOUCHER(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', array_values($spParams));
            }

            Log::channel('trading')->info('USP_ADD_EDIT_GENERAL_VOUCHER result', ['result' => $result]);

            if (!empty($result) && $result[0]->Error_No < 0) {
                $conn->rollBack();
                return response()->json(['error' => $result[0]->Message ?? 'Save failed'], 400);
            }

            $conn->commit();
            return response()->json(['message' => $result[0]->Message ?? 'Voucher saved successfully']);
        } catch (\Exception $e) {
            $conn->rollBack();
            Log::channel('trading')->error('General voucher error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function voucherTypeName(int $type, string $current): string
    {
        if ($type === 3) {
            return 'Contra';
        }
        if ($type === 4) {
            return 'Journal';
        }
        return $current !== '' ? $current : ($type === 1 ? 'Receipt' : 'Payment');
    }

    private function savePairVoucher($conn, int $vouchId, int $mode, int $voucherType, string $vouDate, int $debitLedger, int $creditLedger, float $amount, string $particulars, string $refNo): array
    {
        $heads = $conn->select(
            'SELECT Account_Id, Is_Active FROM mst_acct_glhead WHERE Account_Id IN (?, ?)',
            [$debitLedger, $creditLedger]
        );
        $active = [];
        foreach ($heads as $head) {
            $off = in_array($head->Is_Active ?? 1, [0, '0', false, "\x00"], true);
            if (!$off) {
                $active[(int) $head->Account_Id] = true;
            }
        }
        if (empty($active[$debitLedger]) || empty($active[$creditLedger])) {
            return [(object) ['Error_No' => -1, 'Message' => 'Debit or credit ledger is not active']];
        }

        $userId = (int) session('user_id');
        $yearId = (int) session('year_id');
        $branchId = (int) session('branch_id');
        $vouMode = $voucherType === 3 ? 3 : 4;

        if ($mode === 1) {
            $prefix = $voucherType === 3 ? 'CON' : 'JOU';
            $this->ensureNumberSeries($conn, $prefix, $yearId, $branchId);
            $generated = $conn->selectOne('SELECT UDF_GEN_SYS_NO(?, ?, ?) AS Vou_No', [$prefix, $yearId, $branchId]);
            $vouNo = trim((string) ($generated->Vou_No ?? ''));
            if ($vouNo === '' || str_ends_with($vouNo, '/')) {
                return [(object) ['Error_No' => -1, 'Message' => 'Voucher number could not be generated']];
            }
            $ref = trim($refNo) === '' ? $vouNo : trim($refNo);
            $conn->insert(
                'INSERT INTO trans_voucher_master
                    (Year_Id, Vou_Date, Vou_Type, Vou_Mode, Vou_No, Ref_Vou_No, Particulars, Trans_Type, Type_Id, Status, Created_By, Approved_By, Approved_On)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 9, 0, 2, ?, ?, CURRENT_TIMESTAMP())',
                [$yearId, $vouDate, $voucherType, $vouMode, $vouNo, $ref, $particulars, $userId, $userId]
            );
            $vouchId = (int) $conn->getPdo()->lastInsertId();
        } else {
            $existing = $conn->selectOne(
                'SELECT Voucher_Id, Vou_No FROM trans_voucher_master WHERE Voucher_Id = ? AND Trans_Type = 9 AND IFNULL(Type_Id, 0) = 0',
                [$vouchId]
            );
            if (!$existing) {
                return [(object) ['Error_No' => -1, 'Message' => 'Voucher not found']];
            }
            $vouNo = $existing->Vou_No;
            $ref = trim($refNo) === '' ? $vouNo : trim($refNo);
            $conn->update(
                'UPDATE trans_voucher_master
                    SET Vou_Date = ?, Vou_Type = ?, Vou_Mode = ?, Ref_Vou_No = ?, Particulars = ?, Instrument_No = NULL
                  WHERE Voucher_Id = ?',
                [$vouDate, $voucherType, $vouMode, $ref, $particulars, $vouchId]
            );
            $conn->delete('DELETE FROM trans_voucher_details WHERE Voucher_Id = ?', [$vouchId]);
        }

        $conn->insert(
            'INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount) VALUES (?, ?, ?, ?)',
            [$vouchId, 'D', $debitLedger, $amount]
        );
        $conn->insert(
            'INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount) VALUES (?, ?, ?, ?)',
            [$vouchId, 'C', $creditLedger, $amount]
        );

        $verb = $mode === 1 ? 'Voucher saved. No is ' : 'Voucher updated. No is ';
        return [(object) ['Error_No' => 0, 'Message' => $verb . $vouNo]];
    }

    private function ensureNumberSeries($conn, string $element, int $yearId, int $branchId): void
    {
        $row = $conn->selectOne(
            'SELECT Id FROM stores_id WHERE Element_Name = ? AND Year_Id = ? AND (Branch_Id IS NULL OR Branch_Id = ?) LIMIT 1',
            [$element, $yearId, $branchId]
        );
        if ($row) {
            return;
        }
        $for = $element === 'CON' ? 'For Contra Voucher Generate' : 'For Journal Voucher Generate';
        $conn->insert(
            'INSERT INTO stores_id (Element_Name, Element_For, Cur_Val, Year_Id, Branch_Id) VALUES (?, ?, 0, ?, ?)',
            [$element, $for, $yearId, $branchId]
        );
    }

    public function indexSupplierPayment()
    {
        return $this->indexPartyVoucher(1, 'Supplier Payment', 'Select Supplier', 'supplier-payment');
    }

    public function indexCustomerCollection()
    {
        return $this->indexPartyVoucher(2, 'Customer Collection', 'Select Customer', 'customer-collection');
    }

    public function storeSupplierPayment(Request $request)
    {
        return $this->storePartyVoucher($request, 1);
    }

    public function storeCustomerCollection(Request $request)
    {
        return $this->storePartyVoucher($request, 2);
    }

    public function supplierPaymentLedger(Request $request)
    {
        return $this->partyLedger($request, 1, 'Select a supplier');
    }

    public function customerCollectionLedger(Request $request)
    {
        return $this->partyLedger($request, 2, 'Select a customer');
    }

    private function partyLedger(Request $request, int $partyType, string $emptyMessage)
    {
        $partyId = (int) $request->input('party_id', 0);
        if ($partyId <= 0) {
            return response()->json(['message' => $emptyMessage], 422);
        }

        $asOn = $request->input('as_on_date') ?: date('Y-m-d');
        $yearStart = session('year_start');
        $yearEnd = session('year_end');
        if ($asOn < $yearStart) {
            $asOn = $yearStart;
        }
        if ($asOn > $yearEnd) {
            $asOn = $yearEnd;
        }
        if ($asOn > date('Y-m-d')) {
            $asOn = date('Y-m-d');
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        $rows = DB::connection('coops')->select('CALL USP_RPT_PARTY_LEDGER(?, ?, ?, ?, ?)', [
            $yearStart,
            $asOn,
            (int) (session('branch_id') ?: 0),
            $partyType,
            $partyId,
        ]);

        $due = 0.0;
        if (!empty($rows)) {
            $last = $rows[count($rows) - 1];
            $due = (float) ($last->Balance_Amt ?? 0);
        }

        return response()->json([
            'rows' => $rows,
            'due' => round($due, 2),
            'payable' => round(max(0, $due), 2),
            'as_on_date' => $asOn,
            'from_date' => $yearStart,
        ]);
    }

    public function detailsPartyVoucher($id)
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $data = DB::connection('coops')->select('CALL USP_GET_PARTY_VOUCHER_DTLS(?)', [intval($id)]);
        if (empty($data)) {
            return response()->json(['error' => 'Voucher not found'], 404);
        }
        return response()->json($data[0]);
    }

    private function indexPartyVoucher($partyType, $pageTitle, $partyLabel, $routePrefix)
    {
        Config::set('database.connections.coops.database', session('org_schema'));

        $allParties = DB::connection('coops')->select('CALL USP_GET_PARTY_LIST(?, ?, ?)', [$partyType, session('branch_id'), 0]);
        $parties = array_values(array_filter($allParties, function ($row) {
            return (int) ($row->Status_Cd ?? 1) === 1;
        }));

        $banks    = DB::connection('coops')->select('CALL USP_GET_BANK_LEDGER()');
        $vouchers = DB::connection('coops')->select('CALL USP_GET_PARTY_VOUCHER_LIST(?, ?)', [session('year_id'), $partyType]);

        return view('Admin.party-voucher', compact('parties', 'banks', 'vouchers', 'partyLabel', 'routePrefix'))
            ->with('pageTitle', $pageTitle);
    }

    private function storePartyVoucher(Request $request, $partyType)
    {
        $request->validate([
            'voucher_date' => 'required|date',
            'trans_mode'   => 'required|in:1,2',
            'party_id'     => 'required|integer',
            'amount'       => 'required|numeric|min:0.01|max:99999999.99',
            'particulars'  => 'required|string|max:200',
            'ref_vouch_no' => 'nullable|string|max:50',
            'bank_id'      => 'nullable|integer',
            'instrument_no' => 'nullable|string|max:50',
            'vouch_id'     => 'nullable|integer',
        ]);

        if ((int) $request->trans_mode === 2 && !$request->bank_id) {
            return response()->json(['error' => 'Please select a Bank'], 400);
        }
        if ((int) $request->trans_mode === 2 && !trim((string) $request->input('instrument_no', ''))) {
            return response()->json(['error' => 'Instrument No is required for Bank'], 400);
        }

        $yearStart = session('year_start');
        $yearEnd   = session('year_end');
        if ($request->voucher_date < $yearStart || $request->voucher_date > $yearEnd) {
            return response()->json(['error' => "Voucher Date must be between {$yearStart} and {$yearEnd}"], 400);
        }
        if ($request->voucher_date > date('Y-m-d')) {
            return response()->json(['error' => 'Voucher Date cannot be after today'], 400);
        }

        $vouchId = intval($request->input('vouch_id', 0));
        $mode    = $vouchId > 0 ? 2 : 1;

        Config::set('database.connections.coops.database', session('org_schema'));
        $conn = DB::connection('coops');
        $conn->beginTransaction();

        try {
            $result = $conn->select('CALL USP_ADD_EDIT_PARTY_VOUCHER(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $vouchId,
                $request->input('voucher_date'),
                intval($partyType),
                intval($request->input('party_id')),
                intval($request->input('trans_mode')),
                floatval($request->input('amount')),
                $request->input('particulars'),
                $request->input('ref_vouch_no', ''),
                intval($request->input('bank_id', 0)),
                session('user_id'),
                session('year_id'),
                session('branch_id'),
                $mode,
                (int) $request->input('trans_mode') === 2 ? trim((string) $request->input('instrument_no', '')) : '',
            ]);

            if (!empty($result) && $result[0]->Error_No < 0) {
                $conn->rollBack();
                return response()->json(['error' => $result[0]->Message ?? 'Save failed'], 400);
            }

            $conn->commit();
            return response()->json(['message' => $result[0]->Message ?? 'Voucher saved successfully']);
        } catch (\Exception $e) {
            $conn->rollBack();
            Log::channel('trading')->error('Party voucher error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
