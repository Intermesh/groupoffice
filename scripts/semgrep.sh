#!/bin/bash

DIR="$(cd "$(dirname "$0")" && pwd)"

semgrep scan --config p/php --config p/typescript --config p/javascript --config p/secrets --config p/owasp-top-ten --config p/sql-injection --config p/default "$DIR/../www"
STATUS=$?

# GroupOffice rules run with one job: with parallel jobs Semgrep (1.157) misses some go-acl-bypass-not-restored
# findings at random.
semgrep scan -j 1 --config "$DIR/semgrep/groupoffice.yml" "$DIR/../www" || STATUS=$?

exit $STATUS

