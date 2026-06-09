#!/usr/bin/env bash
# End-to-end smoke test for Sieve inside wp-env: activate, seed products, index,
# place the shortcode on a page, and assert the rendered filter contains facets
# and products. Requires `npm run env:start`.
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cli() { ( cd "${ROOT_DIR}" && npx wp-env run cli wp "$@" ); }

echo "== activate plugins =="
cli plugin activate woocommerce sieve

echo "== seed categories + products =="
cli eval-file wp-content/plugins/sieve/scripts/seed.php

echo "== build index =="
cli eval '\Sieve\Plugin::instance()->boot(); echo "indexed products: " . \Sieve\Plugin::instance()->container()->get(\Sieve\Service\ProductIndexer::class)->indexAll() . "\n"; echo "rows: " . \Sieve\Plugin::instance()->container()->get(\Sieve\Repository\IndexRepository::class)->rowCount() . "\n";'

echo "== place shortcode on a page =="
PAGE_ID=$(cli post create --post_type=page --post_status=publish --post_title="Sieve Smoke" --post_content="[sieve]" --porcelain)
URL=$(cli post get "${PAGE_ID}" --field=url)
echo "page: ${URL}"

echo "== fetch rendered page =="
# Fetch from the host: localhost:8888 inside the cli container points at the
# container itself, not the WordPress service.
HTML=$(curl -s "${URL}")

pass=0
echo "${HTML}" | grep -q 'sieve-app' && { echo "PASS: filter container present"; } || { echo "FAIL: no sieve-app"; pass=1; }
echo "${HTML}" | grep -q 'sieve-facet' && { echo "PASS: facets rendered"; } || { echo "FAIL: no facets"; pass=1; }
echo "${HTML}" | grep -qi 'Hoodie' && { echo "PASS: products rendered"; } || { echo "FAIL: no products"; pass=1; }

exit ${pass}
