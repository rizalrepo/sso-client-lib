# JavaScript / TypeScript SSO Client (`@rizalrepo/sso-client`)

SDK untuk Node 18+, Bun, Deno, dan browser. Zero runtime dependency (native `fetch`).

## Install

**Dari registry (setelah publish):**

```bash
npm install @rizalrepo/sso-client
```

**Dari monorepo lokal:**

```bash
npm install /path/to/sso-client-lib/packages/javascript
```

**Development di repo ini:**

```bash
cd packages/javascript
npm install
npm run build
```

## Konfigurasi (.env)

```env
SSO_URL=https://sirisa.unism.ac.id
SSO_CLIENT_ID=your-uuid
SSO_CLIENT_SECRET=your-secret
SSO_CALLBACK_URL=https://your-app.example.com/callback
```

## Contoh

```typescript
import { SSOClient, createClientFromEnv } from "@rizalrepo/sso-client";

// Manual config
const sso = new SSOClient({
  serverUrl: process.env.SSO_URL!,
  clientId: process.env.SSO_CLIENT_ID!,
  clientSecret: process.env.SSO_CLIENT_SECRET!,
  callbackUrl: process.env.SSO_CALLBACK_URL!,
});

// Atau dari env
const ssoFromEnv = createClientFromEnv();

const state = sso.generateState();
// res.redirect(sso.getAuthorizeUrl(state));

const { token, user } = await sso.handleCallback(code);
const roleId = sso.resolveClientRoleId(user);

const check = await sso.verifyToken(accessToken);
```

Panduan OAuth lengkap: [docs/INTEGRATION.md](../../docs/INTEGRATION.md)
