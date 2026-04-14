#!/bin/bash
set -e
WORKDIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

sync_repo() {
    local REPO_URL="${1:?sync_repo requires a repo URL}"
    local TARGET_DIR="${2:-$(basename "$REPO_URL" .git)}"

    if [ -d "$TARGET_DIR/.git" ]; then
        echo "Updating existing repo in '$TARGET_DIR'..."
        git -C "$TARGET_DIR" pull
    else
        echo "Cloning '$REPO_URL' into '$TARGET_DIR'..."
        git clone "$REPO_URL" "$TARGET_DIR"
    fi
}

cd ../
mkdir -p links
cd  links

sync_repo "git@github.com:Intermesh/goui.git"
cd goui
npm install
npm link

cd ..
sync_repo "git@github.com:Intermesh/groupoffice-core.git"
cd  groupoffice-core
git checkout develop
#npm install
npm link @intermesh/goui
npm link


echo "Linking @intermesh/goui and @intermesh/groupoffice-core in groupoffice"
cd ../../www
#npm install
npm link @intermesh/goui @intermesh/groupoffice-core

echo Done