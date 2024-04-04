<?php

/**
 * untuk laravel 11 :
 * ketikkan command php artisan make:middleware Authenticate kemudian Copy code dibawah ini kemudian
 * akses file bootstrap/app.php lalu copy 'Authenticate' => \App\Http\Middleware\Authenticate::class,
 * 
 * 
 * untuk laravel 10 :
 * Copy code dibawah ini dan edit file Authenticate.php
 */

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
