# `rizalrepo/sso-client-core`

Framework-free PHP SDK for UNISM SSO. Uses `ext-curl` — no Laravel or other framework required.

**Requirements:** PHP 8.1+, `ext-curl`, `ext-json`

**Compatible with:** CodeIgniter, Slim, Symfony, plain PHP, legacy applications.

---

## Installation

```bash
composer require rizalrepo/sso-client-core:^2.0
```

**From local monorepo** (`composer.json`):

```json
{
  "repositories": [
    { "type": "path", "url": "../sso-client-lib/packages/php-native" }
  ],
  "require": { "rizalrepo/sso-client-core": "@dev" }
}
```

---

## Configuration

Store credentials in `.env`:

```env
SSO_URL=https://sirisa.unism.ac.id
SSO_CLIENT_ID=your-uuid
SSO_CLIENT_SECRET=your-secret
SSO_CALLBACK_URL=https://your-app.example.com/callback
```

---

## Quick start (plain PHP)

```php
<?php
require 'vendor/autoload.php';

use Rizalrepo\SsoClient\SSOClient;

$sso = SSOClient::fromEnv();

// 1. Login — store state, redirect user
$state = $sso->generateState();
$_SESSION['oauth_state'] = $state;
header('Location: ' . $sso->getAuthorizeUrl($state));
exit;

// 2. Callback — validate state, exchange code
if ($_GET['state'] !== $_SESSION['oauth_state']) {
    http_response_code(400);
    exit('Invalid state');
}

$result = $sso->handleCallback($_GET['code']);
$user   = $result['user'];
$roleId = $sso->resolveClientRoleId($user, $_GET['role_id'] ?? null);

// 3. API — verify Bearer token
$check = $sso->verifyToken($bearerToken);
if (!($check['valid'] ?? false)) {
    http_response_code(401);
    exit;
}
```

### Manual configuration (without `.env` helper)

```php
$sso = new SSOClient([
    'serverUrl'     => getenv('SSO_URL'),
    'clientId'      => getenv('SSO_CLIENT_ID'),
    'clientSecret'  => getenv('SSO_CLIENT_SECRET'),
    'callbackUrl'   => getenv('SSO_CALLBACK_URL'),
]);
```

---

## API reference

### OAuth

| Method | Description |
|--------|-------------|
| `generateState()` | Generate a CSRF-safe random state string |
| `getAuthorizeUrl($state, $roleId?)` | Build the SSO login redirect URL |
| `exchangeCodeForToken($code)` | Exchange authorization code for tokens |
| `handleCallback($code)` | Full flow: code → token → user profile |

### User & token

| Method | Description |
|--------|-------------|
| `getUser($accessToken)` | Fetch user profile |
| `verifyToken($accessToken)` | Lightweight token validation |
| `verifyTokenFull($accessToken)` | Token validation with username and role |
| `resolveClientRoleId($user, $roleId?)` | Resolve role ID for this client |

### Browser redirects

| Method | Returns |
|--------|---------|
| `getLogoutUrl()` | Global SSO logout URL |
| `getPortalUrl()` | Multi-app portal URL |
| `getProfileUrl()` | Profile edit URL |
| `getEditPasswordUrl()` | Password change URL |
| `defaultAvatarUrl($user?)` | Avatar URL with fallback |

### User management

| Method | Description |
|--------|-------------|
| `findUserByUsername($token, $username)` | Look up existing user |
| `createUser($token, $payload)` | Create SSO user |
| `assignClientRole($token, $userId, $roleId)` | Assign client role |
| `updateUser($token, $old, $new, $payload)` | Update user |
| `setUserActive($token, $username, $active)` | Toggle active status |
| `deleteUser($token, $username, $roleId)` | Remove from client |

---

## Comparison with Laravel package

| | `sso-client-core` | `rizalrepo/sso-client` (Laravel) |
|--|-------------------|----------------------------------|
| Framework | None | Laravel 10 / 11 / 12 |
| Contents | `SSOClient` HTTP class | ServiceProvider + publishable `SSOController` |
| Use case | API backends, legacy PHP, micro-frameworks | Full-stack Laravel apps with session sync |

---

## Further reading

- [OAuth integration guide](../../docs/INTEGRATION.md)
- [Root README](../../README.md)
