<?php

namespace rizalrepo\SsoClient;

use Illuminate\Support\ServiceProvider;

class SSOClientServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/app.blade.php' => resources_path('views/sso/config.blade.php'),
        ], 'sso-controller');
        // $this->publishes([
        //     __DIR__ . '/Authenticate.php' => app_path('Http/Middleware/Authenticate.php'),
        // ], 'sso-controller');
        // $this->publishes([
        //     __DIR__ . '/routes.php' => routes_path('sso.php'),
        // ], 'sso-controller');
        // $this->publishes([
        //     __DIR__ . '/SSOController.php' => app_path('Http/Controllers/SSO/SSOController.php'),
        // ], 'sso-controller');
        // $this->publishes([
        //     __DIR__ . '/users_table.php' => database_path('sso/users_table_structure.php'),
        // ], 'sso-controller');
    }

    public function register()
    {
        //
    }
}
