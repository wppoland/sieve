/**
 * wp-scripts ships the lint rules; this only records one fact about this
 * plugin that its module resolver cannot work out on its own.
 *
 * Flat config, not .eslintrc: wp-scripts 35 runs ESLint 9, which ignores the
 * legacy file entirely rather than warning about it.
 */
const base = require( '@wordpress/scripts/config/eslint.config.cjs' );

module.exports = [
	...base,
	{
		settings: {
			// Not an npm package. webpack's WordPress externals plugin maps it
			// to the wp.serverSideRender global and lists wp-server-side-render
			// in the generated .asset.php, which the built block already does.
			// Installing it would ship a second copy of what WordPress loads.
			'import/core-modules': [ '@wordpress/server-side-render' ],
		},
	},
];
