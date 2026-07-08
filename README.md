# UNISM SSO Client SDK

**Current release:** [v2.0.0](md/RELEASE_NOTES_v2.0.0.md) – Multi-language SDK monorepo. [Changelog](md/CHANGELOG.md)

SDK multi-bahasa untuk integrasi aplikasi client ke **unism-sso** (Laravel Passport OAuth2).

## Quick Install

| Bahasa / Runtime | Package | Install |
|------------------|---------|---------|
| JavaScript / TypeScript | `@rizalrepo/sso-client` | `npm install @rizalrepo/sso-client` |
| PHP / Laravel | `rizalrepo/sso-client` | `composer require rizalrepo/sso-client` |
| PHP native | `rizalrepo/sso-client-core` | `composer require rizalrepo/sso-client-core` |
| Go, Python, Java, C#, dll. | OpenAPI spec | Generate dari `spec/openapi.yaml` — lihat [Bahasa lain](#bahasa-lain-go-python-java-dll) |

> Credential **wajib** di `.env`, jangan hardcode di source code.

## Konfigurasi (.env)

Semua SDK memakai env vars yang sama:

```env
SSO_URL=https://sirisa.unism.ac.id
SSO_CLIENT_ID=your-uuid-client-id
SSO_CLIENT_SECRET=your-client-secret
SSO_CALLBACK_URL=https://your-app.example.com/callback
```

---

## JavaScript / TypeScript

**Requirement:** Node 18+, Bun, Deno, atau browser dengan `fetch`

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

const state = sso.generateState();
// res.redirect(sso.getAuthorizeUrl(state));

const { token, user } = await sso.handleCallback(code);
const roleId = sso.resolveClientRoleId(user, selectedRoleId);

const result = await sso.verifyToken(accessToken);
```

📄 Detail: [packages/javascript/README.md](packages/javascript/README.md)

---

## PHP / Laravel

**Requirement:** PHP 8.1+, Laravel 10/11/12

```bash
composer require rizalrepo/sso-client
php artisan vendor:publish --tag=sso-config
```

Setelah publish, tambahkan routes di `web.php` dan sesuaikan migration `users`.

📄 Detail: [packages/php-laravel/README.md](packages/php-laravel/README.md)

---

## PHP Native (tanpa Laravel)

**Requirement:** PHP 8.1+, ext-curl, ext-json

Cocok untuk CodeIgniter, Slim, Symfony, atau plain PHP.

```bash
composer require rizalrepo/sso-client-core
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

📄 Detail: [packages/php-native/README.md](packages/php-native/README.md)

---

## Bahasa lain (Go, Python, Java, dll.)

SDK resmi belum tersedia per bahasa. Gunakan **OpenAPI spec** + panduan OAuth universal:

1. Generate client API dari `spec/openapi.yaml`
2. Implementasi OAuth manual (`/oauth/authorize`, `/oauth/token`) dari `docs/INTEGRATION.md`

```bash
# Install OpenAPI Generator: https://openapi-generator.tech
openapi-generator-cli generate -i spec/openapi.yaml -g python -o clients/python
openapi-generator-cli generate -i spec/openapi.yaml -g go -o clients/go
openapi-generator-cli generate -i spec/openapi.yaml -g java -o clients/java
openapi-generator-cli generate -i spec/openapi.yaml -g csharp -o clients/csharp
```

📄 Detail: [docs/INTEGRATION.md](docs/INTEGRATION.md)

---

## Install dari monorepo lokal (belum publish)

Jika package belum ada di Packagist / npm registry:

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

## Arsitektur

```
sso-client-lib/
├── spec/openapi.yaml          # Kontrak API /api/*
├── docs/INTEGRATION.md        # Panduan OAuth + integrasi universal
├── packages/
│   ├── javascript/            # @rizalrepo/sso-client
│   ├── php-laravel/           # rizalrepo/sso-client
│   └── php-native/            # rizalrepo/sso-client-core
└── md/                        # Release notes & changelog
```

## Changelog

Lihat [md/CHANGELOG.md](md/CHANGELOG.md) · [md/MULTI_LANGUAGE_SDK.md](md/MULTI_LANGUAGE_SDK.md)
