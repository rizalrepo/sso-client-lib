# Release v1.3.3

## Patch - OAuth hardening, avatar, role-based redirect

-   **Added**: Validasi `state` di OAuth callback dengan pesan error user-friendly dan pembersihan session saat gagal.
-   **Added**: Activity log untuk login via SSO, logout, dan perpindahan ke portal.
-   **Changed**: Pengambilan avatar memakai `avatar_url` dari API SSO dengan fallback default.
-   **Changed**: Redirect setelah login diarahkan ke dashboard sesuai `oauth_client_role_id` (berdasarkan konstanta role di model `User`).
-   **Fixed**: Token lama dihapus dari session sebelum menyimpan token baru untuk mencegah reuse token.

---

**Full Changelog:** https://github.com/rizalrepo/sso-client-lib/compare/v1.3.2...v1.3.3

