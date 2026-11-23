#!/bin/bash

DIR="$(cd "$(dirname "$0")" && pwd)"

semgrep scan --config p/php --config p/typescript --config p/javascript --config p/secrets --config p/owasp-top-ten --config p/sql-injection --config p/default "$DIR/../www"
STATUS=$?

# GroupOffice rules run with one job: with parallel jobs Semgrep (1.157) misses some go-acl-bypass-not-restored
# findings at random.
semgrep scan -j 1 --config "$DIR/semgrep/groupoffice.yml" "$DIR/../www" || STATUS=$?

# Modules in their own git repository, like www/go/modules/business, are not part of the scan above because Semgrep
# only scans the files of the git repository it runs in.
for REPO in $(find "$DIR/../www" -mindepth 2 -name .git -not -path "*/node_modules/*" -not -path "*/vendor/*" -prune | xargs -n1 dirname); do
	semgrep scan -j 1 --config "$DIR/semgrep/groupoffice.yml" "$REPO" || STATUS=$?
done

exit $STATUS

