// Executes the BUILT block bundles against stubbed WordPress globals and
// asserts each one registers with a callable edit. The 1.1.1 lint cleanup
// turned `edit(){}` into a named component assigned to `edit`, which is only
// equivalent if registration still receives a function.
import assert from 'node:assert';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';

const registered = [];
const noop = () => ({});
const wp = {
  blocks: { registerBlockType: (name, settings) => registered.push({ name, settings }) },
  blockEditor: { useBlockProps: () => ({}), InspectorControls: 'InspectorControls' },
  components: { PanelBody: 'PanelBody', ToggleControl: 'ToggleControl', RangeControl: 'RangeControl', TextControl: 'TextControl' },
  element: { createElement: (t, p, ...c) => ({ t, p, c }), Fragment: 'Fragment' },
  i18n: { __: (s) => s, _n: (s) => s, sprintf: (s) => s },
  serverSideRender: 'ServerSideRender',
};

let failures = 0;
for (const block of ['search', 'filter']) {
  registered.length = 0;
  const code = readFileSync(`build/blocks/${block}/index.js`, 'utf8');
  const ctx = vm.createContext({ wp, window: {}, document: { createElement: noop }, console });
  ctx.window.wp = wp;
  try {
    vm.runInContext(code, ctx);
  } catch (e) {
    console.log(`  ${block}: THREW ${e.message}`);
    failures++;
    continue;
  }
  const hit = registered[0];
  const ok = hit && typeof hit.settings?.edit === 'function' && typeof hit.settings?.save === 'function';
  console.log(`  ${block}: registered=${hit?.name ?? 'none'} edit=${typeof hit?.settings?.edit} save=${typeof hit?.settings?.save} ${ok ? 'OK' : 'FAIL'}`);
  if (!ok) failures++;
}

// The search block's edit must still write the block.json attribute keys.
const searchSrc = readFileSync('build/blocks/search/index.js', 'utf8');
for (const key of ['min_chars', 'in_stock_only']) {
  const present = searchSrc.includes(key);
  console.log(`  attribute ${key}: ${present ? 'OK' : 'MISSING'}`);
  if (!present) failures++;
}

assert.strictEqual(failures, 0, `${failures} block check(s) failed`);
console.log('OK both blocks register with a callable edit');
