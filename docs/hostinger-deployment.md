# Hostinger Panel Deployment

This repo deploys to Hostinger through `.github/workflows/deploy-hostinger.yml`.

The workflow runs on `main` pushes and manual `workflow_dispatch`. If SSH secrets are not configured, it exits successfully without deploying.

## GitHub Secrets

Required:

- `HOSTINGER_SSH_HOST`: `147.93.93.168`
- `HOSTINGER_SSH_PORT`: `65002`
- `HOSTINGER_SSH_USER`: Hostinger SSH user
- One of:
  - `HOSTINGER_SSH_PRIVATE_KEY`: private key that can SSH into Hostinger
  - `HOSTINGER_SSH_PASSWORD`: Hostinger SSH password

Optional:

- `PANEL_APP_DIR`: defaults to `~/apps/seb.panel`
- `PANEL_LIVE_PUBLIC_LINK`: symlink path used as the Laravel live app root
- `PANEL_LIVE_TARGET`: defaults to `root`; use `public` only when the domain document root points directly at Laravel `public`
- `PANEL_PHP_BIN`: defaults to `php`
- `PANEL_COMPOSER_BIN`: defaults to `composer`
- `PANEL_HEALTH_URL`: example `https://panel.searchenginebasics.io`

## Hostinger One-Time Setup

Create shared folders:

```bash
mkdir -p ~/apps/seb.panel/shared/storage
```

Put the production env file here:

```bash
~/apps/seb.panel/shared/.env
```

The deploy script creates a new release under `~/apps/seb.panel/releases`, links `.env` and shared `storage`, installs Composer dependencies, runs migrations and Laravel caches, then switches `current` only after the release is ready.

The current Hostinger panel files are under:

```bash
~/domains/searchenginebasics.io/public_html/panel
```

For near-zero downtime, move that existing folder into the first release/shared layout once, then make it a symlink:

```bash
~/domains/searchenginebasics.io/public_html/panel -> ~/apps/seb.panel/current
```

Then set:

```txt
PANEL_LIVE_PUBLIC_LINK=/home/u680035976/domains/searchenginebasics.io/public_html/panel
PANEL_LIVE_TARGET=root
```

The deploy script refuses to overwrite a real non-symlink directory.

Rollback is automatic when `PANEL_HEALTH_URL` is set and the health check fails.
