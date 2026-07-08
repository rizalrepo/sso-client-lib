# Release v2.0.0 — Publish Status

**Date:** July 8, 2026

## GitHub

| Item | Value |
|------|-------|
| Tag | `v2.0.0` |
| Release | https://github.com/rizalrepo/sso-client-lib/releases/tag/v2.0.0 |
| Commit | `91155ab` |

## Packagist

| Package | Status | Install |
|---------|--------|---------|
| `rizalrepo/sso-client` | **Live — v2.0.0** | `composer require rizalrepo/sso-client:^2.0` |
| `rizalrepo/sso-client-core` | **Not registered** | Manual setup required |

### Register `sso-client-core` on Packagist

1. Log in at https://packagist.org
2. Submit package URL: `https://github.com/rizalrepo/sso-client-lib`
3. Set **Composer JSON path**: `packages/php-native/composer.json`

## npm

| Package | Status |
|---------|--------|
| `@rizalrepo/sso-client` | **Requires `npm login`** |

```bash
cd packages/javascript
npm login
npm publish --access public
```

The `prepublishOnly` script runs `npm run build` automatically before publish.

## Version note

This is a **major release (v2.0.0)**, not a patch (v1.3.4), due to the monorepo restructure and breaking path change from `src/` to `packages/php-laravel/src/`.
