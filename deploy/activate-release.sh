#!/usr/bin/env bash

set -Eeuo pipefail

umask 027

readonly RELEASE_SHA="${1:-}"
readonly RELEASE_CHECKSUM="${2:-}"
readonly DEPLOY_ROOT="${SUTELIO_DEPLOY_ROOT:-/www/wwwroot/sutelio.miniserver.fun}"
readonly PHP_BIN="${SUTELIO_PHP_BIN:-/www/server/php/85/bin/php}"
readonly HEALTH_URL="${SUTELIO_HEALTH_URL:-https://sutelio.miniserver.fun/up}"
readonly HEALTH_HOST="${SUTELIO_HEALTH_HOST:-sutelio.miniserver.fun}"
readonly WEB_GROUP="${SUTELIO_WEB_GROUP:-www}"
readonly SHARED_PATH="${DEPLOY_ROOT}/shared"
readonly RELEASES_PATH="${DEPLOY_ROOT}/releases"
readonly INCOMING_PATH="${SHARED_PATH}/incoming"
readonly ARCHIVE_PATH="${INCOMING_PATH}/${RELEASE_SHA}.tar.gz"
readonly RELEASE_PATH="${RELEASES_PATH}/${RELEASE_SHA}"
readonly CURRENT_LINK="${DEPLOY_ROOT}/current"
readonly SHARED_DATABASE_PATH="${DEPLOY_ROOT}/shared/database/database.sqlite"
readonly RUNTIME_LOCK="${DEPLOY_ROOT}/shared/runtime.lock"

temporary_release=''
previous_release=''
switched_release=0

log() {
    printf '[sutelio-deploy] %s\n' "$*"
}

fail() {
    log "ERROR: $*" >&2
    exit 1
}

artisan() {
    local release_path="$1"
    shift

    (
        cd "$release_path"
        "$PHP_BIN" artisan "$@" --no-interaction
    )
}

restore_previous_release() {
    local rollback_link

    trap - ERR INT TERM
    set +e

    if [[ "$switched_release" -eq 1 ]]; then
        rollback_link="${DEPLOY_ROOT}/.current-rollback-${RELEASE_SHA}-$$"

        if [[ -n "$previous_release" && -d "$previous_release" ]]; then
            ln -s "$previous_release" "$rollback_link"
            mv -Tf "$rollback_link" "$CURRENT_LINK"
            log "Restored previous release ${previous_release##*/}."
        else
            rm -f -- "$CURRENT_LINK"
            log 'Removed failed first-release symlink.'
        fi
    fi

    if [[ -L "$CURRENT_LINK" && -f "$CURRENT_LINK/artisan" ]]; then
        artisan "$CURRENT_LINK" up
    elif [[ -f "$RELEASE_PATH/artisan" ]]; then
        artisan "$RELEASE_PATH" up
    fi

    if [[ -n "$temporary_release" && -d "$temporary_release" ]]; then
        rm -rf -- "$temporary_release"
    fi
}

rollback_release() {
    local exit_code=$?

    trap - EXIT INT TERM

    if [[ "$exit_code" -ne 0 ]]; then
        restore_previous_release
    elif [[ -n "$temporary_release" && -d "$temporary_release" ]]; then
        rm -rf -- "$temporary_release"
    fi

    exit "$exit_code"
}

validate_archive_members() {
    local member
    local archive_size

    archive_size="$(stat -c '%s' "$ARCHIVE_PATH")"
    [[ "$archive_size" -le 536870912 ]] || fail 'The release archive exceeds the 512 MiB safety limit.'

    while IFS= read -r member; do
        if [[ "$member" =~ ^/ || "$member" =~ (^|/)\.\.(/|$) ]]; then
            fail "Archive contains an unsafe path: ${member}"
        fi
    done < <(tar -tzf "$ARCHIVE_PATH")
}

prepare_release() {
    local required_path

    temporary_release="${RELEASES_PATH}/.${RELEASE_SHA}.tmp.$$"
    mkdir -p "$temporary_release"
    tar -xzf "$ARCHIVE_PATH" -C "$temporary_release" --no-same-owner --no-same-permissions

    if find "$temporary_release" -mindepth 1 \( -type l -o -type b -o -type c -o -type p -o -type s \) -print -quit | grep -q .; then
        fail 'Release archives may contain only regular files and directories.'
    fi

    for required_path in artisan bootstrap/app.php vendor/autoload.php public/build/manifest.json; do
        [[ -f "${temporary_release}/${required_path}" ]] || fail "Release is missing ${required_path}."
    done

    rm -rf -- "${temporary_release}/storage"
    ln -s "$SHARED_PATH/storage" "${temporary_release}/storage"
    ln -s "$SHARED_PATH/.env" "${temporary_release}/.env"

    rm -rf -- "${temporary_release}/public/storage"
    ln -s "$SHARED_PATH/storage/app/public" "${temporary_release}/public/storage"

    mkdir -p "${temporary_release}/bootstrap/cache"
    chmod -R u=rwX,g=rX,o= "$temporary_release"
    chgrp -R "$WEB_GROUP" "$temporary_release"
    chmod -R ug+rwX,o= "${temporary_release}/bootstrap/cache"

    printf 'open_basedir=%s/:/tmp/\n' "$DEPLOY_ROOT" > "${temporary_release}/public/.user.ini"
    chmod 0640 "${temporary_release}/public/.user.ini"
    chgrp "$WEB_GROUP" "${temporary_release}/public/.user.ini"

    mv "$temporary_release" "$RELEASE_PATH"
    temporary_release=''
}

cache_release() {
    artisan "$RELEASE_PATH" config:clear
    artisan "$RELEASE_PATH" config:cache
    artisan "$RELEASE_PATH" event:cache
    artisan "$RELEASE_PATH" route:cache
    artisan "$RELEASE_PATH" view:cache
}

activate_release() {
    local next_link="${DEPLOY_ROOT}/.current-next-${RELEASE_SHA}-$$"

    ln -s "$RELEASE_PATH" "$next_link"
    mv -Tf "$next_link" "$CURRENT_LINK"
    switched_release=1
}

verify_https_health() {
    curl \
        --fail \
        --silent \
        --show-error \
        --retry 12 \
        --retry-all-errors \
        --retry-delay 5 \
        --connect-timeout 5 \
        --max-time 15 \
        --resolve "${HEALTH_HOST}:443:127.0.0.1" \
        "$HEALTH_URL" > /dev/null
}

prune_old_releases() {
    local current_release
    local release_path
    local retained_other_releases=0
    local -a release_paths=()

    current_release="$(readlink -f "$CURRENT_LINK")"
    mapfile -t release_paths < <(
        find "$RELEASES_PATH" -regextype posix-extended -mindepth 1 -maxdepth 1 -type d \
            -regex '.*/[0-9a-f]{40}' -printf '%T@ %p\n' \
            | sort -nr \
            | cut -d' ' -f2-
    )

    for release_path in "${release_paths[@]}"; do
        [[ "$(realpath -m "$release_path")" == "${RELEASES_PATH}/"* ]] || continue

        if [[ "$release_path" == "$current_release" ]]; then
            continue
        fi

        if [[ "$retained_other_releases" -lt 4 ]]; then
            retained_other_releases=$((retained_other_releases + 1))
            continue
        fi

        if ! rm -rf -- "$release_path"; then
            log "WARNING: Could not remove expired release ${release_path##*/}; operator cleanup is required." >&2
            continue
        fi

        log "Removed expired release ${release_path##*/}."
    done
}

[[ "$RELEASE_SHA" =~ ^[0-9a-f]{40}$ ]] || fail 'The release SHA must be 40 lowercase hexadecimal characters.'
[[ "$RELEASE_CHECKSUM" =~ ^[0-9a-f]{64}$ ]] || fail 'The checksum must be 64 lowercase hexadecimal characters.'
[[ "$DEPLOY_ROOT" == /* && "$DEPLOY_ROOT" != '/' ]] || fail 'The deployment root must be a specific absolute path.'
[[ -x "$PHP_BIN" ]] || fail 'The configured PHP binary is not executable.'
[[ -f "$SHARED_PATH/.env" ]] || fail 'The shared production environment is missing.'
[[ -f "$SHARED_DATABASE_PATH" ]] || fail 'The shared SQLite database is missing.'
[[ -f "$ARCHIVE_PATH" ]] || fail 'The incoming release archive is missing.'

mkdir -p "$RELEASES_PATH" "$INCOMING_PATH"
exec 9> "$SHARED_PATH/deploy.lock"
flock -n 9 || fail 'Another deployment is already active.'

trap rollback_release EXIT
trap 'exit 130' INT
trap 'exit 143' TERM

printf '%s  %s\n' "$RELEASE_CHECKSUM" "$ARCHIVE_PATH" | sha256sum --check --status \
    || fail 'The release checksum does not match.'
validate_archive_members

if [[ ! -d "$RELEASE_PATH" ]]; then
    prepare_release
else
    log "Release ${RELEASE_SHA} already exists; revalidating it."
fi

[[ -f "$RELEASE_PATH/artisan" ]] || fail 'The release directory is invalid.'
[[ -f "$RELEASE_PATH/public/build/manifest.json" ]] || fail 'The release frontend manifest is missing.'

if [[ -L "$CURRENT_LINK" ]]; then
    previous_release="$(readlink -f "$CURRENT_LINK")"
    [[ "$previous_release" == "${RELEASES_PATH}/"* ]] || fail 'The current release link points outside the release directory.'
fi

cache_release

exec 8> "$RUNTIME_LOCK"
log 'Waiting for active scheduler and queue work to finish.'
flock 8

if [[ -n "$previous_release" && -f "$previous_release/artisan" ]]; then
    artisan "$previous_release" backup:run
    artisan "$previous_release" down --retry=60 --refresh=15
else
    artisan "$RELEASE_PATH" down --retry=60 --refresh=15
fi

artisan "$RELEASE_PATH" migrate --force
artisan "$RELEASE_PATH" app:database-health --json

activate_release
artisan "$RELEASE_PATH" reload
artisan "$RELEASE_PATH" schedule:interrupt
artisan "$RELEASE_PATH" up
verify_https_health

trap - EXIT INT TERM
rm -f -- "$ARCHIVE_PATH"
prune_old_releases

log "Release ${RELEASE_SHA} is active and healthy."
