<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\MobileController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\BarangayController;
use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\EncashmentController;
use App\Http\Controllers\PaymentTermController;
use App\Http\Controllers\EncashmentReqController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/* API - Send SMS */
// Route::get('sendSMS', [SmsController::class, 'sendSMS']);
// Route::get('deleteSMS/{id}', [SmsController::class, 'deleteSMS']);

/************** 2024 **************/
/************ SLC APP ************/
/*********************************/

// get clients
Route::get('/app-clients', [ClientController::class, 'app_searchClients']);
// get selected client - for update
Route::get('/app-getClient', [ClientController::class, 'app_getClient']);
// get payment history
Route::get('/app-paymentHistory', [PaymentController::class, 'app_searchPaymentHistory']);
// get regions
Route::get('/app-searchRegions', [RegionController::class, 'app_getRegions']);
// get branch
Route::get('/app-searchBranches', [BranchController::class, 'app_getBranches']);
// get package
Route::get('/app-searchPackages', [PackageController::class, 'app_getPackages']);
// get payment terms
Route::get('/app-searchPaymentTerms', [PaymentTermController::class, 'app_getPaymentTerms']);
// get province
Route::get('/app-searchProvince', [ProvinceController::class, 'app_searchProvince']);
// get cities
Route::get('/app-searchCities', [CityController::class, 'app_searchCities']);
// get barangays
Route::get('/app-searchBarangays', [BarangayController::class, 'app_searchBarangays']);
// get mobile no
Route::get('/app-searchMobileNo', [MobileController::class, 'app_searchMobileNo']);
// get email
Route::get('/app-searchEmails', [EmailController::class, 'app_searchEmail']);
// get comission clients
Route::get('/app-comclients', [EncashmentController::class, 'app_searchComClients']);

// update selected client
Route::put('/app-updateClient', [ClientController::class, 'app_updateClient']);
// update user password
Route::put('/app-updatePass', [UserController::class, 'app_updatePass']);

// login
Route::post('/app-login', [UserController::class, 'app_login']);
// submit new client
Route::post('/app-newClient', [ClientController::class, 'app_newClient']);
// submit new payment
Route::post('/app-newPayment', [PaymentController::class, 'app_newPayment']);
// submit new encashment request
Route::post('/app-newEncashmentReq', [EncashmentReqController::class, 'app_newEncashmentRequest']);