# `rizalrepo/sso-client-core`

PHP OAuth2 client without a framework. See [INTEGRATION.md](../../docs/INTEGRATION.md).

```bash
composer require rizalrepo/sso-client-core:^2.0
```

```php
$sso = new Rizalrepo\SsoClient\SSOClient([
    'serverUrl' => getenv('SSO_URL'),
    'clientId' => getenv('SSO_CLIENT_ID'),
    'clientSecret' => getenv('SSO_CLIENT_SECRET'),
    'callbackUrl' => getenv('SSO_CALLBACK_URL'),
]);
```

User-management API: `$sso->api($method, $path, $token, $json)`.
