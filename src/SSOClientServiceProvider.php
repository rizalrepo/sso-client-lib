<?php

namespace rizalrepo\SsoClient;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;

class SSOClientServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishControllers();
    }

    protected function publishControllers()
    {
        $this->publishes([
            __DIR__ . '/SSOController.php' => App::path('Http/Controllers/SSO/SSOController.php'),
        ], 'sso-config');
    }

    public function register()
    {
        //
    }
}
