import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';
import { createElement } from '@wordpress/element';
import metadata from './block.json';

registerBlockType( metadata.name, {
	edit() {
		const blockProps = useBlockProps();
		return createElement(
			'div',
			blockProps,
			createElement( ServerSideRender, { block: metadata.name } )
		);
	},
	save() {
		return null;
	},
} );
