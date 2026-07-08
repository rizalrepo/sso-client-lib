# UNISM SSO Client SDK

Official client libraries for integrating applications with **UNISM SSO** (`unism-sso`), an OAuth 2.0 server powered by Laravel Passport.

**Current release:** [v2.0.0](md/RELEASE_NOTES_v2.0.0.md) · [Changelog](md/CHANGELOG.md)

---

## Overview

This repository is a **multi-language monorepo**. Each language ships a thin HTTP client on top of the same OAuth 2.0 protocol:

| Language / Runtime | Package | Install |
|--------------------|---------|---------|
| JavaScript / TypeScript | `@rizalrepo/sso-client` | `npm install @rizalrepo/sso-client` |
| PHP / Laravel | `rizalrepo/sso-client` | `composer require rizalrepo/sso-client` |
| PHP (native) | `rizalrepo/sso-client-core` | `composer require rizalrepo/sso-client-core` |
| Go, Python, Java, C#, … | OpenAPI spec | Generate from `spec/openapi.yaml` — see [Other languages](#other-languages) |

> **Security:** Store credentials in `.env`. Never hardcode `SSO_CLIENT_SECRET` in source code.

---

## Configuration

All SDKs use the same environment variables:

```env
SSO_URL=https://sirisa.unism.ac.id
SSO_CLIENT_ID=your-uuid-client-id
SSO_CLIENT_SECRET=your-client-secret
SSO_CALLBACK_URL=https://your-app.example.com/callback
```

| Variable | Description |
|----------|-------------|
| `SSO_URL` | Base URL of the SSO server (no trailing slash) |
| `SSO_CLIENT_ID` | OAuth client UUID (from SSO admin panel) |
| `SSO_CLIENT_SECRET` | OAuth client secret (shown once at registration) |
| `SSO_CALLBACK_URL` | Must exactly match the redirect URI registered in SSO |

---

## JavaScript / TypeScript

**Requirements:** Node.js 18+, Bun, Deno, or any runtime with native `fetch`.

```bash
npm install @rizalrepo/sso-client
```

```typescript
import { SSOClient } from "@rizalrepo/sso-client";

const sso = new SSOClient({
  serverUrl: process.env.SSO_URL!,
  clientId: process.env.SSO_CLIENT_ID!,
  clientSecret: process.env.SSO_CLIENT_SECRET!,
  callbackUrl: process.env.SSO_CALLBACK_URL!,
});

// 1. Start login — store state in session, then redirect
const state = sso.generateState();
// res.redirect(sso.getAuthorizeUrl(state));

// 2. Handle callback
const { token, user } = await sso.handleCallback(code);
const roleId = sso.resolveClientRoleId(user, selectedRoleId);

// 3. Verify Bearer token (API middleware)
const result = await sso.verifyToken(accessToken);
```

📖 Full reference: [packages/javascript/README.md](packages/javascript/README.md)

---

## PHP / Laravel

**Requirements:** PHP 8.1+, Laravel 10 / 11 / 12

```bash
composer require rizalrepo/sso-client:^2.0
php artisan vendor:publish --tag=sso-config
```

After publishing, add routes to `web.php` and update your `users` migration.

📖 Full reference: [packages/php-laravel/README.md](packages/php-laravel/README.md)

---

## PHP Native (no framework)

**Requirements:** PHP 8.1+, `ext-curl`, `ext-json`

Works with CodeIgniter, Slim, Symfony, or plain PHP.

```bash
composer require rizalrepo/sso-client-core:^2.0
```

```php
use Rizalrepo\SsoClient\SSOClient;

$sso = SSOClient::fromEnv();

$state = $sso->generateState();
$_SESSION['oauth_state'] = $state;
header('Location: ' . $sso->getAuthorizeUrl($state));

$result = $sso->handleCallback($_GET['code']);
$user = $result['user'];
```

📖 Full reference: [packages/php-native/README.md](packages/php-native/README.md)

---

## Other languages

Official SDKs are not provided for every language. Use the **OpenAPI spec** plus the universal OAuth guide:

1. Generate an API client from `spec/openapi.yaml`
2. Implement OAuth manually (`/oauth/authorize`, `/oauth/token`) using [docs/INTEGRATION.md](docs/INTEGRATION.md)

```bash
# https://openapi-generator.tech
openapi-generator-cli generate -i spec/openapi.yaml -g python -o clients/python
openapi-generator-cli generate -i spec/openapi.yaml -g go         -o clients/go
openapi-generator-cli generate -i spec/openapi.yaml -g java       -o clients/java
openapi-generator-cli generate -i spec/openapi.yaml -g csharp     -o clients/csharp
```

📖 Full protocol reference: [docs/INTEGRATION.md](docs/INTEGRATION.md)

---

## Local development (monorepo)

Use these commands when packages are not yet published to Packagist or npm:

**Composer (Laravel):**

```json
{
  "repositories": [
    { "type": "path", "url": "../sso-client-lib/packages/php-laravel" }
  ],
  "require": { "rizalrepo/sso-client": "@dev" }
}
```

**Composer (PHP native):**

```json
{
  "repositories": [
    { "type": "path", "url": "../sso-client-lib/packages/php-native" }
  ],
  "require": { "rizalrepo/sso-client-core": "@dev" }
}
```

**npm:**

```bash
npm install ../sso-client-lib/packages/javascript
```

---

## Repository layout

```
sso-client-lib/
├── spec/openapi.yaml          # API contract for /api/*
├── docs/INTEGRATION.md        # Universal OAuth integration guide
├── packages/
│   ├── javascript/            # @rizalrepo/sso-client
│   ├── php-laravel/           # rizalrepo/sso-client
│   └── php-native/            # rizalrepo/sso-client-core
└── md/                        # Changelog & release notes
```

---

## Documentation index

| Document | Description |
|----------|-------------|
| [docs/INTEGRATION.md](docs/INTEGRATION.md) | Language-agnostic OAuth 2.0 protocol |
| [md/MULTI_LANGUAGE_SDK.md](md/MULTI_LANGUAGE_SDK.md) | v2.0 architecture & migration notes |
| [md/CHANGELOG.md](md/CHANGELOG.md) | Version history |
| [md/RELEASE_NOTES_v2.0.0.md](md/RELEASE_NOTES_v2.0.0.md) | v2.0.0 release details |

---

## License

MIT
