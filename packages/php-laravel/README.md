# `rizalrepo/sso-client`

Laravel package for UNISM SSO integration. Publishes a ready-to-use `SSOController` and configuration file.

**Requirements:** PHP 8.1+, Laravel 10 / 11 / 12

---

## Installation

```bash
composer require rizalrepo/sso-client:^2.0
php artisan vendor:publish --tag=sso-config
```

This publishes:

| File | Destination |
|------|-------------|
| `SSOController.php` | `app/Http/Controllers/SSO/SSOController.php` |
| `sso.php` | `config/sso.php` |

---

## Configuration

Set credentials in `.env` — `config/sso.php` reads from environment variables:

```env
SSO_URL=https://sirisa.unism.ac.id
SSO_CLIENT_ID=your-uuid
SSO_CLIENT_SECRET=your-secret
SSO_CALLBACK_URL=https://your-app.example.com/callback
```

```php
// config/sso.php (published)
return [
    'callbackUrl'  => env('SSO_CALLBACK_URL', env('APP_URL') . '/callback'),
    'serverUrl'    => env('SSO_URL', 'https://sirisa.unism.ac.id'),
    'clientId'     => env('SSO_CLIENT_ID'),
    'clientSecret' => env('SSO_CLIENT_SECRET'),
];
```

---

## Routes

Add to `routes/web.php`:

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

### Route reference

| Route | Method | Auth | Description |
|-------|--------|------|-------------|
| `/` | `ssoPage` | No | Redirect to SSO server |
| `/sso/login` | `getLogin` | No | Start OAuth flow |
| `/callback` | `getCallback` | No | Receive authorization code |
| `/sso/connect` | `connectUser` | No | Sync user & log in locally |
| `/sso/logout` | `logout` | Yes | Local logout → SSO global logout |
| `/sso/portal` | `portal` | Yes | Switch to SSO portal |
| `/sso/profile` | `editProfile` | Yes | Redirect to SSO profile |
| `/sso/edit-password` | `editPassword` | Yes | Redirect to SSO password change |

---

## Database migration

Update your `users` table migration:

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

---

## Authentication middleware

Redirect unauthenticated users to SSO login. Use `config('sso.serverUrl')` — do not hardcode URLs.

**Laravel 11** — update `app/Http/Middleware/Authenticate.php`:

```php
protected function redirectTo(Request $request): ?string
{
    return $request->expectsJson()
        ? null
        : config('sso.serverUrl') . '/login';
}
```

Register the alias in `bootstrap/app.php`:

```php
$middleware->alias(['auth' => Authenticate::class]);
```

---

## API token verification middleware

Create middleware to validate Bearer tokens against SSO:

```bash
php artisan make:middleware VerifyApiToken
```

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VerifyApiToken
{
    public function handle($request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            Log::warning('No bearer token provided');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ])
                ->get(Config::get('sso.serverUrl') . '/api/verify-token');

            if ($response->successful()) {
                $request->merge(['sso_user' => $response->json()]);
                return $next($request);
            }

            return response()->json(['error' => 'Invalid token'], 401);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error verifying token'], 500);
        }
    }
}
```

Register alias in `bootstrap/app.php` (Laravel 11) or `app/Http/Kernel.php` (Laravel 10):

```php
'verify.api.token' => \App\Http\Middleware\VerifyApiToken::class,
```

---

## Syncing users to SSO server

Call these methods from your `UserController` after local CRUD operations.

### On create

```php
$ssoController = new \App\Http\Controllers\SSO\SSOController();
$ssoController->createUserOnServer([
    'name'                  => $user->name,
    'username'              => $user->username,
    'phone'                 => $user->phone,
    'oauth_client_role_id'  => $user->oauth_client_role_id,
]);
```

### On update

```php
$oldUsername = $user->username; // capture before update

$user->update($validated);

$ssoController = new \App\Http\Controllers\SSO\SSOController();
$ssoController->updateUserOnServer([
    'name'                  => $user->name,
    'username'              => $user->username,
    'prodi'                 => $user->prodi,
    'phone'                 => $user->phone,
    'oauth_client_role_id'  => $user->oauth_client_role_id,
    'old_username'          => $oldUsername,
]);
```

### On delete

```php
$ssoController = new \App\Http\Controllers\SSO\SSOController();
$ssoController->deleteUserOnServer([
    'username'              => $user->username,
    'oauth_client_role_id'  => $user->oauth_client_role_id,
]);
```

---

## Blade views (user menu)

```blade
<div class="dropdown-menu dropdown-menu-end">
    <img class="b-r-10 avatar-image" src="{{ session('avatar') }}" alt="Avatar">

    @if(session('countAccess', 0) > 1)
        <a class="dropdown-item" href="{{ route('sso.portal') }}">Portal</a>
    @endif

    <a href="{{ route('sso.profile') }}">Edit Profile</a>
    <a class="dropdown-item" href="{{ route('sso.edit-password') }}">Edit Password</a>
    <a class="dropdown-item" href="{{ route('sso.logout') }}"
       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
        Logout
    </a>

    <form id="logout-form" action="{{ route('sso.logout') }}" method="GET" class="d-none">
        @csrf
    </form>
</div>
```

---

## Upgrade from v1.x

```bash
composer update rizalrepo/sso-client
php artisan vendor:publish --tag=sso-config --force
```

If you previously customized `SSOController`, compare your version with the newly published file — especially `getCallback`, `connectUser`, and post-login redirect logic.

---

## Further reading

- [OAuth integration guide](../../docs/INTEGRATION.md)
- [Root README](../../README.md)
- [PHP native SDK](../php-native/README.md) — for non-Laravel PHP apps
