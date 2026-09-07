<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AgentCustomerPayment extends Controller
{
    public function index()
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');

        $customers = DB::connection('coops')->select('CALL USP_GET_PARTY_LIST(?, ?, ?)', [
            2,
            session('branch_id'),
            session('agent_id'),
        ]);
        $customers = array_values(array_filter($customers, function ($row) {
            return (int) ($row->Status_Cd ?? 1) === 1;
        }));

        $vouchers = $this->agentCollectionList();

        return view('Agent.customer-payment', [
            'customers' => $customers,
            'vouchers'  => $vouchers,
            'pageTitle' => 'Customer Payment',
        ]);
    }

    public function store(Request $request)
    {
        $started = microtime(true);
        $log = function (string $step, array $context = []) use ($started) {
            Log::channel('trading')->info('agent.customer.payment.' . $step, array_merge([
                'elapsed_ms' => round((microtime(true) - $started) * 1000, 2),
                'agent_id'   => session('agent_id'),
                'branch_id'  => session('branch_id'),
                'year_id'    => session('year_id'),
                'org_schema' => session('org_schema'),
            ], $context));
        };

        $log('start', ['payload' => $request->except(['_token'])]);

        try {
            $request->validate([
                'voucher_date' => 'required|date',
                'trans_mode'   => 'required|in:1,2',
                'party_id'     => 'required|integer|min:1',
                'amount'       => 'required|numeric|min:0.01|max:99999999.99',
                'particulars'  => 'required|string|max:150',
                'ref_vouch_no' => 'nullable|string|max:50',
                'payment_id'   => 'nullable|integer',
                'vouch_id'     => 'nullable|integer', // legacy alias
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $log('validation_failed', ['errors' => $e->errors()]);
            return response()->json([
                'error'  => collect($e->errors())->flatten()->first() ?? 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        $yearStart = session('year_start');
        $yearEnd   = session('year_end');
        if ($request->voucher_date < $yearStart || $request->voucher_date > $yearEnd) {
            $log('rejected', ['reason' => 'date_out_of_year', 'voucher_date' => $request->voucher_date]);
            return response()->json(['error' => "Payment Date must be between {$yearStart} and {$yearEnd}"], 400);
        }
        if ($request->voucher_date > date('Y-m-d')) {
            $log('rejected', ['reason' => 'date_in_future', 'voucher_date' => $request->voucher_date]);
            return response()->json(['error' => 'Payment Date cannot be after today'], 400);
        }

        if (!session('org_schema') || !session('agent_id') || !session('year_id') || !session('branch_id')) {
            $log('rejected', ['reason' => 'session_incomplete']);
            return response()->json(['error' => 'Session expired. Please login again.'], 401);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');

        $partyId = (int) $request->input('party_id');
        $log('checking_customer', ['party_id' => $partyId]);
        if (!$this->customerBelongsToAgent($partyId)) {
            $log('rejected', ['reason' => 'customer_not_assigned', 'party_id' => $partyId]);
            return response()->json(['error' => 'Selected customer is not assigned to you'], 400);
        }

        $paymentId = (int) ($request->input('payment_id') ?: $request->input('vouch_id', 0));
        if ($paymentId > 0 && !$this->paymentBelongsToAgent($paymentId)) {
            $log('rejected', ['reason' => 'payment_not_found', 'payment_id' => $paymentId]);
            return response()->json(['error' => 'Payment not found'], 404);
        }

        $mode = $paymentId > 0 ? 2 : 1;
        $requestedMode = (int) $request->input('trans_mode');
        $refNo = trim((string) ($request->input('ref_vouch_no') ?: ''));
        if ($requestedMode === 2 && $refNo === '') {
            $refNo = 'QR-' . now()->format('YmdHis') . '-' . random_int(1000, 9999);
        }
        $particulars = trim((string) $request->input('particulars'));
        if ($requestedMode === 2 && stripos($particulars, 'QR') === false && stripos($particulars, 'UPI') === false) {
            $particulars = '[QR/UPI] ' . $particulars;
        }
        $particulars = mb_substr($particulars, 0, 150);

        $params = [
            $paymentId,
            $request->input('voucher_date'),
            $partyId,
            $requestedMode,
            round((float) $request->input('amount'), 2),
            $particulars,
            $refNo,
            (int) session('agent_id'),
            (int) session('year_id'),
            (int) session('branch_id'),
            $mode,
        ];

        $conn = DB::connection('coops');
        $log('calling_sp', ['params' => $params, 'mode' => $mode, 'sp' => 'USP_ADD_EDIT_AGENT_CUST_PAYMENT']);

        try {
            $result = $conn->select(
                'CALL USP_ADD_EDIT_AGENT_CUST_PAYMENT(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                $params
            );
            $log('sp_result', ['result' => $result]);

            if (!empty($result) && (int) ($result[0]->Error_No ?? 0) < 0) {
                $log('sp_error', ['message' => $result[0]->Message ?? null]);
                return response()->json(['error' => $result[0]->Message ?? 'Save failed'], 400);
            }

            $log('success');
            return response()->json(['message' => $result[0]->Message ?? 'Payment saved successfully']);
        } catch (\Throwable $e) {
            $log('exception', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);
            Log::channel('trading')->error('Agent customer payment error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Failed to save payment: ' . $e->getMessage()], 500);
        }
    }

    public function details($id)
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');

        $paymentId = (int) $id;
        $row = $this->paymentDetails($paymentId);
        if (!$row) {
            return response()->json(['error' => 'Payment not found'], 404);
        }

        return response()->json($row);
    }

    public function customerDue(Request $request)
    {
        $started = microtime(true);
        $partyId = (int) $request->input('party_id', 0);
        Log::channel('trading')->info('agent.customer.payment.due.start', [
            'party_id'   => $partyId,
            'as_on'      => $request->input('as_on'),
            'agent_id'   => session('agent_id'),
            'org_schema' => session('org_schema'),
        ]);

        if ($partyId <= 0) {
            return response()->json(['due' => 0, 'payable' => 0]);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');

        if (!$this->customerBelongsToAgent($partyId)) {
            Log::channel('trading')->warning('agent.customer.payment.due.not_assigned', ['party_id' => $partyId]);
            return response()->json(['error' => 'Selected customer is not assigned to you'], 400);
        }

        $asOn = $request->input('as_on') ?: date('Y-m-d');
        try {
            $due = $this->partyOutstanding($partyId, $asOn);
            $payable = round(max(0, $due), 2);
            Log::channel('trading')->info('agent.customer.payment.due.done', [
                'party_id'   => $partyId,
                'due'        => $due,
                'payable'    => $payable,
                'elapsed_ms' => round((microtime(true) - $started) * 1000, 2),
            ]);
            return response()->json([
                'due'     => $due,
                'payable' => $payable,
            ]);
        } catch (\Throwable $e) {
            Log::channel('trading')->error('agent.customer.payment.due.error', [
                'party_id' => $partyId,
                'message'  => $e->getMessage(),
                'elapsed_ms' => round((microtime(true) - $started) * 1000, 2),
            ]);
            return response()->json(['error' => 'Failed to load outstanding', 'due' => 0, 'payable' => 0], 500);
        }
    }

    private function partyOutstanding(int $partyId, string $asOn): float
    {
        $row = DB::connection('coops')->selectOne(
            'SELECT UDF_CAL_PARTY_CLOSING(?, ?) AS Due_Amt',
            [$partyId, $asOn]
        );

        return round((float) ($row->Due_Amt ?? 0), 2);
    }

    private function customerBelongsToAgent(int $partyId): bool
    {
        $party = DB::connection('coops')->selectOne(
            'SELECT Party_Id FROM mst_party WHERE Party_Id = ? AND Party_Type = 2 AND Cust_Agent_Id = ? LIMIT 1',
            [$partyId, session('agent_id')]
        );

        return (bool) $party;
    }

    private function paymentBelongsToAgent(int $paymentId): bool
    {
        $row = DB::connection('coops')->selectOne(
            'SELECT t.Id
               FROM trn_party_trans t
               INNER JOIN mst_party p
                       ON p.Party_Id = t.Party_Id
                      AND p.Party_Type = 2
                      AND p.Cust_Agent_Id = ?
              WHERE t.Id = ?
                AND t.Trans_Type = \'C\'
                AND IFNULL(t.Trading_Id, 0) = 0
                AND t.Trans_Mode IN (1, 2)
                AND t.Created_By = ?
                AND IFNULL(t.Txn_Id, 0) = 0
              LIMIT 1',
            [session('agent_id'), $paymentId, session('agent_id')]
        );

        return (bool) $row;
    }

    private function paymentDetails(int $paymentId): ?object
    {
        $row = DB::connection('coops')->selectOne(
            'SELECT t.Id AS Payment_Id,
                    t.Id AS Voucher_Id,
                    t.Trans_Date AS Vou_Date,
                    t.Party_Id,
                    t.Amount,
                    t.Trans_Mode AS Vou_Mode,
                    t.Txn_Id,
                    CASE
                        WHEN LOCATE(\'|\', t.Remarks) > 0
                        THEN TRIM(SUBSTRING_INDEX(t.Remarks, \'|\', 1))
                        ELSE COALESCE(m.Vou_No, CONCAT(\'ACP-\', t.Id))
                    END AS Vou_No,
                    CASE
                        WHEN (LENGTH(t.Remarks) - LENGTH(REPLACE(t.Remarks, \'|\', \'\'))) >= 2
                        THEN TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(t.Remarks, \'|\', 2), \'|\', -1))
                        ELSE \'\'
                    END AS Ref_Vou_No,
                    CASE
                        WHEN (LENGTH(t.Remarks) - LENGTH(REPLACE(t.Remarks, \'|\', \'\'))) >= 2
                        THEN TRIM(SUBSTRING_INDEX(t.Remarks, \'|\', -1))
                        WHEN LOCATE(\'|\', t.Remarks) > 0
                        THEN TRIM(SUBSTRING_INDEX(t.Remarks, \'|\', -1))
                        ELSE IFNULL(t.Remarks, \'\')
                    END AS Particulars,
                    CASE WHEN IFNULL(t.Txn_Id, 0) = 0 THEN 1 ELSE 0 END AS Can_Edit
               FROM trn_party_trans t
               INNER JOIN mst_party p
                       ON p.Party_Id = t.Party_Id
                      AND p.Party_Type = 2
                      AND p.Cust_Agent_Id = ?
               LEFT JOIN trans_voucher_master m
                      ON m.Voucher_Id = t.Txn_Id
              WHERE t.Id = ?
                AND t.Trans_Type = \'C\'
                AND IFNULL(t.Trading_Id, 0) = 0
                AND t.Trans_Mode IN (1, 2)
                AND t.Created_By = ?
              LIMIT 1',
            [session('agent_id'), $paymentId, session('agent_id')]
        );

        return $row ?: null;
    }

    private function agentCollectionList(): array
    {
        return DB::connection('coops')->select(
            'SELECT t.Id AS Payment_Id,
                    t.Id AS Voucher_Id,
                    DATE_FORMAT(t.Trans_Date, \'%d-%m-%Y\') AS Vou_Date_Disp,
                    t.Trans_Date AS Vou_Date,
                    CASE
                        WHEN LOCATE(\'|\', t.Remarks) > 0
                        THEN TRIM(SUBSTRING_INDEX(t.Remarks, \'|\', 1))
                        ELSE COALESCE(m.Vou_No, CONCAT(\'ACP-\', t.Id))
                    END AS Vou_No,
                    p.Party_Name,
                    t.Amount,
                    CASE WHEN IFNULL(t.Txn_Id, 0) = 0 THEN 1 ELSE 0 END AS Can_Edit
               FROM trn_party_trans t
               INNER JOIN mst_party p
                       ON p.Party_Id = t.Party_Id
                      AND p.Party_Type = 2
                      AND p.Cust_Agent_Id = ?
               LEFT JOIN trans_voucher_master m
                      ON m.Voucher_Id = t.Txn_Id
              WHERE t.Trans_Type = \'C\'
                AND IFNULL(t.Trading_Id, 0) = 0
                AND t.Trans_Mode IN (1, 2)
                AND t.Created_By = ?
              ORDER BY t.Id DESC
              LIMIT 10',
            [session('agent_id'), session('agent_id')]
        );
    }
}
