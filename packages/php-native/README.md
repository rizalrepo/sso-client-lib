# `rizalrepo/sso-client-core`

Framework-free PHP SDK. **Requires:** PHP 8.1+, `ext-json`.

```bash
composer require rizalrepo/sso-client-core:^2.0
```

## Usage

```php
use Rizalrepo\SsoClient\SSOClient;

$sso = SSOClient::fromEnv();

$state = $sso->generateState();
$_SESSION['oauth_state'] = $state;
header('Location: ' . $sso->getAuthorizeUrl($state));

$result = $sso->handleCallback($_GET['code']);
$roleId = $sso->resolveClientRoleId($result['user']);
```

## API

| Method | Description |
|--------|-------------|
| `fromEnv()` | Create from `$_ENV` |
| `generateState()` | CSRF state |
| `getAuthorizeUrl($state, $roleId?)` | Login URL |
| `exchangeCodeForToken($code)` | Code → tokens |
| `handleCallback($code)` | Code → token + user |
| `getUser($token)` | User profile |
| `verifyToken($token, $full = false)` | Validate token |
| `resolveClientRoleId($user, $roleId?)` | Resolve role |
| `ssoUrl($path)` | Build SSO URL |
| `createUser`, `updateUser`, `deleteUser`, … | User management (Laravel apps use these via `SSOController`) |

OAuth protocol: [docs/INTEGRATION.md](../../docs/INTEGRATION.md)
