# UNISM SSO Client SDK

Client libraries for [UNISM SSO](https://github.com/rizalrepo/unism-sso) (Laravel Passport OAuth 2.0).

**Current version:** 2.0.1 · [Changelog](md/CHANGELOG.md)

## Install

| Runtime | Package | Command |
|---------|---------|---------|
| JavaScript / TypeScript | `@rizalrepo/sso-client` | `npm install @rizalrepo/sso-client` |
| PHP / Laravel | `rizalrepo/sso-client` | `composer require rizalrepo/sso-client:^2.0` |
| PHP (native) | `rizalrepo/sso-client-core` | `composer require rizalrepo/sso-client-core:^2.0` |
| Other languages | Live OpenAPI spec | `{SSO_URL}/developer/openapi.yaml` + [docs/INTEGRATION.md](docs/INTEGRATION.md) |

## Configuration (.env)

```env
SSO_URL=https://sirisa.unism.ac.id
SSO_CLIENT_ID=your-uuid
SSO_CLIENT_SECRET=your-secret
SSO_CALLBACK_URL=https://your-app.example.com/callback
```

Never hardcode credentials in source code.

## Quick example (TypeScript)

```typescript
import { SSOClient } from "@rizalrepo/sso-client";

const sso = new SSOClient({
  serverUrl: process.env.SSO_URL!,
  clientId: process.env.SSO_CLIENT_ID!,
  clientSecret: process.env.SSO_CLIENT_SECRET!,
  callbackUrl: process.env.SSO_CALLBACK_URL!,
});

const state = sso.generateState();
// res.redirect(sso.getAuthorizeUrl(state));

const { token, user } = await sso.handleCallback(code);
```

## Documentation

| Doc | Contents |
|-----|----------|
| [docs/INTEGRATION.md](docs/INTEGRATION.md) | OAuth protocol (all languages) |
| [packages/javascript/README.md](packages/javascript/README.md) | JS/TS SDK |
| [packages/php-laravel/README.md](packages/php-laravel/README.md) | Laravel setup |
| [packages/php-native/README.md](packages/php-native/README.md) | Native PHP SDK |

## Repository layout

```
sso-client-lib/
├── docs/INTEGRATION.md
├── packages/javascript/     @rizalrepo/sso-client
├── packages/php-laravel/    rizalrepo/sso-client (Laravel)
└── packages/php-native/     rizalrepo/sso-client-core
```

## License

MIT
