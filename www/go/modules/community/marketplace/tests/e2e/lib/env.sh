# Shared environment for the mp-e2e pair (host side).
#
# TWO dedicated Group-Office instances defined by ../compose.yaml, plus their
# shared MariaDB. They serve this checkout's www/, against their own databases
# and data dirs — never the dev instance (groupoffice68/db68) and never any
# other harness's instance.

if [ -z "${BASH_SOURCE[0]:-}" ]; then
    echo "lib/env.sh must be sourced from bash (run.sh does that)" >&2
    return 1 2>/dev/null || exit 1
fi

MP_SERVER=mpserver
MP_CLIENT=mpclient
MP_DB=mpdb
MP_PROJECT=mp-e2e
MP_SERVER_DB=mpserver
MP_CLIENT_DB=mpclient
MP_SERVER_URL=http://localhost:8081
MP_CLIENT_URL=http://localhost:8082
export MP_CLIENT_URL
# What each instance calls the other ON THE COMPOSE NETWORK. The client stores
# this as the repository URL, so it is also the host the license binds to.
MP_SERVER_INTERNAL=http://mpserver
export MP_SERVER_INTERNAL
MP_CLIENT_INTERNAL=http://mpclient

# The package the vendor publishes into, and the demo module it ships. The
# package name is also the compose bind-mount path, so changing it here means
# changing it in compose.yaml too.
MP_PACKAGE=mpe2e
MP_MODULE=mpdemo
MP_VERSION=1.0.0
export MP_PACKAGE MP_MODULE MP_VERSION

MP_COMPOSE="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)/compose.yaml"
export MP_COMPOSE
# lib -> e2e -> tests -> marketplace -> community -> modules -> go -> www
export MP_WWW="$(cd "$(dirname "${BASH_SOURCE[0]}")/../../../../../../.." && pwd)"
export MP_DATA_ROOT="$HOME/Projects/docker/mp-e2e"

sphp() { docker exec -u www-data "$MP_SERVER" php "$@"; }   # PHP in the vendor instance
cphp() { docker exec -u www-data "$MP_CLIENT" php "$@"; }   # PHP in the customer instance
