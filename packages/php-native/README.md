# `rizalrepo/sso-client-core`

PHP OAuth2 client without a framework (CodeIgniter, Slim, plain PHP).

```bash
composer require rizalrepo/sso-client-core:^2.0
```

```php
use Rizalrepo\SsoClient\SSOClient;

$sso = new SSOClient([
    'serverUrl' => getenv('SSO_URL'),
    'clientId' => getenv('SSO_CLIENT_ID'),
    'clientSecret' => getenv('SSO_CLIENT_SECRET'),
    'callbackUrl' => getenv('SSO_CALLBACK_URL'),
]);
```

## User data after login

After `exchangeCodeForToken` + `getUser`, store in `$_SESSION`:

| Key | Source | Use for |
|-----|--------|---------|
| `username` | `$user['username']` | Display NIP / username |
| `name` | `$user['name']` | Display full name |
| `avatar` | `$user['avatar_url']` | Profile photo |
| `countAccess` | `count($user['oauth_client_users'])` | Show Portal if > 1 |

**Avatar fallback:**

```php
$avatar = $user['avatar_url'] ?? $sso->ssoUrl('assets/images/dashboard/profile.png');
```

## SSO links (profile, password, portal, logout)

Redirect browser to SSO — profile and password are edited on SSO, not in your app:

| Action | PHP |
|--------|-----|
| Edit profile | `header('Location: ' . $sso->ssoUrl('profile'));` |
| Edit password | `header('Location: ' . $sso->ssoUrl('edit-password'));` |
| Portal | `header('Location: ' . $sso->ssoUrl('portal'));` |
| Global logout | Clear `$_SESSION`, then redirect to `$sso->ssoUrl('sso/logout')` |

## Plain PHP example (HTML view)

```php
<?php
session_start();
require 'vendor/autoload.php';

use Rizalrepo\SsoClient\SSOClient;

$sso = new SSOClient([/* serverUrl, clientId, clientSecret, callbackUrl */]);

// --- callback.php (after OAuth redirect) ---
if (isset($_GET['code'])) {
    if ($_GET['state'] !== ($_SESSION['oauth_state'] ?? '')) {
        exit('Invalid state');
    }
    $token = $sso->exchangeCodeForToken($_GET['code']);
    $user = $sso->getUser($token['access_token']);

    $_SESSION['user'] = [
        'username' => $user['username'],
        'name' => $user['name'],
        'avatar' => $user['avatar_url'] ?? $sso->ssoUrl('assets/images/dashboard/profile.png'),
        'countAccess' => count($user['oauth_client_users'] ?? []),
    ];
    $_SESSION['access_token'] = $token['access_token'];
    header('Location: /dashboard.php');
    exit;
}

// --- dashboard.php ---
$user = $_SESSION['user'] ?? null;
if (!$user) {
    header('Location: /login.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<body>
    <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="avatar" width="48">
    <p><?= htmlspecialchars($user['username']) ?> — <?= htmlspecialchars($user['name']) ?></p>

    <?php if ($user['countAccess'] > 1): ?>
        <a href="<?= $sso->ssoUrl('portal') ?>">Portal</a>
    <?php endif; ?>

    <a href="<?= $sso->ssoUrl('profile') ?>">Edit Profile</a>
    <a href="<?= $sso->ssoUrl('edit-password') ?>">Edit Password</a>
    <a href="/logout.php">Logout</a>
</body>
</html>

<?php
// --- logout.php ---
session_start();
session_destroy();
header('Location: ' . $sso->ssoUrl('sso/logout'));
exit;
```

## Refresh avatar after SSO profile edit

When user returns from SSO profile page, call `getUser($_SESSION['access_token'])` again and update `$_SESSION['user']['avatar']`.

User-management API: `$sso->api($method, $path, $token, $json)`.

See [root README](../../README.md) · [INTEGRATION.md](../../docs/INTEGRATION.md)
