<?php

use App\Mail\SlcMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\McprController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ActionsController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\OrBatchController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\BarangayController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\ExpensesController;
use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\CsvImportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EncashmentController;
use App\Http\Controllers\FsaRankingController;
use App\Http\Controllers\LoanPaymentController;
use App\Http\Controllers\LoanRequestController;
use App\Http\Controllers\PaymentTermController;
use App\Http\Controllers\LoanPaymentsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ContractBatchController;
use App\Http\Controllers\EncashmentReqController;
use App\Http\Controllers\OfficialReceiptController;
use App\Http\Controllers\ExpenseDescriptionController;
use App\Http\Controllers\AddressController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

/* 2023 SilverDust) S. Maceren */

/* ----- LOGIN -----*/
Route::get('/', function () {
    return view('pages.login');
});

Route::get('/login', function () {
    return view('pages.login');
});

Route::post('/logout', [UserController::class, 'logout']);
Route::post('/login', [UserController::class, 'login']);

/* ----- HOME -----*/
Route::get('/home', function () {
    return view('pages.home');
});

/* ----- DASHBOARD -----*/
Route::get('/dashboard', [DashboardController::class, 'getDashboard']);

/* ----- FSA RANKINGS -----*/
Route::get('/fsarankings-collections', [FsaRankingController::class, 'getCollectionsRanking']);
Route::get('/fsarankings-sales', [FsaRankingController::class, 'getSalesRanking']);

/* ----- CLIENT VIEW - HOME -----*/
Route::get('/clienthome/{clientId}', [ClientController::class, 'viewClientHomeInfo']);
// client view - loan request
Route::get('/clienthome-loanrequest/{clientId}', [ClientController::class, 'clientHomeLoanRequest']);
// client view - print SOA
Route::get('/clienthome-printsoa/{clientId}', [ClientController::class, 'clientHomePrintSOA']);
// client view - print SOA PDF
Route::get('/clienthome-printsoa-pdf/{clientId}', [ClientController::class, 'clientHomePrintSOAPDF']);
// client view - print Certificate of Full Payment
Route::get('/clienthome-printcofp/{clientId}', [ClientController::class, 'clientHomePrintCOFP']);

// client view - submit loan request
Route::post('/submit-client-loanrequest/{client}', [LoanRequestController::class, 'createClientLoanRequest']);

/* ----- STAFF -----*/
// staff - main screen
Route::get('/staff', [StaffController::class, 'searchAll']);
// staff - view screen
Route::get('/staff-view/{staff}', [StaffController::class, 'viewStaffInfo']);
// staff - insert screen
Route::get('/staff-create', [StaffController::class, 'staffFormScreen']);
// staff - insert data
Route::post('/submit-staff-insert', [StaffController::class, 'createStaff']);
// staff - search selected - update screen
Route::get('/staff-update/{staff}', [StaffController::class, 'staffFormScreen']);
// staff - update data
Route::put('/submit-staff-update/{staff}', [StaffController::class, 'updateStaff']);
// staff - delete data
Route::delete('/submit-staff-delete/{staff}', [StaffController::class, 'deleteStaff']);

/* ----- CLIENT -----*/

// client - main screen
Route::get('/client', [ClientController::class, 'searchAll']);
// client - view screen
Route::get('/client-view/{client}', [ClientController::class, 'viewClientInfo']);
// client - add payment screen
Route::get('/client-addpayment/{client}', [ClientController::class, 'addClientPayment']);
// client - add loan payment screen
Route::get('/client-addloanpayment/{client}', [ClientController::class, 'addClientLoanPayment']);
// client - submit payment screen
Route::post('/client-submit-payment/{client}', [PaymentController::class, 'submitClientPayment']);
// client - submit loan payment screen
Route::post('/client-submit-loanpayment/{client}', [LoanPaymentController::class, 'submitClientLoanPayment']);
// client - void payment
Route::put('/client-void-payment/{payment}', [PaymentController::class, 'voidClientPayment']);
// client - void loan payment
Route::put('/client-void-loanpayment/{loanPayment}', [LoanPaymentController::class, 'voidClientLoanPayment']);
// client - insert screen
Route::get('/client-create', [ClientController::class, 'clientFormScreen']);
// client - insert data
Route::post('/submit-client-insert', [ClientController::class, 'createClient']);
// client - search selected - update screen
Route::get('/client-update/{client}', [ClientController::class, 'clientFormScreen']);
// client - update data
Route::put('/submit-client-update/{client}', [ClientController::class, 'updateClient']);
// client - update status
Route::put('/client-update-status/{client}', [ClientController::class, 'updateClientStatus']);
// client - delete data
Route::delete('/client-delete-submit/{client}', [ClientController::class, 'deleteClient']);
// client - print SOA
Route::get('/client-printsoa/{client}', [ClientController::class, 'printSOA']);
// client - print SOA PDF
Route::get('/client-printsoa-pdf/{client}', [ClientController::class, 'printSOAPDF']);
// client - print Certificate of Full Payment
Route::get('/client-printcofp/{client}', [ClientController::class, 'printCOFP']);
// client - assign to another person
Route::get('/client-assignplan/{client}', [ClientController::class, 'assignPlan']);
// client - assign
Route::post('/submit-client-assign/{client}', [ClientController::class, 'submitClientAssign']);
// client - assign attachment
Route::put('/submit-client-assign-attachment/{assignedPlans}', [ClientController::class, 'uploadAssignAttachment']);
// client - insert screen for transfer
Route::get('/transfer-client-create/{client}', [ClientController::class, 'transferClientFormScreen']);
// client - insert data for client transfer
Route::post('/submit-client-transfer-insert/{client}', [ClientController::class, 'createTransferClient']);
// client - update completed memorial service
Route::put('/submit-complete-memorial/{client}', [ClientController::class, 'updateCompletedMemorial']);

/* ----- BRANCH -----*/
// branch - main screen
Route::get('/branch', [BranchController::class, 'searchAll']);
// branch - insert screen
Route::get('/branch-create', [BranchController::class, 'branchFormScreen']);
// branch - insert data
Route::post('/submit-branch-insert', [BranchController::class, 'createBranch']);
// branch - search selected - update screen
Route::get('/branch-update/{branch}', [BranchController::class, 'branchFormScreen']);
// branch - update data
Route::put('/submit-branch-update/{branch}', [BranchController::class, 'updateBranch']);
// branch - delete data
Route::delete('/submit-branch-delete/{branch}', [BranchController::class, 'deleteBranch']);

/* ----- REGION -----*/
// region - main screen
Route::get('/region', [RegionController::class, 'searchAll']);
// region - insert screen
Route::get('/region-create', [RegionController::class, 'regionFormScreen']);
// region - insert data
Route::post('/submit-region-insert', [RegionController::class, 'createRegion']);
// region - search selected - update screen
Route::get('/region-update/{region}', [RegionController::class, 'regionFormScreen']);
// region - update data
Route::put('/submit-region-update/{region}', [RegionController::class, 'updateRegion']);
// region - delete data
Route::delete('/submit-region-delete/{region}', [RegionController::class, 'deleteRegion']);

/* ----- PROVINCE -----*/
// province - main screen
Route::get('/province', [ProvinceController::class, 'searchAll']);
// province - insert screen
Route::get('/province-create', [ProvinceController::class, 'provinceFormScreen']);
// province - insert data
Route::post('/submit-province-insert', [ProvinceController::class, 'createProvince']);
// province - search selected - update screen
Route::get('/province-update/{province}', [ProvinceController::class, 'provinceFormScreen']);
// province - update data
Route::put('/submit-province-update/{province}', [ProvinceController::class, 'updateProvince']);
// province - delete data
Route::delete('/submit-province-delete/{province}', [ProvinceController::class, 'deleteProvince']);

/* ----- CITY -----*/
// city - main screen
Route::get('/city', [CityController::class, 'searchAll']);
// city - insert screen
Route::get('/city-create', [CityController::class, 'cityFormScreen']);
// city - insert data
Route::post('/submit-city-insert', [CityController::class, 'createCity']);
// city - search selected - update screen
Route::get('/city-update/{city}', [CityController::class, 'cityFormScreen']);
// city - update data
Route::put('/submit-city-update/{city}', [CityController::class, 'updateCity']);
// city - delete data
Route::delete('/submit-city-delete/{city}', [CityController::class, 'deleteCity']);

/* ----- BARANGAY -----*/
// barangay - main screen
Route::get('/barangay', [BarangayController::class, 'searchAll']);
// barangay - insert screen
Route::get('/barangay-create', [BarangayController::class, 'barangayFormScreen']);
// barangay - insert data
Route::post('/submit-barangay-insert', [BarangayController::class, 'createbarangay']);
// barangay - search selected - update screen
Route::get('/barangay-update/{barangay}', [BarangayController::class, 'barangayFormScreen']);
// barangay - update data
Route::put('/submit-barangay-update/{barangay}', [BarangayController::class, 'updateBarangay']);
// barangay - delete data
Route::delete('/submit-barangay-delete/{barangay}', [BarangayController::class, 'deleteBarangay']);

/* ----- OR BATCH -----*/
// or batch - main screen
Route::get('/orbatch', [OrBatchController::class, 'searchOrBatchAll']);
// or batch - insert screen
Route::get('/orbatch-create', [OrBatchController::class, 'orbatchFormScreen']);
// or batch - insert data
Route::post('/submit-orbatch-insert', [OrBatchController::class, 'createOrBatch']);
// or batch - assign staff
Route::get('/orbatch-assign/{orbatch}', [OrBatchController::class, 'assignOrBatchScreen']);
// or batch - submit assign staff
Route::put('/submit-orbatch-assign', [OrBatchController::class, 'assignOrBatch']);
// or batch - delete data
Route::delete('/submit-orbatch-delete/{orbatch}', [OrBatchController::class, 'deleteOrBatch']);

/* ----- OR SERIES -----*/
// or series - main screen
Route::get('/orbatch-viewseries/{orbatch}', [OrBatchController::class, 'searchOrSeriesByOrId']);
// or series - void or number data
Route::put('/submit-orseries-void/{orseries}', [OfficialReceiptController::class, 'voidOrNumber']);

/* ----- CONTRACT BATCH -----*/
// contract batch - main screen
Route::get('/contractbatch', [ContractBatchController::class, 'searchContractBatchAll']);
// contract batch - insert screen
Route::get('/contractbatch-create', [ContractBatchController::class, 'contractBatchFormScreen']);
// contract batch - insert data
Route::post('/submit-contractbatch-insert', [ContractBatchController::class, 'createContractBatch']);
// contract batch - assign staff
Route::get('/contractbatch-assign/{contractbatch}', [ContractBatchController::class, 'assignContractBatchScreen']);
// contract batch - submit assign staff
Route::put('/submit-contractbatch-assign', [ContractBatchController::class, 'assignContractBatch']);
// contract batch - delete data
Route::delete('/submit-contractbatch-delete/{contractbatch}', [ContractBatchController::class, 'deleteContractBatch']);

/* ----- CONTRACT SERIES -----*/
// contract series - main screen
Route::get('/contractbatch-viewseries/{contractbatch}', [ContractBatchController::class, 'searchContractSeriesByContractId']);
// contract series - void or number data
Route::put('/submit-contractseries-void/{contractseries}', [ContractController::class, 'voidContractNumber']);

/* ----- DEPOSITS -----*/
// deposits - main screen
Route::get('/deposit', [DepositController::class, 'searchAll']);
// deposits - insert screen
Route::get('/deposit-create', [DepositController::class, 'depositFormScreen']);
// deposits - insert data
Route::post('/submit-deposit-insert', [DepositController::class, 'createDeposit']);
// deposits - search selected - update screen
Route::get('/deposit-update/{deposit}', [DepositController::class, 'depositFormScreen']);
// deposits - update data
Route::put('/submit-deposit-update/{deposit}', [DepositController::class, 'updateDeposit']);
// deposits - delete data
Route::delete('/submit-deposit-delete/{deposit}', [DepositController::class, 'deleteDeposit']);

/* ----- EXPENSES -----*/
// expenses - main screen
Route::get('/expenses', [ExpensesController::class, 'searchAll']);
// expenses - insert screen
Route::get('/expense-create', [ExpensesController::class, 'expenseFormScreen']);
// expenses - insert data
Route::post('/submit-expense-insert', [ExpensesController::class, 'createExpense']);
// expenses - search selected - update screen
Route::get('/expense-update/{expense}', [ExpensesController::class, 'expenseFormScreen']);
// expenses - update data
Route::put('/submit-expense-update/{expense}', [ExpensesController::class, 'updateExpense']);
// expenses - delete data
Route::delete('/submit-expense-delete/{expense}', [ExpensesController::class, 'deleteExpense']);

/* ----- PAYMENTS -----*/
// payment - main screen
Route::get('/payment', [PaymentController::class, 'searchAll']);
// spot cash approval - main screen
Route::get('/spotcash-approval', [PaymentController::class, 'getPendingSpotCashPayments'])->name('spotcash.approval');
// spot cash approval - approve payment
Route::put('/spotcash-approve/{payment}', [PaymentController::class, 'approveSpotCashPayment']);
// spot cash approval - reject payment
Route::put('/spotcash-reject/{payment}', [PaymentController::class, 'rejectSpotCashPayment']);

/* ----- LOAN PAYMENTS -----*/
// loan payment - main screen
Route::get('/loanpayment', [LoanPaymentController::class, 'searchAll']);

/* ----- REPORTS -----*/
// reports - main screen
Route::get('/reports', [ReportController::class, 'searchReportData']);
Route::post('/reports-daily', [ReportController::class, 'searchDailyReports'])->name('reports.daily');
Route::post('/reports-daily-pdf', [ReportController::class, 'searchDailyReportsPDF'])->name('reports.daily.pdf');
Route::post('/reports-monthly', [ReportController::class, 'searchMonthlyReports'])->name('reports.monthly');
Route::post('/reports-annual', [ReportController::class, 'searchAnnualReports'])->name('reports.annual');
Route::post('/reports-status', [ReportController::class, 'searchStatusReport'])->name('reports.status');
Route::post('/reports-status-pdf', [ReportController::class, 'searchStatusReportPDF'])->name('reports.status.pdf');

/* ----- REQUEST LOANS - ADMIN VIEW -----*/
// loans - main screen
Route::get('/req-loans', [LoanRequestController::class, 'searchAll']);
Route::get('/req-loans/view/{loanRequest}', [LoanRequestController::class, 'viewLoanRequest']);
Route::put('/submit-req-loan/{loanRequest}', [LoanRequestController::class, 'updateLoanRequest']);
Route::delete('/submit-req-loan-delete/{loanRequest}', [LoanRequestController::class, 'deleteLoanRequet']);

/* ----- REQUEST COMMISSIONS - ADMIN VIEW -----*/
Route::get('/req-encashments', [EncashmentReqController::class, 'searchAll']);
Route::get('/view-req-encashment/{encashmentReq}', [EncashmentReqController::class, 'viewEncashmentReq']);
Route::get('/view-req-encashment-adjustment/{encashmentReq}', [EncashmentReqController::class, 'adjustEncashmentReq']);
Route::put('/submit-encashment-adjustment/{encashmentReq}', [EncashmentReqController::class, 'updateEncashmentReqAdjustment']);
Route::put('/submit-encashment-req-release/{encashmentReq}', [EncashmentReqController::class, 'releaseEncashmentReq']);
Route::put('/submit-encashment-req-reject/{encashmentReq}', [EncashmentReqController::class, 'rejectEncashmentReq']);

/* ----- REQUEST COMMISSIONS - FSA / FSC VIEW -----*/
Route::post('/submit-encashment-req', [EncashmentReqController::class, 'createEncashmentRequest']);
Route::put('/submit-cancel-encashment-req', [EncashmentReqController::class, 'cancelEncashmentRequests']);

/* ----- COMMISSIONS -----*/
Route::get('/commission', [EncashmentController::class, 'searchAll']);

/* ----- MCPR -----*/
// mcpr - main screen
Route::get('/mcpr', [McprController::class, 'searchAll']);
// mcpr - insert screen
Route::get('/mcpr-create', [McprController::class, 'McprFormScreen']);
// mcpr - insert data
Route::post('/submit-mcpr-insert', [McprController::class, 'createMcpr']);
// mcpr - search selected - update screen
Route::get('/mcpr-update/{mcpr}', [McprController::class, 'McprFormScreen']);
// mcpr - update data
Route::put('/submit-mcpr-update/{mcpr}', [McprController::class, 'updateMcpr']);
// mcpr - delete data
Route::delete('/submit-mcpr-delete/{mcpr}', [McprController::class, 'deleteMcpr']);

/* ----- BANK -----*/
// bank - main screen
Route::get('/bank', [BankController::class, 'searchAll']);
// bank - insert screen
Route::get('/bank-create', [BankController::class, 'bankFormScreen']);
// bank - insert data
Route::post('/submit-bank-insert', [BankController::class, 'createBank']);
// bank account - insert data
Route::post('/submit-bankaccount-insert', [BankController::class, 'createBankAccount']);
// bank - search selected - update screen
Route::get('/bank-update/{bank}', [BankController::class, 'bankFormScreen']);
// bank - update data
Route::put('/submit-bank-update/{bank}', [BankController::class, 'updateBank']);
// bank - search selected - delete screen
Route::get('/bank-delete', [BankController::class, 'bankDeleteFormScreen']);
// bank - delete data
Route::delete('/submit-bank-delete/{bank}', [BankController::class, 'deleteBank']);
// bank account - insert screen
Route::get('/bankaccount-create', [BankController::class, 'bankAccountFormScreen']);
// bank account - delete data
Route::delete('/submit-bankaccount-delete/{bank}', [BankController::class, 'deleteBankAccount']);

/* ----- EXPENSE DESCRIPTION -----*/
// expense description - main screen
Route::get('/expense-desc', [ExpenseDescriptionController::class, 'searchAll']);
// expense description - insert screen
Route::get('/expense-desc-create', [ExpenseDescriptionController::class, 'expenseDescFormScreen']);
// expense description - insert data
Route::post('/submit-expense-desc-insert', [ExpenseDescriptionController::class, 'createExpenseDesc']);
// expense description - search selected - update screen
Route::get('/expense-desc-update/{expenseDesc}', [ExpenseDescriptionController::class, 'expenseDescFormScreen']);
// expense description - update data
Route::put('/submit-expense-desc-update/{expenseDesc}', [ExpenseDescriptionController::class, 'updateExpenseDesc']);
// expense description - delete data
Route::delete('/submit-expense-desc-delete/{expenseDesc}', [ExpenseDescriptionController::class, 'deleteExpenseDesc']);

/* ----- PACKAGES -----*/
// packages - main screen
Route::get('/package', [PackageController::class, 'searchAll']);
// packages - view screen
Route::get('/package-view/{package}', [PackageController::class, 'searchPackage']);
// packages - disable package
Route::put('/submit-package-disable/{package}', [PackageController::class, 'disablePackage']);
// packages - enable package
Route::put('/submit-package-enable/{package}', [PackageController::class, 'enablePackage']);
// packages - insert screen
Route::get('/package-create', [PackageController::class, 'packageFormScreen']);
// packages - insert data
Route::post('/submit-package-insert', [PackageController::class, 'createPackage']);
// packages - search selected - update screen
Route::get('/package-update/{package}', [PackageController::class, 'packageFormScreen']);
// packages - update data
Route::put('/submit-package-update/{package}', [PackageController::class, 'updatePackage']);
// packages - delete data
Route::delete('/submit-package-delete/{package}', [PackageController::class, 'deletePackage']);

/* ----- NOTIFICATIONS -----*/
// notifications - insert screen
Route::get('/notif', function () {
    return view('pages.notification.notif-create');
});
// notifications - submit to targets
Route::post('/submit-notification', [NotificationController::class, 'submitNotif']);

/* ----- MENU -----*/
// menu - main screen
Route::get('/menu', [MenuController::class, 'searchAll']);
// menu - insert screen
Route::get('/menu-create', [MenuController::class, 'menuFormScreen']);
// menu - search selected - update screen
Route::get('/menu-update/{menu}', [MenuController::class, 'menuFormScreen']);
// menu - insert data
Route::post('/submit-menu-insert', [MenuController::class, 'createMenu']);
// menu - update data
Route::put('/submit-menu-update/{menu}', [MenuController::class, 'updateMenu']);
// menu - delete data
Route::delete('/submit-menu-delete/{menu}', [MenuController::class, 'deleteMenu']);

/* ----- ACTIONS -----*/
// action - main screen
Route::get('/action', [ActionsController::class, 'searchAll']);
// action - insert screen
Route::get('/action-create', [ActionsController::class, 'actionFormScreen']);
// action - insert data
Route::post('/submit-action-insert', [ActionsController::class, 'insertAction']);
// action - search selected - update screen
Route::get('/action-update/{actions}', [ActionsController::class, 'actionFormScreen']);
// action - update data
Route::put('/submit-action-update/{action}', [ActionsController::class, 'updateAction']);
// action - delete data
Route::delete('/submit-action-delete/{action}', [ActionsController::class, 'deleteAction']);

/* ----- ROLES -----*/
// role - main screen
Route::get('/role', [RoleController::class, 'searchAll']);
// role - insert screen
Route::get('/role-create', [RoleController::class, 'roleFormScreen']);
// role - insert data
Route::post('/submit-role-insert', [RoleController::class, 'createRole']);
// role - search selected - update screen
Route::get('/role-update/{role}', [RoleController::class, 'roleFormScreen']);
// role - insert data
Route::post('/submit-role-insert', [RoleController::class, 'createRole']);
// role - update data
Route::put('/submit-role-update/{role}', [RoleController::class, 'updateRole']);
// role - delete data
Route::delete('/submit-role-delete/{role}', [RoleController::class, 'deleteRole']);

/* ----- AJAX HANDLERS -----*/

// dashboard - get collections
Route::get('/get-collections-dashboard', [DashboardController::class, 'searchCollections']);
// dashboard - get new sales
Route::get('/get-newsales-dashboard', [DashboardController::class, 'searchNewSales']);
// dashboard - get sales of the day
Route::get('/get-sales-today', [DashboardController::class, 'getSalesToday']);
// dashboard - get collections of the day
Route::get('/get-collections-today', [DashboardController::class, 'getCollectionsToday']);
// staff - get staff based on branch
Route::get('/get-staff', [StaffController::class, 'getStaffByBranch']);
// branches - get branches based on region
Route::get('/get-branches', [RegionController::class, 'getBranchesByRegion']);
// or series - get or series
Route::get('/get-orseries', [OrBatchController::class, 'getOrSeriesByOrBatchId']);
// or numbers - get or numbers by series code
Route::get('/get-or-numbers', [PaymentController::class, 'getOrNumbersBySeriesCode']);
// or series codes - get list for dropdown
Route::get('/get-or-series-list', [PaymentController::class, 'getOrSeriesCodesList']);
// or series codes - get all available series codes (debugging)
Route::get('/get-all-or-series', [PaymentController::class, 'getAllOrSeriesCodes']);
// or series codes - get series codes filtered by branch
Route::get('/get-or-series-by-branch', [PaymentController::class, 'getOrSeriesCodesByBranch']);
// or batch - get staff for assign
Route::get('/get-staff-assignor', [OrBatchController::class, 'getStaffAssignOr']);
// contract series - get contract series
Route::get('/get-contractseries', [ContractBatchController::class, 'gerContractSeriesByContractBatchId']);
// contract batch - get staff for assign
Route::get('/get-staff-assigncontract', [ContractBatchController::class, 'getStaffAssignContract']);
// bank accounts - get bank accounts based on bank
Route::get('/get-bankaccounts', [BankController::class, 'getBankAccountsByBank']);
// packages - get package price based on the selected package
Route::get('/get-packageprice', [PackageController::class, 'getPackagePrice']);
// payment terms - get payment term based on the selected package
Route::get('/get-paymentterm', [PaymentTermController::class, 'getPaymentTerm']);
// payment term amount - get payment term amount based on the selected term
Route::get('/get-paymenttermamount', [PaymentTermController::class, 'getPaymentTermAmount']);
// payments - get payment history by the selected client
Route::get('/get-payment-history', [PaymentController::class, 'getPaymentHistory']);
// loan payments - get loan payments by the selected client
Route::get('/get-loan-payments', [LoanPaymentController::class, 'getLoanPayments']);
// cities - get cities based on province
Route::get('/get-cities', [BarangayController::class, 'getCitiesByProvince']);
// cities zipcode - get zipcode based on cities
Route::get('/get-cities-zipcode', [CityController::class, 'getCityZipcode']);
// barangays - get barangays based on cities
Route::get('/get-barangays', [BarangayController::class, 'getBarangaysByCity']);
// ref cities - get cities from reference tables based on province
Route::get('/get-ref-cities', [BarangayController::class, 'getRefCitiesByProvince']);
// ref barangays - get barangays from reference tables based on cities
Route::get('/get-ref-barangays', [BarangayController::class, 'getRefBarangaysByCity']);

// ***** NEW TBLADDRESS API ROUTES ***** //
// address - get all regions
Route::get('/get-address-regions', [AddressController::class, 'getRegions']);
// address - get provinces by region
Route::get('/get-address-provinces', [AddressController::class, 'getProvincesByRegion']);
// address - get province from city code
Route::get('/get-address-province-from-city', [AddressController::class, 'getProvinceFromCity']);
// address - get cities by province  
Route::get('/get-address-cities', [AddressController::class, 'getCitiesByProvince']);
// address - get barangays by city
Route::get('/get-address-barangays', [AddressController::class, 'getBarangaysByCity']);
// address - get complete address hierarchy
Route::get('/get-complete-address', [AddressController::class, 'getCompleteAddress']);
// address - search addresses
Route::get('/search-addresses', [AddressController::class, 'searchAddresses']);
// address - get statistics
Route::get('/get-address-stats', [AddressController::class, 'getAddressStats']);
// address - get zipcode by city
Route::get('/get-address-zipcode', [AddressController::class, 'getZipcodeByCity']);
// roles - get roles
Route::get('/get-roles', [RoleController::class, 'checkRolePrivilege']);
// menu - get menu
Route::get('/get-menu', [MenuController::class, 'checkMenuPrivilege']);
// import csv - client payments
Route::post('/importcsv-clientpayments', [CsvImportController::class, 'importClientPayments']);
// encashment req view - clients
Route::get('/encashmentview-req-clients', [EncashmentReqController::class, 'searchEncashmentReqClients']);

// client - get available contracts
Route::get('/get-available-contracts', [ClientController::class, 'getAvailableContracts']);