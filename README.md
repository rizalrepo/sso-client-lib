# Client Usage Config

#installation

```
composer require rizalrepo/sso-client
```

# Configuration

publish SSOController with run command

```
php artisan vendor:publish --tag=sso-config
```

# Connect with SSO

open config/sso.php and adjust config with your preference

```
return [
    'callbackUrl' => "http://127.0.0.1:8000/callback",
    'serverUrl' => "http://127.0.0.1:8081",
    'clientId' => "f9c2bbad-c06d-4028-9786-213c9113ddbb",
    'clientSecret' => "1zJyzTcLmL05ZzMOnaMI6DfhaY9guJLCKBisH4YS",
];
```

# Routes

add code to web.php

```
Route::controller(SSOController::class)->group(function () {
    Route::get("/", 'ssoPage');
    Route::get("/sso/login", 'getLogin')->name("sso.login");
    Route::get("/callback", 'getCallback')->name("sso.callback");
    Route::get("/sso/connect", 'connectUser')->name("sso.connect");

    Route::middleware('auth')->group(function () {
        Route::get("/sso/logout", 'logout')->name("sso.logout");
        Route::get("/sso/edit-password", 'editPassword')->name("sso.edit-password");
        Route::get("/sso/portal", 'portal')->name("sso.portal");
        Route::get("/sso/profile", 'editProfile')->name("sso.profile");
    });
});
```

# Table

modify file users migration with :

```
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

# Middleware Settings

-   for Laravel 11 add command :

```
php artisan make:middleware Authenticate
```

-   then update code bellow to Middleware/Authenticate.php and adjust config with your preference

```
<?php
namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    private function getConfig($configName)
    {
        switch ($configName) {
            case 'serverUrl':
                return "http://127.0.0.1:8000/login";
            default:
                return null;
        }
    }

    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : $this->getConfig('serverUrl');
    }
}
```

-   then copy code below to file bootstrap/app.php

```
$middleware->alias(['auth' => Authenticate::class]);
```

-   for Laravel 10 : update code bellow to Middleware/Authenticate.php and adjust config with your preference

```
<?php
namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    private function getConfig($configName)
    {
        switch ($configName) {
            case 'serverUrl':
                return "http://127.0.0.1:8000/login";
            default:
                return null;
        }
    }

    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : $this->getConfig('serverUrl');
    }
}
```

# Views Config

-   use code bellow for show user avatar, direct url portal, update profile, edit-password and logout

```
{{-- in app blade --}}

<div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
    <img class="b-r-10 avatar-image" src="{{ session('avatar') ? session('avatar') : asset('/assets/images/dashboard/profile.png')  }}" alt="Logo">
    @if(session()->has('countAccess'))
        @if (session('countAccess') > 1)
            <a class="dropdown-item" href="{{ route('sso.portal') }}">Portal</a>
        @endif
    @endif
    <a href="{{ route('sso.profile') }}" onclick="saveReferrer()"><i class="fas fa-user-edit me-2"></i><span>Edit Profile</span></a>
    <a class="dropdown-item" href="{{ route('sso.edit-password') }}" onclick="saveReferrer()">
        Edit Password
    </a>
    <a class="dropdown-item" href="{{ route('sso.logout') }}"
        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
        {{ __('Logout') }}
    </a>

    <form id="logout-form" action="{{ route('sso.logout') }}" method="GET" class="d-none">
        @csrf
    </form>
</div>

{{-- previous url config in js.blade --}}

<script>
    function saveReferrer() {
        var previousUrl = document.referrer;
        var previousUrlInput = document.getElementById("previous_url");
        if (previousUrlInput) {
            previousUrlInput.value = previousUrl;
        }
    }
</script>
```

# User Controller for Client

-   add this code to store function after user created

```
$ssoController = new \App\Http\Controllers\SSO\SSOController();
$userArray = [
    'name' => $user->name,
    'username' => $user->username,
    'phone' => $user->phone,
    'oauth_client_role_id' => $user->oauth_client_role_id,
];

$ssoController->createUserOnServer($userArray);
```

-   add this code to update function after user updated

```
$oldUsername = $user->username; // this code add before update()

$updatedUserArray = [
    'name' => $user->name,
    'username' => $user->username,
    'phone' => $user->phone,
    'old_username' => $oldUsername,
];

$ssoController = new \App\Http\Controllers\SSO\SSOController();
$ssoController->updateUserOnServer($updatedUserArray);
```

-   add this code to destroy function after user deleted

```
$username = $data->username; // this code add before delete()

$ssoController = new \App\Http\Controllers\SSO\SSOController();
$ssoController->deleteUserOnServer($username);
```

# Verify Api Token from Client

-   create new middleware with run command :

```
php artisan make:middleware VerifyApiToken
```

-   then open file VerifyApiToken and replace with the code below

```
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
            Log::warning('No bearer token provided and no access token in session');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $serverUrl = Config::get('sso.serverUrl');
            $response = Http::timeout(5)->withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ])->get($serverUrl . '/api/verify-token');

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

-   add middleware aliases to Kernel.php for Laravel 10 or to bootstrap/app.php for Laravel 11

```
'verify.api.token' => \App\Http\Middleware\VerifyApiToken::class,
```
