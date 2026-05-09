<?php

use App\Http\Controllers\Admin\Auth\ProcessLogin;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MasterSetup;
use App\Http\Controllers\Admin\UserManagement;
use App\Http\controllers\Admin\Purchase;
use App\Http\controllers\Admin\Sales;
use App\Http\Controllers\Admin\BarcodePrint;
use App\Http\Controllers\Agent\Auth\AgentLogin;
use App\Http\Controllers\Agent\AgentSale;
use App\Http\Controllers\Agent\AgentRequisition;
use App\Http\Controllers\Agent\AgentCustomer;

Route::middleware('guest.session')->group(function () {
  Route::get('/', [ProcessLogin::class, 'index_login'])->name('login-index');
  Route::post('/User/Login', [ProcessLogin::class, 'process_login']);
  Route::get('/ForgotPassword', [ProcessLogin::class, 'index_forgot_pass'])->name('forgot-user-pass');
});

Route::middleware('check.session')->group(function () {
  Route::get('/Dashboard', [ProcessLogin::class, 'index_dashboard'])->name('user-dashboard');
  Route::get('/Logout', [ProcessLogin::class, 'logout'])->name('logout');

  //product master
  // Route::get('/product-master', [MasterSetup::class, 'index'])->name('product-master');
  // Route::post('/product-master/save', [MasterSetup::class, 'store'])->name('product-master.store');
  // Route::get('/product-master/subcategories/{categoryId}', [MasterSetup::class, 'getSubCategories'])->name('product-master.subcategories');
  // Route::get('/product-master/search', [MasterSetup::class, 'searchItem'])->name('product-master.search');
  // Route::get('/product-master/details/{id}', [MasterSetup::class, 'getItemDetails'])->name('product-master.details');

  //agent profile
  Route::get('/agent-profile', [MasterSetup::class, 'indexAgent'])->name('agent-profile');
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


  //user creation
  Route::get('/user-creation', [UserManagement::class, 'index'])->name('user-creation');
  Route::post('/user-creation/save', [UserManagement::class, 'store'])->name('user-creation.store');
  Route::put('/user-creation/{id}', [UserManagement::class, 'update'])->name('user-creation.update');


  // Good Received Entry
  Route::get('/good-received', [Purchase::class, 'index'])->name('good-received-entry');
  Route::post('/good-received/save', [Purchase::class, 'store'])->name('good-received.store');
  Route::get('/good-received/subcategories/{catId}', [Purchase::class, 'getSubCategories'])->name('good-received.subcategories');
  Route::get('/good-received/items/{catId}/{subCatId}', [Purchase::class, 'getItems'])->name('good-received.items');
  Route::get('good-received/search', [Purchase::class, 'search'])->name('good-received.search');
  Route::get('good-received/details/{id}', [Purchase::class, 'details'])->name('good-received.details');

  //purchase return
  Route::get('/purchase-return', [Purchase::class, 'purchaseReturnIndex'])->name('purchase-return');
  Route::post('/purchase-return/save', [Purchase::class, 'storePurchaseReturn'])->name('purchase-return.store');
  Route::get('/purchase-return/subcategories/{catId}', [Purchase::class, 'purchasegetSubCategories'])->name('purchase-return.subcategories');
  Route::get('/purchase-return/items/{catId}/{subCatId}', [Purchase::class, 'purchasegetItems'])->name('purchase-return.items');
  Route::get('purchase-return/search', [Purchase::class, 'searchPurchaseReturn'])->name('purchase-return.search');
  Route::get('purchase-return/details/{id}', [Purchase::class, 'purchaseReturnDetails'])->name('purchase-return.details');



  // Agent Indent
  Route::get('/agent-indent', [Sales::class, 'indexAgentIndent'])->name('agent-indent');
  Route::post('/agent-indent/get-product-info', [Sales::class, 'getProductInfo'])->name('agent-indent.get-product-info');
  Route::post('/agent-indent/save', [Sales::class, 'storeAgentIndent'])->name('agent-indent.store');
  Route::post('/agent-indent/pending-indents', [Sales::class, 'getPendingIndents'])->name('agent-indent.pending-indents');


  //agent return
  Route::get('/agent-return', [Sales::class, 'indexAgentReturn'])->name('agent-return');
  Route::post('/agent-return/get-product-info', [Sales::class, 'AgentReturngetProductInfo'])->name('agent-return.get-product-info');
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
  Route::get('/counter-sale/search', [Sales::class, 'countersalesearch'])->name('counter-sale.search');
  Route::get('/counter-sale/details/{id}', [Sales::class, 'details'])->name('counter-sale.details');




  //sale return
  Route::get('/sale-return', [Sales::class, 'indexSaleReturn'])->name('sale-return');
  Route::post('/sale-return/save', [Sales::class, 'storeSaleReturn'])->name('sale-return.store');
  Route::get('/sale-return/barcode', [Sales::class, 'getItemByBarcodeReturn'])->name('sale-return.barcode');
  Route::get('/sale-return/search', [Sales::class, 'saleReturnsearch'])->name('sale-return.search');
  Route::get('/sale-return/details/{id}', [Sales::class, 'saleReturnDetails'])->name('sale-return.details');
});


// Agent Routes
Route::prefix('agent')->group(function () {
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
    Route::get('/requisition/search', [AgentRequisition::class, 'search'])->name('agent.requisition.search');
    Route::post('/requisition/save', [AgentRequisition::class, 'store'])->name('agent.requisition.store');

    //agent sale
    Route::get('/sale', [AgentSale::class, 'index'])->name('agent.sale');
    Route::get('/sale/barcode', [AgentSale::class, 'getItemByBarcode'])->name('agent.sale.barcode');
    Route::post('/sale/save', [AgentSale::class, 'store'])->name('agent.sale.store');

    //customer return
    Route::get('/customer-return', [AgentCustomer::class, 'index'])->name('agent.customer');
    Route::get('/customer-return/barcode', [AgentCustomer::class, 'getItemByBarcode'])->name('agent.customer.barcode');
    Route::post('/customer-return/save', [AgentCustomer::class, 'store'])->name('agent.customer.store');

    //agent error
    Route::fallback(function () {
      return response()->view('errors.agent-404', [], 404);
    });
  });
});
