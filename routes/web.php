<?php

use App\Http\Controllers\Admin\Auth\ProcessLogin;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MasterSetup;
use App\Http\Controllers\Admin\UserManagement;
use App\Http\Controllers\Admin\Purchase;
use App\Http\Controllers\Admin\Sales;
use App\Http\Controllers\Admin\BarcodePrint;
use App\Http\Controllers\Admin\Accounting;
use App\Http\Controllers\Admin\InventoryReports;
use App\Http\Controllers\Admin\AccountsReports;
use App\Http\Controllers\Admin\Wastage;
use App\Http\Controllers\Agent\Auth\AgentLogin;
use App\Http\Controllers\Agent\AgentSale;
use App\Http\Controllers\Agent\AgentRequisition;
use App\Http\Controllers\Agent\AgentCustomer;
use App\Http\Controllers\Agent\AgentCustomerPayment;
use App\Http\Controllers\Agent\AgentReport;
use App\Http\Controllers\Agent\AgentOfficeReturn;

Route::middleware('guest.session')->group(function () {
  Route::get('/', [ProcessLogin::class, 'index_login'])->name('login-index');
  Route::post('/User/Login', [ProcessLogin::class, 'process_login']);
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
  Route::get('/member-share/list', [MasterSetup::class, 'getMemberList'])->name('member-share.list');
  Route::put('/member-share/{id}', [MasterSetup::class, 'updateMember'])->name('member-share.update');

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

  // Address Master
  Route::get('/address-master', [MasterSetup::class, 'indexAddressMaster'])->name('address-master');
  Route::get('/address-master/data/{type}', [MasterSetup::class, 'getAddressData'])->name('address-master.data');
  Route::post('/address-master/save', [MasterSetup::class, 'storeAddressMaster'])->name('address-master.store');
  Route::put('/address-master/{type}/{id}', [MasterSetup::class, 'updateAddressMaster'])->name('address-master.update');

  // GST Codes
  Route::get('/gst-codes', [MasterSetup::class, 'indexGstCodes'])->name('gst-codes');
  Route::post('/gst-codes/save', [MasterSetup::class, 'storeGst'])->name('gst-codes.store');
  Route::put('/gst-codes/{id}', [MasterSetup::class, 'updateGst'])->name('gst-codes.update');

  Route::get('/counter-balance', [MasterSetup::class, 'indexCounterBalance'])->name('counter-balance');
  Route::post('/counter-balance/save', [MasterSetup::class, 'storeCounterBalance'])->name('counter-balance.store');

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

  Route::get('/wastage-damage', [Wastage::class, 'index'])->name('wastage-damage');
  Route::get('/wastage-damage/items', [Wastage::class, 'getItems'])->name('wastage-damage.items');
  Route::get('/wastage-damage/subcats', [Wastage::class, 'getSubCats'])->name('wastage-damage.subcats');
  Route::get('/wastage-damage/item-info', [Wastage::class, 'itemInfo'])->name('wastage-damage.item-info');
  Route::post('/wastage-damage/save', [Wastage::class, 'store'])->name('wastage-damage.store');
  Route::get('/wastage-damage/search', [Wastage::class, 'search'])->name('wastage-damage.search');
  Route::get('/wastage-damage/details/{id}', [Wastage::class, 'details'])->name('wastage-damage.details');
  Route::get('/purchase-return/find-purchase', [Purchase::class, 'searchForReturn'])->name('purchase-return.find-purchase');
  Route::get('/purchase-return/find-purchase-details/{id}', [Purchase::class, 'returnableDetails'])->name('purchase-return.find-purchase-details');



  //purchase return
  Route::get('/purchase-return', [Purchase::class, 'purchaseReturnIndex'])->name('purchase-return');
  Route::post('/purchase-return/save', [Purchase::class, 'storePurchaseReturn'])->name('purchase-return.store');
  Route::get('/purchase-return/items', [Purchase::class, 'purchaseReturnGetItems'])->name('purchase-return.items');
  Route::get('/purchase-return/returnable-qty', [Purchase::class, 'getPurchaseReturnableQty'])->name('purchase-return.returnable-qty');
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
  Route::post('/agent-indent/cancel-item', [Sales::class, 'cancelAgentIndentItem'])->name('agent-indent.cancel-item');
  Route::post('/agent-indent/delete-item', [Sales::class, 'deleteAgentIndentItem'])->name('agent-indent.delete-item');


  //agent return
  Route::get('/agent-return', [Sales::class, 'indexAgentReturn'])->name('agent-return');
  Route::post('/agent-return/get-product-info', [Sales::class, 'AgentReturngetProductInfo'])->name('agent-return.get-product-info');
  Route::get('/agent-return/items', [Sales::class, 'getSaleItems'])->name('agent-return.items');
  Route::get('/agent-return/subcats', [Sales::class, 'getSaleSubCats'])->name('agent-return.subcats');
  Route::get('/agent-return/item-info', [Sales::class, 'getAgentReturnItemByProd'])->name('agent-return.item-info');
  Route::post('/agent-return/pending-indents', [Sales::class, 'getPendingIndents'])->name('agent-return.pending-indents');
  Route::post('/agent-return/save', [Sales::class, 'storeAgentReturn'])->name('agent-return.store');

  Route::get('/agent-settlement', [Sales::class, 'indexAgentSettlement'])->name('agent-settlement');
  Route::get('/agent-settlement/search', [Sales::class, 'agentSettlementSearch'])->name('agent-settlement.search');
  Route::post('/agent-settlement/settle', [Sales::class, 'storeAgentSettlement'])->name('agent-settlement.store');



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
  Route::get('/counter-sale/sale-rates', [Sales::class, 'getSaleRates'])->name('counter-sale.sale-rates');
  Route::get('/counter-sale/search', [Sales::class, 'countersalesearch'])->name('counter-sale.search');
  Route::get('/counter-sale/details/{id}', [Sales::class, 'details'])->name('counter-sale.details');



  
  //sale return
  Route::get('/sale-return', [Sales::class, 'indexSaleReturn'])->name('sale-return');
  Route::post('/sale-return/save', [Sales::class, 'storeSaleReturn'])->name('sale-return.store');
  Route::get('/sale-return/barcode', [Sales::class, 'getItemByBarcodeReturn'])->name('sale-return.barcode');
  Route::get('/sale-return/items', [Sales::class, 'getSaleItems'])->name('sale-return.items');
  Route::get('/sale-return/subcats', [Sales::class, 'getSaleSubCats'])->name('sale-return.subcats');
  Route::get('/sale-return/item-info', [Sales::class, 'getSaleItemByProd'])->name('sale-return.item-info');
  Route::get('/sale-return/sale-rates', [Sales::class, 'getSaleRates'])->name('sale-return.sale-rates');
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
  Route::get('/supplier-payment/ledger', [Accounting::class, 'supplierPaymentLedger'])->name('supplier-payment.ledger');

  Route::get('/customer-collection', [Accounting::class, 'indexCustomerCollection'])->name('customer-collection');
  Route::post('/customer-collection/save', [Accounting::class, 'storeCustomerCollection'])->name('customer-collection.store');
  Route::get('/customer-collection/details/{id}', [Accounting::class, 'detailsPartyVoucher'])->name('customer-collection.details');
  Route::get('/customer-collection/ledger', [Accounting::class, 'customerCollectionLedger'])->name('customer-collection.ledger');

  // Inventory Reports
  Route::get('/stock-summary', [InventoryReports::class, 'stockSummary'])->name('stock-summary');
  Route::get('/stock-summary/search', [InventoryReports::class, 'stockSummarySearch'])->name('stock-summary.search');
  Route::get('/stock-summary/subcats', [InventoryReports::class, 'stockSummarySubCats'])->name('stock-summary.subcats');

  Route::get('/purchase-register', [InventoryReports::class, 'purchaseRegister'])->name('purchase-register');
  Route::get('/purchase-register/search', [InventoryReports::class, 'purchaseRegisterSearch'])->name('purchase-register.search');
  Route::get('/itemwise-purchase', [InventoryReports::class, 'itemwisePurchase'])->name('itemwise-purchase');
  Route::get('/itemwise-purchase/search', [InventoryReports::class, 'itemwisePurchaseSearch'])->name('itemwise-purchase.search');
  Route::get('/sales-register', [InventoryReports::class, 'salesRegister'])->name('sales-register');
  Route::get('/sales-register/search', [InventoryReports::class, 'salesRegisterSearch'])->name('sales-register.search');
  Route::get('/agent-register', [InventoryReports::class, 'agentRegister'])->name('agent-register');
  Route::get('/agent-register/search', [InventoryReports::class, 'agentRegisterSearch'])->name('agent-register.search');
  Route::get('/return-register', [InventoryReports::class, 'returnRegister'])->name('return-register');
  Route::get('/return-register/search', [InventoryReports::class, 'returnRegisterSearch'])->name('return-register.search');
  Route::get('/supplier-register', [InventoryReports::class, 'supplierRegister'])->name('supplier-register');
  Route::get('/supplier-register/search', [InventoryReports::class, 'supplierRegisterSearch'])->name('supplier-register.search');
  Route::get('/customer-register', [InventoryReports::class, 'customerRegister'])->name('customer-register');
  Route::get('/customer-register/search', [InventoryReports::class, 'customerRegisterSearch'])->name('customer-register.search');
  Route::get('/share-register', [InventoryReports::class, 'shareRegister'])->name('share-register');
  Route::get('/share-register/search', [InventoryReports::class, 'shareRegisterSearch'])->name('share-register.search');
  Route::get('/user-scroll', [InventoryReports::class, 'userScroll'])->name('user-scroll');
  Route::get('/user-scroll/search', [InventoryReports::class, 'userScrollSearch'])->name('user-scroll.search');
  Route::get('/gst-register', [InventoryReports::class, 'gstRegister'])->name('gst-register');
  Route::get('/gst-register/search', [InventoryReports::class, 'gstRegisterSearch'])->name('gst-register.search');
  Route::get('/stock-statement', [InventoryReports::class, 'stockStatement'])->name('stock-statement');
  Route::get('/stock-statement/search', [InventoryReports::class, 'stockStatementSearch'])->name('stock-statement.search');
  Route::get('/expiry-report', [InventoryReports::class, 'expiryReport'])->name('expiry-report');
  Route::get('/expiry-report/search', [InventoryReports::class, 'expiryReportSearch'])->name('expiry-report.search');
  Route::get('/reorder-report', [InventoryReports::class, 'reorderReport'])->name('reorder-report');
  Route::get('/reorder-report/search', [InventoryReports::class, 'reorderReportSearch'])->name('reorder-report.search');
  Route::get('/movement-report', [InventoryReports::class, 'movementReport'])->name('movement-report');
  Route::get('/movement-report/search', [InventoryReports::class, 'movementReportSearch'])->name('movement-report.search');

  // Accounts Reports
  Route::get('/account-user-scroll', [AccountsReports::class, 'userScroll'])->name('account-user-scroll');
  Route::get('/account-user-scroll/search', [AccountsReports::class, 'userScrollSearch'])->name('account-user-scroll.search');
  Route::get('/cash-book', [AccountsReports::class, 'cashBook'])->name('cash-book');
  Route::get('/cash-book/search', [AccountsReports::class, 'cashBookSearch'])->name('cash-book.search');
  Route::get('/journal-book', [AccountsReports::class, 'journalBook'])->name('journal-book');
  Route::get('/journal-book/search', [AccountsReports::class, 'journalBookSearch'])->name('journal-book.search');
  Route::get('/cash-account', [AccountsReports::class, 'cashAccount'])->name('cash-account');
  Route::get('/cash-account/search', [AccountsReports::class, 'cashAccountSearch'])->name('cash-account.search');
  Route::get('/ledger-book', [AccountsReports::class, 'ledgerBook'])->name('ledger-book');
  Route::get('/ledger-book/search', [AccountsReports::class, 'ledgerBookSearch'])->name('ledger-book.search');
  Route::get('/receipt-payment', [AccountsReports::class, 'receiptPayment'])->name('receipt-payment');
  Route::get('/receipt-payment/search', [AccountsReports::class, 'receiptPaymentSearch'])->name('receipt-payment.search');
  Route::get('/trial-balance', [AccountsReports::class, 'trialBalance'])->name('trial-balance');
  Route::get('/trial-balance/search', [AccountsReports::class, 'trialBalanceSearch'])->name('trial-balance.search');
  Route::get('/trading-pl', [AccountsReports::class, 'tradingPl'])->name('trading-pl');
  Route::get('/trading-pl/search', [AccountsReports::class, 'tradingPlSearch'])->name('trading-pl.search');
  Route::get('/pl-appropriation', [AccountsReports::class, 'plAppropriation'])->name('pl-appropriation');
  Route::get('/pl-appropriation/search', [AccountsReports::class, 'plAppropriationSearch'])->name('pl-appropriation.search');
  Route::get('/balance-sheet', [AccountsReports::class, 'balanceSheet'])->name('balance-sheet');
  Route::get('/balance-sheet/search', [AccountsReports::class, 'balanceSheetSearch'])->name('balance-sheet.search');
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
    Route::get('/sale/sale-rates', [AgentSale::class, 'getSaleRates'])->name('agent.sale.sale-rates');
    Route::post('/sale/save', [AgentSale::class, 'store'])->name('agent.sale.store');

    //customer payment / collection
    Route::get('/customer-payment', [AgentCustomerPayment::class, 'index'])->name('agent.customer.payment');
    Route::post('/customer-payment/save', [AgentCustomerPayment::class, 'store'])->name('agent.customer.payment.store');
    Route::get('/customer-payment/details/{id}', [AgentCustomerPayment::class, 'details'])->name('agent.customer.payment.details');
    Route::get('/customer-payment/due', [AgentCustomerPayment::class, 'customerDue'])->name('agent.customer.payment.due');

    //customer return
    Route::get('/customer-return', [AgentCustomer::class, 'index'])->name('agent.customer');
    Route::get('/customer-return/barcode', [AgentCustomer::class, 'getItemByBarcode'])->name('agent.customer.barcode');
    Route::get('/customer-return/items', [AgentCustomer::class, 'getItems'])->name('agent.customer.items');
    Route::get('/customer-return/subcats', [AgentCustomer::class, 'getSubCats'])->name('agent.customer.subcats');
    Route::get('/customer-return/item-info', [AgentCustomer::class, 'getItemByProd'])->name('agent.customer.item-info');
    Route::get('/customer-return/sale-rates', [AgentCustomer::class, 'getSaleRates'])->name('agent.customer.sale-rates');
    Route::get('/customer-return/returnable-qty', [AgentCustomer::class, 'getReturnableQty'])->name('agent.customer.returnable-qty');
    Route::post('/customer-return/save', [AgentCustomer::class, 'store'])->name('agent.customer.store');

    //office return
    Route::get('/office-return', [AgentOfficeReturn::class, 'index'])->name('agent.office.return');
    Route::get('/office-return/barcode', [AgentOfficeReturn::class, 'getItemByBarcode'])->name('agent.office.return.barcode');
    Route::get('/office-return/items', [AgentOfficeReturn::class, 'getItems'])->name('agent.office.return.items');
    Route::get('/office-return/subcats', [AgentOfficeReturn::class, 'getSubCats'])->name('agent.office.return.subcats');
    Route::get('/office-return/item-info', [AgentOfficeReturn::class, 'getItemByProd'])->name('agent.office.return.item-info');
    Route::post('/office-return/save', [AgentOfficeReturn::class, 'store'])->name('agent.office.return.store');

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
    Route::get('/report/customer-due', [AgentReport::class, 'customerDue'])->name('agent.report.customer.due');
    Route::get('/report/customer-due/search', [AgentReport::class, 'customerDueSearch'])->name('agent.report.customer.due.search');
    Route::get('/report/customer-due/history/{id}', [AgentReport::class, 'customerDueHistory'])->name('agent.report.customer.due.history');

    Route::get('/settlement', [AgentReport::class, 'settlement'])->name('agent.settlement');
    Route::get('/settlement/search', [AgentReport::class, 'settlementSearch'])->name('agent.settlement.search');
    Route::post('/settlement/generate-token', [AgentReport::class, 'generateSettlementToken'])->name('agent.settlement.generate-token');
    Route::post('/settlement/cancel-token', [AgentReport::class, 'cancelSettlementToken'])->name('agent.settlement.cancel-token');

    //agent error
    Route::fallback(function () {
      return response()->view('errors.agent-404', [], 404);
    });
  });
});
