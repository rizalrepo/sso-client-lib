# PHP Native SSO Client (`rizalrepo/sso-client-core`)

SDK PHP tanpa Laravel — untuk CodeIgniter, Slim, Symfony, atau PHP plain.

## Install

```bash
composer require rizalrepo/sso-client-core
```

**Requirement:** PHP 8.1+, ext-curl, ext-json

## Konfigurasi (.env)

```env
SSO_URL=https://sirisa.unism.ac.id
SSO_CLIENT_ID=your-uuid
SSO_CLIENT_SECRET=your-secret
SSO_CALLBACK_URL=https://your-app.example.com/callback
```

## Contoh (plain PHP)

```php
<?php
require 'vendor/autoload.php';

use Rizalrepo\SsoClient\SSOClient;

$sso = SSOClient::fromEnv();

// 1. Login — simpan state di $_SESSION, redirect user
$state = $sso->generateState();
$_SESSION['oauth_state'] = $state;
header('Location: ' . $sso->getAuthorizeUrl($state));
exit;

// 2. Callback
if ($_GET['state'] !== $_SESSION['oauth_state']) {
    die('Invalid state');
}
$result = $sso->handleCallback($_GET['code']);
$user = $result['user'];
$roleId = $sso->resolveClientRoleId($user, $_GET['role_id'] ?? null);

// 3. Verifikasi Bearer token (API)
$check = $sso->verifyToken($bearerToken);
if (!($check['valid'] ?? false)) {
    http_response_code(401);
    exit;
}
```

## Manual config (tanpa .env)

```php
$sso = new SSOClient([
    'serverUrl' => getenv('SSO_URL'),
    'clientId' => getenv('SSO_CLIENT_ID'),
    'clientSecret' => getenv('SSO_CLIENT_SECRET'),
    'callbackUrl' => getenv('SSO_CALLBACK_URL'),
]);
```

## Perbedaan dengan `rizalrepo/sso-client` (Laravel)

| | `sso-client-core` | `sso-client` (Laravel) |
|--|-------------------|------------------------|
| Framework | Tidak perlu | Laravel |
| Isi | Class `SSOClient` HTTP | ServiceProvider + SSOController publishable |
| Use case | API backend, CI, Slim, legacy PHP | App Laravel full-stack |

Panduan OAuth lengkap: [docs/INTEGRATION.md](../../docs/INTEGRATION.md)
