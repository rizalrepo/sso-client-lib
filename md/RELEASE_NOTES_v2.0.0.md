# Release Notes v2.0.0

**Release Date:** 8 Juli 2026  
**Type:** Major Release  
**Previous Version:** 1.3.3

## Ringkasan

Release ini mengubah `sso-client-lib` menjadi **monorepo multi-bahasa** untuk integrasi client ke UNISM SSO. Selain package Laravel yang sudah ada, kini tersedia SDK JavaScript/TypeScript, PHP native, OpenAPI spec, dan panduan integrasi universal.

## Package Baru

| Package | Install |
|---------|---------|
| `@rizalrepo/sso-client` | `npm install @rizalrepo/sso-client` |
| `rizalrepo/sso-client-core` | `composer require rizalrepo/sso-client-core` |
| `rizalrepo/sso-client` (Laravel) | `composer require rizalrepo/sso-client` |

## Perubahan

### Added

- **JavaScript/TypeScript SDK** (`packages/javascript`) — OAuth2 + API, zero runtime dependency (`fetch`).
- **PHP native SDK** (`packages/php-native`) — class `SSOClient` tanpa Laravel (ext-curl).
- **OpenAPI spec** (`spec/openapi.yaml`) — generate client Go, Python, Java, dll.
- **Integration guide** (`docs/INTEGRATION.md`) — panduan OAuth untuk semua bahasa.
- **README** diperbarui dengan quick install semua bahasa + install dari monorepo lokal.

### Changed

- **BREAKING**: Struktur repo monorepo; PHP Laravel source pindah ke `packages/php-laravel/src/`.
- Namespace `SSOClientServiceProvider` diperbaiki: `Rizalrepo\SsoClient` (PSR-4).
- PHP minimum `^8.1`; `illuminate/support` dan `illuminate/http` dideklarasikan eksplisit.

### Notes

- `composer require rizalrepo/sso-client` tetap kompatibel (autoload root `composer.json`).
- `rizalrepo/sso-client-core` adalah package Composer terpisah untuk PHP native.

## Upgrade Laravel

```bash
composer update rizalrepo/sso-client
php artisan vendor:publish --tag=sso-config --force
```

Jika sebelumnya memodifikasi `SSOController` manual di client, bandingkan dengan versi baru setelah publish.

## Publish

- **Packagist**: `rizalrepo/sso-client` — sync otomatis dari tag GitHub
- **Packagist**: `rizalrepo/sso-client-core` — daftar package terpisah di [packagist.org](https://packagist.org) dengan path `packages/php-native/composer.json`
- **npm**: `@rizalrepo/sso-client` — publish dari `packages/javascript`

---

**Full Changelog:** https://github.com/rizalrepo/sso-client-lib/compare/v1.3.3...v2.0.0
