## v2.2.0 — Impersonate leave support

### Added

- Route `GET /sso/leave-impersonate` (`sso.leave-impersonate`)
- `SSOController::leaveImpersonate()` — local logout + redirect to SSO `/impersonate/leave`
- Session `sso_impersonator` from `GET /api/user` during connect

Requires **unism-sso** with impersonation feature (`/impersonate/leave` + `impersonator` in `/api/user`).

**Full changelog:** [CHANGELOG.md](md/CHANGELOG.md)

**Compare:** https://github.com/rizalrepo/sso-client-lib/compare/v2.1.2...v2.2.0
