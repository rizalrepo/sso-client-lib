# Client Usage Config

# publish controller

```
php artisan vendor:publish --tag=sso-config
```

# SSOController

open SSOController.php and adjust config with your preference

```
private function getConfig($configName)
{
    switch ($configName) {
        case 'callbackUrl':
            return "http://127.0.0.1:8080/callback";
        case 'serverUrl':
            return "http://127.0.0.1:8000";
        case 'clientId':
            return "a4cf7da2-0af1-4137-9bee-498bf9ab64c5";
        case 'clientSecret':
            return "UzZ5LiZSEqaU4TO4fr46sS8ENPOjK0wdQ4AiyMZY";
        default:
            return null;
    }
}
```

# Routes

add code to web.php

```
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
```

# Table

modify file users migration with :

```
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
```

# Middleware Settings

* for Laravel 11 add command :

```
php artisan make:middleware Authenticate
```

* then update code bellow to Middleware/Authenticate.php and adjust config with your preference

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

* then copy code below to file bootstrap/app.php

```
$middleware->alias(['auth' => Authenticate::class]);
```

* for Laravel 10 : update code bellow to Middleware/Authenticate.php and adjust config with your preference

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

* use code bellow for direct url portal, edit-password and logout

```
{{-- in app blade --}}

<div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
    @if(session()->has('countAccess'))
        @if (session('countAccess') > 1)
            <a class="dropdown-item" href="{{ route('sso.portal') }}">Portal</a>
        @endif
    @endif
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


* add this code to store function after user created
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
* add this code to update function after user updated
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
* add this code to destroy function after user deleted
```
$username = $data->username; // this code add before delete()

$ssoController = new \App\Http\Controllers\SSO\SSOController();
$ssoController->deleteUserOnServer($username);
```
