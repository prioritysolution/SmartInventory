<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class AgentReport extends Controller
{
    private function bindOrg(): void
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');
    }

    public function indent()
    {
        return view('Agent.indent-report', [
            'year_start' => session('year_start'),
            'year_end'   => session('year_end'),
        ]);
    }

    private function toIsoDate(?string $date): ?string
    {
        if (!$date) {
            return null;
        }
        $date = trim($date);
        if (preg_match('/^(\d{2})[\/\-](\d{2})[\/\-](\d{4})$/', $date, $m)) {
            return "{$m[3]}-{$m[2]}-{$m[1]}";
        }
        return substr($date, 0, 10);
    }

    public function indentSearch(Request $request)
    {
        $frm = $this->toIsoDate($request->frm_date);
        $to  = $this->toIsoDate($request->to_date);
        if (!$frm || !$to) {
            return response()->json(['message' => 'Select from date and to date'], 422);
        }

        $this->bindOrg();
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
        ]);
    }

    public function stockSearch(Request $request)
    {
        $asOn = $this->toIsoDate($request->as_on_date);
        if (!$asOn) {
            return response()->json(['message' => 'Select date'], 422);
        }

        $this->bindOrg();
        $rows = DB::connection('coops')->select('CALL USP_GET_AGENT_STOCK_REPORT(?, ?)', [
            session('agent_id'),
            $asOn,
        ]);

        return response()->json($rows);
    }

    public function issue()
    {
        return $this->registerView('Issue Report', 'Indent No', url('/agent/report/issue/search'));
    }

    public function issueSearch(Request $request)
    {
        return $this->dateRangeSearch($request, 'CALL USP_SEARCH_AGENT_ISSUE(?, ?, ?)');
    }

    public function sale()
    {
        return $this->registerView('Sale Report', 'Invoice No', url('/agent/report/sale/search'));
    }

    public function saleSearch(Request $request)
    {
        return $this->dateRangeSearch($request, 'CALL USP_SEARCH_AGENT_SALE(?, ?, ?)');
    }

    public function officeReturn()
    {
        return $this->registerView('Return Report', 'Return No', url('/agent/report/return/search'));
    }

    public function officeReturnSearch(Request $request)
    {
        return $this->dateRangeSearch($request, 'CALL USP_SEARCH_AGENT_OFFICE_RETURN(?, ?, ?)');
    }

    private function registerView(string $pageTitle, string $docLabel, string $searchUrl)
    {
        return view('Agent.register-report', [
            'pageTitle'  => $pageTitle,
            'docLabel'   => $docLabel,
            'searchUrl'  => $searchUrl,
            'year_start' => session('year_start'),
            'year_end'   => session('year_end'),
        ]);
    }

    private function dateRangeSearch(Request $request, string $call)
    {
        $frm = $this->toIsoDate($request->frm_date);
        $to  = $this->toIsoDate($request->to_date);
        if (!$frm || !$to) {
            return response()->json(['message' => 'Select from date and to date'], 422);
        }

        $this->bindOrg();
        $rows = DB::connection('coops')->select($call, [
            session('agent_id'),
            $frm,
            $to,
        ]);

        return response()->json($rows);
    }
}
