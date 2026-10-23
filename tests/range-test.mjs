// Dual-thumb slider clamp logic. Run: node tests/range-test.mjs (node >= 23.6 strips TS types).
import assert from 'node:assert/strict';
import { clampPair, percent } from '../resources/js/frontend/range.ts';

assert.deepEqual( clampPair( 20, 80, 0, 100, 'min' ), [ 20, 80 ] );
// Out of bounds clamps to the bounds.
assert.deepEqual( clampPair( -5, 150, 0, 100, 'min' ), [ 0, 100 ] );
// Blank inputs fall back to the bounds (was "NaN-NaN" in the hidden value).
assert.deepEqual( clampPair( NaN, NaN, 10, 90, 'min' ), [ 10, 90 ] );
// The moved thumb stops at the other one.
assert.deepEqual( clampPair( 70, 40, 0, 100, 'min' ), [ 40, 40 ] );
assert.deepEqual( clampPair( 70, 40, 0, 100, 'max' ), [ 70, 70 ] );
assert.equal( percent( 25, 0, 100 ), 25 );
assert.equal( percent( 15, 10, 20 ), 50 );
assert.equal( percent( 5, 5, 5 ), 0 );

console.log( 'range-test: ok' );
