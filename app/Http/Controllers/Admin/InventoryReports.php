<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\PaginatesReportRows;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class InventoryReports extends Controller
{
    use PaginatesReportRows;

    public function stockSummary()
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $categories = DB::connection('coops')->select('CALL USP_GET_ITEM_CAT()');

        return view('Admin.stock-summary', [
            'categories'  => $categories,
            'year_start'  => session('year_start'),
            'year_end'    => session('year_end'),
            'org_name'    => session('org_name'),
            'branch_name' => session('branch_name'),
        ])->with('pageTitle', 'Stock Summary Report');
    }

    public function stockSummarySearch(Request $request)
    {
        $asOn = $request->input('as_on_date');
        if (!$asOn) {
            return response()->json(['message' => 'Select as on date'], 422);
        }
Config::set('database.connections.coops.database', session('org_schema'));
        $rows = DB::connection('coops')->select('CALL USP_GET_STOCK_SUMMARY(?, ?, ?)', [
            $asOn,
            (int) $request->input('cat_id', 0),
            (int) $request->input('sub_cat_id', 0),
        ]);

        $grouped = [];
        foreach ($rows as $row) {
            $id = (int) $row->Prod_Id;
            if (!isset($grouped[$id])) {
                $grouped[$id] = [
                    'Prod_Id'       => $id,
                    'Prod_Code'     => $row->Prod_Code,
                    'Prod_ShortNm'  => $row->Prod_ShortNm,
                    'Cate_Name'     => $row->Cate_Name,
                    'Unit_Name'     => $row->Unit_Name,
                    'Counter_Qty'   => 0,
                    'Agent_Qty'     => 0,
                ];
            }
            $grouped[$id]['Counter_Qty'] = round($grouped[$id]['Counter_Qty'] + (float) $row->Qty, 2);
        }

        $catId = (int) $request->input('cat_id', 0);
        $subCatId = (int) $request->input('sub_cat_id', 0);
        $agentRows = DB::connection('coops')->select(
            'CALL USP_GET_STOCK_SUMMARY_AGENT(?, ?, ?)',
            [$asOn, $catId, $subCatId]
        );

        foreach ($agentRows as $row) {
            $id = (int) $row->Prod_Id;
            $agentQty = round((float) $row->Agent_Qty, 2);
            if (!isset($grouped[$id])) {
                $grouped[$id] = [
                    'Prod_Id'      => $id,
                    'Prod_Code'    => $row->Prod_Code,
                    'Prod_ShortNm' => $row->Prod_ShortNm,
                    'Cate_Name'    => $row->Cate_Name,
                    'Unit_Name'    => $row->Unit_Name,
                    'Counter_Qty'  => 0,
                    'Agent_Qty'    => 0,
                ];
            }
            $grouped[$id]['Agent_Qty'] = $agentQty;
        }

        $itemQ = strtoupper(trim((string) $request->input('item_q', '')));

        $products = array_values(array_filter($grouped, function ($row) use ($itemQ) {
            if (abs($row['Counter_Qty']) <= 0.0001 && abs($row['Agent_Qty']) <= 0.0001) {
                return false;
            }
            if ($itemQ === '') {
                return true;
            }
            $code = strtoupper((string) ($row['Prod_Code'] ?? ''));
            $name = strtoupper((string) ($row['Prod_ShortNm'] ?? ''));
            return strpos($code, $itemQ) !== false || strpos($name, $itemQ) !== false;
        }));
        usort($products, function ($a, $b) {
            return strcmp((string) $a['Prod_ShortNm'], (string) $b['Prod_ShortNm']);
        });

        return $this->pagedReport($request, $products, ['Agent_Qty', 'Counter_Qty']);
    }

    public function stockSummaryDetails(Request $request)
    {
        $asOn = $request->input('as_on_date');
        $prodId = (int) $request->input('prod_id', 0);
        if (!$asOn || $prodId <= 0) {
            return response()->json(['message' => 'Product and date are required'], 422);
        }
Config::set('database.connections.coops.database', session('org_schema'));

        return response()->json($this->productBarcodeStock($prodId, $asOn));
    }

    /**
     * Remaining office stock by barcode. A movement saved without a barcode
     * is added to that MRP when only one barcode exists for it.
     */
    private function productBarcodeStock(int $prodId, string $date): array
    {
        $rows = DB::connection('coops')->select(
            'CALL USP_RPT_STOCK_BARCODE_BARCODED(?, ?)',
            [$prodId, $date]
        );
        $leftover = DB::connection('coops')->select(
            'CALL USP_RPT_STOCK_BARCODE_LEFTOVER(?, ?)',
            [$prodId, $date]
        );

        $all = array_merge($rows, $leftover);

        return array_values(array_filter($all, function ($row) {
            return (float) $row->Avil_Qnty > 0.0001;
        }));
    }

    public function stockSummarySubCats(Request $request)
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $subs = DB::connection('coops')->select('CALL USP_GET_ITEM_SUB_CAT(?)', [
            (int) $request->input('cat_id', 0),
        ]);

        return response()->json($subs);
    }

    public function purchaseRegister()
    {
        return view('Admin.purchase-register', [
            'parties'     => [],
            'year_start'  => session('year_start'),
            'year_end'    => session('year_end'),
            'org_name'    => session('org_name'),
            'branch_name' => session('branch_name'),
        ])->with('pageTitle', 'Purchase Register');
    }

    public function purchaseRegisterSearch(Request $request)
    {
        $frm = $request->input('frm_date');
        $to = $request->input('to_date');
        if (!$frm || !$to) {
            return response()->json(['message' => 'Select from date and to date'], 422);
        }
        if ($frm > $to) {
            return response()->json(['message' => 'From date cannot be after to date'], 422);
        }
Config::set('database.connections.coops.database', session('org_schema'));
        $partyId = (int) $request->input('party_id', 0);
        $branchId = (int) (session('branch_id') ?: 0);
        $gstFilter = (int) $request->input('gst_filter', 0);

        $rows = DB::connection('coops')->select(
            'CALL USP_RPT_PURCHASE_REGISTER(?, ?, ?, ?, ?)',
            [$frm, $to, $partyId, $branchId, $gstFilter]
        );

        return $this->pagedReport($request, $rows);
    }

    public function itemwisePurchase()
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $categories = DB::connection('coops')->select('CALL USP_GET_ITEM_CAT()');

        return view('Admin.itemwise-purchase', [
            'parties'     => [],
            'categories'  => $categories,
            'year_start'  => session('year_start'),
            'year_end'    => session('year_end'),
            'org_name'    => session('org_name'),
            'branch_name' => session('branch_name'),
        ])->with('pageTitle', 'Item Wise Purchase Report');
    }

    public function itemwisePurchaseSearch(Request $request)
    {
        $frm = $request->input('frm_date');
        $to = $request->input('to_date');
        if (!$frm || !$to) {
            return response()->json(['message' => 'Select from date and to date'], 422);
        }
        if ($frm > $to) {
            return response()->json(['message' => 'From date cannot be after to date'], 422);
        }
Config::set('database.connections.coops.database', session('org_schema'));
        $partyId = (int) $request->input('party_id', 0);
        $branchId = (int) (session('branch_id') ?: 0);
        $catId = (int) $request->input('cat_id', 0);
        $subCatId = (int) $request->input('sub_cat_id', 0);

        $rows = DB::connection('coops')->select(
            'CALL USP_RPT_ITEMWISE_PURCHASE(?, ?, ?, ?, ?, ?)',
            [$frm, $to, $partyId, $branchId, $catId, $subCatId]
        );

        return $this->pagedReport($request, $rows);
    }

    public function salesRegister()
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $categories = DB::connection('coops')->select('CALL USP_GET_ITEM_CAT()');
        return view('Admin.sales-register', [
            'categories'  => $categories,
            'year_start'  => session('year_start'),
            'year_end'    => session('year_end'),
            'org_name'    => session('org_name'),
            'branch_name' => session('branch_name'),
        ])->with('pageTitle', 'Sales Register');
    }

    public function salesRegisterSearch(Request $request)
    {
        $frm = $request->input('frm_date');
        $to = $request->input('to_date');
        if (!$frm || !$to) {
            return response()->json(['message' => 'Select from date and to date'], 422);
        }
        if ($frm > $to) {
            return response()->json(['message' => 'From date cannot be after to date'], 422);
        }
Config::set('database.connections.coops.database', session('org_schema'));
        $mode = (int) $request->input('mode', 0);
        $branchId = (int) (session('branch_id') ?: 0);
        $catId = (int) $request->input('cat_id', 0);

        $rows = DB::connection('coops')->select(
            'CALL USP_RPT_SALES_REGISTER(?, ?, ?, ?, ?)',
            [$frm, $to, $branchId, $mode, $catId]
        );

        return $this->pagedReport($request, $rows);
    }

    public function agentRegister()
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        return view('Admin.agent-register', [
            'agents'      => [],
            'year_start'  => session('year_start'),
            'year_end'    => session('year_end'),
            'org_name'    => session('org_name'),
            'branch_name' => session('branch_name'),
        ])->with('pageTitle', 'Agent Register');
    }

    public function agentRegisterSearch(Request $request)
    {
        $mode = (int) $request->input('mode', 1);
        Config::set('database.connections.coops.database', session('org_schema'));

        $limit = (int) $request->input('limit', 0);
        $offset = max(0, (int) $request->input('offset', 0));
        $agentId = (int) $request->input('agent_id', 0);

        if ($mode === 2) {
            $asOn = $request->input('as_on_date');
            if (!$asOn) {
                return response()->json(['message' => 'Select as on date'], 422);
            }
if ($limit > 0) {
                $pageLimit = min(100, max(10, $limit));
                $rows = DB::connection('coops')->select('CALL USP_RPT_AGENT_STOCK_PAGE(?, ?, ?, ?)', [
                    $asOn, $agentId, $pageLimit, $offset,
                ]);
                $total = !empty($rows) ? (int) ($rows[0]->Total_Count ?? 0) : 0;
                $totals = [];
                if ($offset === 0) {
                    $sum = DB::connection('coops')->selectOne('CALL USP_RPT_AGENT_STOCK_TOTALS(?, ?)', [
                        $asOn, $agentId,
                    ]);
                    $totals = ['Qty' => (float) ($sum->Qty ?? 0)];
                    $total = $total ?: (int) ($sum->Total_Count ?? 0);
                }
                return $this->pagedSqlReport($request, $rows, $total, $totals);
            }
            $rows = DB::connection('coops')->select('CALL USP_RPT_AGENT_STOCK(?, ?)', [
                $asOn,
                $agentId,
            ]);
            return $this->pagedReport($request, $rows, ['Qty']);
        }

        $frm = $request->input('frm_date');
        $to = $request->input('to_date');
        if (!$frm || !$to) {
            return response()->json(['message' => 'Select from date and to date'], 422);
        }
        if ($frm > $to) {
            return response()->json(['message' => 'From date cannot be after to date'], 422);
        }
if ($limit > 0) {
            $pageLimit = min(100, max(10, $limit));
            $rows = DB::connection('coops')->select('CALL USP_RPT_AGENT_INDENT_PAGE(?, ?, ?, ?, ?)', [
                $frm, $to, $agentId, $pageLimit, $offset,
            ]);
            $total = !empty($rows) ? (int) ($rows[0]->Total_Count ?? 0) : 0;
            $totals = [];
            if ($offset === 0) {
                $sum = DB::connection('coops')->selectOne('CALL USP_RPT_AGENT_INDENT_TOTALS(?, ?, ?)', [
                    $frm, $to, $agentId,
                ]);
                $totals = [
                    'Req_Qty'    => (float) ($sum->Req_Qty ?? 0),
                    'Reject_Qty' => (float) ($sum->Reject_Qty ?? 0),
                    'Issue_Qty'  => (float) ($sum->Issue_Qty ?? 0),
                ];
                $total = $total ?: (int) ($sum->Total_Count ?? 0);
            }
            return $this->pagedSqlReport($request, $rows, $total, $totals);
        }

        $rows = DB::connection('coops')->select('CALL USP_RPT_AGENT_INDENT(?, ?, ?)', [
            $frm,
            $to,
            $agentId,
        ]);

        return $this->pagedReport($request, $rows, ['Req_Qty', 'Reject_Qty', 'Issue_Qty']);
    }

    public function returnRegister()
    {
        return view('Admin.return-register', [
            'year_start'  => session('year_start'),
            'year_end'    => session('year_end'),
            'org_name'    => session('org_name'),
            'branch_name' => session('branch_name'),
        ])->with('pageTitle', 'Return Register');
    }

    public function returnRegisterSearch(Request $request)
    {
        $frm = $request->input('frm_date');
        $to = $request->input('to_date');
        if (!$frm || !$to) {
            return response()->json(['message' => 'Select from date and to date'], 422);
        }
        if ($frm > $to) {
            return response()->json(['message' => 'From date cannot be after to date'], 422);
        }
Config::set('database.connections.coops.database', session('org_schema'));
        $rows = DB::connection('coops')->select('CALL USP_RPT_RETURN_REGISTER(?, ?, ?, ?)', [
            $frm,
            $to,
            (int) $request->input('mode', 0),
            (int) (session('branch_id') ?: 0),
        ]);

        return $this->pagedReport($request, $rows);
    }

    public function supplierRegister()
    {
        // Parties load via AJAX Select2 (parties.search)
        return view('Admin.supplier-register', [
            'year_start'  => session('year_start'),
            'year_end'    => session('year_end'),
            'org_name'    => session('org_name'),
            'branch_name' => session('branch_name'),
            'parties'     => [],
        ])->with('pageTitle', 'Supplier Register');
    }

    public function supplierRegisterSearch(Request $request)
    {
        $mode = (int) $request->input('mode', 1);
        $frm = $request->input('frm_date');
        $to = $request->input('to_date');
        if (!$frm || !$to) {
            return response()->json(['message' => 'Select from date and to date'], 422);
        }
        if ($frm > $to) {
            return response()->json(['message' => 'From date cannot be after to date'], 422);
        }
Config::set('database.connections.coops.database', session('org_schema'));
        $branchId = (int) (session('branch_id') ?: 0);

        if ($mode === 2) {
            $partyId = (int) $request->input('party_id', 0);
            if ($partyId <= 0) {
                return response()->json(['message' => 'Select a supplier for ledger report'], 422);
            }
            $rows = DB::connection('coops')->select('CALL USP_RPT_PARTY_LEDGER(?, ?, ?, ?, ?)', [
                $frm,
                $to,
                $branchId,
                1,
                $partyId,
            ]);
            return $this->pagedReport($request, $rows);
        }

        $rows = DB::connection('coops')->select('CALL USP_RPT_SUPPLIER_REGISTER(?, ?, ?)', [
            $frm,
            $to,
            $branchId,
        ]);

        return $this->pagedReport($request, $rows);
    }

    public function customerRegister()
    {
        // Parties load via AJAX Select2 (parties.search) — customer list is too large to embed.
        return view('Admin.customer-register', [
            'year_start'  => session('year_start'),
            'year_end'    => session('year_end'),
            'org_name'    => session('org_name'),
            'branch_name' => session('branch_name'),
            'parties'     => [],
        ])->with('pageTitle', 'Customer Register');
    }

    public function customerRegisterSearch(Request $request)
    {
        $mode = (int) $request->input('mode', 1);
        $frm = $request->input('frm_date');
        $to = $request->input('to_date');
        if (!$frm || !$to) {
            return response()->json(['message' => 'Select from date and to date'], 422);
        }
        if ($frm > $to) {
            return response()->json(['message' => 'From date cannot be after to date'], 422);
        }
Config::set('database.connections.coops.database', session('org_schema'));
        $branchId = (int) (session('branch_id') ?: 0);

        if ($mode === 2) {
            $partyId = (int) $request->input('party_id', 0);
            if ($partyId <= 0) {
                return response()->json(['message' => 'Select a customer for ledger report'], 422);
            }
            $rows = DB::connection('coops')->select('CALL USP_RPT_PARTY_LEDGER(?, ?, ?, ?, ?)', [
                $frm,
                $to,
                $branchId,
                2,
                $partyId,
            ]);
            return $this->pagedReport($request, $rows);
        }

        $rows = DB::connection('coops')->select('CALL USP_RPT_CUSTOMER_REGISTER(?, ?, ?)', [
            $frm,
            $to,
            $branchId,
        ]);

        return $this->pagedReport($request, $rows);
    }

    public function shareRegister()
    {
        return view('Admin.share-register', [
            'year_start'  => session('year_start'),
            'year_end'    => session('year_end'),
            'org_name'    => session('org_name'),
            'branch_name' => session('branch_name'),
        ])->with('pageTitle', 'Share Register');
    }

    public function shareRegisterSearch(Request $request)
    {
        $frm = $request->input('frm_date');
        $to = $request->input('to_date');
        if (!$frm || !$to) {
            return response()->json(['message' => 'Select from date and to date'], 422);
        }
        if ($frm > $to) {
            return response()->json(['message' => 'From date cannot be after to date'], 422);
        }
Config::set('database.connections.coops.database', session('org_schema'));
        $rows = DB::connection('coops')->select('CALL USP_RPT_SHARE_REGISTER(?, ?)', [$frm, $to]);

        return $this->pagedReport($request, $rows);
    }

    public function gstRegister()
    {
        return view('Admin.gst-register', [
            'year_start'  => session('year_start'),
            'year_end'    => session('year_end'),
            'org_name'    => session('org_name'),
            'branch_name' => session('branch_name'),
        ])->with('pageTitle', 'GST Register');
    }

    public function gstRegisterSearch(Request $request)
    {
        $frm = $request->input('frm_date');
        $to = $request->input('to_date');
        if (!$frm || !$to) {
            return response()->json(['message' => 'Select from date and to date'], 422);
        }
        if ($frm > $to) {
            return response()->json(['message' => 'From date cannot be after to date'], 422);
        }
Config::set('database.connections.coops.database', session('org_schema'));
        $rows = DB::connection('coops')->select('CALL USP_RPT_GST_REGISTER(?, ?, ?)', [
            $frm,
            $to,
            (int) (session('branch_id') ?: 0),
        ]);

        return $this->pagedReport($request, $rows);
    }

    public function stockStatement()
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $categories = DB::connection('coops')->select('CALL USP_GET_ITEM_CAT()');

        return view('Admin.stock-statement', [
            'categories'  => $categories,
            'year_start'  => session('year_start'),
            'year_end'    => session('year_end'),
            'org_name'    => session('org_name'),
            'branch_name' => session('branch_name'),
        ])->with('pageTitle', 'Stock Statement & Register');
    }

    public function stockStatementSearch(Request $request)
    {
        $frm = $request->input('frm_date');
        $to = $request->input('to_date');
        if (!$frm || !$to) {
            return response()->json(['message' => 'Select from date and to date'], 422);
        }
        if ($frm > $to) {
            return response()->json(['message' => 'From date cannot be after to date'], 422);
        }
Config::set('database.connections.coops.database', session('org_schema'));
        $catId = (int) $request->input('cat_id', 0);
        $subCatId = (int) $request->input('sub_cat_id', 0);

        $rows = DB::connection('coops')->select(
            'CALL USP_RPT_STOCK_STATEMENT_PRODUCTS(?, ?, ?, ?)',
            [$frm, $to, $catId, $subCatId]
        );

        $closing = [];
        foreach ($this->closingBarcodeStock($to, 0) as $line) {
            $id = (int) $line->Prod_Id;
            if (!isset($closing[$id])) {
                $closing[$id] = ['qty' => 0, 'value' => 0];
            }
            $qty = (float) $line->Avil_Qnty;
            $closing[$id]['qty'] = round($closing[$id]['qty'] + $qty, 2);
            $closing[$id]['value'] = round($closing[$id]['value'] + round($qty * (float) $line->MRP, 2), 2);
        }

        $agentRows = DB::connection('coops')->select(
            'CALL USP_RPT_STOCK_STATEMENT_AGENT(?, ?, ?)',
            [$to, $catId, $subCatId]
        );
        $agents = [];
        foreach ($agentRows as $agent) {
            $agents[(int) $agent->Prod_Id] = [
                'qty'   => round((float) $agent->Agent_Qty, 2),
                'value' => round((float) $agent->Agent_Value, 2),
            ];
        }

        $products = [];
        foreach ($rows as $row) {
            $id = (int) $row->Prod_Id;
            $opening = round((float) $row->Opening_Qty, 2);
            $purchaseQty = round((float) $row->Purchase_Qty, 2);
            $purchaseValue = round((float) $row->Purchase_Value, 2);
            $purchaseReturnQty = round((float) $row->Purchase_Return_Qty, 2);
            $purchaseReturnValue = round((float) $row->Purchase_Return_Value, 2);
            $soldQty = round((float) $row->Sold_Qty, 2);
            $soldValue = round((float) $row->Sold_Value, 2);
            $soldReturnQty = round((float) $row->Sold_Return_Qty, 2);
            $soldReturnValue = round((float) $row->Sold_Return_Value, 2);
            $damageQty = round((float) $row->Damage_Qty, 2);
            $damageValue = round((float) $row->Damage_Value, 2);
            $agentQty = $agents[$id]['qty'] ?? 0;
            $agentValueAmt = $agents[$id]['value'] ?? 0;
            $available = $closing[$id]['qty'] ?? 0;
            $value = $closing[$id]['value'] ?? 0;
            if (
                abs($opening) < 0.0001 && abs($purchaseQty) < 0.0001 && abs($purchaseReturnQty) < 0.0001
                && abs($soldQty) < 0.0001 && abs($soldReturnQty) < 0.0001 && abs($damageQty) < 0.0001
                && abs($agentQty) < 0.0001 && abs($available) < 0.0001
            ) {
                continue;
            }
            $products[] = [
                'Prod_Id'                 => $id,
                'Prod_ShortNm'            => $row->Prod_ShortNm,
                'Opening_Qty'             => $opening,
                'Purchase_Qty'            => $purchaseQty,
                'Purchase_Value'          => $purchaseValue,
                'Purchase_Return_Qty'     => $purchaseReturnQty,
                'Purchase_Return_Value'   => $purchaseReturnValue,
                'Sold_Qty'                => $soldQty,
                'Sold_Value'              => $soldValue,
                'Sold_Return_Qty'         => $soldReturnQty,
                'Sold_Return_Value'       => $soldReturnValue,
                'Damage_Qty'              => $damageQty,
                'Damage_Value'            => $damageValue,
                'Agent_Qty'               => $agentQty,
                'Agent_Value'             => $agentValueAmt,
                'Available'               => $available,
                'Value'                   => $value,
            ];
        }

        return $this->pagedReport($request, $products, [
            'Opening_Qty', 'Purchase_Qty', 'Purchase_Value', 'Purchase_Return_Qty', 'Purchase_Return_Value',
            'Sold_Qty', 'Sold_Value', 'Sold_Return_Qty', 'Sold_Return_Value', 'Damage_Qty', 'Damage_Value',
            'Agent_Qty', 'Agent_Value', 'Available', 'Value',
        ]);
    }

    public function stockStatementDetails(Request $request)
    {
        $to = $request->input('to_date');
        $prodId = (int) $request->input('prod_id', 0);
        if (!$to || $prodId <= 0) {
            return response()->json(['message' => 'Product and to date are required'], 422);
        }
Config::set('database.connections.coops.database', session('org_schema'));
        $product = DB::connection('coops')->selectOne(
            'CALL USP_GET_PRODUCT_SHORT_NAME(?)',
            [$prodId]
        );
        $name = $product->Prod_ShortNm ?? '';

        $lines = [];
        foreach ($this->closingBarcodeStock($to, $prodId) as $line) {
            $qty = round((float) $line->Avil_Qnty, 2);
            $lines[] = [
                'Barcode'      => $line->Barcode,
                'Prod_ShortNm' => $name,
                'MRP'          => $line->MRP,
                'Avil_Qnty'    => $qty,
                'Value'        => round($qty * (float) $line->MRP, 2),
            ];
        }

        return response()->json($lines);
    }

    /**
     * Office stock left on each barcode as on a date. $prodId 0 returns every product.
     */
    private function closingBarcodeStock(string $date, int $prodId): array
    {
        $rows = DB::connection('coops')->select(
            'CALL USP_RPT_CLOSING_BARCODE_STOCK(?, ?)',
            [$date, $prodId]
        );
        $leftover = DB::connection('coops')->select(
            'CALL USP_RPT_CLOSING_BARCODE_LEFTOVER(?, ?)',
            [$date, $prodId]
        );

        $all = array_merge($rows, $leftover);

        return array_values(array_filter($all, function ($row) {
            return (float) $row->Avil_Qnty > 0.0001;
        }));
    }

    public function expiryReport()
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $categories = DB::connection('coops')->select('CALL USP_GET_ITEM_CAT()');

        return view('Admin.expiry-report', [
            'categories'  => $categories,
            'year_start'  => session('year_start'),
            'year_end'    => session('year_end'),
            'org_name'    => session('org_name'),
            'branch_name' => session('branch_name'),
        ])->with('pageTitle', 'Expiry Report');
    }

    public function expiryReportSearch(Request $request)
    {
        $asOn = $request->input('as_on_date');
        if (!$asOn) {
            return response()->json(['message' => 'Select as on date'], 422);
        }
Config::set('database.connections.coops.database', session('org_schema'));
        $rows = DB::connection('coops')->select('CALL USP_RPT_EXPIRY(?, ?, ?)', [
            $asOn,
            (int) $request->input('cat_id', 0),
            (int) $request->input('sub_cat_id', 0),
        ]);

        return $this->pagedReport($request, $rows);
    }

    public function reorderReport()
    {
        return view('Admin.reorder-report', [
            'year_start'  => session('year_start'),
            'year_end'    => session('year_end'),
            'org_name'    => session('org_name'),
            'branch_name' => session('branch_name'),
        ])->with('pageTitle', 'Reorder Level Report');
    }

    public function reorderReportSearch(Request $request)
    {
        $asOn = $request->input('as_on_date');
        if (!$asOn) {
            return response()->json(['message' => 'Select as on date'], 422);
        }
Config::set('database.connections.coops.database', session('org_schema'));
        $rows = DB::connection('coops')->select('CALL USP_RPT_REORDER(?)', [$asOn]);

        return $this->pagedReport($request, $rows);
    }

    public function movementReport()
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        $categories = DB::connection('coops')->select('CALL USP_GET_ITEM_CAT()');

        return view('Admin.movement-report', [
            'categories'  => $categories,
            'year_start'  => session('year_start'),
            'year_end'    => session('year_end'),
            'org_name'    => session('org_name'),
            'branch_name' => session('branch_name'),
        ])->with('pageTitle', 'Slow / Fast Moving Items');
    }

    public function movementReportSearch(Request $request)
    {
        $frm = $request->input('frm_date');
        $to = $request->input('to_date');
        if (!$frm || !$to) {
            return response()->json(['message' => 'Select from date and to date'], 422);
        }
        if ($frm > $to) {
            return response()->json(['message' => 'From date cannot be after to date'], 422);
        }
Config::set('database.connections.coops.database', session('org_schema'));
        $rows = DB::connection('coops')->select('CALL USP_RPT_MOVEMENT(?, ?, ?, ?)', [
            $frm,
            $to,
            (int) $request->input('cat_id', 0),
            (int) $request->input('sub_cat_id', 0),
        ]);

        return $this->pagedReport($request, $rows);
    }

    public function agentOfficeReturnReport()
    {
        return view('Admin.agent-office-return-report', [
            'year_start'  => session('year_start'),
            'year_end'    => session('year_end'),
            'org_name'    => session('org_name'),
            'branch_name' => session('branch_name'),
        ])->with('pageTitle', 'Agent Return Report');
    }

    public function agentOfficeReturnReportSearch(Request $request)
    {
        $frm = $request->input('frm_date');
        $to = $request->input('to_date');
        if (!$frm || !$to) {
            return response()->json(['message' => 'Select from date and to date'], 422);
        }
        if ($frm > $to) {
            return response()->json(['message' => 'From date cannot be after to date'], 422);
        }
Config::set('database.connections.coops.database', session('org_schema'));
        $rows = DB::connection('coops')->select('CALL USP_RPT_AGENT_OFFICE_RETURN(?, ?, ?)', [
            $frm,
            $to,
            (int) $request->input('agent_id', 0),
        ]);

        return $this->pagedReport($request, $rows, ['Qty']);
    }

    public function agentCommission()
    {
        $yearStart = session('year_start') ?: date('Y-m-d', strtotime(date('Y') . '-04-01'));
        $yearEnd = session('year_end') ?: date('Y-m-d', strtotime((date('Y') + 1) . '-03-31'));
        $startYear = (int) date('Y', strtotime($yearStart));
        $endYear = (int) date('Y', strtotime($yearEnd));
        $years = range($startYear, max($startYear, $endYear));

        return view('Admin.agent-commission', [
            'agents'      => [],
            'years'       => $years,
            'cur_month'   => (int) date('n'),
            'cur_year'    => (int) date('Y'),
            'year_start'  => $yearStart,
            'year_end'    => $yearEnd,
            'org_name'    => session('org_name'),
            'branch_name' => session('branch_name'),
        ])->with('pageTitle', 'Agent Commission Report');
    }

    public function agentCommissionSearch(Request $request)
    {
        $period = $this->resolveAgentCommissionPeriod($request);
        if ($period instanceof \Illuminate\Http\JsonResponse) {
            return $period;
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        $this->ensureAgentCommissionTables();
        $branchId = (int) session('branch_id');
        $agentId = (int) $request->input('agent_id', 0);

        $rows = DB::connection('coops')->select(
            'CALL USP_RPT_AGENT_COMMISSION_SEARCH(?, ?, ?, ?, ?, ?)',
            [
                $period['month'],
                $period['year'],
                $branchId,
                $period['frm'],
                $period['to'],
                $agentId,
            ]
        );

        return $this->pagedReport($request, $rows);
    }

    public function agentCommissionDetails(Request $request)
    {
        $period = $this->resolveAgentCommissionPeriod($request);
        if ($period instanceof \Illuminate\Http\JsonResponse) {
            return $period;
        }

        $agentId = (int) $request->input('agent_id', 0);
        if ($agentId <= 0) {
            return response()->json(['message' => 'Select an agent'], 422);
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        $this->ensureAgentCommissionTables();
        $branchId = (int) session('branch_id');

        $agent = DB::connection('coops')->selectOne(
            'CALL USP_GET_AGENT_MASTER(?)',
            [$agentId]
        );
        if (!$agent) {
            return response()->json(['message' => 'Agent not found'], 404);
        }

        $generated = DB::connection('coops')->selectOne(
            'CALL USP_GET_AGENT_COMMISSION_HEADER(?, ?, ?, ?)',
            [$agentId, $period['month'], $period['year'], $branchId]
        );

        if ($generated) {
            $rows = DB::connection('coops')->select(
                'CALL USP_GET_AGENT_COMMISSION_DTL(?)',
                [$generated->Comm_Id]
            );

            return response()->json([
                'agent' => $agent,
                'is_generated' => 1,
                'generated_on' => $generated->Created_On,
                'total_sale' => (float) $generated->Total_Sale,
                'total_comm' => (float) $generated->Total_Comm,
                'rows' => $rows,
            ]);
        }

        $rows = $this->agentCommissionCategoryRows($period['frm'], $period['to'], $branchId, $agentId);
        $totalSale = 0.0;
        $totalComm = 0.0;
        foreach ($rows as $row) {
            $totalSale += (float) $row->Total_Sale;
            $totalComm += (float) $row->Total_Comm;
        }

        return response()->json([
            'agent' => $agent,
            'is_generated' => 0,
            'generated_on' => null,
            'total_sale' => round($totalSale, 2),
            'total_comm' => round($totalComm, 2),
            'rows' => $rows,
        ]);
    }

    public function agentCommissionGenerate(Request $request)
    {
        $period = $this->resolveAgentCommissionPeriod($request);
        if ($period instanceof \Illuminate\Http\JsonResponse) {
            return $period;
        }

        Config::set('database.connections.coops.database', session('org_schema'));
        $this->ensureAgentCommissionTables();
        $branchId = (int) session('branch_id');
        $agentId = (int) $request->input('agent_id', 0);
        $userId = (int) session('user_id');

        $agents = DB::connection('coops')->select(
            'CALL USP_GET_AGENT_COMMISSION_SETTLED_AGENTS(?, ?, ?, ?)',
            [$period['frm'], $period['to'], $branchId, $agentId]
        );

        if (!$agents) {
            return response()->json(['message' => 'No settled sales found for selected period'], 422);
        }

        $pendingAgents = [];
        foreach ($agents as $agentRow) {
            $aid = (int) $agentRow->Agent_Id;
            $existing = DB::connection('coops')->selectOne(
                'CALL USP_GET_AGENT_COMMISSION_EXISTS(?, ?, ?, ?)',
                [$aid, $period['month'], $period['year'], $branchId]
            );
            if ($existing) {
                continue;
            }
            $pendingAgents[] = $aid;
        }

        if (!$pendingAgents) {
            return response()->json([
                'message' => 'Commission already generated for this month. Regeneration is not allowed.',
            ], 422);
        }

        if ($agentId > 0 && count($pendingAgents) === 0) {
            return response()->json([
                'message' => 'Commission already generated for this agent for the selected month.',
            ], 422);
        }

        $saved = 0;
        DB::connection('coops')->beginTransaction();
        try {
            foreach ($pendingAgents as $aid) {
                $categoryRows = $this->agentCommissionCategoryRows(
                    $period['frm'],
                    $period['to'],
                    $branchId,
                    $aid
                );
                if (!$categoryRows) {
                    continue;
                }

                $totalSale = 0.0;
                $totalComm = 0.0;
                foreach ($categoryRows as $row) {
                    $totalSale += (float) $row->Total_Sale;
                    $totalComm += (float) $row->Total_Comm;
                }

                $commId = DB::connection('coops')->table('trn_agent_commission')->insertGetId([
                    'Branch_Id'   => $branchId ?: null,
                    'Agent_Id'    => $aid,
                    'Comm_Month'  => $period['month'],
                    'Comm_Year'   => $period['year'],
                    'From_Date'   => $period['frm'],
                    'To_Date'     => $period['to'],
                    'Total_Sale'  => round($totalSale, 2),
                    'Total_Comm'  => round($totalComm, 2),
                    'Status_Cd'   => 1,
                    'Remarks'     => 'Generated from settled sales',
                    'Created_By'  => $userId ?: null,
                    'Created_On'  => now(),
                ], 'Comm_Id');

                foreach ($categoryRows as $row) {
                    DB::connection('coops')->table('trn_agent_commission_dtl')->insert([
                        'Comm_Id'      => $commId,
                        'Cate_Id'      => ((int) $row->Cate_Id) ?: null,
                        'Cate_Name'    => $row->Cate_Name,
                        'Sale_Amt'     => (float) $row->Total_Sale,
                        'Comm_Percent' => (float) $row->Comm_Percent,
                        'Comm_Amt'     => (float) $row->Total_Comm,
                    ]);
                }
                $saved++;
            }

            DB::connection('coops')->commit();
        } catch (\Throwable $e) {
            DB::connection('coops')->rollBack();
            return response()->json(['message' => 'Failed to generate commission: ' . $e->getMessage()], 500);
        }

        if ($saved <= 0) {
            return response()->json([
                'message' => 'Commission already generated for this month. Regeneration is not allowed.',
            ], 422);
        }

        return response()->json([
            'message' => "Commission generated for {$saved} agent(s)",
            'count' => $saved,
        ]);
    }

    private function resolveAgentCommissionPeriod(Request $request)
    {
        $month = (int) $request->input('month', 0);
        $year = (int) $request->input('year', 0);

        if ($month < 1 || $month > 12 || $year < 2000) {
            return response()->json(['message' => 'Select month and year'], 422);
        }

        $frm = sprintf('%04d-%02d-01', $year, $month);
        $to = date('Y-m-t', strtotime($frm));

        $yearStart = session('year_start');
        $yearEnd = session('year_end');
        if ($yearStart && $yearEnd) {
            if ($to < $yearStart || $frm > $yearEnd) {
                return response()->json(['message' => 'Selected month must fall within the accounting year'], 422);
            }
            if ($frm < $yearStart) {
                $frm = $yearStart;
            }
            if ($to > $yearEnd) {
                $to = $yearEnd;
            }
        }

        return [
            'month' => $month,
            'year' => $year,
            'frm' => $frm,
            'to' => $to,
        ];
    }

    private function agentCommissionCategoryRows(string $frm, string $to, int $branchId, int $agentId): array
    {
        return DB::connection('coops')->select(
            'CALL USP_RPT_AGENT_COMMISSION_BY_CATEGORY(?, ?, ?, ?)',
            [$agentId, $frm, $to, $branchId]
        );
    }

    private function ensureAgentCommissionTables(): void
    {
        DB::connection('coops')->statement(
            "CREATE TABLE IF NOT EXISTS `trn_agent_commission` (
                `Comm_Id` bigint(20) NOT NULL AUTO_INCREMENT,
                `Branch_Id` smallint(6) DEFAULT NULL,
                `Agent_Id` smallint(6) NOT NULL,
                `Comm_Month` tinyint(4) NOT NULL,
                `Comm_Year` smallint(6) NOT NULL,
                `From_Date` date DEFAULT NULL,
                `To_Date` date DEFAULT NULL,
                `Total_Sale` decimal(14,2) NOT NULL DEFAULT 0.00,
                `Total_Comm` decimal(14,2) NOT NULL DEFAULT 0.00,
                `Status_Cd` tinyint(4) NOT NULL DEFAULT 1,
                `Remarks` varchar(150) DEFAULT NULL,
                `Created_By` smallint(6) DEFAULT NULL,
                `Created_On` datetime NOT NULL DEFAULT current_timestamp(),
                PRIMARY KEY (`Comm_Id`),
                UNIQUE KEY `uk_agent_comm_period` (`Branch_Id`,`Agent_Id`,`Comm_Month`,`Comm_Year`),
                KEY `idx_agent_comm_period` (`Comm_Year`,`Comm_Month`,`Agent_Id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        DB::connection('coops')->statement(
            "CREATE TABLE IF NOT EXISTS `trn_agent_commission_dtl` (
                `Dtl_Id` bigint(20) NOT NULL AUTO_INCREMENT,
                `Comm_Id` bigint(20) NOT NULL,
                `Cate_Id` int(11) DEFAULT NULL,
                `Cate_Name` varchar(100) DEFAULT NULL,
                `Sale_Amt` decimal(14,2) NOT NULL DEFAULT 0.00,
                `Comm_Percent` decimal(8,2) NOT NULL DEFAULT 0.00,
                `Comm_Amt` decimal(14,2) NOT NULL DEFAULT 0.00,
                PRIMARY KEY (`Dtl_Id`),
                KEY `idx_agent_comm_dtl` (`Comm_Id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }
}
