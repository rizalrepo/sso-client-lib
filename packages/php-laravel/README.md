# `rizalrepo/sso-client` (Laravel)

```bash
composer require rizalrepo/sso-client:^2.0
php artisan vendor:publish --tag=sso-config
```

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

## Data available in views

After `connectUser()`, data comes from two places:

| Source | Keys | Use for |
|--------|------|---------|
| **Session** | `avatar` | Profile photo URL from SSO (`avatar_url` or default) |
| **Session** | `countAccess` | Number of SSO apps user can access (show Portal link if > 1) |
| **Auth user** | `username`, `name`, `phone`, `prodi` | Display name / NIP / etc. |
| **Named routes** | `sso.profile`, `sso.edit-password` | Redirect to SSO to edit profile or password |

```blade
{{-- Username & name from local user (synced from SSO) --}}
{{ Auth::user()->username }}
{{ Auth::user()->name }}

{{-- Profile photo from SSO session --}}
<img src="{{ session('avatar') }}" alt="{{ Auth::user()->name }}">
```

## Blade user menu (avatar, profile, password, logout)

```blade
<div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
    <img class="avatar-image" src="{{ session('avatar') }}" alt="{{ Auth::user()->name }}">

    @if(session('countAccess', 0) > 1)
        <a class="dropdown-item" href="{{ route('sso.portal') }}">Portal</a>
    @endif

    <a class="dropdown-item" href="{{ route('sso.profile') }}">Edit Profile</a>
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

### What each link does

| Link | Route | Behaviour |
|------|-------|-----------|
| **Edit Profile** | `sso.profile` | Redirect to `{SSO_URL}/profile` — user edits name, photo, phone on SSO |
| **Edit Password** | `sso.edit-password` | Redirect to `{SSO_URL}/edit-password` |
| **Portal** | `sso.portal` | Redirect to `{SSO_URL}/portal` (switch app / role) |
| **Logout** | `sso.logout` | Local logout + redirect to SSO global logout |

After the user edits profile on SSO and returns to your app, refresh avatar/session if needed (re-login or middleware that calls `GET /api/user`).

## Users migration

```php
$table->string('username')->unique();
$table->string('phone')->unique();
$table->char('prodi', 5)->nullable();
$table->bigInteger('oauth_client_role_id');
```

## Sync users to SSO server

After local CRUD in `UserController`:

```php
$sso = new \App\Http\Controllers\SSO\SSOController();
$sso->createUserOnServer([...]);
$sso->updateUserOnServer([...]);
$sso->deleteUserOnServer([...]);
```

See [INTEGRATION.md](../../docs/INTEGRATION.md) · [root README](../../README.md)
