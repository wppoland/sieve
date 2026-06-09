#!/usr/bin/env bash
# Sync the prepared package into a WordPress.org SVN working copy: trunk, a
# version tag, and the listing assets (icon/banner/screenshots) into /assets.
#
# First time:
#   svn checkout https://plugins.svn.wordpress.org/sieve /tmp/sieve-svn
#
# Usage: ./scripts/sync-wporg-svn.sh [package-dir] [svn-dir]
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PLUGIN_FILE="${ROOT_DIR}/sieve.php"
PACKAGE_DIR="${1:-/tmp/sieve-wporg-trunk}"
SVN_DIR="${2:-/tmp/sieve-svn}"

if [[ ! -f "${PLUGIN_FILE}" ]]; then
    echo "Could not find plugin bootstrap file: ${PLUGIN_FILE}" >&2
    exit 1
fi

if [[ ! -d "${PACKAGE_DIR}" ]]; then
    echo "Prepared package directory not found: ${PACKAGE_DIR}" >&2
    echo "Run scripts/prepare-wporg-release.sh first." >&2
    exit 1
fi

if [[ ! -d "${SVN_DIR}/.svn" ]]; then
    echo "SVN checkout not found in: ${SVN_DIR}" >&2
    echo "Run: svn checkout https://plugins.svn.wordpress.org/sieve ${SVN_DIR}" >&2
    exit 1
fi

if ! command -v rsync >/dev/null 2>&1; then
    echo "rsync is required." >&2
    exit 1
fi

VERSION="$(php -r '$c = file_get_contents($argv[1]); if (! preg_match("/^ \\* Version:\\s*(.+)$/mi", $c, $m)) { fwrite(STDERR, "Could not read plugin version.\n"); exit(1); } echo trim($m[1]);' "${PLUGIN_FILE}")"

if [[ -z "${VERSION}" ]]; then
    echo "Could not determine plugin version." >&2
    exit 1
fi

mkdir -p "${SVN_DIR}/trunk" "${SVN_DIR}/tags" "${SVN_DIR}/assets"

# trunk + version tag.
rsync -a --delete "${PACKAGE_DIR}/" "${SVN_DIR}/trunk/"
rm -rf "${SVN_DIR}/tags/${VERSION}"
cp -R "${SVN_DIR}/trunk" "${SVN_DIR}/tags/${VERSION}"

# Listing assets live in SVN /assets, not in trunk. screenshot-*.png map to the
# readme's Screenshots section; icon-256x256.png and banner-772x250.png are the
# directory visuals.
if [[ -d "${ROOT_DIR}/assets" ]]; then
    rsync -a --exclude '.DS_Store' "${ROOT_DIR}/assets/" "${SVN_DIR}/assets/"
fi

echo "Synced WordPress.org SVN working copy."
echo "Version: ${VERSION}"
echo "SVN dir: ${SVN_DIR}"
echo
echo "Next steps:"
echo "  cd ${SVN_DIR}"
echo "  svn status"
echo "  svn add --force trunk tags assets --auto-props --parents"
echo "  svn commit -m \"Release ${VERSION}\""
