# Release v1.3.2

## Patch - Role selection, prodi sync, config env

- **Added**: Session `last_sso_profile_refresh` di connectUser untuk mendukung middleware refresh profil di client.
- **Changed**: Role selection dari portal (session/query role_id) dengan fallback; prodi sync dengan logging [PRODI_SYNC]; config sso dari env (SSO_URL, SSO_CLIENT_ID, dll.).
- **Fixed**: connectUser memakai oauth_client_role_id dari role yang dipilih; tambah use Log.

---

**Full Changelog:** https://github.com/rizalrepo/sso-client-lib/compare/1.3.1...v1.3.2
