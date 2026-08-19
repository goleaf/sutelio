#!/usr/bin/env bash

set -Eeuo pipefail

readonly DEPLOY_ROOT="${SUTELIO_DEPLOY_ROOT:-/www/wwwroot/sutelio.miniserver.fun}"
readonly PHP_BIN="${SUTELIO_PHP_BIN:-/www/server/php/85/bin/php}"
readonly CURRENT_PATH="${DEPLOY_ROOT}/current"
readonly RUNTIME_LOCK="${DEPLOY_ROOT}/shared/runtime.lock"

exec 9> "$RUNTIME_LOCK"
flock --shared --wait 120 9 || exit 0

cd "$CURRENT_PATH"
exec "$PHP_BIN" artisan queue:work database \
    --sleep=3 \
    --tries=3 \
    --max-time=300 \
    --timeout=90 \
    --stop-when-empty-for=5 \
    --no-interaction
