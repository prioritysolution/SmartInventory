<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class AgentReport extends Controller
{
    public function indent()
    {
        return view('Agent.indent-report', [
            'year_start' => session('year_start'),
            'year_end'   => session('year_end'),
            'org_name'   => session('org_name'),
            'agent_name' => session('agent_name'),
        ]);
    }

    public function indentSearch(Request $request)
    {
        $frm = $request->input('frm_date');
        $to  = $request->input('to_date');
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
        $rows = DB::connection('coops')->select('CALL USP_SEARCH_AGENT_INDENT(?, ?, ?)', [
            session('agent_id'),
            $frm,
            $to,
        ]);

        return response()->json($rows);
    }

    public function stock()
    {
        return view('Agent.stock-report', [
            'year_start' => session('year_start'),
            'year_end'   => session('year_end'),
            'org_name'   => session('org_name'),
            'agent_name' => session('agent_name'),
        ]);
    }

    public function stockSearch(Request $request)
    {
        $asOn = $request->input('as_on_date');
        if (!$asOn) {
            return response()->json(['message' => 'Select date'], 422);
        }
        if ($asOn < session('year_start') || $asOn > session('year_end')) {
            return response()->json(['message' => 'Date must be within the accounting year'], 422);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        $rows = DB::connection('coops')->select('CALL USP_GET_AGENT_STOCK_REPORT(?, ?)', [
            session('agent_id'),
            $asOn,
        ]);

        return response()->json($rows);
    }

    public function issue()
    {
        return view('Agent.issue-report', [
            'year_start' => session('year_start'),
            'year_end'   => session('year_end'),
            'org_name'   => session('org_name'),
            'agent_name' => session('agent_name'),
        ]);
    }

    public function issueSearch(Request $request)
    {
        $frm = $request->input('frm_date');
        $to  = $request->input('to_date');
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
        $rows = DB::connection('coops')->select('CALL USP_SEARCH_AGENT_ISSUE(?, ?, ?)', [
            session('agent_id'),
            $frm,
            $to,
        ]);

        return response()->json($rows);
    }

    public function sale()
    {
        return view('Agent.sale-report', [
            'year_start' => session('year_start'),
            'year_end'   => session('year_end'),
            'org_name'   => session('org_name'),
            'agent_name' => session('agent_name'),
        ]);
    }

    public function saleSearch(Request $request)
    {
        $frm = $request->input('frm_date');
        $to  = $request->input('to_date');
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
        DB::purge('coops');

        $agentId = (int) session('agent_id');
        $rows = DB::connection('coops')->select('CALL USP_SEARCH_AGENT_SALE(?, ?, ?)', [
            $agentId,
            $frm,
            $to,
        ]);

        // Mode totals from invoice Net_Amt so round-off is included (line sums omit Round_Off).
        $modeTotals = DB::connection('coops')->selectOne(
            'SELECT
                IFNULL(SUM(CASE WHEN IFNULL(Pay_Mode, 0) = 1 THEN Net_Amt ELSE 0 END), 0) AS Cash_Amt,
                IFNULL(SUM(CASE WHEN IFNULL(Pay_Mode, 0) = 2 THEN Net_Amt ELSE 0 END), 0) AS Bank_Amt,
                IFNULL(SUM(CASE WHEN IFNULL(Pay_Mode, 0) = 3 THEN Net_Amt ELSE 0 END), 0) AS Credit_Amt,
                IFNULL(SUM(Net_Amt), 0) AS Net_Amt,
                IFNULL(SUM(IFNULL(Round_Off, 0)), 0) AS Round_Off_Amt
             FROM trans_trading
            WHERE Agent_Id = ?
              AND Tranding_Type = 3
              AND Invoice_Date BETWEEN ? AND ?',
            [$agentId, $frm, $to]
        );

        $cash = (float) ($modeTotals->Cash_Amt ?? 0);
        $bank = (float) ($modeTotals->Bank_Amt ?? 0);
        $credit = (float) ($modeTotals->Credit_Amt ?? 0);
        $net = (float) ($modeTotals->Net_Amt ?? 0);
        $roundOff = (float) ($modeTotals->Round_Off_Amt ?? 0);

        $collected = (float) (DB::connection('coops')->selectOne(
            'SELECT IFNULL(SUM(t.Amount), 0) AS Amt
               FROM trn_party_trans t
               INNER JOIN mst_party p
                       ON p.Party_Id = t.Party_Id
                      AND p.Party_Type = 2
                      AND p.Cust_Agent_Id = ?
              WHERE t.Created_By = ?
                AND t.Trans_Type = \'C\'
                AND IFNULL(t.Trading_Id, 0) = 0
                AND t.Trans_Mode IN (1, 2)
                AND t.Trans_Date BETWEEN ? AND ?',
            [$agentId, $agentId, $frm, $to]
        )->Amt ?? 0);

        // Remaining customer credit due (as on To Date) for parties with credit sales in range.
        $unsettled = (float) (DB::connection('coops')->selectOne(
            'SELECT IFNULL(SUM(GREATEST(UDF_CAL_PARTY_CLOSING(p.Party_Id, ?), 0)), 0) AS Amt
               FROM mst_party p
              WHERE p.Party_Type = 2
                AND p.Cust_Agent_Id = ?
                AND EXISTS (
                    SELECT 1
                      FROM trans_trading t
                     WHERE t.Party_Id = p.Party_Id
                       AND t.Agent_Id = ?
                       AND t.Tranding_Type = 3
                       AND IFNULL(t.Pay_Mode, 0) = 3
                       AND t.Invoice_Date BETWEEN ? AND ?
                )',
            [$to, $agentId, $agentId, $frm, $to]
        )->Amt ?? 0);

        return response()->json([
            'rows' => $rows,
            'summary' => [
                'cash'              => round($cash, 2),
                'bank'              => round($bank, 2),
                'credit'            => round($credit, 2),
                'credit_settled'    => round($collected, 2),
                'credit_unsettled'  => round($unsettled, 2),
                'net'               => round($net, 2),
                'round_off'         => round($roundOff, 2),
            ],
        ]);
    }

    public function officeReturn()
    {
        return view('Agent.return-report', [
            'year_start' => session('year_start'),
            'year_end'   => session('year_end'),
            'org_name'   => session('org_name'),
            'agent_name' => session('agent_name'),
        ]);
    }

    public function officeReturnSearch(Request $request)
    {
        $frm = $request->input('frm_date');
        $to  = $request->input('to_date');
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
        $rows = DB::connection('coops')->select('CALL USP_SEARCH_AGENT_OFFICE_RETURN(?, ?, ?)', [
            session('agent_id'),
            $frm,
            $to,
        ]);

        return response()->json($rows);
    }

    public function settlement()
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');

        $agent = DB::connection('coops')->selectOne(
            'SELECT Settle_Token FROM mst_agent WHERE Agent_Id = ? LIMIT 1',
            [session('agent_id')]
        );
        $settleToken = trim((string) ($agent->Settle_Token ?? ''));
        if ($settleToken !== '') {
            session(['agent_settle_token' => $settleToken]);
        } else {
            session()->forget('agent_settle_token');
        }

        return view('Agent.settlement', [
            'year_start'   => session('year_start'),
            'year_end'     => session('year_end'),
            'org_name'     => session('org_name'),
            'agent_name'   => session('agent_name'),
            'settle_token' => $settleToken,
        ]);
    }

    public function settlementSearch(Request $request)
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');

        $rows = DB::connection('coops')->select('CALL USP_SEARCH_AGENT_SETTLEMENT(?)', [
            session('agent_id'),
        ]);

        $agent = DB::connection('coops')->selectOne(
            'SELECT Settle_Token FROM mst_agent WHERE Agent_Id = ? LIMIT 1',
            [session('agent_id')]
        );
        $settleToken = trim((string) ($agent->Settle_Token ?? ''));
        if ($settleToken !== '') {
            session(['agent_settle_token' => $settleToken]);
        } else {
            session()->forget('agent_settle_token');
        }

        return response()->json([
            'rows'         => $rows,
            'settle_token' => $settleToken,
            'denomination' => $this->getSettlementDenomByToken($settleToken),
        ]);
    }

    public function generateSettlementToken(Request $request)
    {
        $request->validate([
            'coin'   => 'nullable|integer|min:0',
            'rs_5'   => 'nullable|integer|min:0',
            'rs_10'  => 'nullable|integer|min:0',
            'rs_20'  => 'nullable|integer|min:0',
            'rs_50'  => 'nullable|integer|min:0',
            'rs_100' => 'nullable|integer|min:0',
            'rs_200' => 'nullable|integer|min:0',
            'rs_500' => 'nullable|integer|min:0',
            'settle_date' => 'nullable|date',
        ]);

        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');

        $agentId = (int) session('agent_id');
        $coin = (int) $request->input('coin', 0);
        $rs5 = (int) $request->input('rs_5', 0);
        $rs10 = (int) $request->input('rs_10', 0);
        $rs20 = (int) $request->input('rs_20', 0);
        $rs50 = (int) $request->input('rs_50', 0);
        $rs100 = (int) $request->input('rs_100', 0);
        $rs200 = (int) $request->input('rs_200', 0);
        $rs500 = (int) $request->input('rs_500', 0);
        $denomTotal = (float) (
            $coin
            + ($rs5 * 5)
            + ($rs10 * 10)
            + ($rs20 * 20)
            + ($rs50 * 50)
            + ($rs100 * 100)
            + ($rs200 * 200)
            + ($rs500 * 500)
        );
        $cashTotal = $this->getPendingSettlementCashTotal($agentId);

        // Existing token: skip denom validation (view only). New token: denom must match cash.
        $existing = DB::connection('coops')->selectOne(
            'SELECT Settle_Token FROM mst_agent WHERE Agent_Id = ? LIMIT 1',
            [$agentId]
        );
        $existingToken = trim((string) ($existing->Settle_Token ?? ''));

        if ($existingToken === '') {
            if ($cashTotal > 0 && $denomTotal <= 0) {
                return response()->json(['message' => 'Enter cash denomination before generating token'], 422);
            }
            if ($denomTotal > $cashTotal + 0.009) {
                return response()->json([
                    'message' => 'Denomination total cannot exceed Cash Total (₹ ' . number_format($cashTotal, 2, '.', '') . ')',
                ], 422);
            }
            if (abs($denomTotal - $cashTotal) > 0.009) {
                return response()->json([
                    'message' => 'Denomination total must match Cash Total (₹ ' . number_format($cashTotal, 2, '.', '') . ')',
                ], 422);
            }
        }

        $result = DB::connection('coops')->select('CALL USP_GEN_AGENT_SETTLE_TOKEN(?)', [
            $agentId,
        ]);

        $row = $result[0] ?? null;
        if (!$row || (int) ($row->Error_No ?? -1) < 0) {
            session()->forget('agent_settle_token');
            return response()->json([
                'message' => $row->Message ?? 'Failed to generate token',
            ], 422);
        }

        $token = trim((string) ($row->Settle_Token ?? ''));
        $alreadyExists = (int) ($row->Already_Exists ?? 0) === 1;

        if ($token !== '' && !$alreadyExists) {
            $denomResult = DB::connection('coops')->select(
                'CALL USP_SAVE_AGENT_SETTLE_DENOM(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    $agentId,
                    $request->input('settle_date') ?: date('Y-m-d'),
                    $coin,
                    $rs5,
                    $rs10,
                    $rs20,
                    $rs50,
                    $rs100,
                    $rs200,
                    $rs500,
                    $token,
                ]
            );
            $denomRow = $denomResult[0] ?? null;
            if ($denomRow && (int) ($denomRow->Error_No ?? 0) < 0) {
                return response()->json([
                    'message' => $denomRow->Message ?? 'Token generated but denomination save failed',
                    'settle_token' => $token,
                    'already_exists' => false,
                ], 422);
            }
        }

        if ($token !== '') {
            session(['agent_settle_token' => $token]);
        }

        $denom = $this->getSettlementDenomByToken($token);

        return response()->json([
            'message'        => $row->Message ?? 'Token generated',
            'settle_token'   => $token,
            'pending_count'  => (int) ($row->Pending_Count ?? 0),
            'already_exists' => $alreadyExists,
            'denomination'   => $denom,
        ]);
    }

    public function cancelSettlementToken(Request $request)
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');

        $agentId = (int) session('agent_id');
        if ($agentId <= 0) {
            return response()->json(['message' => 'Session expired. Please login again.'], 401);
        }

        $result = DB::connection('coops')->select('CALL USP_CANCEL_AGENT_SETTLE_TOKEN(?)', [$agentId]);
        $row = $result[0] ?? null;
        if (!$row || (int) ($row->Error_No ?? -1) < 0) {
            return response()->json([
                'message' => $row->Message ?? 'Failed to cancel token',
            ], 422);
        }

        session()->forget('agent_settle_token');

        return response()->json([
            'message' => $row->Message ?? 'Settlement token cancelled successfully',
        ]);
    }

    private function getPendingSettlementCashTotal(int $agentId): float
    {
        $rows = DB::connection('coops')->select('CALL USP_SEARCH_AGENT_SETTLEMENT(?)', [$agentId]);
        $cash = 0.0;
        foreach ($rows as $row) {
            $mode = (int) ($row->Pay_Mode ?? 0);
            if ($mode === 1) {
                $cash += (float) ($row->Net_Amt ?? 0);
            }
        }

        return round($cash, 2);
    }

    private function getSettlementDenomByToken(?string $token): ?array
    {
        $token = trim((string) $token);
        if ($token === '') {
            return null;
        }

        $rows = DB::connection('coops')->select('CALL USP_GET_AGENT_SETTLE_DENOM(?)', [$token]);
        $row = $rows[0] ?? null;
        if (!$row) {
            return null;
        }

        return [
            'agent_id'    => (int) ($row->Agent_Id ?? 0),
            'settle_date' => $row->Settle_Date ?? null,
            'coin'        => (int) ($row->Coin ?? 0),
            'rs_5'        => (int) ($row->Rs_5 ?? 0),
            'rs_10'       => (int) ($row->Rs_10 ?? 0),
            'rs_20'       => (int) ($row->Rs_20 ?? 0),
            'rs_50'       => (int) ($row->Rs_50 ?? 0),
            'rs_100'      => (int) ($row->Rs_100 ?? 0),
            'rs_200'      => (int) ($row->Rs_200 ?? 0),
            'rs_500'      => (int) ($row->Rs_500 ?? 0),
            'token'       => $row->Token ?? $token,
            'denom_total' => (float) ($row->Denom_Total ?? 0),
        ];
    }

    public function customerDue()
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

        return view('Agent.customer-due-report', [
            'customers'  => $customers,
            'year_start' => session('year_start'),
            'year_end'   => session('year_end'),
            'org_name'   => session('org_name'),
            'agent_name' => session('agent_name'),
        ]);
    }

    public function customerDueSearch(Request $request)
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');

        $agentId = (int) session('agent_id');
        $asOn = $request->input('as_on') ?: date('Y-m-d');
        $partyId = (int) $request->input('party_id', 0);
        $q = trim((string) $request->input('q', ''));

        $params = [$asOn, $asOn, $asOn, $agentId, $agentId];
        $searchSql = '';
        if ($partyId > 0) {
            $searchSql .= ' AND p.Party_Id = ?';
            $params[] = $partyId;
        } elseif ($q !== '') {
            $searchSql .= ' AND (p.Party_Code LIKE ? OR p.Party_Name LIKE ? OR IFNULL(p.Contact_No, \'\') LIKE ?)';
            $like = '%' . $q . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $rows = DB::connection('coops')->select(
            "SELECT
                p.Party_Id,
                p.Party_Code,
                p.Party_Name,
                IFNULL(p.Contact_No, '') AS Contact_No,
                IFNULL(p.Credit_Limit, 0) AS Credit_Limit,
                IFNULL(p.Opening_Bal, 0) AS Opening_Bal,
                IFNULL((
                    SELECT SUM(t.Amount)
                      FROM trn_party_trans t
                     WHERE t.Party_Id = p.Party_Id
                       AND t.Trans_Type = 'D'
                       AND t.Trans_Date <= ?
                       AND (IFNULL(t.Trading_Id, 0) = 0 OR t.Trans_Mode = 3)
                ), 0) AS Debit_Amt,
                IFNULL((
                    SELECT SUM(t.Amount)
                      FROM trn_party_trans t
                     WHERE t.Party_Id = p.Party_Id
                       AND t.Trans_Type = 'C'
                       AND t.Trans_Date <= ?
                       AND (IFNULL(t.Trading_Id, 0) = 0 OR t.Trans_Mode = 3)
                ), 0) AS Paid_Amt,
                UDF_CAL_PARTY_CLOSING(p.Party_Id, ?) AS Remaining_Amt,
                (
                    SELECT MIN(tr.Invoice_Date)
                      FROM trans_trading tr
                     WHERE tr.Party_Id = p.Party_Id
                       AND tr.Agent_Id = ?
                       AND tr.Tranding_Type = 3
                       AND IFNULL(tr.Pay_Mode, 0) = 3
                       AND IFNULL(tr.Status_Cd, 1) = 1
                ) AS Due_Date
             FROM mst_party p
            WHERE p.Party_Type = 2
              AND p.Cust_Agent_Id = ?
              AND IFNULL(p.Status_Cd, 1) = 1
              {$searchSql}
            ORDER BY Remaining_Amt DESC, p.Party_Name ASC",
            $params
        );

        foreach ($rows as $row) {
            $opening = (float) ($row->Opening_Bal ?? 0);
            $debit = (float) ($row->Debit_Amt ?? 0);
            $paid = (float) ($row->Paid_Amt ?? 0);
            $row->Due_Amt = round($opening + $debit, 2);
            $row->Paid_Amt = round($paid, 2);
            $row->Remaining_Amt = round((float) ($row->Remaining_Amt ?? 0), 2);
        }

        return response()->json($rows);
    }

    public function customerDueHistory(Request $request, $id)
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');

        $agentId = (int) session('agent_id');
        $partyId = (int) $id;

        $party = DB::connection('coops')->selectOne(
            'SELECT Party_Id, Party_Code, Party_Name
               FROM mst_party
              WHERE Party_Id = ?
                AND Party_Type = 2
                AND Cust_Agent_Id = ?
              LIMIT 1',
            [$partyId, $agentId]
        );

        if (!$party) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        $asOn = $request->input('as_on') ?: date('Y-m-d');
        $remaining = (float) (DB::connection('coops')->selectOne(
            'SELECT UDF_CAL_PARTY_CLOSING(?, ?) AS Due_Amt',
            [$partyId, $asOn]
        )->Due_Amt ?? 0);

        $history = DB::connection('coops')->select(
            "SELECT
                t.Id,
                t.Trans_Date,
                t.Trans_Type,
                t.Amount,
                t.Trans_Mode,
                t.Remarks,
                t.Trading_Id,
                t.Txn_Id,
                CASE
                    WHEN t.Trans_Type = 'D' THEN 'Credit Sale'
                    WHEN t.Trans_Type = 'C' THEN 'Payment'
                    ELSE t.Trans_Type
                END AS Entry_Type,
                CASE
                    WHEN t.Trans_Type = 'C' AND t.Trans_Mode = 1 THEN 'Cash'
                    WHEN t.Trans_Type = 'C' AND t.Trans_Mode = 2 THEN 'Bank / UPI'
                    WHEN t.Trans_Type = 'D' AND t.Trans_Mode = 3 THEN 'Credit'
                    ELSE '-'
                END AS Mode_Name,
                CASE
                    WHEN t.Trading_Id IS NOT NULL THEN (
                        SELECT tr.Invoice_No FROM trans_trading tr WHERE tr.Trading_Id = t.Trading_Id LIMIT 1
                    )
                    WHEN LOCATE('|', IFNULL(t.Remarks, '')) > 0 THEN TRIM(SUBSTRING_INDEX(t.Remarks, '|', 1))
                    WHEN t.Txn_Id IS NOT NULL THEN (
                        SELECT m.Vou_No FROM trans_voucher_master m WHERE m.Voucher_Id = t.Txn_Id LIMIT 1
                    )
                    ELSE ''
                END AS Ref_No,
                CASE
                    WHEN (LENGTH(IFNULL(t.Remarks, '')) - LENGTH(REPLACE(IFNULL(t.Remarks, ''), '|', ''))) >= 2
                        THEN TRIM(SUBSTRING_INDEX(t.Remarks, '|', -1))
                    WHEN LOCATE('|', IFNULL(t.Remarks, '')) > 0
                        THEN TRIM(SUBSTRING_INDEX(t.Remarks, '|', -1))
                    ELSE IFNULL(t.Remarks, '')
                END AS Particulars
             FROM trn_party_trans t
            WHERE t.Party_Id = ?
              AND (IFNULL(t.Trading_Id, 0) = 0 OR t.Trans_Mode = 3)
            ORDER BY t.Trans_Date DESC, t.Id DESC",
            [$partyId]
        );

        return response()->json([
            'party' => $party,
            'remaining' => round($remaining, 2),
            'history' => $history,
        ]);
    }
}
