<?php

return [
    'callbackUrl' => env('SSO_CALLBACK_URL', env('APP_URL') . '/callback'),
    'serverUrl' => env('SSO_URL', 'https://sso-dev.unism.ac.id'),
    'clientId' => env('SSO_CLIENT_ID'),
    'clientSecret' => env('SSO_CLIENT_SECRET'),
    'user_model' => env('SSO_USER_MODEL', \App\Models\User::class),
    'register_routes' => env('SSO_REGISTER_ROUTES', true),
];
