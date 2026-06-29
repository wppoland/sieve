import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	ToggleControl,
	RangeControl,
	TextControl,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { createElement, Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';

registerBlockType( metadata.name, {
	edit( { attributes, setAttributes } ) {
		const blockProps = useBlockProps();
		return createElement(
			Fragment,
			null,
			createElement(
				InspectorControls,
				null,
				createElement(
					PanelBody,
					{ title: __( 'Search settings', 'sieve' ) },
					createElement( TextControl, {
						label: __( 'Placeholder', 'sieve' ),
						value: attributes.placeholder,
						onChange: ( placeholder ) =>
							setAttributes( { placeholder } ),
					} ),
					createElement( ToggleControl, {
						label: __( 'Show submit button', 'sieve' ),
						checked: attributes.button,
						onChange: ( button ) => setAttributes( { button } ),
					} ),
					createElement( RangeControl, {
						label: __( 'Max results', 'sieve' ),
						value: attributes.limit,
						min: 1,
						max: 20,
						onChange: ( limit ) => setAttributes( { limit } ),
					} ),
					createElement( RangeControl, {
						label: __( 'Minimum characters', 'sieve' ),
						value: attributes.min_chars,
						min: 1,
						max: 10,
						onChange: ( min_chars ) =>
							setAttributes( { min_chars } ),
					} ),
					createElement( ToggleControl, {
						label: __( 'In-stock products only', 'sieve' ),
						checked: attributes.in_stock_only,
						onChange: ( in_stock_only ) =>
							setAttributes( { in_stock_only } ),
					} )
				)
			),
			createElement(
				'div',
				blockProps,
				createElement( ServerSideRender, {
					block: metadata.name,
					attributes,
				} )
			)
		);
	},
	save() {
		return null;
	},
} );
