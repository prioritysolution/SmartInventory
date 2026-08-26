<?php

use App\Http\Controllers\Admin\Auth\ProcessLogin;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MasterSetup;
use App\Http\Controllers\Admin\UserManagement;
use App\Http\controllers\Admin\Purchase;
use App\Http\controllers\Admin\Sales;
use App\Http\Controllers\Admin\BarcodePrint;
use App\Http\Controllers\Admin\Accounting;
use App\Http\Controllers\Agent\Auth\AgentLogin;
use App\Http\Controllers\Agent\AgentSale;
use App\Http\Controllers\Agent\AgentRequisition;
use App\Http\Controllers\Agent\AgentCustomer;
use App\Http\Controllers\Agent\AgentReport;

Route::middleware('guest.session')->group(function () {
  Route::get('/', [ProcessLogin::class, 'index_login'])->name('login-index');
  Route::post('/User/Login', [ProcessLogin::class, 'process_login']);
  Route::get('/ForgotPassword', [ProcessLogin::class, 'index_forgot_pass'])->name('forgot-user-pass');
});

Route::middleware('check.session')->group(function () {
  Route::get('/Dashboard', [ProcessLogin::class, 'index_dashboard'])->name('user-dashboard');
  Route::get('/Logout', [ProcessLogin::class, 'logout'])->name('logout');
  // Chart of Accounts
  Route::get('/chart-of-accounts', [MasterSetup::class, 'indexChartOfAccounts'])->name('chart-of-accounts');
  Route::get('/chart-of-accounts/data/{type}', [MasterSetup::class, 'getChartData'])->name('chart-of-accounts.data');
  Route::post('/chart-of-accounts/gst/save', [MasterSetup::class, 'storeGst'])->name('chart-of-accounts.gst.store');
  Route::put('/chart-of-accounts/gst/{id}', [MasterSetup::class, 'updateGst'])->name('chart-of-accounts.gst.update');

  //product master
  Route::get('/product-master', [MasterSetup::class, 'index'])->name('product-master');
  Route::post('/product-master/save', [MasterSetup::class, 'store'])->name('product-master.store');
  Route::get('/product-master/subcategories/{categoryId}', [MasterSetup::class, 'getSubCategories'])->name('product-master.subcategories');
  Route::get('/product-master/search', [MasterSetup::class, 'searchItem'])->name('product-master.search');
  Route::get('/product-master/details/{id}', [MasterSetup::class, 'getItemDetails'])->name('product-master.details');

  //agent profile
  Route::get('/agent-profile', [MasterSetup::class, 'indexAgent'])->name('agent-profile');
    Route::get('/agent-profile/list', [MasterSetup::class, 'indexAgent'])->name('agent-profile.list');
  Route::post('/agent-profile/save', [MasterSetup::class, 'storeAgent'])->name('agent-profile.store');
  Route::put('/agent-profile/{id}', [MasterSetup::class, 'updateAgent'])->name('agent-profile.update');
  //Route::delete('/agent-profile/{id}', [MasterSetup::class, 'deleteAgent'])->name('agent-profile.delete');

  //member & share
  Route::get('/member-share', [MasterSetup::class, 'indexMember'])->name('member-share');
  Route::post('/member-share/save', [MasterSetup::class, 'storeMember'])->name('member-share.store');

  //supplier master
  Route::get('/supplier-master', [MasterSetup::class, 'indexSupplier'])->name('supplier-master');
  Route::post('/supplier-master/save', [MasterSetup::class, 'storeSupplier'])->name('supplier-master.store');
  Route::put('/supplier-master/{id}', [MasterSetup::class, 'updateSupplier'])->name('supplier-master.update');

  //customer master
  Route::get('/customer-master', [MasterSetup::class, 'indexCustomer'])->name('customer-master');
  Route::post('/customer-master/save', [MasterSetup::class, 'storeCustomer'])->name('customer-master.store');
  Route::put('/customer-master/{id}', [MasterSetup::class, 'updateCustomer'])->name('customer-master.update');

  //user roles
  Route::get('/user-roles', [MasterSetup::class, 'indexUserGroup'])->name('user-roles');
  Route::post('/user-roles/save', [MasterSetup::class, 'storeUserGroup'])->name('user-roles.store');
  Route::get('/user-roles/permissions/{id}', [MasterSetup::class, 'getUserPermissions'])->name('user-roles.permissions');

  // Product Category
  Route::get('/prod-category', [MasterSetup::class, 'indexProdCategory'])->name('prod-category');
  Route::post('/prod-category/save', [MasterSetup::class, 'storeProdCategory'])->name('prod-category.store');

  // Product Sub Category
  Route::get('/prod-subcategory', [MasterSetup::class, 'indexProdSubCategory'])->name('prod-subcategory');
  Route::post('/prod-subcategory/save', [MasterSetup::class, 'storeProdSubCategory'])->name('prod-subcategory.store');

  // Unit Master
  Route::get('/unit-master', [MasterSetup::class, 'indexUnit'])->name('unit-master');
  Route::post('/unit-master/save', [MasterSetup::class, 'storeUnit'])->name('unit-master.store');

  // Accounting Year
  Route::get('/accounting-year', [MasterSetup::class, 'indexAccountingYear'])->name('accounting-year');
  Route::post('/accounting-year/save', [MasterSetup::class, 'storeAccountingYear'])->name('accounting-year.store');

  // GST Codes
  Route::get('/gst-codes', [MasterSetup::class, 'indexGstCodes'])->name('gst-codes');
  Route::post('/gst-codes/save', [MasterSetup::class, 'storeGst'])->name('gst-codes.store');
  Route::put('/gst-codes/{id}', [MasterSetup::class, 'updateGst'])->name('gst-codes.update');

  //test
  Route::get('/organization-profiless', [MasterSetup::class, 'organizationprofiless'])->name('organization-profile'); ///for testingggggggggggggggggggggggggggggggggggggggggggggggggggg


  //user creation
  Route::get('/user-creation', [UserManagement::class, 'index'])->name('user-creation');
  Route::post('/user-creation/save', [UserManagement::class, 'store'])->name('user-creation.store');
  Route::put('/user-creation/{id}', [UserManagement::class, 'update'])->name('user-creation.update');


// Good Received Entry
Route::get('/good-received', [Purchase::class, 'index'])->name('good-received-entry');
Route::get('/good-received/items', [Purchase::class, 'getItems'])->name('good-received.items');
Route::get('/good-received/search', [Purchase::class, 'search'])->name('good-received.search');
Route::get('/good-received/details/{id}', [Purchase::class, 'details'])->name('good-received.details');
Route::post('/good-received/save', [Purchase::class, 'store'])->name('good-received.store');
Route::get('/good-received/subcats', [Purchase::class, 'getSubCats'])->name('good-received.subcats');
Route::get('/purchase-return/find-purchase', [Purchase::class, 'searchForReturn'])->name('purchase-return.find-purchase');
Route::get('/purchase-return/find-purchase-details/{id}', [Purchase::class, 'returnableDetails'])->name('purchase-return.find-purchase-details');



  //purchase return
  Route::get('/purchase-return', [Purchase::class, 'purchaseReturnIndex'])->name('purchase-return');
  Route::post('/purchase-return/save', [Purchase::class, 'storePurchaseReturn'])->name('purchase-return.store');
  Route::get('/purchase-return/items', [Purchase::class, 'purchaseReturnGetItems'])->name('purchase-return.items');
  Route::get('/purchase-return/subcats', [Purchase::class, 'purchaseReturnGetSubCats'])->name('purchase-return.subcats');
  Route::get('purchase-return/search', [Purchase::class, 'searchPurchaseReturn'])->name('purchase-return.search');
  Route::get('purchase-return/details/{id}', [Purchase::class, 'purchaseReturnDetails'])->name('purchase-return.details');

  // Agent Indent
  Route::get('/agent-indent', [Sales::class, 'indexAgentIndent'])->name('agent-indent');
  Route::post('/agent-indent/get-product-info', [Sales::class, 'getProductInfo'])->name('agent-indent.get-product-info');
  Route::get('/agent-indent/items', [Sales::class, 'getSaleItems'])->name('agent-indent.items');
  Route::get('/agent-indent/subcats', [Sales::class, 'getSaleSubCats'])->name('agent-indent.subcats');
  Route::get('/agent-indent/item-info', [Sales::class, 'getSaleItemByProd'])->name('agent-indent.item-info');
  Route::post('/agent-indent/save', [Sales::class, 'storeAgentIndent'])->name('agent-indent.store');
  Route::post('/agent-indent/pending-indents', [Sales::class, 'getPendingIndents'])->name('agent-indent.pending-indents');


  //agent return
  Route::get('/agent-return', [Sales::class, 'indexAgentReturn'])->name('agent-return');
  Route::post('/agent-return/get-product-info', [Sales::class, 'AgentReturngetProductInfo'])->name('agent-return.get-product-info');
  Route::get('/agent-return/items', [Sales::class, 'getSaleItems'])->name('agent-return.items');
  Route::get('/agent-return/subcats', [Sales::class, 'getSaleSubCats'])->name('agent-return.subcats');
  Route::get('/agent-return/item-info', [Sales::class, 'getSaleItemByProd'])->name('agent-return.item-info');
  Route::post('/agent-return/save', [Sales::class, 'storeAgentReturn'])->name('agent-return.store');



  //barcode level
  Route::get('/barcode-label', [BarcodePrint::class, 'indexBarcode'])->name('barcode-label');
  Route::get('/barcode-label/pending/{mode}', [BarcodePrint::class, 'getPendingBarcodeList'])->name('barcode-label.pending');
  Route::post('/barcode-label/generate', [BarcodePrint::class, 'generateBarcode'])->name('barcode-label.generate');

  //print barcode
  Route::get('/print-barcode', [BarcodePrint::class, 'index'])->name('print-barcode');
  Route::get('/print-barcode/list/{mode}', [BarcodePrint::class, 'getList'])->name('print-barcode.list');

  // Counter Sale
  Route::get('/counter-sale', [Sales::class, 'indexCounterSale'])->name('counter-sale');
  Route::post('/counter-sale/save', [Sales::class, 'storeCounterSale'])->name('counter-sale.store');
  Route::get('/counter-sale/barcode', [Sales::class, 'getItemByBarcode'])->name('counter-sale.barcode');
  Route::get('/counter-sale/items', [Sales::class, 'getSaleItems'])->name('counter-sale.items');
  Route::get('/counter-sale/subcats', [Sales::class, 'getSaleSubCats'])->name('counter-sale.subcats');
  Route::get('/counter-sale/item-info', [Sales::class, 'getSaleItemByProd'])->name('counter-sale.item-info');
  Route::get('/counter-sale/search', [Sales::class, 'countersalesearch'])->name('counter-sale.search');
  Route::get('/counter-sale/details/{id}', [Sales::class, 'details'])->name('counter-sale.details');



  
  //sale return
  Route::get('/sale-return', [Sales::class, 'indexSaleReturn'])->name('sale-return');
  Route::post('/sale-return/save', [Sales::class, 'storeSaleReturn'])->name('sale-return.store');
  Route::get('/sale-return/barcode', [Sales::class, 'getItemByBarcodeReturn'])->name('sale-return.barcode');
  Route::get('/sale-return/items', [Sales::class, 'getSaleItems'])->name('sale-return.items');
  Route::get('/sale-return/subcats', [Sales::class, 'getSaleSubCats'])->name('sale-return.subcats');
  Route::get('/sale-return/item-info', [Sales::class, 'getSaleItemByProd'])->name('sale-return.item-info');
  Route::get('/sale-return/search', [Sales::class, 'saleReturnsearch'])->name('sale-return.search');
  Route::get('/sale-return/details/{id}', [Sales::class, 'saleReturnDetails'])->name('sale-return.details');
  Route::get('/sale-return/find-sale', [Sales::class, 'searchForReturn'])->name('sale-return.find-sale');
  Route::get('/sale-return/find-sale-details/{id}', [Sales::class, 'returnableDetails'])->name('sale-return.find-sale-details');

  // Accounting Entry
  Route::get('/general-voucher', [Accounting::class, 'indexGeneralVoucher'])->name('general-voucher');
  Route::post('/general-voucher/save', [Accounting::class, 'storeGeneralVoucher'])->name('general-voucher.store');
  Route::get('/general-voucher/list', [Accounting::class, 'listGeneralVoucher'])->name('general-voucher.list');
  Route::get('/general-voucher/details/{id}', [Accounting::class, 'detailsGeneralVoucher'])->name('general-voucher.details');

  Route::get('/supplier-payment', [Accounting::class, 'indexSupplierPayment'])->name('supplier-payment');
  Route::post('/supplier-payment/save', [Accounting::class, 'storeSupplierPayment'])->name('supplier-payment.store');
  Route::get('/supplier-payment/details/{id}', [Accounting::class, 'detailsPartyVoucher'])->name('supplier-payment.details');

  Route::get('/customer-collection', [Accounting::class, 'indexCustomerCollection'])->name('customer-collection');
  Route::post('/customer-collection/save', [Accounting::class, 'storeCustomerCollection'])->name('customer-collection.store');
  Route::get('/customer-collection/details/{id}', [Accounting::class, 'detailsPartyVoucher'])->name('customer-collection.details');
});




// Agent Routes
Route::prefix('agent')->group(function () {
  Route::get('/', fn() => redirect()->route('agent.login'));
  Route::middleware('agent.guest')->group(function () {
    Route::get('/login', [AgentLogin::class, 'Agent_login'])->name('agent.login');
    Route::post('/login', [AgentLogin::class, 'login']);
  });
  Route::middleware('agent.auth')->group(function () {
    Route::get('/dashboard', [AgentLogin::class, 'Agent_dashboard'])->name('agent.dashboard');
    Route::get('/logout', [AgentLogin::class, 'logout'])->name('agent.logout');

    //agent requisation
    Route::get('/requisition', [AgentRequisition::class, 'index'])->name('agent.requisition');
    Route::get('/requisition/search-item', [AgentRequisition::class, 'searchItem'])->name('agent.requisition.search-item');
    Route::get('/requisition/items', [AgentRequisition::class, 'getItems'])->name('agent.requisition.items');
    Route::get('/requisition/subcats', [AgentRequisition::class, 'getSubCats'])->name('agent.requisition.subcats');
    Route::get('/requisition/search', [AgentRequisition::class, 'search'])->name('agent.requisition.search');
    Route::post('/requisition/save', [AgentRequisition::class, 'store'])->name('agent.requisition.store');

    //agent sale
    Route::get('/sale', [AgentSale::class, 'index'])->name('agent.sale');
    Route::get('/sale/barcode', [AgentSale::class, 'getItemByBarcode'])->name('agent.sale.barcode');
    Route::get('/sale/items', [AgentSale::class, 'getItems'])->name('agent.sale.items');
    Route::get('/sale/subcats', [AgentSale::class, 'getSubCats'])->name('agent.sale.subcats');
    Route::get('/sale/item-info', [AgentSale::class, 'getItemByProd'])->name('agent.sale.item-info');
    Route::post('/sale/save', [AgentSale::class, 'store'])->name('agent.sale.store');

    //customer return
    Route::get('/customer-return', [AgentCustomer::class, 'index'])->name('agent.customer');
    Route::get('/customer-return/barcode', [AgentCustomer::class, 'getItemByBarcode'])->name('agent.customer.barcode');
    Route::get('/customer-return/items', [AgentCustomer::class, 'getItems'])->name('agent.customer.items');
    Route::get('/customer-return/subcats', [AgentCustomer::class, 'getSubCats'])->name('agent.customer.subcats');
    Route::get('/customer-return/item-info', [AgentCustomer::class, 'getItemByProd'])->name('agent.customer.item-info');
    Route::post('/customer-return/save', [AgentCustomer::class, 'store'])->name('agent.customer.store');

    //register reports
    Route::get('/report/indent', [AgentReport::class, 'indent'])->name('agent.report.indent');
    Route::get('/report/indent/search', [AgentReport::class, 'indentSearch'])->name('agent.report.indent.search');
    Route::get('/report/issue', [AgentReport::class, 'issue'])->name('agent.report.issue');
    Route::get('/report/issue/search', [AgentReport::class, 'issueSearch'])->name('agent.report.issue.search');
    Route::get('/report/sale', [AgentReport::class, 'sale'])->name('agent.report.sale');
    Route::get('/report/sale/search', [AgentReport::class, 'saleSearch'])->name('agent.report.sale.search');
    Route::get('/report/return', [AgentReport::class, 'officeReturn'])->name('agent.report.return');
    Route::get('/report/return/search', [AgentReport::class, 'officeReturnSearch'])->name('agent.report.return.search');
    Route::get('/report/stock', [AgentReport::class, 'stock'])->name('agent.report.stock');
    Route::get('/report/stock/search', [AgentReport::class, 'stockSearch'])->name('agent.report.stock.search');

    //agent error
    Route::fallback(function () {
      return response()->view('errors.agent-404', [], 404);
    });
  });
});
