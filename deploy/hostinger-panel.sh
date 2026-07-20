#!/usr/bin/env bash
set -Eeuo pipefail

APP_NAME="${APP_NAME:-softtestpanel}"
REPO_URL="${REPO_URL:-https://github.com/Muhammedbeig/softtestpanel.git}"
BRANCH="${BRANCH:-main}"
APP_DIR="${APP_DIR:-$HOME/apps/softtestpanel}"
LIVE_PUBLIC_LINK="${LIVE_PUBLIC_LINK:-}"
LIVE_TARGET="${LIVE_TARGET:-root}"
PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"
KEEP_RELEASES="${KEEP_RELEASES:-5}"
RUN_MIGRATIONS="${RUN_MIGRATIONS:-0}"
RUN_SEEDERS="${RUN_SEEDERS:-0}"
ARTIFACT_PATH="${ARTIFACT_PATH:-}"

RELEASES_DIR="$APP_DIR/releases"
SHARED_DIR="$APP_DIR/shared"
CURRENT_LINK="$APP_DIR/current"
RELEASE_ID="$(date +%Y%m%d%H%M%S)"

if [ -n "${GITHUB_SHA:-}" ]; then
  RELEASE_ID="$RELEASE_ID-$(printf '%s' "$GITHUB_SHA" | cut -c1-12)"
fi

RELEASE_DIR="$RELEASES_DIR/$RELEASE_ID"
PREVIOUS_RELEASE=""
PREVIOUS_PUBLIC_RELEASE=""

log() {
  printf '[%s] %s\n' "$(date '+%F %T')" "$*"
}

require_command() {
  if ! command -v "$1" >/dev/null 2>&1; then
    printf 'Missing required command: %s\n' "$1" >&2
    exit 1
  fi
}

validate_live_public_link() {
  if [ "$LIVE_TARGET" != "root" ] && [ "$LIVE_TARGET" != "public" ]; then
    printf 'LIVE_TARGET must be either root or public.\n' >&2
    exit 1
  fi

  if [ -z "$LIVE_PUBLIC_LINK" ]; then
    return
  fi

  if [ -e "$LIVE_PUBLIC_LINK" ] && [ ! -L "$LIVE_PUBLIC_LINK" ]; then
    printf '%s exists and is not a symlink. Refusing to overwrite it.\n' "$LIVE_PUBLIC_LINK" >&2
    printf 'Convert the Hostinger document root to a symlink once, or leave LIVE_PUBLIC_LINK empty.\n' >&2
    exit 1
  fi
}

switch_symlink() {
  local target="$1"
  local link="$2"

  ln -sfn "$target" "$link.next"
  mv -Tf "$link.next" "$link"
}

live_release_target() {
  if [ "$LIVE_TARGET" = "public" ]; then
    printf '%s/public' "$1"
    return
  fi

  printf '%s' "$1"
}

queue_restart() {
  cd "$CURRENT_LINK"
  "$PHP_BIN" artisan queue:restart >/dev/null 2>&1 || true
}

rollback() {
  if [ -n "$PREVIOUS_RELEASE" ] && [ -d "$PREVIOUS_RELEASE" ]; then
    log "Rolling back $APP_NAME to $PREVIOUS_RELEASE"
    switch_symlink "$PREVIOUS_RELEASE" "$CURRENT_LINK"

    if [ -n "$LIVE_PUBLIC_LINK" ] && [ -n "$PREVIOUS_PUBLIC_RELEASE" ] && [ -d "$PREVIOUS_PUBLIC_RELEASE" ]; then
      switch_symlink "$PREVIOUS_PUBLIC_RELEASE" "$LIVE_PUBLIC_LINK"
    fi

    queue_restart || true
  fi
}

cleanup_failed_release() {
  if [ -d "$RELEASE_DIR" ] && [ ! -f "$RELEASE_DIR/.deploy-complete" ]; then
    rm -rf "$RELEASE_DIR"
  fi
}

trap cleanup_failed_release ERR

require_command "$PHP_BIN"
require_command "$COMPOSER_BIN"

if [ -n "$ARTIFACT_PATH" ]; then
  require_command tar
else
  require_command git
fi

validate_live_public_link

mkdir -p \
  "$RELEASES_DIR" \
  "$SHARED_DIR" \
  "$SHARED_DIR/storage/app/public" \
  "$SHARED_DIR/storage/framework/cache/data" \
  "$SHARED_DIR/storage/framework/sessions" \
  "$SHARED_DIR/storage/framework/views" \
  "$SHARED_DIR/storage/logs"

if [ ! -f "$SHARED_DIR/.env" ]; then
  printf 'Missing shared Laravel env file: %s/.env\n' "$SHARED_DIR" >&2
  exit 1
fi

if [ -L "$CURRENT_LINK" ]; then
  PREVIOUS_RELEASE="$(readlink "$CURRENT_LINK" || true)"
fi

if [ -n "$LIVE_PUBLIC_LINK" ] && [ -L "$LIVE_PUBLIC_LINK" ]; then
  PREVIOUS_PUBLIC_RELEASE="$(readlink "$LIVE_PUBLIC_LINK" || true)"
fi

if [ -n "$ARTIFACT_PATH" ]; then
  if [ ! -f "$ARTIFACT_PATH" ]; then
    printf 'Missing panel artifact: %s\n' "$ARTIFACT_PATH" >&2
    exit 1
  fi

  log "Deploying $APP_NAME artifact into $RELEASE_DIR"
  mkdir -p "$RELEASE_DIR"
  tar -xzf "$ARTIFACT_PATH" -C "$RELEASE_DIR"
else
  log "Deploying $APP_NAME from $REPO_URL#$BRANCH into $RELEASE_DIR"
  git clone --depth 1 --branch "$BRANCH" "$REPO_URL" "$RELEASE_DIR"
fi

cd "$RELEASE_DIR"

ln -sfn "$SHARED_DIR/.env" .env
rm -rf storage
ln -sfn "$SHARED_DIR/storage" storage
mkdir -p bootstrap/cache

"$COMPOSER_BIN" install --no-dev --prefer-dist --optimize-autoloader --no-interaction

if [ "$RUN_MIGRATIONS" = "1" ]; then
  "$PHP_BIN" artisan migrate --force
else
  log "Skipping database migrations"
fi

if [ "$RUN_SEEDERS" = "1" ]; then
  "$PHP_BIN" artisan db:seed --force
fi

"$PHP_BIN" artisan storage:link || true
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:cache
"$PHP_BIN" artisan view:cache
touch "$RELEASE_DIR/.deploy-complete"

log "Switching $APP_NAME current release"
switch_symlink "$RELEASE_DIR" "$CURRENT_LINK"

if [ -n "$LIVE_PUBLIC_LINK" ]; then
  log "Switching Laravel live link"
  switch_symlink "$(live_release_target "$RELEASE_DIR")" "$LIVE_PUBLIC_LINK"
fi

queue_restart

if [ -n "${HEALTH_URL:-}" ]; then
  log "Checking $HEALTH_URL"

  for attempt in 1 2 3 4 5 6 7 8 9 10; do
    if curl -fsS --max-time 10 "$HEALTH_URL" >/dev/null; then
      log "Health check passed"
      break
    fi

    if [ "$attempt" = "10" ]; then
      log "Health check failed"
      rollback
      exit 1
    fi

    sleep 3
  done
fi

find "$RELEASES_DIR" -mindepth 1 -maxdepth 1 -type d | sort -r | tail -n +"$((KEEP_RELEASES + 1))" | xargs -r rm -rf
log "$APP_NAME deploy complete: $RELEASE_ID"
