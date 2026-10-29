#!/usr/bin/env bash
#
# Group-Office release script
#
# Runs the unit tests, builds the tar.gz and Debian package and publishes them to
# GitHub, the Debian repository, SourceForge and Docker Hub.
#
# Usage: ./build.sh [--distro=testing] [--branch=develop] [--test] [--skip-tests]
#
#   --distro      Debian distribution. "testing" creates a GitHub prerelease and
#                 pushes the Docker image as intermesh/groupoffice:testing. (default: testing)
#   --branch      Branch of groupoffice, promodules and business to build. (default: develop)
#   --test        Run tests and build the packages, but don't publish anything.
#   --skip-tests  Skip the unit tests. Only for emergencies.
#
# Requirements on the build server:
#   git, php 8.4, composer, npm, debuild, reprepro, gpg, gh (logged in with
#   "gh auth login" or GH_TOKEN in config.sh), SourceGuardian encoder and docker with buildx for linux/amd64
#   and linux/arm64 (logged in with "docker login" or DOCKER_TOKEN in config.sh).
#   To prepare multi platform builds:
#
#     docker run --privileged --rm tonistiigi/binfmt --install all
#     docker buildx create --name builder --driver docker-container --use
#     docker buildx inspect --bootstrap
#
# Machine specific settings can be overridden in config.sh next to this script.
# See config.sh.example.

set -euo pipefail

BUILD_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_DIR="$(dirname "$BUILD_ROOT")"

# Defaults, can be overridden in config.sh
DISTRO="testing"
BRANCH="develop"
GITHUB_REPO="intermesh/groupoffice"
PRO_REPO="git@git.intermesh.nl:groupoffice/promodules.git"
BUSINESS_REPO="git@git.intermesh.nl:groupoffice/business.git"
DOCKER_REPO="git@github.com:Intermesh/docker-groupoffice.git"
DOCKER_IMAGE="intermesh/groupoffice"
DOCKER_PLATFORMS="linux/amd64,linux/arm64"
TEST_IMAGE="intermesh/groupoffice:latest"
TEST_DB_IMAGE="mariadb:11.8"
SF_USER="mschering"
ENCODER=(/usr/local/share/sourceguardian/bin/sourceguardian --phpversion 8.2+)
PRO_MODULES=(gota documenttemplates savemailas professional tickets scanbox leavedays projects2 timeregistration2 hoursapproval2 pr2analyzer workflow filesearch assistant billing)

if [[ -f "$BUILD_ROOT/config.sh" ]]; then
	# shellcheck source=/dev/null
	source "$BUILD_ROOT/config.sh"
fi

TEST=false
SKIP_TESTS=false

for arg in "$@"; do
	case "$arg" in
		--distro=*) DISTRO="${arg#*=}" ;;
		--branch=*) BRANCH="${arg#*=}" ;;
		--test) TEST=true ;;
		--skip-tests) SKIP_TESTS=true ;;
		*) echo "Unknown argument: $arg" >&2; exit 1 ;;
	esac
done

SOURCE_DIR="$BUILD_ROOT/deploy/source"
REPREPRO_DIR="$BUILD_ROOT/deploy/reprepro"

log() {
	echo
	echo "==> $*"
	echo
}

die() {
	echo "ERROR: $*" >&2
	exit 1
}

# Clone the repository if needed and update it to the latest commit of the branch
update_repo() {
	local url=$1 dir=$2 branch=$3

	if [[ ! -d "$dir" ]]; then
		git clone "$url" -b "$branch" "$dir"
	fi

	git -C "$dir" fetch
	git -C "$dir" checkout "$branch"
	git -C "$dir" pull
}

pull_source() {
	log "Pulling source"

	mkdir -p "$SOURCE_DIR"

	git -C "$REPO_DIR" fetch
	git -C "$REPO_DIR" checkout "$BRANCH"
	git -C "$REPO_DIR" pull
	git -C "$REPO_DIR" submodule update --init

	update_repo "$PRO_REPO" "$SOURCE_DIR/promodules" "$BRANCH"
	update_repo "$BUSINESS_REPO" "$SOURCE_DIR/business" "$BRANCH"
}

TEST_NETWORK="go-build-test-$$"

cleanup_tests() {
	docker rm -f "$TEST_NETWORK-go" "$TEST_NETWORK-db" >/dev/null 2>&1 || true
	docker network rm "$TEST_NETWORK" >/dev/null 2>&1 || true
}

# Runs phpunit on the open source checkout in a throwaway MariaDB and Group-Office container.
# Runs twice like tools/phpunit: on a new install and on an upgraded database.
run_tests() {
	log "Running unit tests"

	"$REPO_DIR/scripts/install-composer.sh"

	trap cleanup_tests EXIT

	docker pull "$TEST_IMAGE"
	docker network create "$TEST_NETWORK" >/dev/null
	docker run -d --name "$TEST_NETWORK-db" --network "$TEST_NETWORK" --network-alias db \
		-e MARIADB_ROOT_PASSWORD=groupoffice "$TEST_DB_IMAGE" >/dev/null

	echo "Waiting for database..."
	local i
	for i in $(seq 1 60); do
		if docker exec "$TEST_NETWORK-db" healthcheck.sh --connect --innodb_initialized >/dev/null 2>&1; then
			break
		fi
		[[ $i -eq 60 ]] && die "Database did not start"
		sleep 1
	done

	# Apache serves the checkout because tests/go/api requests http://localhost/api/.
	# The image entrypoint writes /etc/groupoffice/docker-config.php from the MYSQL_* variables.
	docker run -d --name "$TEST_NETWORK-go" --network "$TEST_NETWORK" \
		-e MYSQL_HOST=db -e MYSQL_USER=root -e MYSQL_PASSWORD=groupoffice -e MYSQL_DATABASE=groupoffice_phpunit \
		-v "$REPO_DIR:/usr/local/share/src:ro" -v "$REPO_DIR/www:/usr/local/share/groupoffice:ro" \
		"$TEST_IMAGE" >/dev/null

	echo "Waiting for web server..."
	for i in $(seq 1 60); do
		if docker exec "$TEST_NETWORK-go" sh -c 'test -f /etc/groupoffice/docker-config.php && curl -s -o /dev/null http://localhost/' >/dev/null 2>&1; then
			break
		fi
		[[ $i -eq 60 ]] && die "Web server did not start"
		sleep 1
	done

	local phpunit=(docker exec -u www-data -w /usr/local/share/src "$TEST_NETWORK-go"
		php ./www/vendor/phpunit/phpunit/phpunit -c tests/phpunit.xml)

	"${phpunit[@]}"

	log "Running unit tests on upgraded database"
	"${phpunit[@]}" --bootstrap=tests/bootstrap-upgrade.php

	cleanup_tests
	trap - EXIT
}

# Encode PHP files in $SOURCE_DIR/<parent>/<name> into $PACKAGE_DIR/<target>
# Usage: encode <parent> <name> <target> [exclude pattern...]
encode() {
	local parent=$1 name=$2 target=$3
	shift 3

	local excludes=() pattern
	for pattern in "$@"; do
		excludes+=(-c "$pattern")
	done

	(
		cd "$SOURCE_DIR/$parent"
		"${ENCODER[@]}" -r "${excludes[@]}" -f "*.php" -o "$PACKAGE_DIR$target" "$name"/*
	)
}

encode_pro_and_business() {
	log "Encoding professional modules"

	local module
	for module in "${PRO_MODULES[@]}"; do
		encode promodules "$module" /modules "vendor/*"
	done

	encode promodules/tickets model /modules/tickets/
	encode promodules/tickets/customfields model /modules/tickets/customfields/

	local src
	for module in "${PRO_MODULES[@]}"; do
		src="$SOURCE_DIR/promodules/$module"

		# keep lang and module install stuff open source
		if [[ -d "$src/language" ]]; then
			cp "$src"/language/* "$PACKAGE_DIR/modules/$module/language/"
		fi

		if [[ -d "$src/install" ]]; then
			cp -r "$src"/install/* "$PACKAGE_DIR/modules/$module/install/"
		fi

		if [[ -f "$src/${module^}Module.php" ]]; then
			cp "$src/${module^}Module.php" "$PACKAGE_DIR/modules/$module/"
		fi
	done

	cp "$SOURCE_DIR/promodules/professional/Module.php" "$PACKAGE_DIR/modules/professional"
	cp "$SOURCE_DIR"/promodules/projects2/report/* "$PACKAGE_DIR/modules/projects2/report/"
	cp "$SOURCE_DIR/promodules/billing/Pdf.php" "$PACKAGE_DIR/modules/billing/Pdf.php"

	log "Encoding business package"

	encode . business /go/modules "*/vendor/*"

	local dir name target
	for dir in "$SOURCE_DIR"/business/*/; do
		dir="${dir%/}"
		name="$(basename "$dir")"
		target="$PACKAGE_DIR/go/modules/business/$name"

		echo "Building business/$name"

		if [[ -f "$dir/Module.php" ]] && ! grep -q "requiredLicense" "$dir/Module.php"; then
			die "$dir/Module.php must contain a 'requiredLicense()' function override."
		fi

		if [[ -d "$dir/language" ]]; then
			cp "$dir"/language/* "$target/language/"
		fi

		if [[ -d "$dir/install" ]]; then
			cp -r "$dir"/install/* "$target/install/"
		fi

		if [[ -f "$dir/Module.php" ]]; then
			cp "$dir/Module.php" "$target/"
		fi
	done

	rm -rf "$PACKAGE_DIR"/go/modules/business/.git*

	# needs to be open source as it's used by Module files
	cp "$SOURCE_DIR/business/finance/model/PaymentProviderInterface.php" "$PACKAGE_DIR/go/modules/business/finance/model/"
	cp "$SOURCE_DIR/business/finance/model/RecurringPaymentProviderInterface.php" "$PACKAGE_DIR/go/modules/business/finance/model/"
}

build_tar() {
	log "Building $PACKAGE_NAME"

	cp -r "$REPO_DIR/www/" "$PACKAGE_DIR"

	encode_pro_and_business

	# npm ci builds the GOUI modules with their "prepare" script
	"$REPO_DIR/scripts/install-npm.sh" "$PACKAGE_DIR"
	(cd "$PACKAGE_DIR" && npm prune --omit=dev)
	"$REPO_DIR/scripts/install-composer.sh" --no-dev "$PACKAGE_DIR"

	cd "$PACKAGE_DIR"

	# remove sensitive files OWASP WSTG - WSTG-INFO-05
	rm composer.json composer.lock vendor/composer/installed.json

	cd "$BUILD_DIR"
	tar czf "$PACKAGE_NAME.tar.gz" "$PACKAGE_NAME"
	echo "Created $BUILD_DIR/$PACKAGE_NAME.tar.gz"
}

build_deb() {
	log "Building Debian package"

	local deb_dir="$BUILD_DIR/debian"
	rm -rf "$deb_dir"
	mkdir "$deb_dir"
	cp -r "$BUILD_ROOT"/debian/* "$deb_dir"

	cat > "$deb_dir/debian/changelog" <<EOF
groupoffice ($VERSION-$DISTRO) $DISTRO; urgency=medium

  * Changes can be found in /usr/share/groupoffice/CHANGELOG.md

 -- Intermesh BV (Developer key) <info@intermesh.nl>  $(date -R)
EOF

	cp -r "$PACKAGE_DIR"/* "$deb_dir/usr/share/groupoffice"
	(cd "$deb_dir" && debuild --no-lintian -A)
}

github_release() {
	log "Creating GitHub release $TAG"

	cd "$BUILD_DIR"

	if gh release view "$TAG" --repo "$GITHUB_REPO" >/dev/null 2>&1; then
		gh release upload "$TAG" "$PACKAGE_NAME.tar.gz" --repo "$GITHUB_REPO" --clobber
		return
	fi

	local prerelease=()
	if [[ "$DISTRO" == "testing" ]]; then
		prerelease=(--prerelease)
	fi

	gh release create "$TAG" "$PACKAGE_NAME.tar.gz" --repo "$GITHUB_REPO" \
		--target "$BRANCH" --title "$TAG" "${prerelease[@]}" \
		--notes "Use the $PACKAGE_NAME.tar.gz file for installations. It contains all the code, libraries and compiled code. For installation instructions read: https://groupoffice.readthedocs.io/en/latest/install/install.html"
}

add_to_debian_repository() {
	log "Adding package to Debian repository"

	if [[ ! -d "$REPREPRO_DIR" ]]; then
		mkdir -p "$REPREPRO_DIR/conf"
		cp "$SOURCE_DIR/debian-groupoffice/reprepro/distributions" "$REPREPRO_DIR/conf"
	fi

	reprepro -b "$REPREPRO_DIR" include "$DISTRO" "$BUILD_DIR/groupoffice_${VERSION}-${DISTRO}_amd64.changes"
}

send_tar_to_sourceforge() {
	log "Uploading to SourceForge"

	scp "$BUILD_DIR/$PACKAGE_NAME.tar.gz" "$SF_USER@frs.sourceforge.net:/home/frs/project/group-office/$MAJOR_VERSION/"
}

# The Dockerfile downloads the tar.gz from the GitHub release, so this must run after github_release
push_docker_image() {
	log "Building and pushing Docker image"

	local docker_dir="$SOURCE_DIR/docker-groupoffice"
	update_repo "$DOCKER_REPO" "$docker_dir" master

	local tags=()
	if [[ "$DISTRO" == "testing" ]]; then
		tags=(-t "$DOCKER_IMAGE:testing")
	else
		tags=(-t "$DOCKER_IMAGE:latest" -t "$DOCKER_IMAGE:$MAJOR_VERSION" -t "$DOCKER_IMAGE:$VERSION")
	fi

	docker buildx build --push --pull --no-cache --platform "$DOCKER_PLATFORMS" \
		--build-arg "VERSION=$VERSION" "${tags[@]}" "$docker_dir"
}

# dummy sign just to enter the GPG password at the start
gpg --clear-sign -o- /dev/null >/dev/null

# fail before the build instead of after it when publishing would fail
if [[ "$TEST" == false ]]; then
	if ! gh auth status --hostname github.com >/dev/null 2>&1; then
		die "gh is not logged in to GitHub. Run \"gh auth login\" or set GH_TOKEN in config.sh."
	fi

	# Without arguments docker login verifies the stored credentials and only prompts when there are none
	if [[ -n "${DOCKER_TOKEN:-}" ]]; then
		printf '%s' "$DOCKER_TOKEN" | docker login --username "${DOCKER_USERNAME:?Set DOCKER_USERNAME in config.sh}" --password-stdin
	else
		docker login
	fi

	buildx_platforms="$(docker buildx inspect --bootstrap)"
	IFS=, read -ra platforms <<< "$DOCKER_PLATFORMS"
	for platform in "${platforms[@]}"; do
		if ! grep -q "$platform" <<< "$buildx_platforms"; then
			die "docker buildx can't build $platform. See the multi platform setup at the top of this script."
		fi
	done
fi

pull_source

VERSION="$(php -r 'echo require $argv[1];' "$REPO_DIR/www/version.php")"
MAJOR_VERSION="${VERSION%.*}"
TAG="v$VERSION"
PACKAGE_NAME="groupoffice-$VERSION"
BUILD_DIR="$BUILD_ROOT/deploy/build/$MAJOR_VERSION/$DISTRO"
PACKAGE_DIR="$BUILD_DIR/$PACKAGE_NAME"

log "Building Group-Office $VERSION for $DISTRO from $BRANCH"

if [[ "$SKIP_TESTS" == false ]]; then
	run_tests
fi

rm -rf "$BUILD_DIR"
mkdir -p "$BUILD_DIR"

build_tar
build_deb

if [[ "$TEST" == true ]]; then
	log "Test build done. Nothing was published. Packages are in $BUILD_DIR"
	exit 0
fi

github_release
add_to_debian_repository
send_tar_to_sourceforge
push_docker_image

log "Released Group-Office $VERSION"
