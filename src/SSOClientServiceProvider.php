<?php

namespace rizalrepo\SSOClient;

use Illuminate\Support\ServiceProvider;

class SSOClientServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
        // $this->publishes([
        //     __DIR__ . '/SSOController.php' => $this->getControllerPath('SSOController.php'),
        // ], 'sso-controller');

        $this->publishes([
            __DIR__ . '/path/to/controller' => app_path('Http/Controllers/SSOController.php'),
        ]);
    }

    public function register()
    {
        //
    }

    // protected function getControllerPath($fileName)
    // {
    //     return realpath(__DIR__ . '/../../app/Http/Controllers/SSO/') . '/' . $fileName;
    // }
}
