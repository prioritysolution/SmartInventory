<?php

namespace App\Http\Controllers\Agent\Auth;  

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;

class AgentLogin extends Controller
{
    public function Agent_login()
    {
        if (Session::has('agent_id') ) {
            return redirect()->route('agent.dashboard');
        }
        
        return view('AgentAuth.login');
    }

    public function Agent_dashboard()
    {
        return view('AgentDashboard.dashboard');
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
        $orgSchema = DB::connection('coops')->selectOne(
            'SELECT UDF_GET_ORG_SCHEMA(?) AS Org_Schema',
            [$request->org_code]
        );

        if (!$orgSchema || !$orgSchema->Org_Schema) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Organization Code !!'
            ], 400);
        }

        // Step 2: Run login SP against the resolved schema
        Config::set('database.connections.coops.database', $orgSchema->Org_Schema);
        DB::purge('coops');

        $result = DB::connection('coops')->select('CALL USP_VALIDATE_AGENT_LOGIN(?, ?)', [
            $request->agent_code,
            $request->password
        ]);

        if (!empty($result)) {
            $loginResult = $result[0];

            if ($loginResult->Error_No == 0) {
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
            }

            return response()->json([
                'success' => false,
                'message' => $loginResult->Message
            ], 400);
        }

        return response()->json([
            'success' => false,
            'message' => 'Login failed. Please try again.'
        ], 400);

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
