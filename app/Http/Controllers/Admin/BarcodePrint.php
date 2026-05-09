<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BarcodePrint extends Controller
{
    
//barcode generate

public function indexBarcode()
{
    return view('Admin.barcode-label')->with('pageTitle', 'Barcode & Label Management');
}

public function getPendingBarcodeList($mode)
{
    try {
        Config::set('database.connections.coops.database', session('org_schema'));
        Log::channel('trading')->info('getPendingBarcodeList', [
            'mode' => $mode,
            'org_schema' => session('org_schema'),
        ]);
        $data = DB::connection('coops')->select('CALL USP_GET_PENDING_BARCODE_LIST(?)', [$mode]);
        Log::channel('trading')->info('getPendingBarcodeList result', ['count' => count($data), 'data' => $data]);
        return response()->json($data);
    } catch (\Exception $e) {
        Log::channel('trading')->error('getPendingBarcodeList error: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to load data'], 500);
    }
}

public function generateBarcode(Request $request)
{
    $request->validate(['stock_id' => 'required|integer', 'stock_date' => 'required|date']);
    try {
        Config::set('database.connections.coops.database', session('org_schema'));
        $result = DB::connection('coops')->select(
            'CALL USP_GENERATE_BARCODE(?, ?, ?, ?)',
            [session('org_id'), session('branch_code'), $request->stock_date, $request->stock_id]
        );
        Log::channel('trading')->info('USP_GENERATE_BARCODE result', ['result' => $result]);
        if (!empty($result) && $result[0]->Error_No < 0) {
            return response()->json(['error' => $result[0]->Message], 422);
        }
        // Read back the generated barcode
        $row = DB::connection('coops')->selectOne(
            'SELECT Barcode, MRP FROM trns_stockinout WHERE Stock_Id = ?',
            [$request->stock_id]
        );
        return response()->json(['barcode' => $row->Barcode, 'mrp' => $row->MRP ?? 0]);
    } catch (\Exception $e) {
        Log::channel('trading')->error('generateBarcode error: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to generate barcode'], 500);
    }
}



//PRINT BARCODE

 public function index()
    {
        return view('Admin.print-barcode')->with('pageTitle', 'Print Barcode');
    }

   public function getList($mode)
{
    try {
        Config::set('database.connections.coops.database', session('org_schema'));
        $data = DB::connection('coops')->select('CALL USP_GET_BARCODE_PRINT_LIST(?)', [$mode]);
        return response()->json($data);
    } catch (\Exception $e) {
        Log::channel('trading')->error('getPrintBarcodeList error: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to load data'], 500);
    }
}


}
