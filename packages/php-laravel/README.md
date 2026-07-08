# `rizalrepo/sso-client` (Laravel)

```bash
composer require rizalrepo/sso-client:^2.0
php artisan vendor:publish --tag=sso-config
```

Publishes `SSOController` and `config/sso.php`. The controller delegates HTTP calls to `Rizalrepo\SsoClient\SSOClient` (bundled in this package).

## Routes (`routes/web.php`)

```php
use App\Http\Controllers\SSO\SSOController;

Route::controller(SSOController::class)->group(function () {
    Route::get('/', 'ssoPage');
    Route::get('/sso/login', 'getLogin')->name('sso.login');
    Route::get('/callback', 'getCallback')->name('sso.callback');
    Route::get('/sso/connect', 'connectUser')->name('sso.connect');

    Route::middleware('auth')->group(function () {
        Route::get('/sso/logout', 'logout')->name('sso.logout');
        Route::get('/sso/portal', 'portal')->name('sso.portal');
        Route::get('/sso/profile', 'editProfile')->name('sso.profile');
        Route::get('/sso/edit-password', 'editPassword')->name('sso.edit-password');
    });
});
```

## Users migration

```php
$table->string('username')->unique();
$table->string('phone')->unique();
$table->char('prodi', 5)->nullable();
$table->bigInteger('oauth_client_role_id');
```

## Sync users to SSO

After local CRUD, call `SSOController` methods:

- `createUserOnServer($userArray)`
- `updateUserOnServer($userArray)`
- `updateUserActiveOnServer($userArray)`
- `deleteUserOnServer($userData)`

## API token middleware

Validate Bearer tokens via `GET {SSO_URL}/api/verify-token`. See [root README](../../README.md) and [INTEGRATION.md](../../docs/INTEGRATION.md).
