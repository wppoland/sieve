#!/usr/bin/env bash
# Prepare a clean WordPress.org trunk package for Sieve, honouring .distignore.
# Build the assets first: `npm run build` (build/ must exist and ship).
#
# Usage: ./scripts/prepare-wporg-release.sh [dist-dir]
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DIST_DIR="${1:-/tmp/sieve-wporg-trunk}"

if ! command -v rsync >/dev/null 2>&1; then
    echo "rsync is required to prepare a WordPress.org release package." >&2
    exit 1
fi

if [[ ! -d "${ROOT_DIR}/build" ]]; then
    echo "WARNING: build/ is missing. Run 'npm run build' before packaging." >&2
fi

rm -rf "${DIST_DIR}"
mkdir -p "${DIST_DIR}"

rsync -a \
    --delete \
    --exclude-from="${ROOT_DIR}/.distignore" \
    --exclude '.git' --exclude 'node_modules' --exclude 'vendor' --exclude '.DS_Store' \
    "${ROOT_DIR}/" \
    "${DIST_DIR}/"

# Belt-and-braces: remove anything still matching a .distignore pattern.
while IFS= read -r pattern; do
    [[ -z "${pattern}" ]] && continue
    rm -rf "${DIST_DIR}/${pattern#/}"
done < "${ROOT_DIR}/.distignore"

# macOS regenerates .DS_Store constantly; rsync excludes can miss nested ones, so
# purge them explicitly (wp.org rejects hidden files).
find "${DIST_DIR}" -name '.DS_Store' -delete
find "${DIST_DIR}" -type d -empty -delete

echo "Prepared WordPress.org trunk package in: ${DIST_DIR}"
echo "Listing assets (icon/banner/screenshots) are synced separately by sync-wporg-svn.sh."
