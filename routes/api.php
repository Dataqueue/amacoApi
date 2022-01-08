<?php

use App\Http\Controllers\Api\AccountCategoryController;
use App\Http\Controllers\Api\AccountStatementController;
use App\Http\Controllers\Api\AdvancePaymentController;
use App\Http\Controllers\Api\AdvancePaymentStatementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductQuotationDetail;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PartyController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\RFQController;
use App\Http\Controllers\Api\RFQDetailsController;
use App\Http\Controllers\Api\AnalyseController;
use App\Http\Controllers\Api\ColumnController;
use App\Http\Controllers\Api\ColumnDataController;
use App\Http\Controllers\Api\QuotationController;
use App\Http\Controllers\Api\QuotationDetailController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\SaleDetailController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\FileUploadController;
use App\Http\Controllers\Api\RFQImageController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\InvoiceDetailController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\ManufacturerController;
use App\Http\Controllers\Api\PaymentAccountController;
use App\Http\Controllers\Api\ProductPriceController;
use App\Http\Controllers\Api\PurchaseInvoiceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyBankController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\DeliveryNoteController;
use App\Http\Controllers\Api\DeliveryNoteDetailController;
use App\Http\Controllers\Api\ReceiptController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\TaxController;
use App\Http\Controllers\Api\PartyBankController;
use App\Http\Controllers\Api\demo;
use App\Http\Controllers\Api\DivisionController;
use App\Http\Controllers\Api\MasterAccountController;
use App\Http\Controllers\Api\InvestmentsDetailsController;
use App\Http\Controllers\Api\ProfitLossController;

use App\Http\Controllers\Api\PurchaseReturnController;
use App\Http\Controllers\Api\SalesReturnController;
use App\Http\Controllers\Api\EmployeesController;
use App\Http\Controllers\Api\ModuleController;
use App\Http\Controllers\Api\MobileController;
use App\Http\Controllers\Api\PermissionDeniedController;
use App\Http\Controllers\Api\StockController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return [$request->user()];
});

// jwt auth links

Route::group(
    [

        'middleware' => 'api',
        'prefix' => 'auth'

    ],
    function ($router) {

        Route::post('login', [AuthController::class, 'login']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::post('me', [AuthController::class, 'me']);
    }
);

// resource api links

Route::apiResource('products',ProductController::class);
Route::apiResource('parties',PartyController::class);
Route::apiResource('categories',CategoryController::class);
Route::apiResource('rfq',RFQController::class);
Route::apiResource('rfq-details',RFQDetailsController::class);
Route::apiResource('analyse',AnalyseController::class);
Route::apiResource('purchase-quotation',QuotationController::class);
Route::apiResource('sale-quotation',QuotationController::class);
Route::apiResource('quotation-detail',QuotationDetailController::class);
Route::apiResource('sale',SaleController::class);
Route::apiResource('sale-detail',SaleDetailController::class);
Route::apiResource('contact',ContactController::class);
Route::apiResource('fileUpload',FileUploadController::class);
Route::apiResource('invoice',InvoiceController::class);
Route::apiResource('invoice-detail',InvoiceDetailController::class);
Route::apiResource('expense',ExpenseController::class);
Route::apiResource('employee',EmployeeController::class);
Route::apiResource('manufacturer',ManufacturerController::class);
Route::apiResource('product-price',ProductPriceController::class);
Route::apiResource('payment-account',PaymentAccountController::class);
Route::apiResource('purchase-invoice',PurchaseInvoiceController::class);
Route::apiResource('account-categories',AccountCategoryController::class);
Route::apiResource('columns',ColumnController::class);
Route::apiResource('columnDatas',ColumnDataController::class);
Route::apiResource('delivery-notes',DeliveryNoteController::class);
Route::apiResource('delivery-notes-details',DeliveryNoteDetailController::class);
Route::apiResource('receipts', ReceiptController::class);
Route::apiResource('advance-payments', AdvancePaymentController::class);
Route::apiResource('roles', RoleController::class);
Route::apiResource('users', UserController::class);
Route::apiResource('party-bank', PartyBankController::class);
Route::apiResource('company', CompanyController::class);
Route::apiResource('company-bank', CompanyBankController::class);
Route::apiResource('demo', demo::class);
Route::apiResource('division', DivisionController::class);
Route::apiResource('stock', StockController::class);
// restful api links
Route::post('company_edit',[CompanyController::class,'company_edit']);
Route::post('rfq-history', [RFQController::class, 'history'])->name('rfq.history');
Route::post('invoice-history', [InvoiceController::class, 'history'])->name('invoice.history');
Route::post('Invoiceupdate', [InvoiceController::class, 'Invoiceupdate'])->name('invoice.Invoiceupdate');
Route::post('quotation-history', [QuotationController::class, 'history'])->name('quotation.history');
Route::get('categorized-products/{id}',[CategoryController::class, 'categorized_products'])->name('categorized.products');
Route::get('quotation-po/',[QuotationController::class, 'invoice_list'])->name('invoice.list');
Route::post('add-user', [UserController::class, 'add'])->name('add.user');
Route::post('upload-file', [RFQImageController::class, 'store'])->name('file.upload');
Route::get('parties-vendor',[PartyController::class, 'vendor'])->name('parties.vendor');
Route::get('products-in-category',[CategoryController::class, 'products_in_category'])->name('products.in.category');
Route::get('sub-category/{id}', [CategoryController::class, 'subCategory'])->name('subCategory');
Route::get('category/{name}', [CategoryController::class, 'search'])->name('category.name');
Route::get('parties-except/{product}', [PartyController::class, 'allVendorExcept'])->name('except.vendor');
Route::get('product-quotation-detail/{id}', [ProductQuotationDetail::class, 'show'])->name('product.quotationdetail');
Route::get('expense-paid', [ExpenseController::class, 'paid'])->name('expense.paid');
Route::get('customer-list', [PartyController::class, 'customer'])->name('customer.list');
Route::get('sales-list', [QuotationController::class, 'salesList'])->name('sales.list');
Route::get('purchase-invoice-list',[PurchaseInvoiceController::class, 'purchaseInvoiceList'])->name('purchase.invoice.list');
Route::get('account-subcategories/{id}', [AccountCategoryController::class, 'subCategory'])->name('account.category.subcategory');
Route::get('account-categories-search/{name}', [AccountCategoryController::class, 'search'])->name('account.category.search');
Route::get('quotations-accepted-list', [QuotationController::class, 'acceptedList'])->name('quotaions.accepted.list');
Route::get('quotations-rejected-list', [QuotationController::class, 'rejectedList'])->name('quotaions.rejected.list');
Route::put('update-quotation/{id}', [QuotationController::class, 'updateQuotation'])->name('quotations.status.update');
Route::post('old-password', [UserController::class, 'oldPassword']);
Route::post('account-statement', [AccountStatementController::class, 'accountStatement']);
Route::post('all-account-statement', [AccountStatementController::class, 'allAccountStatement']);
Route::post('vendorStatement', [AccountStatementController::class, 'vendorStatement']);
Route::post('all-account-masterstatement', [MasterAccountController::class, 'allAccountmasterStatement']);
Route::post('advance-payment-statement',[AdvancePaymentStatementController::class,'statement']);
Route::post('all-advance-payment-statement',[AdvancePaymentStatementController::class, 'allAdvancePaymentStatement']);
Route::post('rfq-update',[RFQController::class, 'update']);
Route::post('sale-quotation-update',[QuotationController::class, 'update']);
Route::delete('delete-quotation-detail/{quotation_detail}', [QuotationController::class, 'deleteFile']);
Route::post('sale-tax', [TaxController::class, 'saleTax']);
Route::post('purchase-tax', [TaxController::class, 'purchaseTax']);
Route::get('all-categories',[CategoryController::class, 'categories']);
Route::delete('quotation_details/{id}',[QuotationController::class, 'destroy_details']);
Route::delete('rfq_details/{id}',[RFQController::class, 'destroy_details']);
Route::post('sale-report',[QuotationController::class, 'saleReport']);
Route::get('purchase-quote',[PurchaseInvoiceController::class, 'PurchaseInvoice'])->name('purchase.get');
Route::post('expenseUpdate',[ExpenseController::class, 'expenseUpdate']);
Route::get('singleExpenses/{id}', [ExpenseController::class, 'singleExpense']);
Route::get('singleDivision/{id}', [DivisionController::class, 'singleDivision']);
Route::get('singleReceipt/{id}', [ReceiptController::class, 'singleReceipt']);
Route::post('updateReceipt', [ReceiptController::class, 'updateReceipt']);
Route::post('updateQuotestatus', [QuotationController::class, 'updateQuotestatus']);
Route::post('updateAdvancePay', [AdvancePaymentController::class, 'updateAdvancepay']);
Route::post('masterstatement', [MasterAccountController::class, 'masterStatement']);
Route::post('all-account-masterstatement', [MasterAccountController::class, 'allAccountmasterStatement']);
Route::post('accountSummary', [AdvancePaymentStatementController::class, 'accountSummary']);
Route::get('paidDivision', [DivisionController::class, 'paidDivision']);
//  Route::post('all-account-masterstatementvbbbbbbb', [MasterAccountController::class, 'allAccountmasterStatement']);
Route::get('Userstatus/{id}', [UserController::class, 'Userstatus']);
Route::post('Usersprofile', [UserController::class, 'Usersprofile']);
Route::post('Expense_delete_verify', [ExpenseController::class, 'Expense_delete_verify']);
Route::post('partyDelete_all', [PartyController::class, 'partyDelete_all']);
Route::get('expense_chart', [ExpenseController::class, 'expense_chart']);
Route::get('accountCategory', [AccountCategoryController::class, 'accountCategory']);
Route::get('salesTax', [InvoiceController::class, 'salesTax']);
Route::get('purchaseTax', [ExpenseController::class,'purchaseTax']);
Route::get('salesExpenseReport', [AccountCategoryController::class, 'salesExpenseReport']);
Route::get('profitLoss', [AccountStatementController::class, 'profitLoss']);
Route::post('InvestmentsDetails', [InvestmentsDetailsController::class, 'store']);
Route::post('vat', [AccountStatementController::class, 'vat']);
Route::post('update_company', [QuotationController::class, 'update_company']);
Route::get('responseData', [AccountStatementController::class, 'responseData']);
Route::get('accountcategories/{id}', [AccountCategoryController::class, 'accountcategories']);
Route::put('accountEdit/{id}', [AccountCategoryController::class, 'accountEdit']);
// Invoice delivery note
Route::post('invoce_note', [DeliveryNoteController::class, 'invoce_note']);

//purchase Return API's

Route::delete('purchase-return-delete/{id}', [PurchaseReturnController::class, 'deletepurchasereturn']);
Route::get('getPurchaseReturnINV/{id}', [PurchaseReturnController::class, 'getPurchaseReturnINV']);
Route::get('purchase-return-data/{id}', [PurchaseReturnController::class, 'index']);
Route::get('getProductsPR/{id}', [PurchaseReturnController::class, 'getProductsPR']);
Route::get('purchase-return-table', [PurchaseReturnController::class, 'purchaseReturnTableData']);
Route::post('purchase-return', [PurchaseReturnController::class, 'purchasereturn']);



// sales Return API's
Route::get('getSalesFormData/{id}', [SalesReturnController::class, 'index']);
Route::get('getSalesReturnINV/{id}', [SalesReturnController::class, 'getSalesReturnINV']);
Route::get('getInvSr/{id}', [SalesReturnController::class, 'getProductsSR']);

Route::get('sales-return-table', [SalesReturnController::class, 'SalesReturnTableData']);

// empoyee Api
Route::post('save-emp', [EmployeesController::class, 'store']);
Route::post('update-emp', [EmployeesController::class, 'update']);
Route::get('getEmp', [EmployeesController::class, 'index']);
Route::delete('delete-emp/{id}', [EmployeesController::class, 'destroy']);
Route::get('getAllCat', [CategoryController::class, 'getAllCat']);

//userPermission
Route::post('add-permission', [PermissionDeniedController::class, 'store']);
Route::get('get-modules-per/{id}/{i}', [PermissionDeniedController::class, 'index']);Route::get('userPermission/{id}', [PermissionDeniedController::class, 'userPermission']);

//Module API's
Route::post('add-module', [ModuleController::class, 'store']);
Route::delete('delete-modules/{id}', [ModuleController::class, 'destroy']);
Route::get('edit-data-modules/{id}', [ModuleController::class, 'edit']);
Route::put('update-module/{id}', [ModuleController::class, 'update']);
Route::get('get-modules/{id}', [ModuleController::class, 'index']);
Route::get('unCategorized-products',[CategoryController::class, 'unCategorized_products']);
Route::get('check/{id}', [StockController::class, 'check']);

// mobile routes 


Route::get('getMCat', [MobileController::class, 'getMCat']);
Route::post('expenceStore', [MobileController::class, 'storeExpence']);
Route::get('divisionbyid/{id}', [DivisionController::class, 'getDivbyId']);








