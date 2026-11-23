#!/bin/bash
# Regenerates tests/phpstan-baseline.neon the way CI analyses the code (.github/workflows/phpstan.yml).
#
# The baseline is generated in a copy with only the files of this repository, so it doesn't contain errors of modules
# in other repositories, like www/go/modules/business, and those modules can't hide errors. Uncommitted changes are
# included. Runs on the host and needs Docker.
#
# Fix errors instead of adding them to the baseline. After fixing an old error, it's enough to lower its count or remove
# its entry in the baseline by hand.

set -e

DIR="$(cd "$(dirname "$0")/.." && pwd)"
TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT

cd "$DIR"

# Tracked and new files that aren't ignored. Submodules are left out, like in the CI checkout.
git ls-files --cached --others --exclude-standard | while IFS= read -r FILE; do
	if [ -f "$FILE" ] || [ -L "$FILE" ]; then
		echo "$FILE"
	fi
done | tar -cf - -T - | tar -xf - -C "$TMP"

for COMPOSER_DIR in www www/go/modules/community/maildomains; do
	docker run --rm -v "$TMP:/app" -w "/app/$COMPOSER_DIR" composer:2 \
		composer install --no-interaction --no-progress --quiet --ignore-platform-reqs
done

printf 'parameters:\n\tignoreErrors: []\n' > "$TMP/tests/phpstan-baseline.neon"

# PHP 8.4 like the development container and CI. With short_open_tag on, "<?xml" in templates is parsed as PHP.
docker run --rm -v "$TMP:/app" -w /app/www php:8.4-cli sh -c '
	echo "short_open_tag=Off" > /usr/local/etc/php/conf.d/phpstan.ini
	./vendor/bin/phpstan analyse -c ../tests/phpstan.neon --memory-limit=2G --no-progress \
		--generate-baseline ../tests/phpstan-baseline.neon
'

cp "$TMP/tests/phpstan-baseline.neon" "$DIR/tests/phpstan-baseline.neon"
echo "Updated tests/phpstan-baseline.neon"
