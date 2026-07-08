# Multi-Language SDK — sso-client-lib v2.0

**Date:** 2026-07-08

## Summary

`sso-client-lib` was restructured from a PHP-only Laravel package into a **multi-language monorepo** for integrating client applications with `unism-sso`.

A single library file cannot run in every programming language. The solution is:

1. **HTTP contract** — `spec/openapi.yaml` (covers `/api/*` endpoints)
2. **Universal OAuth guide** — `docs/INTEGRATION.md` (covers `/oauth/*` flow)
3. **Per-language SDKs** — thin HTTP clients built on standard OAuth 2.0

---

## Repository structure

```
sso-client-lib/
├── spec/openapi.yaml
├── docs/INTEGRATION.md
├── packages/
│   ├── javascript/     → @rizalrepo/sso-client (npm)
│   ├── php-laravel/    → rizalrepo/sso-client (Composer)
│   └── php-native/     → rizalrepo/sso-client-core (Composer)
└── composer.json       → backward-compatible root package
```

---

## Available packages

| Language | Package | Status |
|----------|---------|--------|
| JavaScript / TypeScript | `@rizalrepo/sso-client` | **New in v2.0** — Node 18+, Bun, Deno, browser |
| PHP (native) | `rizalrepo/sso-client-core` | **New in v2.0** — no framework (`ext-curl`) |
| PHP / Laravel | `rizalrepo/sso-client` | **Maintained** — moved to `packages/php-laravel/` |
| Go, Python, Java, … | Generated from OpenAPI | Follow `docs/INTEGRATION.md` |

---

## Breaking changes in v2.0

| Change | Impact |
|--------|--------|
| Monorepo folder structure | Direct imports from `src/` no longer valid |
| Namespace fix | `Rizalrepo\SsoClient` (PSR-4 compliant) |
| PHP minimum version | `^8.1` (was `^7.4`) |
| Explicit Laravel deps | `illuminate/support` and `illuminate/http` declared |

`composer require rizalrepo/sso-client` continues to work via the root `composer.json` autoload path.

---

## JavaScript SDK features

- OAuth: `generateState()`, `getAuthorizeUrl()`, `exchangeCodeForToken()`, `handleCallback()`
- User: `getUser()`, `verifyToken()`, `verifyTokenFull()`, `resolveClientRoleId()`
- User management: `createUser()`, `assignClientRole()`, `updateUser()`, `deleteUser()`
- Zero runtime dependencies (native `fetch`)

---

## Migrating from a local `ssoService.ts`

Replace project-local implementations (e.g. in `unism-presensi`) with the published package:

```bash
npm install @rizalrepo/sso-client
```

```typescript
import { SSOClient } from "@rizalrepo/sso-client";

const sso = new SSOClient({ serverUrl, clientId, clientSecret, callbackUrl });
```

---

## Other languages

```bash
openapi-generator-cli generate -i spec/openapi.yaml -g python -o clients/python
```

Implement OAuth Steps 1–2 manually per [docs/INTEGRATION.md](../docs/INTEGRATION.md).
