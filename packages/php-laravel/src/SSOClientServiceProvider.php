<?php

namespace Rizalrepo\SsoClient;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class SSOClientServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/SSOController.php' => App::path('Http/Controllers/SSO/SSOController.php'),
            __DIR__ . '/sso.php' => config_path('sso.php'),
        ], 'sso-config');
    }
}
