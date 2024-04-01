<?php

use App\Http\Controllers\SSO\SSOController;
use Illuminate\Support\Facades\Route;

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

Route::controller(SSOController::class)->group(function () {
    Route::get("/sso/login", 'getLogin')->name("sso.login");
    Route::get("/callback", 'getCallback')->name("sso.callback");
    Route::get("/sso/connect", 'connectUser')->name("sso.connect");
    Route::get("/sso/logout", 'logout')->name("sso.logout");
    Route::get("/sso/edit-password", 'editPassword')->name("sso.edit-password");
    Route::get("/sso/portal", 'portal')->name("sso.portal");
});
