/**
 * Pure helpers for the dual-thumb price slider. No DOM, so
 * tests/range-test.mjs can run them under plain node.
 */

/**
 * Clamp a min/max pair into [min, max]. A blank (NaN) side falls back to its
 * bound, and the thumb that moved never passes the other one.
 * @param lo
 * @param hi
 * @param min
 * @param max
 * @param moved
 */
export function clampPair(
	lo: number,
	hi: number,
	min: number,
	max: number,
	moved: 'min' | 'max'
): [ number, number ] {
	let a = Number.isFinite( lo ) ? Math.min( max, Math.max( min, lo ) ) : min;
	let b = Number.isFinite( hi ) ? Math.min( max, Math.max( min, hi ) ) : max;
	if ( a > b ) {
		if ( 'min' === moved ) {
			a = b;
		} else {
			b = a;
		}
	}
	return [ a, b ];
}

/**
 * Position of a value along the track, 0 to 100.
 * @param value
 * @param min
 * @param max
 */
export function percent( value: number, min: number, max: number ): number {
	return max > min ? ( ( value - min ) / ( max - min ) ) * 100 : 0;
}
