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
            return in_array((int) $row->Value_Id, [1, 2], true);
        }));

        $allLedgers = DB::connection('coops')->select('CALL USP_GET_ACCT_GLHEAD()');
        $ledgers = array_values(array_filter($allLedgers, function ($row) {
            $isCash = strtoupper((string) ($row->Account_For ?? '')) === 'C';
            $isOff  = in_array($row->Is_Active ?? 1, [0, '0', false, "\x00"], true);
            return !$isCash && !$isOff;
        }));

        $banks    = DB::connection('coops')->select('CALL USP_GET_BANK_LEDGER()');
        $vouchers = DB::connection('coops')->select('CALL USP_GET_GENERAL_VOUCHER_LIST(?)', [session('year_id')]);

        return view('Admin.general-voucher', compact('voucherTypes', 'ledgers', 'banks', 'vouchers'))
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
        $data = DB::connection('coops')->select('CALL USP_GET_GENERAL_VOUCHER_DTLS(?)', [intval($id)]);
        if (empty($data)) {
            return response()->json(['error' => 'Voucher not found'], 404);
        }
        return response()->json($data[0]);
    }

    public function storeGeneralVoucher(Request $request)
    {
        $request->validate([
            'voucher_date' => 'required|date',
            'voucher_type' => 'required|in:1,2',
            'trans_mode'   => 'required|in:1,2',
            'ledger_id'    => 'required|integer',
            'amount'       => 'required|numeric|min:0.01|max:99999999.99',
            'particulars'  => 'required|string|max:200',
            'ref_vouch_no' => 'nullable|string|max:50',
            'bank_id'      => 'nullable|integer',
            'vouch_id'     => 'nullable|integer',
        ]);

        if ((int) $request->trans_mode === 2 && !$request->bank_id) {
            return response()->json(['error' => 'Please select a Bank'], 400);
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
            ];

            Log::channel('trading')->info('USP_ADD_EDIT_GENERAL_VOUCHER params', $spParams);

            $result = $conn->select('CALL USP_ADD_EDIT_GENERAL_VOUCHER(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', array_values($spParams));

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
            'vouch_id'     => 'nullable|integer',
        ]);

        if ((int) $request->trans_mode === 2 && !$request->bank_id) {
            return response()->json(['error' => 'Please select a Bank'], 400);
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
            $result = $conn->select('CALL USP_ADD_EDIT_PARTY_VOUCHER(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
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
