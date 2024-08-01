<?php

namespace rizalrepo\SsoClient;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

class SSOClientServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishConfig();
    }

    protected function publishConfig()
    {
        $this->publishes([
            __DIR__ . '/SSOController.php' => App::path('Http/Controllers/SSO/SSOController.php'),
            __DIR__ . '/sso.php' => Config::path('sso.php'),
        ], 'sso-config');
    }

    public function register()
    {
        //
    }
}
