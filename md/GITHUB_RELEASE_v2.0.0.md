# Release v2.0.0

## Major — Multi-language SDK monorepo

- **Added**: `@rizalrepo/sso-client` — JavaScript/TypeScript SDK (Node 18+, Bun, Deno, browser).
- **Added**: `rizalrepo/sso-client-core` — native PHP SDK (no framework).
- **Added**: `spec/openapi.yaml` + `docs/INTEGRATION.md` for universal integration.
- **Changed**: Monorepo structure — Laravel package at `packages/php-laravel/`.
- **Changed**: Fixed `Rizalrepo\SsoClient` namespace; PHP minimum `^8.1`.

### Install

```bash
# Laravel
composer require rizalrepo/sso-client:^2.0

# PHP native
composer require rizalrepo/sso-client-core:^2.0

# JavaScript / TypeScript
npm install @rizalrepo/sso-client
```

---

**Full changelog:** https://github.com/rizalrepo/sso-client-lib/compare/v1.3.3...v2.0.0
