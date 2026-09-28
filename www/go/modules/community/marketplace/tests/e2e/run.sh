#!/usr/bin/env bash
#
# marketplace <-> marketplaceserver end-to-end harness.
#
# Two throwaway Group-Office instances on one compose project: mpserver runs the
# vendor module, mpclient runs the customer module, and the scenario drives them
# through the real API over a real network hop — registration, e-mail
# verification, bearer auth, catalogue, package signature, install and the
# license JWT.
#
# Usage:
#   ./run.sh up        # build + start both instances
#   ./run.sh reset     # wipe both databases and install core + the two modules
#   ./run.sh seed      # publish the demo product/release on the vendor
#   ./run.sh test      # run scenario.py against the running pair
#   ./run.sh all       # up + reset + seed + test  (the full proof from scratch)
#   ./run.sh logs      # tail both apache error logs
#   ./run.sh shell <server|client>
#   ./run.sh down      # stop and REMOVE the containers, the volume and the data dirs
set -euo pipefail
HERE="$(cd "$(dirname "$0")" && pwd)"
source "$HERE/lib/env.sh"

die() { echo "!! $*" >&2; exit 1; }

guard() {
    # Refuse to touch anything that is not this harness. The dev instance shares
    # the same image and the same www/, so the only thing telling them apart is
    # the container name, the compose project and the database each app config
    # points at.
    [ "$MP_SERVER" = mpserver ] && [ "$MP_CLIENT" = mpclient ] && [ "$MP_DB" = mpdb ] \
        || die "env.sh does not point at the mp-e2e pair"
    for c in "$MP_SERVER" "$MP_CLIENT" "$MP_DB"; do
        docker ps --format '{{.Names}}' | grep -qx "$c" || die "$c is not running — ./run.sh up"
        local project
        project=$(docker inspect -f '{{ index .Config.Labels "com.docker.compose.project" }}' "$c")
        [ "$project" = "$MP_PROJECT" ] || die "$c belongs to compose project '$project', not $MP_PROJECT — refusing"
    done
    local sdb cdb
    sdb=$(docker exec "$MP_SERVER" php -r 'require "/var/www/html/config.php"; echo $config["db_name"];')
    cdb=$(docker exec "$MP_CLIENT" php -r 'require "/var/www/html/config.php"; echo $config["db_name"];')
    [ "$sdb" = "$MP_SERVER_DB" ] || die "$MP_SERVER talks to db '$sdb', expected $MP_SERVER_DB — refusing"
    [ "$cdb" = "$MP_CLIENT_DB" ] || die "$MP_CLIENT talks to db '$cdb', expected $MP_CLIENT_DB — refusing"
}

wait_db() {
    for _ in $(seq 1 90); do
        docker exec "$MP_DB" mariadb -uroot -proot -e 'SELECT 1' >/dev/null 2>&1 && return 0
        sleep 1
    done
    die "$MP_DB did not accept connections"
}

wait_http() {
    local url=$1
    for _ in $(seq 1 90); do
        curl -s -o /dev/null "$url/" && return 0
        sleep 1
    done
    die "$url did not come up"
}

cmd_up() {
    mkdir -p "$MP_DATA_ROOT"/server/{data,tmp,log} "$MP_DATA_ROOT"/client/{data,tmp,log,modules}
    chmod -R 0777 "$MP_DATA_ROOT"
    docker compose -f "$MP_COMPOSE" up -d --build
    wait_http "$MP_SERVER_URL"
    wait_http "$MP_CLIENT_URL"
    echo "== up: vendor $MP_SERVER_URL, customer $MP_CLIENT_URL (admin / admin123 once installed)"
}

cmd_reset() {
    guard
    wait_db
    echo "== wiping both databases and data dirs"
    docker exec "$MP_DB" mariadb -uroot -proot -e "
        DROP DATABASE IF EXISTS $MP_SERVER_DB; CREATE DATABASE $MP_SERVER_DB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
        DROP DATABASE IF EXISTS $MP_CLIENT_DB; CREATE DATABASE $MP_CLIENT_DB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    docker exec "$MP_SERVER" bash -c 'find /var/lib/groupoffice /tmp/groupoffice -mindepth 1 -delete 2>/dev/null; true'
    docker exec "$MP_CLIENT" bash -c 'find /var/lib/groupoffice /tmp/groupoffice -mindepth 1 -delete 2>/dev/null; true'
    # The downloaded module must not survive a reset either, or "did the download
    # land?" would pass on last run's files.
    docker exec "$MP_CLIENT" bash -c "find /var/www/html/go/modules/$MP_PACKAGE -mindepth 1 -delete 2>/dev/null; true"
    # APCu lives in the Apache process and survives a database wipe.
    docker restart "$MP_SERVER" "$MP_CLIENT" >/dev/null
    wait_http "$MP_SERVER_URL"; wait_http "$MP_CLIENT_URL"

    # The e2e harness of the amd package owns the headless installer; reuse it
    # rather than keeping a second copy in sync.
    local installer=/var/www/html/go/modules/amd/tests/e2e/container/install.php
    echo "== installing the vendor (community/marketplaceserver)"
    sphp "$installer" "$MP_SERVER_INTERNAL" community/marketplaceserver
    echo "== installing the customer (community/marketplace)"
    cphp "$installer" "$MP_CLIENT_INTERNAL" community/marketplace
}

# Paths INSIDE the containers: both mount this checkout at /var/www/html.
MP_IN=/var/www/html/go/modules/community/marketplace/tests/e2e/container

cmd_seed() {
    guard
    sphp "$MP_IN/seed-server.php" "$MP_PACKAGE" "$MP_MODULE" "$MP_VERSION"
}

cmd_test() {
    guard
    python3 "$HERE/scenario.py" "$@"
}

cmd_all() {
    cmd_up
    cmd_reset
    cmd_seed
    cmd_test "$@"
}

cmd_logs() {
    docker exec "$MP_SERVER" tail -n 40 /var/log/apache2/error.log | sed 's/^/[vendor]   /'
    docker exec "$MP_CLIENT" tail -n 40 /var/log/apache2/error.log | sed 's/^/[customer] /'
}

cmd_shell() {
    case "${1:-}" in
        server) docker exec -it -u www-data "$MP_SERVER" bash ;;
        client) docker exec -it -u www-data "$MP_CLIENT" bash ;;
        *) die "shell <server|client>" ;;
    esac
}

cmd_down() {
    # Deliberately destructive: this harness is meant to leave nothing behind.
    docker compose -f "$MP_COMPOSE" down -v --remove-orphans
    rm -rf "$MP_DATA_ROOT"
    # Docker creates a bind mount's destination if it is missing, and the
    # customer's download target lives under the checkout's go/modules — so
    # starting the pair leaves an EMPTY go/modules/$MP_PACKAGE behind. rmdir,
    # never rm -rf: if anything is in there the mount did not cover the
    # download and that is worth noticing, not deleting.
    rmdir "$MP_WWW/go/modules/$MP_PACKAGE" 2>/dev/null \
        || [ ! -d "$MP_WWW/go/modules/$MP_PACKAGE" ] \
        || echo "!! $MP_WWW/go/modules/$MP_PACKAGE is not empty — look before removing it"
    echo "== down: containers, volume, $MP_DATA_ROOT and the mount point removed"
}

case "${1:-}" in
    up) shift; cmd_up "$@" ;;
    reset) shift; cmd_reset "$@" ;;
    seed) shift; cmd_seed "$@" ;;
    test) shift; cmd_test "$@" ;;
    all) shift; cmd_all "$@" ;;
    logs) shift; cmd_logs "$@" ;;
    shell) shift; cmd_shell "$@" ;;
    down) shift; cmd_down "$@" ;;
    *) sed -n '2,20p' "$0"; exit 1 ;;
esac
