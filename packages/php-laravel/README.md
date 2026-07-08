# `rizalrepo/sso-client` (Laravel)

## Satu perintah

```bash
composer require rizalrepo/sso-client:^2.1
```

Setelah install, Laravel **otomatis** mendaftarkan:

- **Config** `sso.*` (via `mergeConfigFrom`)
- **Routes** SSO (`/sso/login`, `/callback`, `/sso/connect`, dll.)

Tambahkan env di `.env`:

```env
SSO_URL=https://sirisa.unism.ac.id
SSO_CLIENT_ID=your-uuid
SSO_CLIENT_SECRET=your-secret
SSO_CALLBACK_URL=https://your-app.example.com/callback
```

Pastikan route `home` ada (redirect setelah login).

## Kustomisasi (opsional)

Publish hanya jika perlu override config atau controller:

```bash
php artisan vendor:publish --tag=sso-config
```

| File | Fungsi |
|------|--------|
| `config/sso.php` | Override env defaults |
| `app/Http/Controllers/SSO/SSOController.php` | Extend package controller |

Nonaktifkan auto-routes jika pakai controller/routes sendiri:

```env
SSO_REGISTER_ROUTES=false
```

Model user custom:

```env
SSO_USER_MODEL=App\Models\CustomUser
```

## Routes (otomatis terdaftar)

| Method | URI | Name |
|--------|-----|------|
| GET | `/` | — |
| GET | `/sso/login` | `sso.login` |
| GET | `/callback` | `sso.callback` |
| GET | `/sso/connect` | `sso.connect` |
| GET | `/sso/logout` | `sso.logout` (auth) |
| GET | `/sso/portal` | `sso.portal` (auth) |
| GET | `/sso/profile` | `sso.profile` (auth) |
| GET | `/sso/edit-password` | `sso.edit-password` (auth) |

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
use Rizalrepo\SsoClient\Http\Controllers\SSOController;

app(SSOController::class)->createUserOnServer([...]);
app(SSOController::class)->updateUserOnServer([...]);
app(SSOController::class)->deleteUserOnServer([...]);
```

See [INTEGRATION.md](../../docs/INTEGRATION.md) · [root README](../../README.md)
