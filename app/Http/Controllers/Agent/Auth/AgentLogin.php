<?php

namespace App\Http\Controllers\Agent\Auth;  

use App\Http\Controllers\Concerns\VerifiesStoredPassword;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class AgentLogin extends Controller
{
    use VerifiesStoredPassword;

    public function Agent_login()
    {
        if (Session::has('agent_id') ) {
            return redirect()->route('agent.dashboard');
        }
        
        return view('AgentAuth.login');
    }

    public function Agent_dashboard()
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');

        $agentId = session('agent_id');
        $yearId  = session('year_id');
        $today   = Carbon::now('Asia/Kolkata')->toDateString();

        $stats     = null;
        $chartData = [];
        $lowStock  = [];

        try {
            $rows = DB::connection('coops')->select(
                'CALL USP_GET_AGENT_DASHBOARD_STATS(?, ?, ?)',
                [$agentId, $yearId, $today]
            );
            $stats = !empty($rows) ? $rows[0] : null;

            $chartData = DB::connection('coops')->select(
                'CALL USP_GET_AGENT_MONTHLY_SALE_RETURN(?, ?)',
                [$agentId, $yearId]
            );

            $lowStock = DB::connection('coops')->select(
                'CALL USP_GET_AGENT_LOW_STOCK(?, ?)',
                [$agentId, $today]
            );
        } catch (\Exception $e) {
            Log::error('Agent dashboard error: ' . $e->getMessage());
        }

        return view('AgentDashboard.dashboard', compact('stats', 'chartData', 'lowStock'));
    }

    public function login(Request $request)
{
    $request->validate([
        'org_code'   => 'required|string|max:4',
        'agent_code' => 'required|numeric',
        'password'   => 'required'
    ]);

    try {
        // Step 1: Get org schema from core db using org code
        $orgSchema = DB::selectOne(
            'SELECT UDF_GET_ORG_SCHEMA(?) AS Org_Schema',
            [$request->org_code]
        );

        if (!$orgSchema || !$orgSchema->Org_Schema) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Organization Code !!'
            ], 400);
        }

        Config::set('database.connections.coops.database', $orgSchema->Org_Schema);
        DB::purge('coops');

        $result = DB::connection('coops')->select('CALL USP_VALIDATE_AGENT_LOGIN(?)', [
            $request->agent_code,
        ]);

        if (empty($result)) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed. Please try again.'
            ], 400);
        }

        $loginResult = $result[0];

        if ($loginResult->Error_No != 0) {
            return response()->json([
                'success' => false,
                'message' => $loginResult->Message
            ], 400);
        }

        if (!$this->loginPasswordIsValid($loginResult->LogIn_Pwd ?? '', (string) $request->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Password Entred !!'
            ], 400);
        }

        session([
            'agent_id'    => $loginResult->Agent_Id,
            'agent_code'  => $loginResult->Agent_Code,
            'agent_name'  => $loginResult->Agent_Name,
            'branch_id'   => $loginResult->Branch_Id,
            'org_id'      => $loginResult->Org_Id,
            'org_code'    => $loginResult->Org_Code,
            'org_name'    => $loginResult->Org_Name,
            'org_schema'  => $orgSchema->Org_Schema,
            'branch_code' => $loginResult->Branch_Code,
            'branch_name' => $loginResult->Branch_Name,
            'year_id'     => $loginResult->Year_Id,
            'year_desc'   => $loginResult->Year_Desc,
            'year_start'  => $loginResult->Year_Start,
            'year_end'    => $loginResult->Year_End,
            'gst_have'    => $loginResult->Gst_Have,
            'gst_type'    => $loginResult->Gst_Type,
        ]);

        return response()->json(['success' => true]);

    } catch (\Exception $e) {
     
        return response()->json([
            'success' => false,
            'message' => 'System error: ' . $e->getMessage()
        ], 500);
    }
}


    public function logout()
    {
        Session::flush();
        return redirect()->route('agent.login')->with('success', 'Logged out successfully!');
    }
}
