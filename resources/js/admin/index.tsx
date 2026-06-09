/**
 * Sieve admin - the React facet builder.
 *
 * Auto-discovers facet sources from the store, lets the merchant assemble the
 * facet set (add, reorder, retype, remove), saves to the REST API, and triggers
 * a re-index. Built as a WordPress IIFE bundle (wp.element / wp.components).
 */
import { createRoot, useEffect, useState } from '@wordpress/element';
import {
	Button,
	Card,
	CardBody,
	CardHeader,
	Flex,
	FlexBlock,
	FlexItem,
	Notice,
	Panel,
	PanelBody,
	SelectControl,
	Spinner,
	TextControl,
	__experimentalNumberControl as NumberControl,
} from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

interface Facet {
	slug: string;
	label: string;
	type: string;
	source: string;
}

interface Settings {
	facets: Facet[];
	per_page: number;
	columns: number;
}

interface Source {
	source: string;
	label: string;
	suggested_type: string;
	group: string;
}

const TYPE_OPTIONS = [
	{ label: __( 'Checkboxes', 'sieve' ), value: 'checkbox' },
	{ label: __( 'Radio', 'sieve' ), value: 'radio' },
	{ label: __( 'Dropdown', 'sieve' ), value: 'dropdown' },
	{ label: __( 'Range slider', 'sieve' ), value: 'range_slider' },
	{ label: __( 'Search box', 'sieve' ), value: 'search' },
];

function slugFromSource( source: string ): string {
	return source.startsWith( 'tax:' ) ? source.slice( 4 ) : source;
}

function App() {
	const [ settings, setSettings ] = useState< Settings | null >( null );
	const [ sources, setSources ] = useState< Source[] >( [] );
	const [ indexedRows, setIndexedRows ] = useState< number >( 0 );
	const [ newSource, setNewSource ] = useState< string >( '' );
	const [ saving, setSaving ] = useState( false );
	const [ reindexing, setReindexing ] = useState( false );
	const [ notice, setNotice ] = useState< { type: string; text: string } | null >( null );

	useEffect( () => {
		apiFetch< Settings >( { path: 'sieve/v1/settings' } ).then( setSettings );
		apiFetch< { sources: Source[]; indexed_rows: number } >( { path: 'sieve/v1/catalog' } ).then(
			( data ) => {
				setSources( data.sources );
				setIndexedRows( data.indexed_rows );
			}
		);
	}, [] );

	if ( ! settings ) {
		return (
			<div style={ { padding: '2rem' } }>
				<Spinner /> { __( 'Loading Sieve...', 'sieve' ) }
			</div>
		);
	}

	const update = ( patch: Partial< Settings > ) => setSettings( { ...settings, ...patch } );

	const updateFacet = ( index: number, patch: Partial< Facet > ) => {
		const facets = settings.facets.map( ( facet, i ) =>
			i === index ? { ...facet, ...patch } : facet
		);
		update( { facets } );
	};

	const move = ( index: number, delta: number ) => {
		const target = index + delta;
		if ( target < 0 || target >= settings.facets.length ) {
			return;
		}
		const facets = [ ...settings.facets ];
		[ facets[ index ], facets[ target ] ] = [ facets[ target ], facets[ index ] ];
		update( { facets } );
	};

	const remove = ( index: number ) =>
		update( { facets: settings.facets.filter( ( _, i ) => i !== index ) } );

	const addFacet = () => {
		if ( ! newSource ) {
			return;
		}
		const source = sources.find( ( s ) => s.source === newSource );
		if ( ! source ) {
			return;
		}
		const slug = slugFromSource( source.source );
		if ( settings.facets.some( ( f ) => f.slug === slug ) ) {
			setNotice( { type: 'warning', text: __( 'That facet is already added.', 'sieve' ) } );
			return;
		}
		update( {
			facets: [
				...settings.facets,
				{ slug, label: source.label, type: source.suggested_type, source: source.source },
			],
		} );
		setNewSource( '' );
	};

	const save = () => {
		setSaving( true );
		apiFetch< Settings >( {
			path: 'sieve/v1/settings',
			method: 'POST',
			data: settings,
		} )
			.then( ( saved ) => {
				setSettings( saved );
				setNotice( { type: 'success', text: __( 'Settings saved.', 'sieve' ) } );
			} )
			.finally( () => setSaving( false ) );
	};

	const reindex = () => {
		setReindexing( true );
		apiFetch< { indexed_products: number; indexed_rows: number } >( {
			path: 'sieve/v1/reindex',
			method: 'POST',
		} )
			.then( ( data ) => {
				setIndexedRows( data.indexed_rows );
				setNotice( {
					type: 'success',
					text: __( 'Re-indexed ', 'sieve' ) + data.indexed_products + __( ' products.', 'sieve' ),
				} );
			} )
			.finally( () => setReindexing( false ) );
	};

	const availableSources = sources.filter(
		( s ) => ! settings.facets.some( ( f ) => f.slug === slugFromSource( s.source ) )
	);

	return (
		<div style={ { maxWidth: 880, margin: '1rem 0' } }>
			<h1>{ __( 'Sieve', 'sieve' ) }</h1>
			<p>
				{ __( 'Place the filter anywhere with the shortcode', 'sieve' ) }{ ' ' }
				<code>[sieve]</code> { __( 'or the "Sieve Filter" block.', 'sieve' ) }
			</p>

			{ notice && (
				<Notice status={ notice.type } onRemove={ () => setNotice( null ) }>
					{ notice.text }
				</Notice>
			) }

			<Card style={ { marginBottom: '1rem' } }>
				<CardHeader>
					<strong>{ __( 'Index', 'sieve' ) }</strong>
				</CardHeader>
				<CardBody>
					<Flex align="center">
						<FlexBlock>
							{ __( 'Indexed rows: ', 'sieve' ) }
							<strong>{ indexedRows }</strong>
						</FlexBlock>
						<FlexItem>
							<Button variant="secondary" onClick={ reindex } isBusy={ reindexing } disabled={ reindexing }>
								{ __( 'Rebuild index', 'sieve' ) }
							</Button>
						</FlexItem>
					</Flex>
				</CardBody>
			</Card>

			<Panel>
				<PanelBody title={ __( 'Layout', 'sieve' ) } initialOpen={ true }>
					<Flex>
						<FlexItem>
							<NumberControl
								label={ __( 'Products per page', 'sieve' ) }
								value={ settings.per_page }
								min={ 1 }
								onChange={ ( v?: string ) => update( { per_page: parseInt( v || '12', 10 ) } ) }
							/>
						</FlexItem>
						<FlexItem>
							<NumberControl
								label={ __( 'Columns', 'sieve' ) }
								value={ settings.columns }
								min={ 1 }
								max={ 6 }
								onChange={ ( v?: string ) => update( { columns: parseInt( v || '3', 10 ) } ) }
							/>
						</FlexItem>
					</Flex>
				</PanelBody>
			</Panel>

			<h2>{ __( 'Facets', 'sieve' ) }</h2>
			{ settings.facets.map( ( facet, index ) => (
				<Card key={ facet.slug } size="small" style={ { marginBottom: '0.75rem' } }>
					<CardBody>
						<Flex align="flex-end" gap={ 3 }>
							<FlexBlock>
								<TextControl
									label={ __( 'Label', 'sieve' ) }
									value={ facet.label }
									onChange={ ( label: string ) => updateFacet( index, { label } ) }
								/>
							</FlexBlock>
							<FlexBlock>
								<SelectControl
									label={ __( 'Type', 'sieve' ) }
									value={ facet.type }
									options={ TYPE_OPTIONS }
									onChange={ ( type: string ) => updateFacet( index, { type } ) }
								/>
							</FlexBlock>
							<FlexItem>
								<code>{ facet.source }</code>
							</FlexItem>
							<FlexItem>
								<Button icon="arrow-up-alt2" label={ __( 'Move up', 'sieve' ) } onClick={ () => move( index, -1 ) } />
								<Button icon="arrow-down-alt2" label={ __( 'Move down', 'sieve' ) } onClick={ () => move( index, 1 ) } />
								<Button icon="trash" isDestructive label={ __( 'Remove', 'sieve' ) } onClick={ () => remove( index ) } />
							</FlexItem>
						</Flex>
					</CardBody>
				</Card>
			) ) }

			<Flex align="flex-end" gap={ 3 } style={ { marginTop: '1rem' } }>
				<FlexBlock>
					<SelectControl
						label={ __( 'Add a facet', 'sieve' ) }
						value={ newSource }
						options={ [
							{ label: __( 'Select a source...', 'sieve' ), value: '' },
							...availableSources.map( ( s ) => ( { label: s.label, value: s.source } ) ),
						] }
						onChange={ setNewSource }
					/>
				</FlexBlock>
				<FlexItem>
					<Button variant="secondary" onClick={ addFacet } disabled={ ! newSource }>
						{ __( 'Add', 'sieve' ) }
					</Button>
				</FlexItem>
			</Flex>

			<div style={ { marginTop: '1.5rem' } }>
				<Button variant="primary" onClick={ save } isBusy={ saving } disabled={ saving }>
					{ __( 'Save settings', 'sieve' ) }
				</Button>
			</div>
		</div>
	);
}

const root = document.getElementById( 'sieve-admin-root' );
if ( root ) {
	createRoot( root ).render( <App /> );
}
