#!/usr/bin/env bash
# Run the official WordPress Plugin Check (PCP) against the dev-mounted Sieve
# plugin inside wp-env. Requires `npm run env:start` and Docker.
#
# Usage:
#   ./scripts/plugin-check.sh            # severity 7 (errors only)
#   SEVERITY=5 ./scripts/plugin-check.sh # include warnings
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SEVERITY="${SEVERITY:-7}"

run_cli() { ( cd "${ROOT_DIR}" && npx wp-env run cli wp "$@" ); }

if ! run_cli plugin is-active plugin-check >/dev/null 2>&1; then
    echo "Installing plugin-check..."
    run_cli plugin install plugin-check --activate
fi

echo "=== Plugin Check: sieve ==="
run_cli plugin check sieve --format=table --severity="${SEVERITY}"
