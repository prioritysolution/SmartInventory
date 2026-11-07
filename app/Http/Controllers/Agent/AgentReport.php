<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Concerns\PaginatesReportRows;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class AgentReport extends Controller
{
    use PaginatesReportRows;

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
Config::set('database.connections.coops.database', session('org_schema'));
        $rows = DB::connection('coops')->select('CALL USP_SEARCH_AGENT_INDENT(?, ?, ?)', [
            session('agent_id'),
            $frm,
            $to,
        ]);

        return $this->pagedReport($request, $rows);
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
Config::set('database.connections.coops.database', session('org_schema'));
        $rows = DB::connection('coops')->select('CALL USP_GET_AGENT_STOCK_REPORT(?, ?)', [
            session('agent_id'),
            $asOn,
        ]);

        return $this->pagedReport($request, $rows);
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
Config::set('database.connections.coops.database', session('org_schema'));
        $rows = DB::connection('coops')->select('CALL USP_SEARCH_AGENT_ISSUE(?, ?, ?)', [
            session('agent_id'),
            $frm,
            $to,
        ]);

        return $this->pagedReport($request, $rows);
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
Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');

        $agentId = (int) session('agent_id');
        $rows = DB::connection('coops')->select('CALL USP_SEARCH_AGENT_SALE(?, ?, ?)', [
            $agentId,
            $frm,
            $to,
        ]);

        $summary = DB::connection('coops')->selectOne(
            'CALL USP_GET_AGENT_SALE_REPORT_SUMMARY(?, ?, ?)',
            [$agentId, $frm, $to]
        );

        $cash = (float) ($summary->Cash_Amt ?? 0);
        $bank = (float) ($summary->Bank_Amt ?? 0);
        $credit = (float) ($summary->Credit_Amt ?? 0);
        $net = (float) ($summary->Net_Amt ?? 0);
        $roundOff = (float) ($summary->Round_Off_Amt ?? 0);
        $collected = (float) ($summary->Collected_Amt ?? 0);
        $unsettled = (float) ($summary->Unsettled_Amt ?? 0);
        $summaryPayload = [
            'cash'              => round($cash, 2),
            'bank'              => round($bank, 2),
            'credit'            => round($credit, 2),
            'credit_settled'    => round($collected, 2),
            'credit_unsettled'  => round($unsettled, 2),
            'net'               => round($net, 2),
            'round_off'         => round($roundOff, 2),
        ];

        if ((int) $request->input('limit', 0) > 0) {
            $page = $this->pagedReport($request, $rows);
            $payload = json_decode($page->getContent(), true) ?: [];
            $payload['summary'] = $summaryPayload;
            return response()->json($payload);
        }

        return response()->json([
            'rows' => $rows,
            'summary' => $summaryPayload,
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
Config::set('database.connections.coops.database', session('org_schema'));
        $rows = DB::connection('coops')->select('CALL USP_SEARCH_AGENT_OFFICE_RETURN(?, ?, ?)', [
            session('agent_id'),
            $frm,
            $to,
        ]);

        return $this->pagedReport($request, $rows);
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

        // Customers via AJAX Select2 (agent.parties.search)
        $customers = [];

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

        $filterPartyId = $partyId > 0 ? $partyId : 0;
        $search = ($partyId > 0 || $q === '') ? '' : $q;

        $rows = DB::connection('coops')->select(
            'CALL USP_SEARCH_AGENT_CUSTOMER_DUE(?, ?, ?, ?)',
            [$asOn, $agentId, $filterPartyId, $search]
        );

        foreach ($rows as $row) {
            $opening = (float) ($row->Opening_Bal ?? 0);
            $debit = (float) ($row->Debit_Amt ?? 0);
            $paid = (float) ($row->Paid_Amt ?? 0);
            $row->Due_Amt = round($opening + $debit, 2);
            $row->Paid_Amt = round($paid, 2);
            $row->Remaining_Amt = round((float) ($row->Remaining_Amt ?? 0), 2);
        }

        return $this->pagedReport($request, $rows);
    }

    public function customerDueHistory(Request $request, $id)
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');

        $agentId = (int) session('agent_id');
        $partyId = (int) $id;

        $party = DB::connection('coops')->selectOne(
            'CALL USP_GET_AGENT_CUSTOMER_PARTY(?, ?)',
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
            'CALL USP_GET_AGENT_CUSTOMER_PAYMENT_HISTORY(?)',
            [$partyId]
        );

        return response()->json([
            'party' => $party,
            'remaining' => round($remaining, 2),
            'history' => $history,
        ]);
    }
}
