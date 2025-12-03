<?php
use App\DataTables\BillDataTable;
use App\DataTables\ProductDataTable;
use App\DataTables\CategoryDataTable;
use App\DataTables\ShoppingDataTable;
use App\DataTables\PaymentMethodDataTable;
use App\DataTables\CurrencyDataTable;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShoppingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\RepaymentController;
use App\Http\Controllers\ClosureController;
use App\Http\Controllers\CompanyInfoController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\CreditController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\ReportPDFController;
use App\Http\Controllers\InventoryAdjustmentController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\StoreController;
use App\Http\Middleware\LocaleMiddleware;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\OrderController;
use App\DataTables\OrderDataTable;
use App\Http\Controllers\InventoryTransferController;

Route::post('/lang/change', [LocaleController::class, 'change'])->name('changeLang');
Route::middleware('guest')->group(function () {
    Route::get('/', [LoginController::class, 'index'])->name('login');
    Route::post('logeo',[LoginController::class, 'login'])->name('logeo');
});

// Rutas públicas para catálogo (sin login) - ahora usan el nombre/slug de la sucursal en la URL
Route::get('/sucursal/{sucursal}/{price?}', [CatalogController::class, 'index'])->name('catalog.index');
// Descargar catálogo filtrado en PDF (público)
Route::get('/sucursal/{sucursal}/catalog-pdf/{price?}', [CatalogController::class, 'pdf'])->name('catalog.pdf');
Route::post('/sucursal/{sucursal}/order', [CatalogController::class, 'storeOrder'])->name('catalog.order');
// Lookup de cliente por CI para autocompletar en el catálogo público
Route::post('/sucursal/{sucursal}/client-lookup', [CatalogController::class, 'clientLookup'])->name('catalog.clientLookup');
// Desbloquear acceso a precios restringidos mediante contraseña de la compañía
Route::post('/sucursal/{sucursal}/unlock-price', [CatalogController::class, 'unlockPrice'])->name('catalog.unlock');

Route::middleware('auth')->group(function () {
    Route::get('indexStore', [StoreController::class, 'indexStore'])->name('indexStore');
    Route::post('indexStoreAjax', [StoreController::class, 'indexStoreAjax'])->name('indexStoreAjax');
    Route::post('mostrarProduct', [StoreController::class, 'mostrarProduct'])->name('mostrarProduct');

    Route::get('/dashboard', 'App\Http\Controllers\DashboardController@index')->name('dashboard');
    Route::get('ajax-crud-datatableProductDashboard', [DashboardController::class, 'ajaxProductDashboard']);
    Route::get('logout', 'App\Http\Controllers\LoginController@logout')->name('logout');

    Route::get('indexEmployee', [EmployeeController::class, 'indexEmployee'])->name('indexEmployee');
    Route::post('storeEmployee', [EmployeeController::class, 'storeEmployee']);
    Route::post('editEmployee', [EmployeeController::class, 'editEmployee']);
    Route::post('deleteEmployee', [EmployeeController::class, 'deleteEmployee']);
    Route::get('statusEmployee', [EmployeeController::class, 'statusEmployee']);

    Route::get('indexClient', [ClientController::class, 'indexClient'])->name('indexClient');
    Route::post('storeClient', [ClientController::class, 'storeClient']);
    Route::post('editClient', [ClientController::class, 'editClient']);
    Route::get('statusClient', [ClientController::class, 'statusClient']);

    Route::get('indexProduct', [ProductController::class, 'indexProduct'])->name('indexProduct');
    Route::get('ajax-crud-datatableProduct', [ProductDataTable::class, 'ajax'])->name('ajax-crud-datatableProduct');
    // Bulk update utility for selected utility field
    Route::post('products/bulk-update-utility', [ProductController::class, 'bulkUpdateUtility'])->name('products.bulkUpdateUtility');
    Route::post('storeProduct', [ProductController::class, 'storeProduct']);
    Route::post('editProduct', [ProductController::class, 'editProduct']);
    Route::post('deleteProduct', [ProductController::class, 'destroyProduct']);
    Route::get('statusProduct', [ProductController::class, 'statusProduct']);
    Route::post('/upload-image', [ProductController::class, 'uploadImage']);
    Route::get('indexLabel',  [ProductController::class, 'indexLabel'])->name('indexLabel');
    Route::get('ajax-crud-datatableLabel', [ProductController::class, 'ajaxLabel']);
    Route::post('storeLabel', [ProductController::class, 'storeLabel']);
    Route::post('storeLabelAll', [ProductController::class, 'storeLabelAll']);
    Route::post('findLabelProduct', [ProductController::class, 'findLabelProduct']);
    Route::post('deleteProductImage', [ProductController::class, 'deleteProductImage'])->name('deleteProductImage');

    Route::get('indexProfile', 'App\Http\Controllers\ProfileController@indexProfile')->name('indexProfile');
    Route::post('updateProfile', [ProfileController::class, 'updateProfile'])->name('updateProfile');
    Route::post('updateProfileFile', [ProfileController::class, 'updateProfileFile'])->name('updateProfileFile');
    Route::post('passwordProfile', [ProfileController::class, 'passwordProfile'])->name('passwordProfile');
    
    Route::get('open-whatsapp-chat/{phone}/{status}', [WhatsAppController::class, 'openWhatsAppChat'])->name('openWhatsAppChat');
    Route::get('open-whatsapp-chat-client/{phone}', [WhatsAppController::class, 'openWhatsAppChatClient'])->name('openWhatsAppChatClient');
    Route::get('open-whatsapp-chat-service/{phone}/{status}', [WhatsAppController::class, 'openWhatsAppChatService'])->name('openWhatsAppChatService');
    Route::get('open-whatsapp-chat-ctedit/{phone}/{id_client}', [WhatsAppController::class, 'openWhatsAppChatCredit'])->name('openWhatsAppChatCredit');

    Route::get('Chart', 'App\Http\Controllers\ChartController@chart')->name('chart');
    Route::get('Chart2', 'App\Http\Controllers\ChartController@chart2')->name('chart2');
    Route::get('/dashboard/monthly-summary', 'App\Http\Controllers\ChartController@monthlySummary')->name('dashboard.monthly_summary');

    Route::get('indexStock/{id}', [StockController::class, 'indexStock'])->name('indexStock');
    Route::post('storeStock', [StockController::class, 'storeStock']);
    Route::get('ajax-crud-datatableStock/{id_product}', 'App\Http\Controllers\StockController@ajaxStock')->name('ajax-crud-datatableStock');
    Route::get('ajax-crud-datatableShopping', 'App\Http\Controllers\StockController@ajaxShopping')->name('ajax-crud-datatableShopping');
    Route::post('verifySerialOld', [StockController::class, 'verifySerialOld'])->name('verifySerialOld');
    Route::post('verifySerialNew', [StockController::class, 'verifySerialNew'])->name('verifySerialNew');
    Route::post('storeSerials', [StockController::class, 'storeSerials'])->name('storeSerials');

    Route::get('indexInventoryAdjustment', [InventoryAdjustmentController::class, 'indexInventoryAdjustment'])->name('indexInventoryAdjustment');
    Route::post('storeStocktaking', [InventoryAdjustmentController::class, 'storeStocktaking'])->name('storeStocktaking');
    Route::get('indexStocktaking', [InventoryAdjustmentController::class, 'indexStocktaking'])->name('indexStocktaking');
    Route::get('ajax-crud-datatableStocktaking', [InventoryAdjustmentController::class,'ajaxStocktaking'])->name('ajax-crud-datatableStocktaking');
    Route::post('stocktakingReset', [InventoryAdjustmentController::class, 'stocktakingReset'])->name('stocktakingReset');

    Route::get('shopping', [ShoppingController::class, 'index'])->name('shopping.index'); 
    Route::get('shopping', function (ShoppingDataTable $dataTable) {
        return $dataTable->render('products.shopping');
    })->name('shopping.index');
    Route::get('shopping/add', [ShoppingController::class, 'indexAddShopping'])->name('indexAddShopping');
    Route::post('shopping/store', [ShoppingController::class, 'storeShopping'])->name('storeShopping');
    Route::post('codeProduct', [ShoppingController::class, 'codeProduct'])->name('codeProduct');
    Route::post('addProductShopping', [ShoppingController::class, 'addProductShopping'])->name('addProductShopping');

    Route::get('indexBilling', [BillingController::class, 'indexBilling'])->name('indexBilling');
    Route::get('ajax-crud-datatableBilling', [BillingController::class, 'ajaxBilling']);
    Route::get('ajax-crud-datatableBillWait', [BillingController::class, 'ajaxBillWait']);
    Route::post('addBill', [BillingController::class, 'addBill']);
    Route::post('storeShopper', [BillingController::class, 'storeShopper'])->name('storeShopper');
    Route::post('deleteBillDetail', [BillingController::class, 'deleteBillDetail']);
    Route::post('mostrarBill', [BillingController::class, 'mostrarBill']);
    Route::post('summaryBill', [BillingController::class, 'summaryBill']);
    Route::post('changeClient', [BillingController::class, 'changeClient']);
    Route::post('changeClientVerify', [BillingController::class, 'changeClientVerify']);
    Route::post('deleteBill', [BillingController::class, 'deleteBill']);
    Route::post('updateQuantity', [BillingController::class, 'updateQuantity']);
    Route::post('changeStatusBill', [BillingController::class, 'changeStatusBill']);
    Route::post('facturar', [BillingController::class, 'facturar']);
    Route::post('paymentBill', [BillingController::class, 'paymentBill']);
    Route::post('deletePaymentBill', [BillingController::class, 'deletePaymentBill']);
    Route::post('storeBill', [BillingController::class, 'storeBill']);
    Route::post('changeMethod', [BillingController::class, 'changeMethod']);
        Route::post('/billing/validate-supervisor', [App\Http\Controllers\BillingController::class, 'validateSupervisor'])->name('billing.validateSupervisor');
    Route::post('updateAmount', [BillingController::class, 'updateAmount']);
    Route::post('storeBudget', [BillingController::class, 'storeBudget']);
    Route::post('storeCredit', [BillingController::class, 'storeCredit']);
    Route::post('updateAmountCredit', [BillingController::class, 'updateAmountCredit']);
    Route::post('budget', [BillingController::class, 'budget'])->name('budget');
    Route::post('changeNoteCredit', [BillingController::class, 'changeNoteCredit']);
    Route::post('noteCredit', [BillingController::class, 'noteCredit']);
    Route::post('changeType', [BillingController::class, 'changeType']);
    Route::post('updateDiscount', [BillingController::class, 'updateDiscount']);
    Route::post('verificaDiscount', [BillingController::class, 'verificaDiscount']);
    Route::post('changeBillIn', [BillingController::class, 'changeBillIn']);
    Route::post('verifyStock', [BillingController::class, 'verifyStock']);
    Route::post('verifyStockQuantity', [BillingController::class, 'verifyStockQuantity']);
    Route::post('authorizeDiscount', [BillingController::class, 'authorizeDiscount']);
    Route::post('changeClientCredit', [BillingController::class, 'changeClientCredit']);
    Route::post('storeBillWait', [BillingController::class, 'storeBillWait']);
    Route::post('billWaitStore', [BillingController::class, 'billWaitStore'])->name('billWaitStore');
    Route::post('/smallbox/store', [BillingController::class, 'storeSmallbox'])->name('smallbox.store');
    Route::post('/getProductPrices', [BillingController::class, 'getProductPrices'])->name('getProductPrices');
    Route::post('mostrarProductBilling', [BillingController::class, 'mostrarProductBilling'])->name('mostrarProductBilling');

    Route::get('indexBill', [BillController::class, 'index'])->name('indexBill');
    Route::get('ajax-crud-datatableBill', [BillDataTable::class, 'ajax'])->name('ajax-crud-datatableBill');
    Route::post('mostrarBillPayment', [BillController::class, 'mostrarBillPayment'])->name('mostrarBillPayment');
    Route::post('modalRepayment', [BillController::class, 'modalRepayment'])->name('modalRepayment');
    Route::post('saveRepayment', [BillController::class, 'saveRepayment'])->name('saveRepayment');
    Route::post('saveRepaymentAll', [BillController::class, 'saveRepaymentAll'])->name('saveRepaymentAll');

    Route::get('indexOrder', [OrderController::class, 'index'])->name('indexOrder');
    Route::post('changeStatusOrder', [OrderController::class, 'changeStatusOrder']);
    Route::post('mostrarOrder', [OrderController::class, 'mostrarOrder']);
    Route::post('updateQuantityOrder', [OrderController::class, 'updateQuantityOrder']);
    Route::post('deleteDetailOrder', [OrderController::class, 'deleteDetailOrder']);
    Route::get('ajax-crud-datatableOrder', [OrderController::class, 'ajax'])->name('ajax-crud-datatableOrder');

    Route::get('indexCredit', [CreditController::class, 'indexCredit'])->name('indexCredit');
    Route::get('ajax-crud-datatableCredit', [CreditController::class, 'ajaxCredit'])->name('ajax-crud-datatableCredit');
    Route::post('paymentBillCredit', [CreditController::class, 'paymentBillCredit']);
    Route::post('credit', [CreditController::class, 'credit']);
    Route::post('storePaymentCredit', [CreditController::class, 'storePaymentCredit']);
    Route::post('mostrarCredit', [CreditController::class, 'mostrarCredit']);

    Route::get('pdf/{id}', [PdfController::class, 'pdf'])->name('pdf');
    Route::get('pdfNoteCredit/{id}', [PdfController::class, 'pdfNoteCredit'])->name('pdfNoteCredit');
    Route::get('pdfClosure/{id}', [PdfController::class, 'pdfClosure'])->name('pdfClosure');
    Route::get('pdflast', [PdfController::class, 'pdfLast'])->name('pdf.last');
    Route::get('pdfBillService/{id}', [PdfController::class, 'pdfBillService'])->name('pdfBillService');
    Route::get('pdfStock/{id_inventory_adjustment}', [PdfController::class, 'pdfStock'])->name('pdfStock');
    Route::get('pdfLabel/{id}/{quantity}', [PdfController::class, 'pdfLabel'])->name('pdfLabel');
    Route::get('pdfLabelAll/{code}', [PdfController::class, 'pdfLabelAll'])->name('pdfLabelAll');
    Route::get('pdfClosureDetail/{id}', [PdfController::class, 'pdfClosureDetail'])->name('pdfClosureDetail');
    Route::get('pdfClosureDetailGlobal/{id}', [PdfController::class, 'pdfClosureDetailGlobal'])->name('pdfClosureDetailGlobal');
    Route::get('pdfProduct', [PdfController::class, 'pdfProduct'])->name('pdfProduct');
    Route::get('pdfShopping/{id}', [PdfController::class, 'pdfShopping'])->name('pdfShopping');
    Route::get('pdfPayment/{id}', [PdfController::class, 'pdfPayment'])->name('pdfPayment');
    Route::get('pdfGuarantee/{id}', [PdfController::class, 'pdfGuarantee'])->name('pdfGuarantee');
    Route::get('pdfCatalog', [PdfController::class, 'pdfCatalog'])->name('pdfCatalog');
    Route::get('pdfCreditPayment/{id}', [PdfController::class, 'pdfCreditPayment'])->name('pdfCreditPayment');
    Route::get('pdfRepaymentDetail/{code}', [PdfController::class, 'pdfRepaymentDetail'])->name('pdfRepaymentDetail');

    Route::get('indexRepayment', [RepaymentController::class, 'indexRepayment'])->name('indexRepayment');


    Route::get('closures', [ClosureController::class, 'indexClosure'])->name('closures.index');
    Route::get('ajax-crud-datatableClosure', [ClosureController::class, 'ajaxClosure'])->name('ajaxClosure');
    Route::post('storeClosure', [ClosureController::class, 'storeClosure'])->name('storeClosure');
    Route::get('individual-closures', [ClosureController::class, 'indexIndividualClosure'])->name('individualClosures.index');
    Route::get('ajax-individual-closures', [ClosureController::class, 'ajaxIndividualClosure'])->name('ajaxIndividualClosures');
    Route::post('store-individual-closure', [ClosureController::class, 'storeIndividualClosure'])->name('storeIndividualClosure');

    Route::get('productIndex', [ReportController::class, 'productIndex'])->name('productIndex');
    Route::get('reporte/product', [ ReportController::class, 'productReport'])->name('reporte.product');
    Route::get('billIndex', [ReportController::class, 'billIndex'])->name('billIndex');
    Route::get('reporte/bill', [ ReportController::class, 'billReport'])->name('reporte.bill');
    Route::get('/reports/service', [ReportController::class, 'serviceIndex'])->name('serviceIndex');
    Route::get('/reporte/service', [ReportController::class, 'serviceReport'])->name('reporte.service');
    Route::get('/reports/payment', [ReportController::class, 'paymentIndex'])->name('paymentIndex');
    Route::get('/reporte/payment', [ReportController::class, 'paymentReport'])->name('reporte.payment');
    Route::get('reports/credit', [ReportController::class, 'creditIndex'])->name('creditIndex');
    Route::get('reporte/credit', [ReportController::class, 'creditReport'])->name('reports.accounts_receivable.chart');
    Route::get('reports/employee', [ReportController::class, 'employeeIndex'])->name('employeeIndex');
    Route::get('reporte/employee', [ReportController::class, 'employeeReport'])->name('reports.sales_performance.chart');

    Route::get('reports/products/pdf', [ReportPDFController::class, 'indexProductPDF'])->name('indexProductPDF');
    Route::get('ajax-crud-datatableProductPDF', [ReportPDFController::class, 'ajaxProductPDF']);
    Route::get('generate-product-pdf', [ReportPDFController::class, 'pdfProduct'])->name('pdfProduct');
    Route::get('reports/bills/pdf', [ReportPDFController::class, 'indexBillPDF'])->name('indexBillPDF');
    Route::get('ajax-crud-datatableBillPDF', [ReportPDFController::class, 'ajaxBillPDF'])->name('reports.bills.ajax');
    Route::get('pdf-bills', [ReportPDFController::class, 'pdfBill'])->name('reports.bills.pdf');
    Route::get('reports/services', [ReportPDFController::class, 'indexServicePDF'])->name('indexServicePDF');
    Route::get('ajax-crud-datatableServicePDF', [ReportPDFController::class, 'ajaxServicePDF'])->name('reports.services.ajax');
    Route::get('pdf-services', [ReportPDFController::class, 'pdfService'])->name('reports.services.pdf');
    Route::get('reports/profits', [ReportPDFController::class, 'indexProfitPDF'])->name('indexProfitPDF');
    Route::get('ajax-crud-datatableProfitPDF', [ReportPDFController::class, 'ajaxProfitPDF'])->name('reports.profits.ajax');
    Route::get('pdf-profits', [ReportPDFController::class, 'pdfProfit'])->name('reports.profits.pdf');
    Route::get('reports/accounts-receivable', [ReportPDFController::class, 'indexCreditPDF'])->name('indexCreditPDF');
    Route::get('ajax-crud-datatableAccountsReceivablePDF', [ReportPDFController::class, 'ajaxCreditPDF'])->name('reports.accounts_receivable.ajax');
    Route::get('pdf-accounts-receivable', [ReportPDFController::class, 'pdfCredit'])->name('reports.accounts_receivable.pdf');
    Route::get('reports/employee/pdf', [ReportPDFController::class, 'indexEmployeePDF'])->name('indexEmployeePDF');
    Route::get('ajax-crud-datatableSalesPerformance', [ReportPDFController::class, 'ajaxEmployeePDF'])->name('reports.sales_performance.ajax');
    Route::get('pdf-sales-performance', [ReportPDFController::class, 'EmployeePdf'])->name('reports.sales_performance.pdf');

    Route::prefix('payment-methods')->name('payment-methods.')->group(function () {
        Route::get('/', [PaymentMethodController::class, 'index'])->name('index');
        Route::post('/', [PaymentMethodController::class, 'store'])->name('store');
        Route::get('{payment_method}/edit', [PaymentMethodController::class, 'edit'])->name('edit');
        Route::put('{payment_method}', [PaymentMethodController::class, 'update'])->name('update');
        Route::post('{payment_method}/toggle-status', [PaymentMethodController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('payment_methods_data', [PaymentMethodDataTable::class, 'ajax'])->name('data');
    });

    Route::prefix('currencies')->name('currencies.')->group(function () {
        Route::get('/', [CurrencyController::class, 'index'])->name('index');
        Route::post('store', [CurrencyController::class, 'storeCurrency'])->name('store');
        Route::get('{currency}/edit', [CurrencyController::class, 'editCurrency'])->name('edit');
        Route::put('{currency}', [CurrencyController::class, 'updateCurrency'])->name('update');
        Route::get('currencies_data', function (CurrencyDataTable $dataTable) {
            return $dataTable->ajax();
        })->name('data');
    });


    Route::get('indexCategory', [CategoryController::class, 'indexCategory'])->name('indexCategory');
    Route::post('storeCategory', [CategoryController::class, 'storeCategory']);
    Route::post('editCategory', [CategoryController::class, 'editCategory']);
    Route::post('deleteCategory', [CategoryController::class, 'destroyCategory']);
    Route::get('statusCategory', [CategoryController::class, 'statusCategory']);

    Route::get('company-info', [CompanyInfoController::class, 'show'])->name('company_info.show');
    Route::get('company-info/edit', [CompanyInfoController::class, 'edit'])->middleware('auth')->name('company_info.edit');
    Route::post('company-info/update', [CompanyInfoController::class, 'update'])->middleware('auth')->name('company_info.update');

    Route::resource('sucursals', SucursalController::class);
    Route::post('/set-sucursal', [SucursalController::class, 'setSucursal']);
    
    // Inventory transfers between sucursals
    Route::get('inventory-transfers', [InventoryTransferController::class, 'index'])->name('inventory-transfers.index');
    Route::get('inventory-transfers/datatable', [InventoryTransferController::class, 'datatable'])->name('inventory-transfers.datatable');
    Route::post('inventory-transfers', [InventoryTransferController::class, 'store'])->name('inventory-transfers.store');
    Route::post('inventory-transfers/{id}', [InventoryTransferController::class, 'update'])->name('inventory-transfers.update');
    // route to get product stock must come before the {id} route to avoid conflicts
    Route::get('inventory-transfers/stock', [InventoryTransferController::class, 'productStock'])->name('inventory-transfers.stock');
    Route::get('inventory-transfers/{id}', [InventoryTransferController::class, 'show'])->name('inventory-transfers.show');
    Route::post('inventory-transfers/{id}/approve', [InventoryTransferController::class, 'approve'])->name('inventory-transfers.approve');
    Route::post('inventory-transfers/{id}/reject', [InventoryTransferController::class, 'reject'])->name('inventory-transfers.reject');
    Route::post('inventory-transfers/{id}/mark-lista', [InventoryTransferController::class, 'markAsLista'])->name('inventory-transfers.mark-lista');
    Route::get('inventory-transfers/{id}/pdf', [InventoryTransferController::class, 'pdf'])->name('inventory-transfers.pdf');
});


