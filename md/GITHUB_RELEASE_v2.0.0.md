# Release v2.0.0

## Major - Multi-language SDK monorepo

- **Added**: `@rizalrepo/sso-client` — JavaScript/TypeScript SDK (Node 18+, Bun, Deno, browser).
- **Added**: `rizalrepo/sso-client-core` — PHP native SDK tanpa framework.
- **Added**: `spec/openapi.yaml` + `docs/INTEGRATION.md` untuk integrasi semua bahasa.
- **Changed**: Monorepo structure — Laravel package di `packages/php-laravel/`.
- **Changed**: Namespace `Rizalrepo\SsoClient` diperbaiki; PHP minimum `^8.1`.

### Install

```bash
# Laravel
composer require rizalrepo/sso-client

# PHP native
composer require rizalrepo/sso-client-core

# JavaScript/TypeScript
npm install @rizalrepo/sso-client
```

---

**Full Changelog:** https://github.com/rizalrepo/sso-client-lib/compare/v1.3.3...v2.0.0
