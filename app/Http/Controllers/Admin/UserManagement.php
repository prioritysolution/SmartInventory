<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class UserManagement extends Controller
{
    public function index()
    {
        Config::set('database.connections.coops.database', session('org_schema'));    
        $users = DB::connection('coops')->select('CALL USP_GET_USER_LIST(?)', [session('branch_id')]);
        $groups = DB::connection('coops')->select('CALL USP_GET_USER_GROUP()');
        $statusOptions = DB::select('CALL USP_GET_APPLICATION_OPTION(?)', [1]); 
        return view('Admin.user-creation', compact('users', 'groups', 'statusOptions'))
            ->with('pageTitle', 'User Management');
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_code' => 'required|string|max:25',
            'user_name' => 'required|string|max:25',
            'full_name' => 'required|string|max:100',
            'mobile' => 'required|digits:10',
            'mail' => 'nullable|email|max:25',
            'password' => 'required|string|min:6|max:20',
            'grp_id' => 'required|integer',
        ]);

        Config::set('database.connections.coops.database', session('org_schema'));
        DB::connection('coops')->beginTransaction();

        try {
            $result = DB::connection('coops')->select('CALL USP_ADD_EDIT_USER(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                0,
                $request->user_code,
                $request->full_name,
                $request->mobile,
                $request->mail,
                $request->user_name,
                $request->password,
                $request->grp_id,
                session('branch_id'),
                session('user_id'),
                null,
                1
            ]);

            if (!empty($result)) {
                $errorNo = $result[0]->Error_No ?? 0;
                $message = $result[0]->Message ?? '';

                if ($errorNo < 0) {
                    DB::connection('coops')->rollBack();
                    return response()->json(['error' => $message], 422);
                }

                DB::connection('coops')->commit();
                return response()->json(['message' => 'User added successfully']);
            }

            DB::connection('coops')->rollBack();
            return response()->json(['error' => 'No response from database'], 500);

        } catch (\Exception $e) {
            DB::connection('coops')->rollBack();
            Log::channel('trading')->error('User save error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'full_name' => 'required|string|max:100',
            'mobile' => 'required|digits:10',
            'mail' => 'nullable|email|max:25',
            'password' => 'nullable|string|min:6|max:255',
            'grp_id' => 'required|integer',
            'status' => 'required|integer',
        ]);

        Config::set('database.connections.coops.database', session('org_schema'));
        DB::connection('coops')->beginTransaction();

        try {
            $result = DB::connection('coops')->select('CALL USP_ADD_EDIT_USER(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $id,
                null,
                $request->full_name,
                $request->mobile,
                $request->mail,
                null,
                $request->password,
                $request->grp_id,
                session('branch_id'),
                session('user_id'),
                $request->status,
                2
            ]);

            if (!empty($result)) {
                $errorNo = $result[0]->Error_No ?? 0;
                $message = $result[0]->Message ?? '';

                if ($errorNo < 0) {
                    DB::connection('coops')->rollBack();
                    return response()->json(['error' => $message], 422);
                }

                DB::connection('coops')->commit();
                return response()->json(['message' => 'User updated successfully']);
            }

            DB::connection('coops')->rollBack();
            return response()->json(['error' => 'No response from database'], 500);

        } catch (\Exception $e) {
            DB::connection('coops')->rollBack();
            Log::channel('trading')->error('User update error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
