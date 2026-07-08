# UNISM SSO Client SDK

**v2.0.2** · [Changelog](md/CHANGELOG.md) · [Integration guide](docs/INTEGRATION.md)

| Runtime | Package | Install |
|---------|---------|---------|
| JavaScript / TypeScript | `@rizalrepo/sso-client` | `npm install @rizalrepo/sso-client` |
| PHP / Laravel | `rizalrepo/sso-client` | `composer require rizalrepo/sso-client:^2.0` |
| PHP (native) | `rizalrepo/sso-client-core` | `composer require rizalrepo/sso-client-core:^2.0` |
| Other languages | Live OpenAPI | `{SSO_URL}/developer/openapi.yaml` |

```env
SSO_URL=https://sirisa.unism.ac.id
SSO_CLIENT_ID=your-uuid
SSO_CLIENT_SECRET=your-secret
SSO_CALLBACK_URL=https://your-app.example.com/callback
```

```typescript
import { SSOClient } from "@rizalrepo/sso-client";

const sso = new SSOClient({ serverUrl, clientId, clientSecret, callbackUrl });
const token = await sso.exchangeCodeForToken(code);
const user = await sso.getUser(token.access_token);
```

Laravel: `php artisan vendor:publish --tag=sso-config` — see [packages/php-laravel/README.md](packages/php-laravel/README.md).
