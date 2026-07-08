# Release Notes v2.0.0

**Release date:** July 8, 2026  
**Type:** Major release  
**Previous version:** 1.3.3

## Summary

This release transforms `sso-client-lib` into a **multi-language monorepo** for UNISM SSO client integration. In addition to the existing Laravel package, it adds JavaScript/TypeScript and native PHP SDKs, an OpenAPI specification, and a universal integration guide.

## New packages

| Package | Install |
|---------|---------|
| `@rizalrepo/sso-client` | `npm install @rizalrepo/sso-client` |
| `rizalrepo/sso-client-core` | `composer require rizalrepo/sso-client-core` |
| `rizalrepo/sso-client` (Laravel) | `composer require rizalrepo/sso-client:^2.0` |

## Changes

### Added

- **JavaScript/TypeScript SDK** (`packages/javascript`) — OAuth 2.0 + API client, zero runtime dependencies.
- **Native PHP SDK** (`packages/php-native`) — `SSOClient` class without Laravel (`ext-curl`).
- **OpenAPI spec** (`spec/openapi.yaml`) — generate clients for Go, Python, Java, etc.
- **Integration guide** (`docs/INTEGRATION.md`) — language-agnostic OAuth documentation.
- **Updated README** with quick-install instructions for all supported languages.

### Changed

- **BREAKING:** Monorepo structure; Laravel source moved to `packages/php-laravel/src/`.
- Fixed `SSOClientServiceProvider` namespace to `Rizalrepo\SsoClient` (PSR-4).
- PHP minimum version raised to `^8.1`; `illuminate/support` and `illuminate/http` declared explicitly.

### Notes

- `composer require rizalrepo/sso-client` remains compatible via root `composer.json` autoload.
- `rizalrepo/sso-client-core` is a separate Composer package for native PHP.

## Upgrading (Laravel)

```bash
composer update rizalrepo/sso-client
php artisan vendor:publish --tag=sso-config --force
```

If you previously customized `SSOController` manually, compare it with the newly published version after upgrade.

## Publishing status

| Registry | Package | Status |
|----------|---------|--------|
| Packagist | `rizalrepo/sso-client` | Available at v2.0.0 |
| Packagist | `rizalrepo/sso-client-core` | Requires separate registration |
| npm | `@rizalrepo/sso-client` | Run `npm publish` from `packages/javascript` |

---

**Full changelog:** https://github.com/rizalrepo/sso-client-lib/compare/v1.3.3...v2.0.0
