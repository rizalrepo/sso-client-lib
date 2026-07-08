# Laravel — Satu Perintah Install

## Masalah

Sebelum v2.1.0, integrasi Laravel butuh 2 langkah:

```bash
composer require rizalrepo/sso-client:^2.0
php artisan vendor:publish --tag=sso-config
```

Plus manual copy routes ke `routes/web.php`.

## Solusi (v2.1.0+)

```bash
composer require rizalrepo/sso-client:^2.1
```

ServiceProvider otomatis:

1. **mergeConfigFrom** — config `sso.*` dari package
2. **loadRoutesFrom** — routes `/sso/login`, `/callback`, dll.

Tidak perlu `vendor:publish` untuk setup dasar.

## Env wajib

```env
SSO_URL=https://sirisa.unism.ac.id
SSO_CLIENT_ID=your-uuid
SSO_CLIENT_SECRET=your-secret
SSO_CALLBACK_URL=https://your-app.example.com/callback
```

## Kapan perlu publish?

Hanya jika ingin override:

```bash
php artisan vendor:publish --tag=sso-config
```

| File | Alasan publish |
|------|----------------|
| `config/sso.php` | Override default config |
| `app/Http/Controllers/SSO/SSOController.php` | Custom logic (extends package) |

Set `SSO_REGISTER_ROUTES=false` jika pakai routes sendiri.

## PHP native

Package sama — `composer require rizalrepo/sso-client`. Pakai class `Rizalrepo\SsoClient\SSOClient` langsung; ServiceProvider tidak di-boot di luar Laravel.
