<?php
/*
silahkan tambahkan code dibawah ke file web.php
*/

Route::controller(SSOController::class)->group(function () {
    Route::get("/sso/login", 'getLogin')->name("sso.login");
    Route::get("/callback", 'getCallback')->name("sso.callback");
    Route::get("/sso/connect", 'connectUser')->name("sso.connect");

    Route::middleware('auth')->group(function () {
        Route::get("/sso/logout", 'logout')->name("sso.logout");
        Route::get("/sso/edit-password", 'editPassword')->name("sso.edit-password");
        Route::get("/sso/portal", 'portal')->name("sso.portal");
    });
});
