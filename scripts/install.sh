#!/usr/bin/env bash
set -euo pipefail

# Lighthouse installer
# Usage:
#   bash -c "$(curl -fsSL https://raw.githubusercontent.com/max-yterb/Lighthouse/main/scripts/install.sh)" -- [target-dir]
# or
#   bash -c "$(wget -qO- https://raw.githubusercontent.com/max-yterb/Lighthouse/main/scripts/install.sh)" -- [target-dir]

REPO_HTTPS="https://github.com/max-yterb/Lighthouse.git"
TARGET_DIR="${1:-lighthouse-app}"

if command -v tput >/dev/null 2>&1; then
  bold=$(tput bold); reset=$(tput sgr0); green=$(tput setaf 2); red=$(tput setaf 1)
else
  bold=""; reset=""; green=""; red=""
fi

echo "${bold}Lighthouse installer${reset}"

if ! command -v git >/dev/null 2>&1; then
  echo "${red}Error:${reset} git is required. Please install git and retry." >&2
  exit 1
fi

if [ -e "$TARGET_DIR" ]; then
  echo "${red}Error:${reset} target already exists: $TARGET_DIR" >&2
  exit 1
fi

# Shallow clone
echo "Cloning into $TARGET_DIR ..."
GIT_URL="$REPO_HTTPS"

git clone --depth 1 "$GIT_URL" "$TARGET_DIR"

# Remove git history to make it a fresh project
rm -rf "$TARGET_DIR/.git"

# Ensure CLI is executable
chmod +x "$TARGET_DIR/cli" || true

# Remove development database if present
[ -f "$TARGET_DIR/database/database.sqlite" ] && rm -f "$TARGET_DIR/database/database.sqlite"

cat <<EOT
${green}Done!${reset}

Next steps:
  cd $TARGET_DIR
  php -S localhost:8000 -t public/

Useful commands:
  ./cli version
  ./cli db make:migration CreateUsers
  ./cli db migrate
  ./cli test run
EOT
