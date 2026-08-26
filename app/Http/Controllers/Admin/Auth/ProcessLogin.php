<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Exception;

class ProcessLogin extends Controller
{
    public function index_login()
    {
    
        return view('Auth.login');
    }

    public function process_login(Request $request)
    {
        $request->validate([
            'pOrg_Code' => 'required|digits:4',
            'pUser_Name' => 'required|string|max:100',
            'pUser_Pass' => 'required|string|min:3',
        ], [
            'pOrg_Code.digits' => 'Organization Code must be exactly 4 digits',
            'pUser_Name.max' => 'User Name must not exceed 100 characters',
            'pUser_Pass.min' => 'Password must be at least 3 characters',
        ]);
        
        try {
            $sql = DB::select("SELECT UDF_GET_ORG_SCHEMA(?) as db", [$request->pOrg_Code]);

            if (!$sql || !$sql[0]->db) {
                return response()->json(['status' => 'error', 'data' => 'Invalid organization code']);
            }

            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);

            $user = DB::connection('coops')->select("CALL USP_VALIDATE_USER_LOGIN(?, ?)", [$request->pUser_Name, $request->pUser_Pass]);

            if (!$user || empty($user)) {
                return response()->json(['status' => 'error', 'data' => 'Invalid credentials']);
            }

            $userData = $user[0];

            if ($userData->Error_No != 0) {
                return response()->json(['status' => 'error', 'data' => $userData->Message]);
            }

            session([
                'user_id' => $userData->User_Id,
                'user_code' => $userData->User_Code,
                'user_name' => $userData->User_FullName,
                'user_group_id' => $userData->UGrp_Id,
                'branch_id' => $userData->Branch_Id,
                'org_id' => $userData->Org_Id,
                'org_code' => $userData->Org_Code,
                'org_name' => $userData->Org_Name,
                'branch_code' =>  $userData->Branch_Code,
                'branch_name' =>  $userData->Branch_Name,
                'org_schema' =>   $org_schema,
                'year_id'     =>  $userData->Year_Id,      
                'year_desc'   =>  $userData->Year_Desc,    
                'year_start'  =>  $userData->Year_Start,   
                'year_end'    =>  $userData->Year_End,  
                'gst_have'   =>   $userData->Gst_Have,
                'gst_type'   =>   $userData->Gst_Type,
            ]);
            return response()->json(['status' => 'success', 'data' => 'Login successful']);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'data' => $e->getMessage()]);
        }
    }


    public function index_dashboard()
    {
        if (!session()->has('user_id')) {
            return redirect()->route('login-index');
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');

        $branchId = session('branch_id');
        $yearId   = session('year_id');

        $stats = DB::connection('coops')->select('CALL USP_GET_DASHBOARD_STATS(?, ?)', [$branchId, $yearId]);
        $stats = !empty($stats) ? $stats[0] : null;

        $chartData = DB::connection('coops')->select('CALL USP_GET_MONTHLY_PURCHASE_SALE(?, ?)', [$branchId, $yearId]);

        $reorderAlerts = DB::connection('coops')->select('CALL USP_GET_REORDER_ALERTS(?, ?)', [$branchId, $yearId]);

        return view('Dashboard.dashboard', compact(
            'stats', 'chartData', 'reorderAlerts'
        ));
    }




    public function logout()
    {
        session()->flush();
        return redirect()->route('login-index');
    }

    public function index_forgot_pass()
    {
        return view('Auth.forgot-password');
    }
}
