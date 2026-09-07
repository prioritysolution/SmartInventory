<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\SearchesItemsByCode;
use App\Http\Controllers\Concerns\VerifiesStoredPassword;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class MasterSetup extends Controller
{
    use SearchesItemsByCode;
    use VerifiesStoredPassword;

    //product master
    public function index()
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $orgId = session('org_id');
        $units = DB::connection('coops')->select('CALL USP_GET_ITEM_UNIT(?)', [$orgId]);
        $categories = DB::connection('coops')->select('CALL USP_GET_ITEM_CAT(?)', [$orgId]);
        $hsn = DB::connection('coops')->select(' CALL USP_GET_GST_CODES()');

        return view('Admin.product-master', compact('units', 'categories', 'hsn'))->with('pageTitle', 'Product Master');
    }

    public function searchItem(Request $request)
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');

        $code = (string) ($request->input('code') ?? $request->input('search') ?? '');
        $items = $this->searchItemsByCode(
            (int) $request->input('cat_id', 0),
            (int) $request->input('sub_cat_id', 0),
            $code
        );

        return response()->json($items);
    }


    public function getItemDetails($id)
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $item = DB::connection('coops')->select('CALL USP_GET_ITEM_DETAILS(?)', [$id]);

        return response()->json($item[0] ?? null);
    }


    public function getSubCategories($categoryId)
    {
         Config::set('database.connections.coops.database', session('org_schema'));
        $orgId = session('org_id');

        $subCategories = DB::connection('coops')->select('CALL USP_GET_ITEM_SUB_CAT(?, ?)', [$orgId, $categoryId]);

        return response()->json($subCategories);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_code' => 'required|string|max:20',
            'product_name' => 'required|string|max:50',
            'print_name' => 'required|string|max:50',
            'unit' => 'required|integer',
            'category' => 'required|integer',
            'sub_category' => 'required|integer',
            'hsn' => 'nullable|integer',
            'sale_margin'  => 'nullable|numeric|min:0|max:100',
            'reorder_qnty' => 'nullable|integer|min:0|max:9999',
            'prod_life' => 'nullable|integer|min:0|max:30000',
        ]);
        $mode = $request->input('product_id') ? 2 : 1;
        try {
            Config::set('database.connections.coops.database', session('org_schema'));
            DB::connection('coops')->beginTransaction();
            $result = DB::connection('coops')->select('CALL USP_ADD_EDIT_PRODUCT(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)', [
                $request->input('product_id'),
                $request->input('product_code'),
                $request->input('product_name'),
                $request->input('print_name'),
                $request->input('unit'),
                $request->input('category'),
                $request->input('sub_category'),
                $request->input('hsn'),
                0,
                $request->input('sale_margin') ?? 0,
                $request->input('reorder_qnty'),
                $request->input('prod_life'),
                $mode
            ]);

            if ($result && isset($result[0]->Error_No) && $result[0]->Error_No == -1) {
                DB::connection('coops')->rollBack();
                return response()->json(['success' => false, 'message' => $result[0]->Message], 400);
            }
            DB::connection('coops')->commit();
            return response()->json(['success' => true, 'message' => $mode === 2 ? 'Product updated successfully' : 'Product saved successfully']);
        } catch (\Exception $e) {
            DB::connection('coops')->rollBack();
            Log::channel('trading')->error('Product save error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to save product'], 500);
        }
    }




    //agent profile
public function indexAgent(Request $request)
{
    $branchId = session('branch_id');
    $page     = (int) $request->input('page', 1);
    $pageSize = 10;
    $search   = $request->input('search', '');

    Config::set('database.connections.coops.database', session('org_schema'));

    $pdo  = DB::connection('coops')->getPdo();
    $stmt = $pdo->prepare('CALL USP_GET_AGENT_LIST(?, ?, ?, ?)');
    $stmt->execute([$branchId, $search, $page, $pageSize]);
    $agents   = $stmt->fetchAll(\PDO::FETCH_OBJ);
    $stmt->nextRowset();
    $totalRow = $stmt->fetch(\PDO::FETCH_OBJ);
    $total    = $totalRow->Total ?? 0;
    $lastPage = $total > 0 ? (int) ceil($total / $pageSize) : 1;

    return view('Admin.agent-profile', compact('agents', 'total', 'page', 'pageSize', 'lastPage'))
        ->with('pageTitle', 'Agent Profile');
}




    public function storeAgent(Request $request)
    {
        $request->validate(array_merge([
            'agent_name' => 'required|string|max:50',
            'address' => 'nullable|string|max:100',
            'mobile' => 'required|digits:10',
            'join_date' => 'required|date|before_or_equal:today',
            'stock_limit' => 'nullable|numeric|min:0|max:99999999.99',
            'credit_sell_limit' => 'nullable|numeric|min:0|max:99999999.99',
            'password' => 'nullable|string|min:6|max:20',
        ], $this->addressMasterValidationRules()));
        try {
            Config::set('database.connections.coops.database', session('org_schema'));
            DB::connection('coops')->beginTransaction();
            $result = DB::connection('coops')->select('CALL USP_ADD_EDIT_AGENT(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                0,
                $request->input('agent_name'),
                $request->input('address'),
                $request->input('mobile'),
                $request->input('join_date'),
                $request->input('stock_limit'),
                $request->input('credit_sell_limit'),
                session('branch_id'),
                $request->filled('password') ? $this->hashPlainPassword($request->input('password')) : null,
                1,
                1
            ]);

            if (!empty($result) && isset($result[0]->Error_No) && $result[0]->Error_No < 0) {
                DB::connection('coops')->rollBack();
                return response()->json(['success' => false, 'message' => $result[0]->Message], 400);
            }
            $agentCode = null;
            if (!empty($result[0]->Message) && preg_match('/Agent Code is\s+(\S+)/i', $result[0]->Message, $m)) {
                $agentCode = $m[1];
            }
            $this->syncEntityAddressMaster('agent', 0, $request, $agentCode);
            DB::connection('coops')->commit();
            return response()->json([
                'success' => true,
                'message' => $result[0]->Message ?? 'Agent added successfully'
            ]);
        } catch (\Exception $e) {
            DB::connection('coops')->rollBack();
            Log::channel('trading')->error('Agent save error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to save agent'], 500);
        }
    }

    public function updateAgent(Request $request, $id)
    {

        $request->validate(array_merge([
            'agent_name' => 'required|string|max:50',
            'address' => 'nullable|string|max:100',
            'mobile' => 'required|digits:10',
            'join_date' => 'required|date|before_or_equal:today',
            'stock_limit' => 'nullable|numeric|min:0|max:99999999.99',
            'credit_sell_limit' => 'nullable|numeric|min:0|max:99999999.99',
            'password' => 'nullable|string|min:6|max:20',
        ], $this->addressMasterValidationRules()));
        try {
            Config::set('database.connections.coops.database', session('org_schema'));
            DB::connection('coops')->beginTransaction();
            $result = DB::connection('coops')->select('CALL USP_ADD_EDIT_AGENT(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $id,
                $request->input('agent_name'),
                $request->input('address'),
                $request->input('mobile'),
                $request->input('join_date'),
                $request->input('stock_limit'),
                $request->input('credit_sell_limit'),
                session('branch_id'),
                $request->filled('password') ? $this->hashPlainPassword($request->input('password')) : null,
                1,
                2
            ]);

            if (!empty($result) && isset($result[0]->Error_No) && $result[0]->Error_No < 0) {
                DB::connection('coops')->rollBack();
                return response()->json(['success' => false, 'message' => $result[0]->Message], 400);
            }
            $this->syncEntityAddressMaster('agent', $id, $request);
            DB::connection('coops')->commit();
            return response()->json([
                'success' => true,
                'message' => $result[0]->Message ?? 'Agent updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::connection('coops')->rollBack();
            Log::channel('trading')->error('Agent update error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update agent'], 500);
        }
    }

    // public function deleteAgent($id)
    // {
    //     try {
    //         Config::set('database.connections.coops.database', session('org_schema'));

    //         $result = DB::connection('coops')->select('CALL USP_ADD_EDIT_AGENT(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
    //             $id, null, null, null, null, null, 0, session('branch_id'), null, 0, 3
    //         ]);

    //         if ($result && isset($result[0]->Error_No) && $result[0]->Error_No == -1) {
    //             return response()->json(['success' => false, 'message' => $result[0]->Message], 400);
    //         }

    //         return response()->json(['success' => true, 'message' => 'Agent deleted successfully']);
    //     } catch (\Exception $e) {
    //         Log::error('Agent delete error: ' . $e->getMessage());
    //         return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    //     }
    // }



    // Supplier Master
    public function indexSupplier()
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $branchId = session('branch_id');
        $suppliers = DB::connection('coops')->select('CALL USP_GET_PARTY_LIST(?, ?, ?)', [1, $branchId, 0]);
        Log::channel('trading')->info('USP_GET_PARTY_LIST result', ['result' => $suppliers]);
        return view('Admin.supplier-master', compact('suppliers'))->with('pageTitle', 'Supplier Master');
    }

    public function storeSupplier(Request $request)
    {
        $request->validate(array_merge([
            'party_name' => 'required|string|max:100',
            'mobile_no' => 'nullable|digits:10',
            'contact_no' => 'nullable|digits:10',
            'mail_id' => 'nullable|email|max:50',
            'contact_person' => 'nullable|string|max:150',
            'designation' => 'nullable|string|max:50',
            'address1' => 'nullable|string|max:200',
            'address2' => 'nullable|string|max:200',
            'city' => 'nullable|string|max:50',
            'district' => 'nullable|string|max:50',
            'state' => 'nullable|string|max:25',
            'pin_code' => 'nullable|string|max:10',
            'pan_no' => 'nullable|string|max:25|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
            'gstin' => 'nullable|string|max:25|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
            'state_code' => 'nullable|numeric|max:99',
            'credit_limit' => 'nullable|numeric|min:0|max:99999999.99',
            'opening_balance' => 'nullable|numeric|min:0|max:999999999999.99',
        ], $this->addressMasterValidationRules()));

        try {
            Config::set('database.connections.coops.database', session('org_schema'));
            DB::connection('coops')->beginTransaction();
            $district = $this->addressMasterName('dist', $request->input('dist_id')) ?: $request->input('district');
            $pinCode  = $this->addressMasterName('pin', $request->input('pin_id')) ?: $request->input('pin_code');
            $result = DB::connection('coops')->select('CALL USP_ADD_EDIT_PARTY(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                0,
                null,
                $request->input('party_name'),
                $request->input('mobile_no'),
                $request->input('contact_no'),
                $request->input('mail_id'),
                $request->input('contact_person'),
                $request->input('designation'),
                $request->input('address1'),
                $request->input('address2'),
                $request->input('city'),
                $district,
                $request->input('state'),
                $pinCode,
                $request->input('pan_no'),
                $request->input('gstin'),
                $request->input('state_code'),
                date('Y-m-d'),
                $request->input('credit_limit'),
                $request->input('opening_balance', 0),
                session('branch_id'),
                session('user_id'),
                1
            ]);

            Log::channel('trading')->info('USP_ADD_EDIT_PARTY result', ['result' => $result]);

            if (!empty($result)) {

                $errorNo = $result[0]->Error_No ?? 0;
                $message = $result[0]->Message ?? '';

                if ($errorNo < 0) {
                    DB::connection('coops')->rollBack();
                    return response()->json(['error' => $message], 422);
                }

                if ($errorNo == 0) {
                    $this->syncEntityAddressMaster('party', 0, $request, $result[0]->Party_Code ?? null);
                    DB::connection('coops')->commit();
                    return response()->json(['message' => 'Supplier added successfully', 'party_code' => $result[0]->Party_Code ?? '']);
                }
            }

            DB::connection('coops')->rollBack();
            return response()->json(['error' => 'No response from database'], 500);
        } catch (\Exception $e) {
            DB::connection('coops')->rollBack();
            Log::channel('trading')->error('Supplier save error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to Add Supplier'], 500);
        }
    }

    public function updateSupplier(Request $request, $id)
    {
        $request->validate(array_merge([
            'party_code' => 'nullable|string|max:25',
            'party_name' => 'required|string|max:100',
            'mobile_no' => 'nullable|digits:10',
            'contact_no' => 'nullable|digits:10',
            'mail_id' => 'nullable|email|max:50',
            'contact_person' => 'nullable|string|max:150',
            'designation' => 'nullable|string|max:50',
            'address1' => 'nullable|string|max:200',
            'address2' => 'nullable|string|max:200',
            'city' => 'nullable|string|max:50',
            'district' => 'nullable|string|max:50',
            'state' => 'nullable|string|max:25',
            'pin_code' => 'nullable|string|max:10',
            'pan_no' => 'nullable|string|max:25|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
            'gstin' => 'nullable|string|max:25|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
            'state_code' => 'nullable|numeric|max:99',
            'credit_limit' => 'nullable|numeric|min:0|max:99999999.99',
            'opening_balance' => 'nullable|numeric|min:0|max:999999999999.99',
        ], $this->addressMasterValidationRules()));

        try {
            Config::set('database.connections.coops.database', session('org_schema'));
            DB::connection('coops')->beginTransaction();
            $district = $this->addressMasterName('dist', $request->input('dist_id')) ?: $request->input('district');
            $pinCode  = $this->addressMasterName('pin', $request->input('pin_id')) ?: $request->input('pin_code');
            $result = DB::connection('coops')->select('CALL USP_ADD_EDIT_PARTY(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $id,
                null,
                $request->input('party_name'),
                $request->input('mobile_no'),
                $request->input('contact_no'),
                $request->input('mail_id'),
                $request->input('contact_person'),
                $request->input('designation'),
                $request->input('address1'),
                $request->input('address2'),
                $request->input('city'),
                $district,
                $request->input('state'),
                $pinCode,
                $request->input('pan_no'),
                $request->input('gstin'),
                $request->input('state_code'),
                date('Y-m-d'),
                $request->input('credit_limit'),
                $request->input('opening_balance', 0),
                session('branch_id'),
                session('user_id'),
                2
            ]);

            Log::channel('trading')->info('USP_ADD_EDIT_PARTY update result', ['result' => $result]);

            if (!empty($result)) {

                $errorNo = $result[0]->Error_No ?? 0;
                $message = $result[0]->Message ?? '';

                if ($errorNo < 0) {
                    DB::connection('coops')->rollBack();
                    return response()->json(['error' => $message], 422);
                }

                $this->syncEntityAddressMaster('party', $id, $request);
                DB::connection('coops')->commit();
                return response()->json(['message' => 'Supplier updated successfully']);
            }

            DB::connection('coops')->rollBack();
            return response()->json(['error' => 'No response from database'], 500);
        } catch (\Exception $e) {
            DB::connection('coops')->rollBack();
            Log::channel('trading')->error('Supplier update error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to Update Supplier'], 500);
        }
    }



    // Customer Master
    public function indexCustomer()
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $branchId = session('branch_id');
        $customers = DB::connection('coops')->select('CALL USP_GET_PARTY_LIST(?, ?, ?)', [2, $branchId, 0]);

        $pdo = DB::connection('coops')->getPdo();
        $stmt = $pdo->prepare('CALL USP_GET_AGENT_LIST(?, ?, ?, ?)');
        $stmt->execute([$branchId, '', 1, 0]);
        $agents = $stmt->fetchAll(\PDO::FETCH_OBJ);
        $stmt->closeCursor();

        return view('Admin.customer-master', compact('customers', 'agents'))->with('pageTitle', 'Customer Master');
    }

    public function storeCustomer(Request $request)
    {
        $request->validate(array_merge([
            'party_name' => 'required|string|max:100',
            'mobile_no' => 'nullable|digits:10',
            'alt_mobile_no' => 'nullable|digits:10',
            'mail_id' => 'nullable|email|max:50',
            'address1' => 'nullable|string|max:200',
            'address2' => 'nullable|string|max:200',
            'city' => 'nullable|string|max:50',
            'district' => 'nullable|string|max:50',
            'state' => 'nullable|string|max:25',
            'pin_code' => 'nullable|string|max:10',
            'pan_no' => 'nullable|string|max:25|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
            'gstin' => 'nullable|string|max:25|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
            'state_code' => 'nullable|numeric|max:99',
            'credit_limit' => 'nullable|numeric|min:0|max:99999999.99',
            'opening_balance' => 'nullable|numeric|min:0|max:999999999999.99',
            'cust_agent_id' => 'nullable|integer|min:0',
        ], $this->addressMasterValidationRules()));

        Config::set('database.connections.coops.database', session('org_schema'));
        DB::connection('coops')->beginTransaction();

        try {
            $district = $this->addressMasterName('dist', $request->input('dist_id')) ?: $request->district;
            $pinCode  = $this->addressMasterName('pin', $request->input('pin_id')) ?: $request->pin_code;
            $result = DB::connection('coops')->select("CALL USP_ADD_EDIT_CUSTOMER(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
                0,
                null,
                $request->party_name,
                $request->mobile_no,
                $request->alt_mobile_no,
                $request->mail_id,
                $request->address1,
                $request->address2,
                $request->city,
                $district,
                $request->state,
                $pinCode,
                $request->pan_no,
                $request->gstin,
                $request->state_code,
                date('Y-m-d'),
                $request->credit_limit,
                $request->input('opening_balance', 0),
                (int) $request->input('cust_agent_id', 0),
                session('branch_id'),
                session('user_id'),
                1
            ]);

            Log::channel('trading')->info('USP_ADD_EDIT_CUSTOMER result', ['result' => $result]);

            if (!empty($result)) {
                $errorNo = $result[0]->Error_No ?? 0;
                $message = $result[0]->Message ?? '';

                if ($errorNo == -1 || $errorNo == -2) {
                    DB::connection('coops')->rollBack();
                    return response()->json(['error' => $message], 422);
                }

                if ($errorNo == 0) {
                    $this->syncEntityAddressMaster('party', 0, $request, $result[0]->Party_Code ?? null);
                    DB::connection('coops')->commit();
                    return response()->json(['message' => 'Customer added successfully', 'party_code' => $result[0]->Party_Code ?? '']);
                }
            }

            DB::connection('coops')->rollBack();
            return response()->json(['error' => 'No response from database'], 500);
        } catch (\Exception $e) {
            DB::connection('coops')->rollBack();
            Log::channel('trading')->error('Customer store error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to Add Customer'], 500);
        }
    }


    public function updateCustomer(Request $request, $id)
    {
        $request->validate(array_merge([
            'party_name' => 'required|string|max:250',
            'mobile_no' => 'nullable|digits:10',
            'alt_mobile_no' => 'nullable|digits:10',
            'mail_id' => 'nullable|email|max:50',
            'address1' => 'nullable|string|max:200',
            'address2' => 'nullable|string|max:200',
            'city' => 'nullable|string|max:50',
            'district' => 'nullable|string|max:50',
            'state' => 'nullable|string|max:25',
            'pin_code' => 'nullable|string|max:10',
            'pan_no' => 'nullable|string|max:25|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
            'gstin' => 'nullable|string|max:25|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
            'state_code' => 'nullable|numeric|max:99',
            'credit_limit' => 'nullable|numeric|min:0|max:99999999.99',
            'opening_balance' => 'nullable|numeric|min:0|max:999999999999.99',
            'cust_agent_id' => 'nullable|integer|min:0',
        ], $this->addressMasterValidationRules()));

        try {
            Config::set('database.connections.coops.database', session('org_schema'));


            DB::connection('coops')->beginTransaction();
            $district = $this->addressMasterName('dist', $request->input('dist_id')) ?: $request->input('district');
            $pinCode  = $this->addressMasterName('pin', $request->input('pin_id')) ?: $request->input('pin_code');

            $result = DB::connection('coops')->select('CALL USP_ADD_EDIT_CUSTOMER(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $id,
                null,
                $request->input('party_name'),
                $request->input('mobile_no'),
                $request->input('alt_mobile_no'),
                $request->input('mail_id'),
                $request->input('address1'),
                $request->input('address2'),
                $request->input('city'),
                $district,
                $request->input('state'),
                $pinCode,
                $request->input('pan_no'),
                $request->input('gstin'),
                $request->input('state_code'),
                date('Y-m-d'),
                $request->input('credit_limit'),
                $request->input('opening_balance', 0),
                (int) $request->input('cust_agent_id', 0),
                session('branch_id'),
                session('user_id'),
                2
            ]);

            Log::channel('trading')->info('USP_ADD_EDIT_CUSTOMER update result', ['result' => $result]);

            if (!empty($result)) {

                $errorNo = $result[0]->Error_No ?? 0;
                $message = $result[0]->Message ?? '';

                if ($errorNo < 0) {
                    DB::connection('coops')->rollBack();
                    return response()->json([
                        'error' => $message ?: 'Customer update failed'
                    ], 422);
                }

                if ($errorNo == 0) {
                    $this->syncEntityAddressMaster('party', $id, $request);
                    DB::connection('coops')->commit();
                    return response()->json([
                        'message' => 'Customer updated successfully'
                    ]);
                }
            }

            DB::connection('coops')->rollBack();
            return response()->json([
                'error' => 'No response from database'
            ], 500);
        } catch (\Exception $e) {
            DB::connection('coops')->rollBack();
            Log::channel('trading')->error('Customer update error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update Customer'], 500);
        }
    }
    public function destroyCustomer($id)
    {
        try {
            Config::set('database.connections.coops.database', session('org_schema'));
            DB::connection('coops')->table('mst_party')
                ->where('Party_Id', $id)
                ->where('Party_Type', 2)
                ->delete();
            return response()->json(['message' => 'Customer deleted successfully']);
        } catch (\Exception $e) {
            Log::channel('trading')->error('Customer delete error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete customer'], 500);
        }
    }

    // Member & Share Management
    public function indexMember()
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $memberTypes = DB::select('CALL USP_GET_APPLICATION_OPTION(?)', [9]);
        $banks       = DB::connection('coops')->select('CALL USP_GET_BANK_LEDGER()');
        $shareConfig = DB::connection('coops')->select('CALL USP_GET_SHARE_CONFIG()');
        $shareConfig = !empty($shareConfig) ? $shareConfig[0] : null;

        return view('Admin.member-share', compact('memberTypes', 'banks', 'shareConfig'))->with('pageTitle', 'Member & Share Management');
    }

    public function getMemberList()
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $members = DB::connection('coops')->select('CALL USP_GET_MEMBER_LIST(?)', [session('branch_id')]);
        return response()->json($members);
    }

    public function updateMember(Request $request, $id)
    {
        $request->validate(array_merge([
            'mem_type' => 'required|integer',
            'mem_name' => 'required|string|max:50',
            'gur_name' => 'required|string|max:50',
            'address'  => 'nullable|string|max:100',
            'mob_no'   => 'nullable|digits:10',
            'adhar_no' => 'nullable|digits:12',
            'voter_no' => 'nullable|string|max:25',
            'pan_no'   => 'nullable|string|max:25|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
            'adm_date' => 'required|date',
            'share_no' => 'nullable|integer|min:0',
        ], $this->addressMasterValidationRules()));
        try {
            Config::set('database.connections.coops.database', session('org_schema'));
            DB::connection('coops')->beginTransaction();
            $result = DB::connection('coops')->select('CALL USP_ADD_EDIT_MEMBER(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $id,
                $request->mem_type,
                $request->mem_name,
                $request->gur_name,
                $request->address,
                $request->mob_no,
                $request->adhar_no,
                $request->voter_no,
                $request->pan_no,
                $request->adm_date,
                $request->input('adm_fees', 0),
                $request->input('share_no', 0),
                $request->input('share_amt', 0),
                session('user_id'),
                1, 0, null,
                $request->input('rate_share', 0),
                $request->input('tot_amt', 0),
                null,
                null,
                session('year_id'),
                session('branch_id'),
                2
            ]);
            if (!empty($result)) {
                $errorNo = $result[0]->Error_No ?? 0;
                $message = $result[0]->Message ?? $result[0]->Messgae ?? '';
                if ($errorNo < 0) {
                    DB::connection('coops')->rollBack();
                    return response()->json(['error' => $message], 422);
                }
            }
            $this->syncEntityAddressMaster('member', $id, $request);
            DB::connection('coops')->commit();
            return response()->json(['message' => 'Member updated successfully']);
        } catch (\Exception $e) {
            DB::connection('coops')->rollBack();
            Log::channel('trading')->error('Member update error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update member'], 500);
        }
    }

    public function storeMember(Request $request)
    {
        $request->validate(array_merge([
            'mem_type' => 'required|integer',
            'mem_name' => 'required|string|max:50',
            'gur_name' => 'required|string|max:50',
            'address' => 'nullable|string|max:100',
            'mob_no' => 'nullable|digits:10',
            'adhar_no' => 'nullable|digits:12',
            'voter_no' => 'nullable|string|max:25',
            'pan_no' => 'nullable|string|max:25|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
            'adm_date' => 'required|date||before_or_equal:today',
            'adm_fees' => 'required|numeric|min:0',
            'share_no' => 'nullable|digits_between:0,6',
            'rate_share' => 'required|numeric|min:0',
            'share_amt' => 'required|numeric|min:0',
            'tot_amt' => 'required|numeric|min:0',
            'trans_mode' => 'required|in:1,2',
            'bank_id' => 'nullable|integer',
            'ref_mem_no' => 'nullable|string|max:25',
            'ref_voucher' => 'nullable|string|max:50',
            'bank_remarks' => 'nullable|string|max:100',
        ], $this->addressMasterValidationRules()), [
            'share_no.digits_between' => 'Number of Share maximum 999999.',
        ]);

        if ($request->trans_mode == 2 && empty($request->bank_id)) {
            return response()->json(['error' => 'Please select a bank'], 422);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        DB::connection('coops')->beginTransaction();

        try {
            $result = DB::connection('coops')->select('CALL USP_ADD_EDIT_MEMBER(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                0,
                $request->mem_type,
                $request->mem_name,
                $request->gur_name,
                $request->address,
                $request->mob_no,
                $request->adhar_no,
                $request->voter_no,
                $request->pan_no,
                $request->adm_date,
                $request->adm_fees,
                $request->share_no,
                $request->share_amt,
                session('user_id'),
                $request->trans_mode,
                $request->bank_id ?: 0,
                $request->ref_mem_no,
                $request->rate_share,
                $request->tot_amt,
                $request->ref_voucher,
                $request->input('bank_remarks'),
                session('year_id'),
                session('branch_id'),
                1
            ]);

            Log::channel('trading')->info('USP_ADD_EDIT_MEMBER result', ['result' => $result]);

            if (!empty($result)) {
                $errorNo = $result[0]->Error_No ?? 0;
                $message = $result[0]->Message ?? $result[0]->Messgae ?? '';

                if ($errorNo < 0) {
                    DB::connection('coops')->rollBack();
                    return response()->json(['error' => $message], 422);
                }

                if ($errorNo == 0) {
                    $this->syncEntityAddressMaster('member', 0, $request, $result[0]->Member_Code ?? null);
                    DB::connection('coops')->commit();
                    return response()->json(['message' => $message ?: 'Member added successfully']);
                }
            }

            DB::connection('coops')->rollBack();
            return response()->json(['error' => 'No response from database'], 500);
        } catch (\Exception $e) {
            DB::connection('coops')->rollBack();
            Log::channel('trading')->error('Member save error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to Add Member'], 500);
        }
    }


    // User Roles & Permissions
    public function indexUserGroup()
    {
        Config::set('database.connections.coops.database', session('org_schema'));

        $groups = DB::connection('coops')->select('CALL USP_GET_USER_GROUP()');
        $menus  = DB::connection('coops')->select('CALL USP_GET_ALL_MENUE()');
        Log::channel('trading')->info('USP_GET_ALL_MENUE result', $menus);

        return view('Admin.user-roles', compact('groups', 'menus'))
            ->with('pageTitle', 'User Roles & Permissions');
    }
    public function getUserPermissions($grpId)
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        log::channel('trading')->info($grpId);
        $permissions = DB::connection('coops')->select('CALL USP_GET_GRPWISE_MENUE(?)', [$grpId]);
        Log::channel('trading')->info('USP_GET_GRPWISE_MENUE result', ['data' => $permissions, 'grpId' => $grpId]);
        return response()->json($permissions);
    }

    public function storeUserGroup(Request $request)
    {
        $request->validate([
            'grp_name' => 'required|max:50',
            'grp_desc' => 'nullable|max:150'
        ]);

        Config::set('database.connections.coops.database', session('org_schema'));
        DB::connection('coops')->beginTransaction();

        try {
            // Create temporary table
            DB::connection('coops')->statement("CREATE TEMPORARY TABLE IF NOT EXISTS tempmenue(Parraint_Id INT, Child_Id INT)");
            DB::connection('coops')->statement("DELETE FROM tempmenue");

            if (!empty($request->menus)) {
                foreach ($request->menus as $menu) {
                    DB::connection('coops')->statement(
                        "INSERT INTO tempmenue(Parraint_Id,Child_Id) VALUES (?,?)",
                        [$menu['parent'], $menu['child']]
                    );
                }
            }
            $result = DB::connection('coops')->select(
                "CALL USP_ADD_USER_GROUP(?,?,?,?,?)",
                [
                    $request->grp_id,
                    $request->grp_name,
                    $request->grp_desc,
                    $request->is_admin,
                    $request->mode
                ]
            );

            if ($result[0]->Error_No < 0) {
                DB::connection('coops')->rollBack();
                return response()->json(['error' => $result[0]->Message], 422);
            }

            DB::connection('coops')->commit();
            return response()->json(['message' => 'User Group saved successfully']);
        } catch (\Exception $e) {
            DB::connection('coops')->rollBack();
            Log::channel('trading')->error('User Group save error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to Add User group'], 500);
        }
    }




    public function organizationprofiless(Request $request)
    {
        return view('Admin.test');
    }

    // Product Category
   public function indexProdCategory()
{
    Config::set('database.connections.coops.database', session('org_schema'));
    $categories = DB::connection('coops')->select('CALL USP_GET_ITEM_CAT(?)', [session('org_id')]);
    $purchaseLedgers = DB::connection('coops')->select('CALL USP_GET_PRM_LEDGER(?)', [1]);
    $salesLedgers    = DB::connection('coops')->select('CALL USP_GET_PRM_LEDGER(?)', [2]);
    return view('Admin.prod-category', compact('categories', 'purchaseLedgers','salesLedgers'))->with('pageTitle', 'Product Category');
}

public function storeProdCategory(Request $request)
{
    $request->validate([
        'cate_nm'    => 'required|string|max:50',
        'is_fmcg'    => 'required|in:0,1',
        'agent_comm' => 'nullable|numeric|min:0|max:100',
        'pur_ledg'   => 'nullable|integer',
        'sale_ledg'  => 'nullable|integer',
    ]);
    $mode = $request->input('cate_id') ? 2 : 1;
    try {
        Config::set('database.connections.coops.database', session('org_schema'));
        DB::connection('coops')->beginTransaction();
        $result = DB::connection('coops')->select('CALL USP_ADD_EDIT_PROD_CATEGORY(?,?,?,?,?,?,?,?)', [
            $request->input('cate_id') ?: 0,
            $request->input('cate_nm'),
            $request->input('is_fmcg'),
            $request->input('agent_comm') ?? 0,
            $request->input('pur_ledg') ?? 0,
            $request->input('sale_ledg') ?? 0,
            session('user_id'),
            $mode
        ]);
        if (!empty($result) && $result[0]->Error_No < 0) {
            DB::connection('coops')->rollBack();
            return response()->json(['error' => $result[0]->Message], 422);
        }
        DB::connection('coops')->commit();
        return response()->json(['message' => $result[0]->Message ?? 'Saved successfully']);
    } catch (\Exception $e) {
        DB::connection('coops')->rollBack();
        Log::channel('trading')->error('ProdCategory save error: ' . $e->getMessage());
        return response()->json(['message' => 'Failed to save category'], 500);
    }
}


    // Product Sub Category
    public function indexProdSubCategory()
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $orgId = session('org_id');
        $subCategories = DB::connection('coops')->select('CALL USP_GET_ITEM_SUB_CAT(?,?)', [$orgId, 0]);
        $categories    = DB::connection('coops')->select('CALL USP_GET_ITEM_CAT(?)', [$orgId]);
        return view('Admin.prod-subcategory', compact('subCategories', 'categories'))->with('pageTitle', 'Product Sub Category');
    }

    public function storeProdSubCategory(Request $request)
    {
        $request->validate([
            'sub_cate_nm' => 'required|string|max:50',
            'cate_id'     => 'required|integer',
        ]);
        $mode = $request->input('sub_cate_id') ? 2 : 1;
        try {
            Config::set('database.connections.coops.database', session('org_schema'));
            DB::connection('coops')->beginTransaction();
            $result = DB::connection('coops')->select('CALL USP_ADD_EDIT_PROD_SUBCATEGORY(?,?,?,?,?)', [
                $request->input('sub_cate_id') ?: 0,
                $request->input('sub_cate_nm'),
                $request->input('cate_id'),
                session('user_id'),
                $mode
            ]);
            if (!empty($result) && $result[0]->Error_No < 0) {
                DB::connection('coops')->rollBack();
                return response()->json(['error' => $result[0]->Message], 422);
            }
            DB::connection('coops')->commit();
            return response()->json(['message' => $result[0]->Message ?? 'Saved successfully']);
        } catch (\Exception $e) {
            DB::connection('coops')->rollBack();
            Log::channel('trading')->error('ProdSubCategory save error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to save sub category'], 500);
        }
    }

    // Unit Master
    public function indexUnit()
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $units = DB::connection('coops')->select('CALL USP_GET_ITEM_UNIT(?)', [session('org_id')]);
        return view('Admin.unit-master', compact('units'))->with('pageTitle', 'Unit Master');
    }

    public function storeUnit(Request $request)
    {
        $request->validate(['unit_name' => 'required|string|max:25']);
        $mode = $request->input('unit_id') ? 2 : 1;
        try {
            Config::set('database.connections.coops.database', session('org_schema'));
            DB::connection('coops')->beginTransaction();
            $result = DB::connection('coops')->select('CALL USP_ADD_EDIT_UNIT(?,?,?,?)', [
                $request->input('unit_id') ?: 0,
                $request->input('unit_name'),
                session('user_id'),
                $mode
            ]);
            if (!empty($result) && $result[0]->Error_No < 0) {
                DB::connection('coops')->rollBack();
                return response()->json(['error' => $result[0]->Message], 422);
            }
            DB::connection('coops')->commit();
            return response()->json(['message' => $result[0]->Message ?? 'Saved successfully']);
        } catch (\Exception $e) {
            DB::connection('coops')->rollBack();
            Log::channel('trading')->error('Unit save error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to save unit'], 500);
        }
    }

    // Accounting Year
    public function indexAccountingYear()
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $years = DB::connection('coops')->select('CALL USP_GET_ACCOUNTING_YEAR()');
        return view('Admin.accounting-year', compact('years'))->with('pageTitle', 'Accounting Year');
    }

    public function storeAccountingYear(Request $request)
    {
        $request->validate([
            'year_start' => 'required|date',
            'year_end'   => 'required|date',
        ]);

        $yearStart = $request->input('year_start');
        $yearEnd   = $request->input('year_end');
        $yearDesc  = $request->input('year_desc');

        try {
            Config::set('database.connections.coops.database', session('org_schema'));
            DB::connection('coops')->beginTransaction();
            $result = DB::connection('coops')->select('CALL USP_ADD_EDIT_ACCOUNTING_YEAR(?,?,?,?)', [
                $yearDesc,
                $yearStart,
                $yearEnd,
                session('user_id')
            ]);

            if (!empty($result) && $result[0]->Error_No < 0) {
                DB::connection('coops')->rollBack();
                return response()->json(['error' => $result[0]->Message], 422);
            }
            DB::connection('coops')->commit();

            // Logout after setting new accounting year
            session()->flush();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json(['message' => $result[0]->Message ?? 'Accounting year added successfully']);
        } catch (\Exception $e) {
            DB::connection('coops')->rollBack();
            Log::channel('trading')->error('AccountingYear save error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to save accounting year'], 500);
        }
    }



    // Address Master
    public function indexAddressMaster()
    {
        return view('Admin.address-master')->with('pageTitle', 'Address Master');
    }

    public function getAddressData($type)
    {
        if (!$this->addressTable($type)) {
            return response()->json(['error' => 'Invalid address type'], 400);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');

        try {
            $data = DB::connection('coops')->select('CALL USP_GET_ADDRESS_MASTER(?)', [$type]);
            return response()->json($data);
        } catch (\Exception $e) {
            Log::channel('trading')->error('Address master load error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load data'], 500);
        }
    }

    public function storeAddressMaster(Request $request)
    {
        $request->validate(['type' => 'required|string', 'name' => 'required|string|max:100']);
        if (!$this->addressTable($request->input('type'))) {
            return response()->json(['error' => 'Invalid address type'], 400);
        }
        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');
        try {
            DB::connection('coops')->beginTransaction();
            $result = DB::connection('coops')->select('CALL USP_ADD_EDIT_ADDRESS_MASTER(?, ?, ?, ?)', [
                $request->input('type'),
                0,
                $request->input('name'),
                1
            ]);
            if (!empty($result) && $result[0]->Error_No < 0) {
                DB::connection('coops')->rollBack();
                return response()->json(['error' => $result[0]->Message], 422);
            }
            DB::connection('coops')->commit();
            return response()->json(['message' => $result[0]->Message ?? 'Added successfully']);
        } catch (\Exception $e) {
            DB::connection('coops')->rollBack();
            Log::channel('trading')->error('Address master save error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to save'], 500);
        }
    }

    public function updateAddressMaster(Request $request, $type, $id)
    {
        $request->validate(['name' => 'required|string|max:100']);
        if (!$this->addressTable($type)) {
            return response()->json(['error' => 'Invalid address type'], 400);
        }
        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');
        try {
            DB::connection('coops')->beginTransaction();
            $result = DB::connection('coops')->select('CALL USP_ADD_EDIT_ADDRESS_MASTER(?, ?, ?, ?)', [
                $type,
                $id,
                $request->input('name'),
                2
            ]);
            if (!empty($result) && $result[0]->Error_No < 0) {
                DB::connection('coops')->rollBack();
                return response()->json(['error' => $result[0]->Message], 422);
            }
            DB::connection('coops')->commit();
            return response()->json(['message' => $result[0]->Message ?? 'Updated successfully']);
        } catch (\Exception $e) {
            DB::connection('coops')->rollBack();
            Log::channel('trading')->error('Address master update error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update'], 500);
        }
    }

    private function addressTable($type)
    {
        $map = [
            'village' => ['table' => 'mst_village', 'pk' => 'Village_Id', 'col' => 'Village_Name'],
            'ps'      => ['table' => 'mst_ps',      'pk' => 'Ps_Id',      'col' => 'Ps_Name'],
            'post'    => ['table' => 'mst_post',    'pk' => 'Post_Id',    'col' => 'Post_Name'],
            'pin'     => ['table' => 'mst_pin',     'pk' => 'Pin_Id',     'col' => 'Pin_Code'],
            'dist'    => ['table' => 'mst_dist',    'pk' => 'Dist_Id',    'col' => 'Dist_Name'],
        ];
        return $map[$type] ?? null;
    }

    private function addressMasterValidationRules(): array
    {
        return [
            'village_id' => 'required|integer|min:1',
            'ps_id'      => 'required|integer|min:1',
            'post_id'    => 'required|integer|min:1',
            'pin_id'     => 'required|integer|min:1',
            'dist_id'    => 'required|integer|min:1',
        ];
    }

    private function addressMasterName(string $type, $id): ?string
    {
        if (!$this->addressTable($type) || !$id) {
            return null;
        }
        $rows = DB::connection('coops')->select('CALL USP_GET_ADDRESS_NAME(?, ?)', [$type, (int) $id]);
        return !empty($rows) && isset($rows[0]->Name) ? (string) $rows[0]->Name : null;
    }

    private function syncEntityAddressMaster(string $entity, $id, Request $request, ?string $code = null): void
    {
        if (!$id && !$code) {
            return;
        }
        DB::connection('coops')->select('CALL USP_UPDATE_ENTITY_ADDRESS(?, ?, ?, ?, ?, ?, ?, ?)', [
            $entity,
            (int) ($id ?: 0),
            $code,
            (int) $request->input('village_id'),
            (int) $request->input('ps_id'),
            (int) $request->input('post_id'),
            (int) $request->input('pin_id'),
            (int) $request->input('dist_id'),
        ]);
    }

    // Chart of Accounts
public function indexChartOfAccounts()
{
    return view('Admin.chart-of-accounts')->with('pageTitle', 'Chart of Accounts');
}

public function getChartData($type)
{
    Config::set('database.connections.coops.database', session('org_schema'));
    switch ($type) {
        case 'category':
            $data = DB::connection('coops')->select('CALL USP_GET_ACCT_CATEGORY()');
            break;
        case 'mainhd':
            $data = DB::connection('coops')->select('CALL USP_GET_ACCT_MAINHD()');
            break;
        case 'glhead':
            $data = DB::connection('coops')->select('CALL USP_GET_ACCT_GLHEAD()');
            break;
        default:
            $data = [];
    }
    return response()->json($data);
}
//gst 
public function indexGstCodes()
{
    Config::set('database.connections.coops.database', session('org_schema'));
    $gstCodes = DB::connection('coops')->select('CALL USP_GET_GST_CODES()');
    return view('Admin.gst-codes', compact('gstCodes'))->with('pageTitle', 'GST Codes');
}

public function storeGst(Request $request)
{
    $request->validate([
        'gst_code'    => 'required|string|max:20',
        'category'    => 'required|string|max:50',
        'tax_percent' => 'required|numeric|min:0|max:100',
        'sgst'        => 'required|numeric|min:0|max:100',
        'cgst'        => 'required|numeric|min:0|max:100',
        'igst'        => 'required|numeric|min:0|max:100',
        'ugst'        => 'nullable|numeric|min:0|max:100',
        'is_active'   => 'required|in:0,1',
    ]);
    try {
        Config::set('database.connections.coops.database', session('org_schema'));
        DB::connection('coops')->beginTransaction();
        $result = DB::connection('coops')->select('CALL USP_ADD_EDIT_GST_CODE(?,?,?,?,?,?,?,?,?,?,?)', [
            0,
            $request->gst_code,
            $request->category,
            $request->tax_percent,
            $request->sgst,
            $request->cgst,
            $request->igst,
            $request->ugst ?? 0,
            $request->is_active,
            session('user_id'),
            1
        ]);
        if (!empty($result) && $result[0]->Error_No < 0) {
            DB::connection('coops')->rollBack();
            return response()->json(['error' => $result[0]->Message], 422);
        }
        DB::connection('coops')->commit();
        return response()->json(['message' => $result[0]->Message ?? 'GST Code saved successfully']);
    } catch (\Exception $e) {
        DB::connection('coops')->rollBack();
        Log::channel('trading')->error('GST save error: ' . $e->getMessage());
        return response()->json(['message' => 'Failed to save GST code'], 500);
    }
}

public function updateGst(Request $request, $id)
{
    $request->validate([
        'gst_code'    => 'required|string|max:20',
        'category'    => 'required|string|max:50',
        'tax_percent' => 'required|numeric|min:0|max:100',
        'sgst'        => 'required|numeric|min:0|max:100',
        'cgst'        => 'required|numeric|min:0|max:100',
        'igst'        => 'required|numeric|min:0|max:100',
        'ugst'        => 'nullable|numeric|min:0|max:100',
        'is_active'   => 'required|in:0,1',
    ]);
    try {
        Config::set('database.connections.coops.database', session('org_schema'));
        DB::connection('coops')->beginTransaction();
        $result = DB::connection('coops')->select('CALL USP_ADD_EDIT_GST_CODE(?,?,?,?,?,?,?,?,?,?,?)', [
            $id,
            $request->gst_code,
            $request->category,
            $request->tax_percent,
            $request->sgst,
            $request->cgst,
            $request->igst,
            $request->ugst ?? 0,
            $request->is_active,
            session('user_id'),
            2
        ]);
        if (!empty($result) && $result[0]->Error_No < 0) {
            DB::connection('coops')->rollBack();
            return response()->json(['error' => $result[0]->Message], 422);
        }
        DB::connection('coops')->commit();
        return response()->json(['message' => $result[0]->Message ?? 'GST Code updated successfully']);
    } catch (\Exception $e) {
        DB::connection('coops')->rollBack();
        Log::channel('trading')->error('GST update error: ' . $e->getMessage());
        return response()->json(['message' => 'Failed to update GST code'], 500);
    }
}

    public function indexCounterBalance()
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $users = DB::connection('coops')->select('CALL USP_GET_USER_LIST(?)', [session('branch_id')]);
        $rows  = DB::connection('coops')->select('CALL USP_GET_COUNTER_BALANCE()');

        return view('Admin.counter-balance', [
            'users'      => $users,
            'rows'       => $rows,
            'year_start' => session('year_start'),
        ])->with('pageTitle', 'Counter Balance');
    }

    public function storeCounterBalance(Request $request)
    {
        $request->validate([
            'counter_id' => 'required|integer|min:1',
            'balance'    => 'required|numeric|min:0',
        ]);

        $balDate = session('year_start');

        $mode = $request->input('id') ? 2 : 1;
        try {
            Config::set('database.connections.coops.database', session('org_schema'));
            DB::connection('coops')->beginTransaction();
            $result = DB::connection('coops')->select('CALL USP_ADD_EDIT_COUNTER_BALANCE(?,?,?,?,?)', [
                $request->input('id') ?: 0,
                (int) $request->input('counter_id'),
                $request->input('balance'),
                $balDate,
                $mode,
            ]);
            if (!empty($result) && $result[0]->Error_No < 0) {
                DB::connection('coops')->rollBack();
                return response()->json(['error' => $result[0]->Message], 422);
            }
            DB::connection('coops')->commit();
            return response()->json(['message' => $result[0]->Message ?? 'Saved successfully']);
        } catch (\Exception $e) {
            DB::connection('coops')->rollBack();
            Log::channel('trading')->error('Counter balance save error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to save counter balance'], 500);
        }
    }
}
