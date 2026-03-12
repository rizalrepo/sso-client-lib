# Release Notes v1.3.3

**Release Date:** 12 Maret 2026  
**Type:** Patch Release  
**Previous Version:** 1.3.2

## Ringkasan

Patch ini menyelaraskan `SSOController` library dengan implementasi terbaru di client (mis. unism-santi) untuk keamanan OAuth, penanganan avatar, dan alur redirect berbasis role, sekaligus menambah logging aktivitas penting.

## Perubahan

### Added

-   Validasi `state` di callback dengan pesan error yang jelas ke user dan redirect ulang ke `sso.login` jika tidak valid.
-   Logging aktivitas login via SSO, logout, dan perpindahan ke portal menggunakan activity log di sisi client.

### Changed

-   Pengambilan avatar user memakai `avatar_url` dari API SSO dengan fallback ke avatar default bawaan SSO.
-   Alur redirect setelah `Auth::login()` diarahkan ke dashboard sesuai `oauth_client_role_id` menggunakan konstanta role pada model `User`.

### Fixed

-   Penghapusan token lama di session sebelum menyimpan token baru, untuk mencegah reuse token/session lama pada proses autentikasi berikutnya.

## File yang Diubah

| File                    | Perubahan                                                                    |
| ----------------------- | ---------------------------------------------------------------------------- |
| `src/SSOController.php` | Validasi state & token, avatar_url + fallback, activity log, redirect by role. |

## Breaking Changes

Tidak ada perubahan breaking di level API package. Namun, agar fitur redirect berbasis role dan activity log berjalan optimal:

-   Client disarankan memiliki konstanta role di model `User` (mis. `ROLE_SUPERADMIN`, `ROLE_ADMIN_BAAK`, dll.) yang konsisten dengan implementasi SSO.
-   Client yang tidak menggunakan activity log dapat menyesuaikan atau menghapus pemanggilan `activity()` setelah publish controller.

## Upgrade

```bash
composer update rizalrepo/sso-client
```

Setelah update, publish ulang atau sesuaikan `SSOController` di client jika sebelumnya telah dimodifikasi manual, terutama pada bagian callback, connectUser, dan alur redirect setelah login.

---

**Full Changelog:** https://github.com/rizalrepo/sso-client-lib/compare/v1.3.2...v1.3.3

