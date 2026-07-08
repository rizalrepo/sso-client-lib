# Changelog

All notable changes are documented here. Format: [Keep a Changelog](https://keepachangelog.com/).

## [2.1.1] - 2026-07-08

### Added

- Laravel: `php artisan sso:install` to publish config + controller stub and print required `.env` keys.

## [2.1.0] - 2026-07-08

### Added

- Laravel: auto-register SSO routes & config via `SSOClientServiceProvider` — satu perintah `composer require`.
- Config keys: `user_model`, `register_routes`.
- Optional publish: stub `App\Http\Controllers\SSO\SSOController` extends package controller.

### Changed

- Unified package: `SSOClient` (native) + Laravel integration in satu `rizalrepo/sso-client`.
- Controller dipindah ke `Rizalrepo\SsoClient\Http\Controllers\SSOController`.
- Removed dependency `rizalrepo/sso-client-core` (merged into root autoload).

## [2.0.2] - 2026-07-08

### Changed

- `SSOClient` OAuth-only; CRUD via generic `api()` (PHP) or Laravel controller.
- Laravel: lazy client cache, `token()` helper, simplified user sync (no PRODI logging).
- Removed `fromEnv()`, `handleCallback()`, `defaultAvatarUrl()`, configurable `scope`.
- JS: `crypto.randomUUID()` for state, unified `ssoUrl()` in fetch.
- Laravel `require`s `rizalrepo/sso-client-core` (no duplicate autoload).
- Package READMEs trimmed to links.

## [2.0.1] - 2026-07-08

### Changed

- Laravel `SSOController` delegates to `SSOClient` (no duplicate HTTP logic).
- PHP HTTP via `file_get_contents` instead of `curl` (`ext-curl` no longer required).
- Merged `verifyToken` + `verifyTokenFull` into `verifyToken($token, $full)`.
- Replaced four URL getters with `ssoUrl($path)`.
- JS SDK trimmed to OAuth essentials (removed user-management CRUD).
- Removed vendored `openapi.yaml` — use `{SSO_URL}/developer/openapi.yaml`.
- Consolidated docs; removed duplicate release-note files.

### Removed

- `packages/javascript/src/index.ts` barrel, `types.ts` (merged into `client.ts`).
- `packages/php-laravel/composer.json` (root `composer.json` is canonical).
- `md/MULTI_LANGUAGE_SDK.md`, `md/RELEASE_*`, `md/GITHUB_RELEASE_*`.

## [2.0.0] - 2026-07-08

### Added

- **Multi-language monorepo**: `packages/javascript`, `packages/php-laravel`, `packages/php-native`.
- **OpenAPI spec**: `spec/openapi.yaml` for generating clients in other languages.
- **Integration guide**: `docs/INTEGRATION.md` — universal OAuth documentation.

### Changed

- **BREAKING**: Monorepo structure; PHP Laravel source moved to `packages/php-laravel/src/`.
- Fixed `SSOClientServiceProvider` namespace to `Rizalrepo\SsoClient`.
- PHP minimum `^8.1`; `illuminate/support` and `illuminate/http` declared explicitly.

### Notes

- `composer require rizalrepo/sso-client` remains compatible (root `composer.json` autoload).

**Diff:** https://github.com/rizalrepo/sso-client-lib/compare/v1.3.3...v2.0.0

## [1.3.3] - 2026-03-12

### Added

-   **OAuth state & token validation**: Validasi `state` di callback dengan pesan error user-friendly dan pembersihan session saat gagal.
-   **Activity logging**: Logging aktivitas login SSO, logout, dan portal switch menggunakan activity log di client.

### Changed

-   **Avatar handling**: Gunakan `avatar_url` dari API SSO dengan fallback avatar default.
-   **Redirect by role**: Setelah login, redirect ke dashboard sesuai `oauth_client_role_id` mengikuti konstanta role di model `User`.

### Fixed

-   **Token reuse prevention**: Pastikan token lama dihapus dari session sebelum menyimpan token baru, cegah reuse session lama.

**Diff:** https://github.com/rizalrepo/sso-client-lib/compare/v1.3.2...v1.3.3

## [1.3.2] - 2026-02-04

### Added

-   **last_sso_profile_refresh**: Session menyimpan `last_sso_profile_refresh` di `connectUser()` agar client yang memakai middleware refresh profil (e.g. RefreshSsoProfile) tidak trigger refresh ganda setelah login.

### Changed

-   **SSO role selection**: Dukungan pemilihan role dari portal SSO; `selected_role_id` dari session atau query `role_id`; pemilihan `oauth_client_role_id` dengan fallback ke role pertama.
-   **Prodi sync dengan logging**: User baru—prodi null jika SSO kosong + Log warning; user existing—pertahankan prodi lama jika SSO kosong + Log warning, update + Log info jika prodi berubah.
-   **Config env**: `sso.php` memakai `env('SSO_CALLBACK_URL')`, `env('SSO_URL')`, `env('SSO_CLIENT_ID')`, `env('SSO_CLIENT_SECRET')` dengan fallback/default.

### Fixed

-   **SSOController::connectUser()**: Filter `oauth_client_users` by clientId; gunakan `oauth_client_role_id` dari role yang dipilih; penanganan prodi konsisten dengan logging (`[PRODI_SYNC]`).
-   Tambah `use Illuminate\Support\Facades\Log` di SSOController.

**Diff:** https://github.com/rizalrepo/sso-client-lib/compare/1.3.1...v1.3.2
