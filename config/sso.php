<?php

return [
    'routes' => [
        'web' => [
            'prefix' => 'sso',
            'routes' => [
                ['method' => 'get', 'uri' => 'login', 'action' => 'SSOController@getLogin', 'name' => 'sso.login'],
                ['method' => 'get', 'uri' => 'callback', 'action' => 'SSOController@getCallback', 'name' => 'sso.callback'],
                ['method' => 'get', 'uri' => 'connect', 'action' => 'SSOController@connectUser', 'name' => 'sso.connect'],
                ['method' => 'get', 'uri' => 'logout', 'action' => 'SSOController@logout', 'name' => 'sso.logout'],
                ['method' => 'get', 'uri' => 'edit-password', 'action' => 'SSOController@editPassword', 'name' => 'sso.edit-password'],
                ['method' => 'get', 'uri' => 'portal', 'action' => 'SSOController@portal', 'name' => 'sso.portal'],
            ],
        ],
    ],
];
