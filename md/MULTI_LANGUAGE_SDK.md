# Multi-Language SDK — sso-client-lib v2.0

**Tanggal:** 2026-07-08

## Ringkasan

`sso-client-lib` direstrukturisasi dari package PHP-only menjadi **monorepo multi-bahasa** untuk integrasi client ke `unism-sso`.

Satu file library tidak bisa dipakai di semua bahasa. Solusinya:

1. **Kontrak HTTP** — `spec/openapi.yaml` (API `/api/*`)
2. **Panduan OAuth universal** — `docs/INTEGRATION.md` (flow `/oauth/*`)
3. **SDK per bahasa** — implementasi tipis di atas HTTP standar

## Struktur Baru

```
sso-client-lib/
├── spec/openapi.yaml
├── docs/INTEGRATION.md
├── packages/
│   ├── javascript/     → @rizalrepo/sso-client (npm)
│   └── php-laravel/    → rizalrepo/sso-client (Composer)
└── composer.json       → backward compat untuk Composer
```

## Package Tersedia

| Bahasa | Package | Status |
|--------|---------|--------|
| JavaScript/TypeScript | `@rizalrepo/sso-client` | **Baru v2.0** — Node 18+, Bun, Deno, browser |
| PHP native | `rizalrepo/sso-client-core` | **Baru v2.0** — tanpa framework (curl) |
| PHP/Laravel | `rizalrepo/sso-client` | **Tetap** — path pindah ke `packages/php-laravel/` |
| Lainnya (Go, Python, Java, …) | Generate dari OpenAPI | Ikuti `docs/INTEGRATION.md` |

## Breaking Changes v2.0

- Struktur folder berubah; `composer require rizalrepo/sso-client` tetap jalan (autoload dari `packages/php-laravel/src/`)
- Namespace `SSOClientServiceProvider` diperbaiki: `Rizalrepo\SsoClient` (PSR-4)
- PHP minimum: `^8.1` + illuminate/support & http eksplisit

## Fitur JavaScript SDK

- `generateState()`, `getAuthorizeUrl()`, `exchangeCodeForToken()`
- `getUser()`, `verifyToken()`, `verifyTokenFull()`
- `handleCallback()`, `resolveClientRoleId()`
- User CRUD API (`createUser`, `assignClientRole`, dll.)
- Zero runtime dependency (native `fetch`)

## Migrasi dari unism-presensi

Ganti `ssoService.ts` lokal dengan:

```bash
npm install @rizalrepo/sso-client
```

```typescript
import { SSOClient } from "@rizalrepo/sso-client";
const sso = new SSOClient({ serverUrl, clientId, clientSecret, callbackUrl });
```

## Bahasa lain

```bash
openapi-generator-cli generate -i spec/openapi.yaml -g python -o clients/python
```

OAuth flow manual: lihat `docs/INTEGRATION.md` Step 1–2.
