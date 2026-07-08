# Laravel SSO Client (`rizalrepo/sso-client`)

Package Composer untuk integrasi Laravel dengan UNISM SSO.

## Install

```bash
composer require rizalrepo/sso-client
php artisan vendor:publish --tag=sso-config
```

## Konfigurasi (.env)

```env
SSO_URL=https://sirisa.unism.ac.id
SSO_CLIENT_ID=your-uuid
SSO_CLIENT_SECRET=your-secret
SSO_CALLBACK_URL=https://your-app.example.com/callback
```

File `config/sso.php` dibaca dari env — jangan hardcode credential.

## Routes

Tambahkan ke `routes/web.php`:

```php
use App\Http\Controllers\SSO\SSOController;

Route::controller(SSOController::class)->group(function () {
    Route::get('/', 'ssoPage');
    Route::get('/sso/login', 'getLogin')->name('sso.login');
    Route::get('/callback', 'getCallback')->name('sso.callback');
    Route::get('/sso/connect', 'connectUser')->name('sso.connect');

    Route::middleware('auth')->group(function () {
        Route::get('/sso/logout', 'logout')->name('sso.logout');
        Route::get('/sso/edit-password', 'editPassword')->name('sso.edit-password');
        Route::get('/sso/portal', 'portal')->name('sso.portal');
        Route::get('/sso/profile', 'editProfile')->name('sso.profile');
    });
});
```

## Migration users

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('username')->unique();
    $table->string('phone')->unique();
    $table->char('prodi', 5)->nullable();
    $table->bigInteger('oauth_client_role_id');
    $table->timestamp('email_verified_at')->nullable();
    $table->rememberToken();
    $table->timestamps();
});
```

## Middleware Authenticate

Redirect guest ke SSO login — gunakan `config('sso.serverUrl')` bukan hardcode URL.

## Verify API Token

Middleware `VerifyApiToken` memanggil `GET {SSO_URL}/api/verify-token` dengan Bearer token.

Lihat dokumentasi lengkap di root [README.md](../../README.md) dan [docs/INTEGRATION.md](../../docs/INTEGRATION.md).
