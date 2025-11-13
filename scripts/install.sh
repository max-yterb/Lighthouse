#!/usr/bin/env bash
set -euo pipefail

# Lighthouse installer
# Usage:
#   bash -c "$(curl -fsSL https://raw.githubusercontent.com/max-yterb/Lighthouse/main/scripts/install.sh)"
# or
#   bash -c "$(wget -qO- https://raw.githubusercontent.com/max-yterb/Lighthouse/main/scripts/install.sh)"

REPO_HTTPS="https://github.com/max-yterb/Lighthouse.git"
INSTALL_DIR="/tmp/lighthouse-install-$$"
BIN_DIR="/usr/local/bin"

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

# Check if we have write permissions to install directory
if [ ! -w "$(dirname "$BIN_DIR")" ] && [ "$EUID" -ne 0 ]; then
  echo "${red}Error:${reset} Installation requires sudo privileges to write to $BIN_DIR" >&2
  echo "Please run: sudo bash -c \"\$(curl -fsSL https://raw.githubusercontent.com/max-yterb/Lighthouse/main/scripts/install.sh)\""
  exit 1
fi

# Create temporary directory for installation
echo "Installing Lighthouse CLI..."
mkdir -p "$INSTALL_DIR"

# Shallow clone
echo "Downloading Lighthouse..."
GIT_URL="$REPO_HTTPS"

git clone --depth 1 "$GIT_URL" "$INSTALL_DIR"

# Copy the lighthouse script to bin directory
cp "$INSTALL_DIR/lighthouse" "$BIN_DIR/lighthouse"
chmod +x "$BIN_DIR/lighthouse"

# Clean up temporary directory
rm -rf "$INSTALL_DIR"

cat <<EOT
${green}Done!${reset}

Lighthouse CLI has been installed globally.

Useful commands:
  lighthouse version
  lighthouse new my-project

To create a new project:
  lighthouse new my-app
  cd my-app
  php -S localhost:8000 -t public/
EOT
