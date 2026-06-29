#!/usr/bin/env bash
# Build a clean, installable sieve.zip for testing, honouring .distignore.
# Produces /tmp/sieve-build/sieve and /tmp/sieve.zip.
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
OUT_DIR="${1:-/tmp/sieve-build}"
STAGE="${OUT_DIR}/sieve"

rm -rf "${OUT_DIR}"
mkdir -p "${STAGE}"

composer install --no-dev --working-dir="${ROOT_DIR}" --quiet

# Copy everything except .distignore patterns.
rsync -a --exclude-from="${ROOT_DIR}/.distignore" \
    --exclude '.git' --exclude 'node_modules' \
    --exclude '.DS_Store' \
    "${ROOT_DIR}/" "${STAGE}/"

find "${STAGE}" -name '.DS_Store' -delete

# Exclude .DS_Store at the zip layer too: macOS/Spotlight can recreate it between
# the find above and the zip, so -x is the only bulletproof guard.
( cd "${OUT_DIR}" && zip -rqX /tmp/sieve.zip sieve -x '*.DS_Store' )
echo "Built /tmp/sieve.zip from ${STAGE}"
