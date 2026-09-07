<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Concerns\VerifiesStoredPassword;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Exception;

class ProcessLogin extends Controller
{
    use VerifiesStoredPassword;

    private const REMEMBER_MINUTES = 60 * 24 * 30; // 30 days

    public function index_login()
    {
        return view('Auth.login', [
            'rememberOrg' => Cookie::get('si_remember_org', ''),
            'rememberUser' => Cookie::get('si_remember_user', ''),
            'rememberPass' => Cookie::get('si_remember_pass', ''),
            'rememberChecked' => Cookie::get('si_remember_me') === '1',
        ]);
    }

    public function process_login(Request $request)
    {
        $request->validate([
            'pOrg_Code' => 'required|digits:4',
            'pUser_Name' => 'required|string|max:100',
            'pUser_Pass' => 'required|string|min:3',
            'remember_me' => 'nullable|boolean',
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
            Config::set('database.connections.coops.database', $org_schema);
            DB::purge('coops');

            $user = DB::connection('coops')->select(
                'CALL USP_VALIDATE_USER_LOGIN(?)',
                [$request->pUser_Name]
            );

            if (!$user || empty($user)) {
                return response()->json(['status' => 'error', 'data' => 'Invalid credentials']);
            }

            $userData = $user[0];

            if ($userData->Error_No != 0) {
                return response()->json(['status' => 'error', 'data' => $userData->Message]);
            }

            if (!$this->loginPasswordIsValid($userData->User_Pwd ?? '', $request->pUser_Pass)) {
                return response()->json(['status' => 'error', 'data' => 'Invalid Password Please Enter A Valid Password !!']);
            }

            session([
                'user_id' => $userData->User_Id,
                'user_code' => $userData->User_Code,
                'user_name' => $userData->User_FullName,
                'user_group_id' => $userData->UGrp_Id,
                'is_admin' => isset($userData->Is_Admin) ? (bool)$userData->Is_Admin : ($userData->UGrp_Id == 1),
                'branch_id' => $userData->Branch_Id,
                'org_id' => $userData->Org_Id,
                'org_code' => $userData->Org_Code,
                'org_name' => $userData->Org_Name,
                'branch_code' => $userData->Branch_Code,
                'branch_name' => $userData->Branch_Name,
                'org_schema' => $org_schema,
                'year_id' => $userData->Year_Id,
                'year_desc' => $userData->Year_Desc,
                'year_start' => $userData->Year_Start,
                'year_end' => $userData->Year_End,
                'gst_have' => $userData->Gst_Have,
                'gst_type' => $userData->Gst_Type,
            ]);

            $response = response()->json(['status' => 'success', 'data' => 'Login successful']);

            if ($request->boolean('remember_me')) {
                $response
                    ->withCookie(cookie('si_remember_me', '1', self::REMEMBER_MINUTES))
                    ->withCookie(cookie('si_remember_org', $request->pOrg_Code, self::REMEMBER_MINUTES))
                    ->withCookie(cookie('si_remember_user', $request->pUser_Name, self::REMEMBER_MINUTES))
                    ->withCookie(cookie('si_remember_pass', $request->pUser_Pass, self::REMEMBER_MINUTES));
            } else {
                $response
                    ->withCookie(Cookie::forget('si_remember_me'))
                    ->withCookie(Cookie::forget('si_remember_org'))
                    ->withCookie(Cookie::forget('si_remember_user'))
                    ->withCookie(Cookie::forget('si_remember_pass'));
            }

            return $response;
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
}
