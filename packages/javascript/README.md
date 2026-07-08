# `@rizalrepo/sso-client`

TypeScript/JavaScript OAuth2 client for UNISM SSO. Zero runtime dependencies.

**Requires:** Node 18+, Bun, Deno, or browser with `fetch`.

```bash
npm install @rizalrepo/sso-client
```

## Usage

```typescript
import { SSOClient } from "@rizalrepo/sso-client";

const sso = new SSOClient({
  serverUrl: process.env.SSO_URL!,
  clientId: process.env.SSO_CLIENT_ID!,
  clientSecret: process.env.SSO_CLIENT_SECRET!,
  callbackUrl: process.env.SSO_CALLBACK_URL!,
});

const state = sso.generateState();
const { token, user } = await sso.handleCallback(code);
const roleId = sso.resolveClientRoleId(user);
const check = await sso.verifyToken(token.access_token);
```

## API

| Method | Description |
|--------|-------------|
| `generateState()` | CSRF state string |
| `getAuthorizeUrl(state, roleId?)` | Login redirect URL |
| `exchangeCodeForToken(code)` | Code → tokens |
| `handleCallback(code)` | Code → token + user |
| `getUser(accessToken)` | User profile |
| `verifyToken(token, full?)` | Validate token |
| `resolveClientRoleId(user, roleId?)` | Resolve client role |
| `ssoUrl(path)` | Build SSO URL |
| `defaultAvatarUrl(user?)` | Avatar with fallback |

OAuth protocol details: [docs/INTEGRATION.md](../../docs/INTEGRATION.md)
