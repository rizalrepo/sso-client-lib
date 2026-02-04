# Changelog - SSO Client Lib

Semua perubahan penting pada package ini didokumentasikan di file ini.

Format berdasarkan [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
dan project ini mengikuti [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.3.2] - 2026-02-04

### Added

- **last_sso_profile_refresh**: Session menyimpan `last_sso_profile_refresh` di `connectUser()` agar client yang memakai middleware refresh profil (e.g. RefreshSsoProfile) tidak trigger refresh ganda setelah login.

### Changed

- **SSO role selection**: Dukungan pemilihan role dari portal SSO; `selected_role_id` dari session atau query `role_id`; pemilihan `oauth_client_role_id` dengan fallback ke role pertama.
- **Prodi sync dengan logging**: User baru—prodi null jika SSO kosong + Log warning; user existing—pertahankan prodi lama jika SSO kosong + Log warning, update + Log info jika prodi berubah.
- **Config env**: `sso.php` memakai `env('SSO_CALLBACK_URL')`, `env('SSO_URL')`, `env('SSO_CLIENT_ID')`, `env('SSO_CLIENT_SECRET')` dengan fallback/default.

### Fixed

- **SSOController::connectUser()**: Filter `oauth_client_users` by clientId; gunakan `oauth_client_role_id` dari role yang dipilih; penanganan prodi konsisten dengan logging (`[PRODI_SYNC]`).
- Tambah `use Illuminate\Support\Facades\Log` di SSOController.

**Diff:** https://github.com/rizalrepo/sso-client-lib/compare/1.3.1...v1.3.2
