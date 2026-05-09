<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class MasterSetup extends Controller
{

//product master
    public function index()
    {
        $orgId = session('org_id');
        $units = DB::select('CALL USP_GET_ITEM_UNIT(?)', [$orgId]);
        $categories = DB::select('CALL USP_GET_ITEM_CAT(?)', [$orgId]);
        $subCategories = DB::select('CALL USP_GET_ITEM_SUB_CAT(?, ?)', [$orgId, 0]);
        $hsn = DB::select('CALL USP_GET_GST_HSN()');
        
        return view('Admin.product-master', compact('units', 'categories', 'subCategories', 'hsn'))->with('pageTitle', 'Product Master');
    }

 public function searchItem(Request $request)
{
    $searchTerm = $request->input('search');

    Config::set('database.connections.coops.database', session('org_schema'));
    $items = DB::connection('coops')->select('CALL USP_SEARCH_ITEM(?)', [$searchTerm]);

    $formattedItems = array_map(function($item) {
        return [
            'Prod_Id'     => $item->Prod_Id,
            'ItemDisplay' => $item->Item_Name
        ];
    }, $items);

    return response()->json($formattedItems);
}


   public function getItemDetails($id)
   {
    Config::set('database.connections.coops.database', session('org_schema'));
    $item = DB::connection('coops')->select('CALL USP_GET_ITEM_DETAILS(?)', [$id]);
    
    return response()->json($item[0] ?? null);
   }


    public function getSubCategories($categoryId)
    {
        $orgId = session('org_id');
        
        $subCategories = DB::select('CALL USP_GET_ITEM_SUB_CAT(?, ?)', [$orgId, $categoryId]);
        
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
        'mrp' => 'required|numeric|min:0|max:999999.99',
        'discount' => 'nullable|numeric|min:0|max:100',
        'sale_price' => 'required|numeric|min:0|max:99999.99',
        'agent_comm' => 'nullable|numeric|min:0|max:999.99',
        'reorder_qnty' => 'nullable|integer|min:0|max:9999',
        'prod_life' => 'nullable|digits_between:1,3|integer|min:0|max:200',
    ]);
        $mode = $request->input('product_id') ? 2 : 1;
        try {
            Config::set('database.connections.coops.database', session('org_schema'));
             DB::connection('coops')->beginTransaction();
            $result = DB::connection('coops')->select('CALL USP_ADD_EDIT_PRODUCT(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)', [
                $request->input('product_id'),
                $request->input('product_code'),
                $request->input('product_name'),
                $request->input('print_name'),
                $request->input('unit'),
                $request->input('category'),
                $request->input('sub_category'),
                $request->input('hsn'),
                $request->input('mrp'),
                $request->input('discount'),
                $request->input('sale_price'),
                $request->input('agent_comm'),
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
           return response()->json(['success' => false,'message' => 'Failed to save product'], 500);
        }
    }




//agent profile
    public function indexAgent()
{
    $branchId = session('branch_id');
    Config::set('database.connections.coops.database', session('org_schema'));
    $agents = DB::connection('coops')->select('CALL USP_GET_AGENT_LIST(?)', [$branchId]);
    
    return view('Admin.agent-profile', compact('agents'))->with('pageTitle', 'Agent Profile');
}

public function storeAgent(Request $request)
{
    $request->validate([
        'agent_code' => 'required|digits:4',
        'agent_name' => 'required|string|max:50',
        'address' => 'required|string|max:100',
        'mobile' => 'required|digits:10',
        'join_date' => 'required|date|before_or_equal:today',
        'stock_limit' => 'nullable|numeric|min:0|max:99999',
        'password' => 'nullable|string|min:6|max:20',
    ]);
     try {
        Config::set('database.connections.coops.database', session('org_schema'));
             DB::connection('coops')->beginTransaction();
        $result = DB::connection('coops')->select('CALL USP_ADD_EDIT_AGENT(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
            0,
            $request->input('agent_code'),
            $request->input('agent_name'),
            $request->input('address'),
            $request->input('mobile'),
            $request->input('join_date'),
            $request->input('stock_limit'),
            session('branch_id'),
            $request->input('password'),
            1,
            1
        ]);
        
        if ($result && isset($result[0]->Error_No) && $result[0]->Error_No == -1) {
              DB::connection('coops')->rollBack();
            return response()->json(['success' => false, 'message' => $result[0]->Message], 400);
        }
          DB::connection('coops')->commit();
        return response()->json(['success' => true, 'message' => 'Agent added successfully']);
    } catch (\Exception $e) {
        DB::connection('coops')->rollBack();
        Log::channel('trading')->error('Agent save error: ' . $e->getMessage());
       return response()->json(['success' => false,'message' => 'Failed to save agent'], 500);
    }
}

public function updateAgent(Request $request, $id)
{
    
$request->validate([
       'agent_code' => 'required|digits:4',
        'agent_name' => 'required|string|max:50',
        'address' => 'required|string|max:100',
        'mobile' => 'required|digits:10',
        'join_date' => 'required|date|before_or_equal:today',
        'stock_limit' => 'nullable|numeric|min:0|max:99999',
        'password' => 'nullable|string|min:6|max:20',
    ]);
    try {
        Config::set('database.connections.coops.database', session('org_schema'));
          DB::connection('coops')->beginTransaction();
        $result = DB::connection('coops')->select('CALL USP_ADD_EDIT_AGENT(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
            $id,
            $request->input('agent_code'),
            $request->input('agent_name'),
            $request->input('address'),
            $request->input('mobile'),
            $request->input('join_date'),
            $request->input('stock_limit'),
            session('branch_id'),
            $request->input('password'),
            1,
            2
        ]);
        
        if ($result && isset($result[0]->Error_No) && $result[0]->Error_No == -1) {
               DB::connection('coops')->rollBack();
            return response()->json(['success' => false, 'message' => $result[0]->Message], 400);
        }
          DB::connection('coops')->commit();
        return response()->json(['success' => true, 'message' => 'Agent updated successfully']);
    } catch (\Exception $e) {
         DB::connection('coops')->rollBack();
        Log::channel('trading')->error('Agent update error: ' . $e->getMessage());
        return response()->json(['success' => false,'message' => 'Failed to update agent'], 500);
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
    $suppliers = DB::connection('coops')->select('CALL USP_GET_PARTY_LIST(?,?)', [1, $branchId ]);
    Log::channel('trading')->info('USP_GET_PARTY_LIST result', ['result' => $suppliers]);
    return view('Admin.supplier-master', compact('suppliers'))->with('pageTitle', 'Supplier Master');
}

public function storeSupplier(Request $request)
{
    $request->validate([
        'party_code' => 'required|string|max:25',
        'party_name' => 'required|string|max:100',
        'mobile_no' => 'required|digits:10',
        'contact_no'=> 'nullable|digits:10',
        'mail_id' => 'nullable|email|max:50',
        'contact_person' => 'nullable|string|max:150',
        'designation' => 'nullable|string|max:50',
        'address1' => 'required|string|max:200',
        'address2' => 'nullable|string|max:200',
        'city' => 'nullable|string|max:50',
        'district' => 'nullable|string|max:50',
        'state' => 'nullable|string|max:25',
        'pin_code' => 'nullable|numeric|max_digits:10',
        'pan_no' =>'nullable|string|max:25|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
        'gstin' => 'nullable|string|max:25|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
        'state_code' => 'nullable|numeric|max:99',
       'credit_limit' => 'nullable|numeric|min:0|max:99999.99',
    ]);

    try {
        Config::set('database.connections.coops.database', session('org_schema'));
          DB::connection('coops')->beginTransaction();
        $result = DB::connection('coops')->select('CALL USP_ADD_EDIT_PARTY(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
            0,
            $request->input('party_code'),
            $request->input('party_name'),
            $request->input('mobile_no'),
            $request->input('contact_no'),
            $request->input('mail_id'),
            $request->input('contact_person'),
            $request->input('designation'),
            $request->input('address1'),
            $request->input('address2'),
            $request->input('city'),
            $request->input('district'),
            $request->input('state'),
            $request->input('pin_code'),
            $request->input('pan_no'),
            $request->input('gstin'),
            $request->input('state_code'),
            date('Y-m-d'),
            $request->input('credit_limit'),
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
                DB::connection('coops')->commit();
                return response()->json(['message' => 'Supplier added successfully']);
            }
        }

        DB::connection('coops')->rollBack();
        return response()->json(['error' => 'No response from database'], 500);
    } catch (\Exception $e) {
           DB::connection('coops')->rollBack();
        Log::channel('trading')->error('Supplier save error: ' . $e->getMessage());
        return response()->json(['success' => false,'message' => 'Failed to Add Supplier'], 500);
    }
}

public function updateSupplier(Request $request, $id)
{
    $request->validate([
        'party_code' => 'required|string|max:25',
        'party_name' => 'required|string|max:100',
        'mobile_no' => 'required|digits:10',
        'contact_no'=> 'nullable|digits:10',
        'mail_id' => 'nullable|email|max:50',
        'contact_person' => 'nullable|string|max:150',
        'designation' => 'nullable|string|max:50',
        'address1' => 'required|string|max:200',
        'address2' => 'nullable|string|max:200',
        'city' => 'nullable|string|max:50',
        'district' => 'nullable|string|max:50',
        'state' => 'nullable|string|max:25',
        'pin_code' => 'nullable|numeric|max_digits:10',
        'pan_no' =>'nullable|string|max:25|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
        'gstin' => 'nullable|string|max:25|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
        'state_code' => 'nullable|numeric|max:99',
        'credit_limit' => 'nullable|numeric|min:0|max:99999.99',
    ]);

    try {
        Config::set('database.connections.coops.database', session('org_schema'));
            DB::connection('coops')->beginTransaction();
        $result = DB::connection('coops')->select('CALL USP_ADD_EDIT_PARTY(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
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
            $request->input('district'),
            $request->input('state'),
            $request->input('pin_code'),
            $request->input('pan_no'),
            $request->input('gstin'),
            $request->input('state_code'),
            date('Y-m-d'),
            $request->input('credit_limit'),
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

            DB::connection('coops')->commit();
            return response()->json(['message' => 'Supplier updated successfully']);
        }

        DB::connection('coops')->rollBack();
        return response()->json(['error' => 'No response from database'], 500);

    } catch (\Exception $e) {
          DB::connection('coops')->rollBack();
        Log::channel('trading')->error('Supplier update error: ' . $e->getMessage());
           return response()->json(['success' => false,'message' => 'Failed to Update Supplier'], 500);
    }
}



// Customer Master
public function indexCustomer()
{
    Config::set('database.connections.coops.database', session('org_schema'));
     $branchId = session('branch_id');
    $customers = DB::connection('coops')->select('CALL USP_GET_PARTY_LIST(?,?)', [2,$branchId]);

    
    return view('Admin.customer-master', compact('customers'))->with('pageTitle', 'Customer Master');
}

public function storeCustomer(Request $request)
{
    $request->validate([
        'party_code' => 'required|string|max:25',
        'party_name' => 'required|string|max:100',
        'mobile_no' => 'required|digits:10',
        'alt_mobile_no' => 'nullable|digits:10',
        'mail_id' => 'nullable|email|max:50',
        'address1' => 'required|string|max:200',
        'address2' => 'nullable|string|max:200',
        'city' => 'nullable|string|max:50',
        'district' => 'nullable|string|max:50',
        'state' => 'nullable|string|max:25',
        'pin_code' => 'nullable|numeric|max_digits:10',
        'pan_no' => 'nullable|string|max:25|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
        'gstin' => 'nullable|string|max:25|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
        'state_code' => 'nullable|numeric|max:99',
        'credit_limit' => 'nullable|numeric|min:0|max:99999',
    ]);

    Config::set('database.connections.coops.database', session('org_schema'));
    DB::connection('coops')->beginTransaction();

    try {
        $result = DB::connection('coops')->select("CALL USP_ADD_EDIT_CUSTOMER(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
            0,
            $request->party_code,
            $request->party_name,
            $request->mobile_no,
            $request->alt_mobile_no,
            $request->mail_id,
            $request->address1,
            $request->address2,
            $request->city,
            $request->district,
            $request->state,
            $request->pin_code,
            $request->pan_no,
            $request->gstin,
            $request->state_code,
            date('Y-m-d'),
            $request->credit_limit,
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
                DB::connection('coops')->commit();
                return response()->json(['message' => 'Customer added successfully']);
            }
        }

        DB::connection('coops')->rollBack();
        return response()->json(['error' => 'No response from database'], 500);

    } catch (\Exception $e) {
        DB::connection('coops')->rollBack();
        Log::channel('trading')->error('Customer store error: ' . $e->getMessage());
         return response()->json(['success' => false,'message' => 'Failed to Add Customer'], 500);
    }
}


public function updateCustomer(Request $request, $id)
{
    $request->validate([
        'party_name' => 'required|string|max:250',
        'mobile_no' => 'required|digits:10',
        'alt_mobile_no' => 'nullable|digits:10',
        'mail_id' => 'nullable|email|max:50',
        'address1' => 'required|string|max:200',
        'address2' => 'nullable|string|max:200',
        'city' => 'nullable|string|max:50',
        'district' => 'nullable|string|max:50',
        'state' => 'nullable|string|max:25',
        'pin_code' => 'nullable|numeric|max_digits:10',
        'pan_no' => 'nullable|string|max:25|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
        'gstin' => 'nullable|string|max:25|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
        'state_code' => 'nullable|numeric|max:99',
        'credit_limit' => 'nullable|numeric|min:0|max:99999',
    ]);

    try {
        Config::set('database.connections.coops.database', session('org_schema'));
        

        DB::connection('coops')->beginTransaction();
        
        $result = DB::connection('coops')->select('CALL USP_ADD_EDIT_CUSTOMER(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
            $id,
            null,
            $request->input('party_name'),
            $request->input('mobile_no'),
            $request->input('alt_mobile_no'),
            $request->input('mail_id'),
            $request->input('address1'),
            $request->input('address2'),
            $request->input('city'),
            $request->input('district'),
            $request->input('state'),
            $request->input('pin_code'),
            $request->input('pan_no'),
            $request->input('gstin'),
            $request->input('state_code'),
            date('Y-m-d'),
            $request->input('credit_limit'),
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
        return response()->json(['success' => false,'message' => 'Failed to update Customer'], 500);
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
    $memberTypes = DB::select('CALL USP_GET_APPLICATION_OPTION(?)', [9]);
    
    return view('Admin.member-share', compact('memberTypes'))->with('pageTitle', 'Member & Share Management');
}

public function storeMember(Request $request)
{
    $request->validate([
        'mem_type' => 'required|integer',
        'mem_name' => 'required|string|max:50',
        'gur_name' => 'required|string|max:50',
        'address' => 'required|string|max:100',
        'mob_no' => 'required|digits:10',
        'adhar_no' => 'nullable|digits:12',
        'voter_no' => 'nullable|string|max:25',
        'pan_no' => 'nullable|string|max:25|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
        'adm_date' => 'required|date||before_or_equal:today',
        'adm_fees' => 'required|numeric|min:0|max:99999',
        'share_no' => 'required|digits_between:1,5',
        'share_amt' => 'required|numeric|min:0|max:99999',
        'trans_mode' => 'required|in:1,2',
        'bank_id' => 'nullable|integer',
    ]);

    Config::set('database.connections.coops.database', session('org_schema'));
    DB::connection('coops')->beginTransaction();

    try {
        $result = DB::connection('coops')->select('CALL USP_ADD_EDIT_MEMBER(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
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
            $request->bank_id,
            1
        ]);

        Log::channel('trading')->info('USP_ADD_EDIT_MEMBER result', ['result' => $result]);

        if (!empty($result)) {
            $errorNo = $result[0]->Error_No ?? 0;
            $message = $result[0]->Messgae ?? '';

            if ($errorNo < 0) {
                DB::connection('coops')->rollBack();
                return response()->json(['error' => $message], 422);
            }

            if ($errorNo == 0) {
                DB::connection('coops')->commit();
                return response()->json(['message' => 'Member added successfully']);
            }
        }

        DB::connection('coops')->rollBack();
        return response()->json(['error' => 'No response from database'], 500);

    } catch (\Exception $e) {
        DB::connection('coops')->rollBack();
        Log::channel('trading')->error('Member save error: ' . $e->getMessage());
          return response()->json(['success' => false,'message' => 'Failed to Add Member'], 500);
    }
}


// User Roles & Permissions
public function indexUserGroup()
{
    Config::set('database.connections.coops.database', session('org_schema'));

    $groups = DB::connection('coops')->select('CALL USP_GET_USER_GROUP()');
    $menus  = DB::connection('coops')->select('CALL USP_GET_ALL_MENUE()');
    Log::channel('trading')->info('USP_GET_ALL_MENUE result',$menus);

    return view('Admin.user-roles', compact('groups','menus'))
        ->with('pageTitle','User Roles & Permissions');
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

    try{
        // Create temporary table
        DB::connection('coops')->statement("CREATE TEMPORARY TABLE IF NOT EXISTS tempmenue(Parraint_Id INT, Child_Id INT)");
        DB::connection('coops')->statement("DELETE FROM tempmenue");
        
        if(!empty($request->menus))
        {
            foreach($request->menus as $menu)
            {
                DB::connection('coops')->statement("INSERT INTO tempmenue(Parraint_Id,Child_Id) VALUES (?,?)",
                    [$menu['parent'],$menu['child']]
                );
            }
        }
        $result = DB::connection('coops')->select(
            "CALL USP_ADD_USER_GROUP(?,?,?,?,?)",
            [
                $request->grp_id ,
                $request->grp_name,
                $request->grp_desc , 
                $request->is_admin ,
                $request->mode
            ]
        );

        if($result[0]->Error_No < 0){
            DB::connection('coops')->rollBack();
            return response()->json(['error'=>$result[0]->Message],422);
        }

        DB::connection('coops')->commit();
        return response()->json(['message'=>'User Group saved successfully']);

    }catch(\Exception $e){
        DB::connection('coops')->rollBack();
        Log::channel('trading')->error('User Group save error: ' . $e->getMessage());
        return response()->json(['success' => false,'message' => 'Failed to Add User group'], 500);
    }
}



}



