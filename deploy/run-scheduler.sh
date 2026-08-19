#!/usr/bin/env bash

set -Eeuo pipefail

readonly DEPLOY_ROOT="${SUTELIO_DEPLOY_ROOT:-/www/wwwroot/sutelio.miniserver.fun}"
readonly PHP_BIN="${SUTELIO_PHP_BIN:-/www/server/php/85/bin/php}"
readonly CURRENT_PATH="${DEPLOY_ROOT}/current"
readonly RUNTIME_LOCK="${DEPLOY_ROOT}/shared/runtime.lock"

exec 9> "$RUNTIME_LOCK"
flock --shared --nonblock 9 || exit 0

cd "$CURRENT_PATH"
exec "$PHP_BIN" artisan schedule:run --no-interaction
