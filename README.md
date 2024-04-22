# Client Usage Config

# publish controller

```
php artisan vendor:publish --tag=sso-config
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

for Laravel 11 add command :

```
php artisan make:middleware Authenticate
```

then update code bellow to Middleware/Authenticate.php

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

then copy code below to file bootstrap/app.php

```
$middleware->alias(['auth' => Authenticate::class]);
```

for Laravel 10 :
update code bellow to Middleware/Authenticate.php

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

use code bellow for direct url portal, edit-password and logout

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
