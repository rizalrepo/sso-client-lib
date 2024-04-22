
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

public function up(): void
{
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('username')->unique();
        $table->string('phone')->unique();
        $table->bigInteger('oauth_client_role_id');
        $table->timestamp('email_verified_at')->nullable();
        $table->rememberToken();
        $table->timestamps();
    });
}
