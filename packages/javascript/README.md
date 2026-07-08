# `@rizalrepo/sso-client`

OAuth2 client for UNISM SSO (Express, Next.js API routes, etc.).

```bash
npm install @rizalrepo/sso-client
```

## User data after login

After `exchangeCodeForToken` + `getUser`, use these fields in your UI:

| Field | Source | Use for |
|-------|--------|---------|
| `user.username` | `GET /api/user` | Display NIP / username |
| `user.name` | `GET /api/user` | Display full name |
| `user.avatar_url` | `GET /api/user` | Profile photo (pre-signed S3 URL from SSO) |
| `user.oauth_client_users.length` | `GET /api/user` | Show Portal link if > 1 |

**Avatar fallback** when `avatar_url` is empty:

```typescript
const avatar = user.avatar_url ?? sso.ssoUrl("assets/images/dashboard/profile.png");
```

## SSO links (profile, password, portal, logout)

Redirect the browser to SSO — do not build edit forms locally:

| Action | URL |
|--------|-----|
| Edit profile (name, photo, phone) | `sso.ssoUrl("profile")` |
| Edit password | `sso.ssoUrl("edit-password")` |
| Multi-app portal | `sso.ssoUrl("portal")` |
| Global logout | `sso.ssoUrl("sso/logout")` |

## Express example (session + HTML)

```typescript
import express from "express";
import session from "express-session";
import { SSOClient } from "@rizalrepo/sso-client";

const app = express();
const sso = new SSOClient({
  serverUrl: process.env.SSO_URL!,
  clientId: process.env.SSO_CLIENT_ID!,
  clientSecret: process.env.SSO_CLIENT_SECRET!,
  callbackUrl: process.env.SSO_CALLBACK_URL!,
});

app.use(session({ secret: process.env.SESSION_SECRET!, resave: false, saveUninitialized: false }));

app.get("/sso/login", (req, res) => {
  const state = sso.generateState();
  req.session.oauthState = state;
  res.redirect(sso.getAuthorizeUrl(state));
});

app.get("/callback", async (req, res) => {
  if (req.query.state !== req.session.oauthState) return res.status(400).send("Invalid state");
  const token = await sso.exchangeCodeForToken(req.query.code as string);
  const user = await sso.getUser(token.access_token);

  req.session.user = {
    username: user.username,
    name: user.name,
    avatar: user.avatar_url ?? sso.ssoUrl("assets/images/dashboard/profile.png"),
    countAccess: user.oauth_client_users?.length ?? 0,
  };
  req.session.accessToken = token.access_token;
  res.redirect("/dashboard");
});

app.get("/dashboard", (req, res) => {
  const u = req.session.user;
  if (!u) return res.redirect("/sso/login");

  res.send(`
    <img src="${u.avatar}" alt="${u.name}" width="48">
    <p>${u.username} — ${u.name}</p>
    <a href="${sso.ssoUrl("profile")}">Edit Profile</a>
    <a href="${sso.ssoUrl("edit-password")}">Edit Password</a>
    ${u.countAccess > 1 ? `<a href="${sso.ssoUrl("portal")}">Portal</a>` : ""}
    <a href="/sso/logout">Logout</a>
  `);
});

app.get("/sso/logout", (req, res) => {
  req.session.destroy(() => res.redirect(sso.ssoUrl("sso/logout")));
});
```

## React frontend (API returns user JSON)

```typescript
// GET /api/me — backend reads session/JWT, returns:
{
  "username": "1234567890",
  "name": "John Doe",
  "avatar": "https://...",
  "profileUrl": sso.ssoUrl("profile"),
  "editPasswordUrl": sso.ssoUrl("edit-password"),
  "portalUrl": sso.ssoUrl("portal"),
  "showPortal": countAccess > 1
}
```

After user edits profile on SSO, call `getUser(accessToken)` again to refresh `avatar_url`.

See [root README](../../README.md) · [INTEGRATION.md](../../docs/INTEGRATION.md)
