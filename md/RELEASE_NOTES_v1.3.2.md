# Release Notes v1.3.2

**Release Date:** 4 Februari 2026  
**Type:** Patch Release  
**Previous Version:** 1.3.1

## Ringkasan

Patch ini menyelaraskan SSO client dengan role selection dari portal, prodi sync + logging, config via env, dan session key untuk refresh profil.

## Perubahan

### Added

- Session `last_sso_profile_refresh` di `connectUser()` untuk mendukung middleware refresh profil di client (e.g. RefreshSsoProfile) tanpa refresh ganda setelah login.

### Changed

- **Role selection**: Baca `selected_role_id` dari session atau `role_id` dari query; filter client by clientId; pilih `oauth_client_role_id` dengan fallback ke role pertama.
- **Prodi sync**: User baru simpan prodi null jika SSO kosong (log warning); user existing pertahankan prodi jika SSO kosong (log warning), update jika berubah (log info).
- **sso.php**: Config memakai `env('SSO_CALLBACK_URL', env('APP_URL').'/callback')`, `env('SSO_URL', 'https://sso-dev.unism.ac.id')`, `env('SSO_CLIENT_ID')`, `env('SSO_CLIENT_SECRET')`.

### Fixed

- SSOController memakai role yang dipilih (bukan selalu role pertama); penanganan prodi konsisten; tambah `use Illuminate\Support\Facades\Log`.

## File yang Diubah

| File | Perubahan |
|------|-----------|
| `src/SSOController.php` | last_sso_profile_refresh, role selection, prodi sync + Log, tambah use Log. |
| `src/sso.php` | Config dari env dengan fallback. |
| `.gitignore` | Tambah `.cursor/`. |

## Breaking Changes

Tidak ada. Client yang sudah pakai config hardcoded perlu memindahkan nilai ke `.env` dan memakai key `SSO_CALLBACK_URL`, `SSO_URL`, `SSO_CLIENT_ID`, `SSO_CLIENT_SECRET` (opsional jika tetap publish dan edit `config/sso.php`).

## Upgrade

```bash
composer update rizalrepo/sso-client
```

Setelah update, sesuaikan config: gunakan env atau publish ulang `config/sso.php`.

---

**Full Changelog:** https://github.com/rizalrepo/sso-client-lib/compare/1.3.1...v1.3.2
