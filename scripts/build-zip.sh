#!/usr/bin/env bash
# Build a clean, installable sieve.zip for testing, honouring .distignore.
# Produces /tmp/sieve-build/sieve and /tmp/sieve.zip.
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
OUT_DIR="${1:-/tmp/sieve-build}"
STAGE="${OUT_DIR}/sieve"

rm -rf "${OUT_DIR}"
mkdir -p "${STAGE}"

# Copy everything except .distignore patterns.
rsync -a --exclude-from="${ROOT_DIR}/.distignore" \
    --exclude '.git' --exclude 'node_modules' --exclude 'vendor' \
    --exclude '.DS_Store' \
    "${ROOT_DIR}/" "${STAGE}/"

( cd "${OUT_DIR}" && zip -rq /tmp/sieve.zip sieve )
echo "Built /tmp/sieve.zip from ${STAGE}"
