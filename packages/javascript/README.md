# `@rizalrepo/sso-client`

TypeScript / JavaScript SDK for UNISM SSO. Zero runtime dependencies — uses native `fetch`.

**Requirements:** Node.js 18+, Bun, Deno, or any environment with `fetch` and `crypto.getRandomValues`.

---

## Installation

**From npm registry:**

```bash
npm install @rizalrepo/sso-client
```

**From local monorepo:**

```bash
npm install /path/to/sso-client-lib/packages/javascript
```

**Development (this repo):**

```bash
cd packages/javascript
npm install
npm run build
```

---

## Configuration

Set credentials in `.env` (never commit secrets):

```env
SSO_URL=https://sirisa.unism.ac.id
SSO_CLIENT_ID=your-uuid
SSO_CLIENT_SECRET=your-secret
SSO_CALLBACK_URL=https://your-app.example.com/callback
```

---

## Quick start

```typescript
import { SSOClient, createClientFromEnv } from "@rizalrepo/sso-client";

// Option A — explicit config
const sso = new SSOClient({
  serverUrl: process.env.SSO_URL!,
  clientId: process.env.SSO_CLIENT_ID!,
  clientSecret: process.env.SSO_CLIENT_SECRET!,
  callbackUrl: process.env.SSO_CALLBACK_URL!,
});

// Option B — from environment variables
const ssoFromEnv = createClientFromEnv();

// 1. Login — store state in session, redirect user
const state = sso.generateState();
// res.redirect(sso.getAuthorizeUrl(state, roleId));

// 2. Callback — validate state, exchange code
const { token, user } = await sso.handleCallback(code);
const roleId = sso.resolveClientRoleId(user, selectedRoleId);

// 3. API middleware — verify Bearer token
const check = await sso.verifyToken(accessToken);
if (!check.valid) throw new Error("Unauthorized");
```

---

## API reference

### OAuth

| Method | Description |
|--------|-------------|
| `generateState()` | Generate a CSRF-safe random state string |
| `getAuthorizeUrl(state, roleId?)` | Build the SSO login redirect URL |
| `exchangeCodeForToken(code)` | Exchange authorization code for tokens |
| `handleCallback(code)` | Full flow: code → token → user profile |

### User & token

| Method | Description |
|--------|-------------|
| `getUser(accessToken)` | Fetch user profile with `oauth_client_users` |
| `verifyToken(accessToken)` | Lightweight token validation |
| `verifyTokenFull(accessToken)` | Token validation with username and role |
| `resolveClientRoleId(user, roleId?)` | Resolve `oauth_client_role_id` for this client |

### Browser redirects

| Method | Returns |
|--------|---------|
| `getLogoutUrl()` | `{SSO_URL}/sso/logout` |
| `getPortalUrl()` | `{SSO_URL}/portal` |
| `getProfileUrl()` | `{SSO_URL}/profile` |
| `getEditPasswordUrl()` | `{SSO_URL}/edit-password` |
| `defaultAvatarUrl(user?)` | Avatar URL with SSO fallback |

### User management (requires `write-user` or `access-user` scope)

| Method | Description |
|--------|-------------|
| `findUserByUsername(token, username)` | Look up an existing user |
| `createUser(token, payload)` | Create a new SSO user |
| `assignClientRole(token, userId, roleId)` | Assign a client role |
| `updateUser(token, oldUser, newUser, payload)` | Update user fields |
| `setUserActive(token, username, isActive)` | Toggle active status |
| `deleteUser(token, username, roleId)` | Remove user from client |

---

## Express.js example

```typescript
import express from "express";
import { SSOClient } from "@rizalrepo/sso-client";

const app = express();
const sso = createClientFromEnv();

app.get("/sso/login", (req, res) => {
  const state = sso.generateState();
  req.session.oauthState = state;
  res.redirect(sso.getAuthorizeUrl(state));
});

app.get("/callback", async (req, res) => {
  if (req.query.state !== req.session.oauthState) {
    return res.status(400).send("Invalid state");
  }
  const { token, user } = await sso.handleCallback(req.query.code as string);
  req.session.accessToken = token.access_token;
  req.session.user = user;
  res.redirect("/dashboard");
});
```

---

## Further reading

- [OAuth integration guide](../../docs/INTEGRATION.md)
- [Root README](../../README.md)
