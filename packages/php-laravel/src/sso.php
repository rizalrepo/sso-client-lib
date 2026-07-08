<?php

return [
    'callbackUrl' => env('SSO_CALLBACK_URL', env('APP_URL') . '/callback'),
    'serverUrl' => env('SSO_URL', 'https://sso-dev.unism.ac.id'),
    'clientId' => env('SSO_CLIENT_ID'),
    'clientSecret' => env('SSO_CLIENT_SECRET'),
];
