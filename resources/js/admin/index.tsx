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
	// NumberControl is still experimental in @wordpress/components as of
	// WP 6.8, and there is no stable equivalent: TextControl type="number"
	// loses the spinner and the min/max clamping these two fields rely on.
	// If a future WordPress drops the export, the two usages below are the
	// whole blast radius.
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalNumberControl as NumberControl,
} from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import { __, _n, sprintf } from '@wordpress/i18n';
import AppearancePanel, {
	DEFAULT_APPEARANCE,
	type Appearance,
} from './AppearancePanel';

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
	layout?: 'sidebar' | 'stacked' | 'inline';
	zero_counts?: 'hide' | 'show';
	appearance: Appearance;
}

interface Source {
	source: string;
	label: string;
	suggested_type: string;
	group: string;
}

interface FacetTypeOption {
	value: string;
	label: string;
	help: string;
}

const TYPE_OPTIONS = [
	{ label: __( 'Checkboxes', 'sieve' ), value: 'checkbox' },
	{ label: __( 'Radio', 'sieve' ), value: 'radio' },
	{ label: __( 'Dropdown', 'sieve' ), value: 'dropdown' },
	{ label: __( 'Swatches (color / image)', 'sieve' ), value: 'swatch' },
	{ label: __( 'Hierarchy (tree)', 'sieve' ), value: 'hierarchy' },
	{
		label: __( 'Autocomplete (searchable options)', 'sieve' ),
		value: 'autocomplete',
	},
	{ label: __( 'A-Z index', 'sieve' ), value: 'az_index' },
	{ label: __( 'Range slider', 'sieve' ), value: 'range_slider' },
	{ label: __( 'Search box', 'sieve' ), value: 'search' },
];

// Concise, shopper-facing explanation of each facet type, shown as inline help
// under the Type selector so the owner knows what each control looks like.
const TYPE_HELP: Record< string, string > = {
	checkbox: __(
		'Multiple choices can be selected at once. Best for most attributes.',
		'sieve'
	),
	radio: __(
		'Only one choice at a time. Good for mutually exclusive options.',
		'sieve'
	),
	dropdown: __(
		'A compact select menu. Saves space when there are many options.',
		'sieve'
	),
	swatch: __(
		'Colour or image squares. Ideal for colour and pattern attributes.',
		'sieve'
	),
	hierarchy: __(
		'A nested tree. Best for categories with parent/child levels.',
		'sieve'
	),
	autocomplete: __(
		'A checkbox list with a type-to-filter box. Best for very long option lists.',
		'sieve'
	),
	az_index: __(
		'An A-Z bar that filters options by first letter. Good for brand lists.',
		'sieve'
	),
	range_slider: __(
		'A min/max range for the price source. Other sources have no numeric values to slide over.',
		'sieve'
	),
	search: __(
		'A live search box that narrows the product grid as shoppers type.',
		'sieve'
	),
};

function slugFromSource( source: string ): string {
	return source.startsWith( 'tax:' ) ? source.slice( 4 ) : source;
}

// The Type list used to offer all nine types for every source, but the render
// pipeline dispatches on the source: price and search facets are handed an empty
// option list, so a Price facet set to Checkboxes rendered as nothing at all on
// the shop, and a range slider on a taxonomy or on rating came out 0 to 0 and
// dropped the shopper on an empty grid. Only offer what the source can render.
function typesForSource(
	source: string,
	options: FacetTypeOption[]
): FacetTypeOption[] {
	const allowed = options.filter( ( type ) => {
		if ( source === 'price' ) {
			return type.value === 'range_slider';
		}
		if ( source === 'search' ) {
			return type.value === 'search';
		}
		return type.value !== 'range_slider';
	} );

	return allowed.length ? allowed : options;
}

function App() {
	const [ settings, setSettings ] = useState< Settings | null >( null );
	const [ sources, setSources ] = useState< Source[] >( [] );
	const [ typeOptions, setTypeOptions ] =
		useState< FacetTypeOption[] >( TYPE_OPTIONS );
	const [ typeHelp, setTypeHelp ] =
		useState< Record< string, string > >( TYPE_HELP );
	const [ indexedRows, setIndexedRows ] = useState< number >( 0 );
	const [ newSource, setNewSource ] = useState< string >( '' );
	const [ saving, setSaving ] = useState( false );
	const [ reindexing, setReindexing ] = useState( false );
	const [ notice, setNotice ] = useState< {
		type: string;
		text: string;
	} | null >( null );
	const [ loadError, setLoadError ] = useState< string | null >( null );

	useEffect( () => {
		Promise.all( [
			apiFetch< Settings >( { path: 'sieve/v1/settings' } ),
			apiFetch< {
				sources: Source[];
				facet_types: FacetTypeOption[];
				indexed_rows: number;
			} >( { path: 'sieve/v1/catalog' } ),
		] )
			.then( ( [ settingsData, catalog ] ) => {
				setSettings( {
					...settingsData,
					appearance: settingsData.appearance ?? DEFAULT_APPEARANCE,
				} );
				setSources( catalog.sources );
				setIndexedRows( catalog.indexed_rows );
				if ( catalog.facet_types?.length ) {
					setTypeOptions( catalog.facet_types );
					const help: Record< string, string > = { ...TYPE_HELP };
					catalog.facet_types.forEach( ( type ) => {
						if ( type.help ) {
							help[ type.value ] = type.help;
						}
					} );
					setTypeHelp( help );
				}
			} )
			.catch( () => {
				setLoadError(
					__(
						'Could not load Sieve settings. Refresh the page or check that WooCommerce and the REST API are available.',
						'sieve'
					)
				);
			} );
	}, [] );

	if ( loadError ) {
		return (
			<div style={ { maxWidth: 880, margin: '1rem 0' } }>
				<h1>{ __( 'Sieve', 'sieve' ) }</h1>
				<Notice status="error" isDismissible={ false }>
					{ loadError }
				</Notice>
			</div>
		);
	}

	if ( ! settings ) {
		return (
			<div style={ { padding: '2rem' } }>
				<Spinner /> { __( 'Loading Sieve…', 'sieve' ) }
			</div>
		);
	}

	const update = ( patch: Partial< Settings > ) =>
		setSettings( { ...settings, ...patch } );

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
		[ facets[ index ], facets[ target ] ] = [
			facets[ target ],
			facets[ index ],
		];
		update( { facets } );
	};

	const remove = ( index: number ) =>
		update( { facets: settings.facets.filter( ( _, i ) => i !== index ) } );

	const addFacet = () => {
		if ( ! newSource || newSource.startsWith( '__group_' ) ) {
			return;
		}
		const source = sources.find( ( s ) => s.source === newSource );
		if ( ! source ) {
			return;
		}
		const slug = slugFromSource( source.source );
		if ( settings.facets.some( ( f ) => f.slug === slug ) ) {
			setNotice( {
				type: 'warning',
				text: __( 'That facet is already added.', 'sieve' ),
			} );
			return;
		}
		update( {
			facets: [
				...settings.facets,
				{
					slug,
					label: source.label,
					type: source.suggested_type,
					source: source.source,
				},
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
				setNotice( {
					type: 'success',
					text: __( 'Settings saved.', 'sieve' ),
				} );
			} )
			.catch( () => {
				setNotice( {
					type: 'error',
					text: __(
						'Could not save settings. Please try again.',
						'sieve'
					),
				} );
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
					text: sprintf(
						/* translators: %d: number of products re-indexed. */
						_n(
							'Re-indexed %d product.',
							'Re-indexed %d products.',
							data.indexed_products,
							'sieve'
						),
						data.indexed_products
					),
				} );
			} )
			.catch( () => {
				setNotice( {
					type: 'error',
					text: __(
						'Could not rebuild the index. Please try again.',
						'sieve'
					),
				} );
			} )
			.finally( () => setReindexing( false ) );
	};

	const availableSources = sources.filter(
		( s ) =>
			! settings.facets.some(
				( f ) => f.slug === slugFromSource( s.source )
			)
	);

	const sourceGroupLabel = ( group: string ): string => {
		switch ( group ) {
			case 'taxonomy':
				return __( 'Taxonomies', 'sieve' );
			case 'attribute':
				return __( 'Attributes', 'sieve' );
			case 'field':
				return __( 'Product fields', 'sieve' );
			default:
				return __( 'Other sources', 'sieve' );
		}
	};

	const groupedSourceOptions = () => {
		const groups = new Map< string, Source[] >();
		availableSources.forEach( ( source ) => {
			const key = source.group || 'other';
			if ( ! groups.has( key ) ) {
				groups.set( key, [] );
			}
			groups.get( key )!.push( source );
		} );

		const options: Array< {
			label: string;
			value: string;
			disabled?: boolean;
		} > = [
			{
				label: __( 'Select a source…', 'sieve' ),
				value: '',
			},
		];

		for ( const [ group, items ] of groups ) {
			options.push( {
				label: `--- ${ sourceGroupLabel( group ) } ---`,
				value: `__group_${ group }`,
				disabled: true,
			} );
			items.forEach( ( source ) => {
				options.push( {
					label: source.label,
					value: source.source,
				} );
			} );
		}

		return options;
	};

	return (
		<div style={ { maxWidth: 880, margin: '1rem 0' } }>
			<h1>{ __( 'Sieve', 'sieve' ) }</h1>
			<p>
				{ __(
					'Place the filter anywhere with the shortcode',
					'sieve'
				) }{ ' ' }
				<code>[sieve]</code>{ ' ' }
				{ __( 'or the "Sieve Filter" block.', 'sieve' ) }
			</p>

			{ notice && (
				<Notice
					status={ notice.type }
					onRemove={ () => setNotice( null ) }
				>
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
							{ __( 'Indexed rows:', 'sieve' ) }{ ' ' }
							<strong>{ indexedRows }</strong>
						</FlexBlock>
						<FlexItem>
							<Button
								variant="secondary"
								onClick={ reindex }
								isBusy={ reindexing }
								disabled={ reindexing }
							>
								{ __( 'Rebuild index', 'sieve' ) }
							</Button>
						</FlexItem>
					</Flex>
					<p
						style={ {
							margin: '0.75rem 0 0',
							color: '#757575',
							fontSize: '12px',
						} }
					>
						{ __(
							'Sieve keeps a pre-built index so filtered queries stay fast on large catalogs. Rebuild after a bulk import or if counts look out of date, new and edited products are indexed automatically.',
							'sieve'
						) }
					</p>
					{ indexedRows === 0 && (
						<Notice status="warning" isDismissible={ false }>
							{ __(
								'The index is empty. Publish WooCommerce products, then click Rebuild index before placing [sieve] on a page.',
								'sieve'
							) }
						</Notice>
					) }
				</CardBody>
			</Card>

			<Panel>
				<PanelBody
					title={ __( 'Layout & Display', 'sieve' ) }
					initialOpen={ true }
				>
					<div style={ { marginBottom: '1rem' } }>
						<SelectControl
							label={ __( 'Filter layout', 'sieve' ) }
							help={ __(
								'Choose how the filters and the product grid are arranged on the page.',
								'sieve'
							) }
							value={ settings.layout ?? 'sidebar' }
							options={ [
								{
									label: __(
										'Sidebar (filters on left, products on right)',
										'sieve'
									),
									value: 'sidebar',
								},
								{
									label: __(
										'Stacked (filters above product grid)',
										'sieve'
									),
									value: 'stacked',
								},
								{
									label: __(
										'Inline bar (compact horizontal filters)',
										'sieve'
									),
									value: 'inline',
								},
							] }
							onChange={ ( layout: string ) =>
								update( {
									layout: layout as Settings[ 'layout' ],
								} )
							}
						/>
					</div>
					<div style={ { marginBottom: '1rem' } }>
						<SelectControl
							label={ __( 'Options with no matches', 'sieve' ) }
							help={ __(
								'What to do with a filter option that would return no products for the current selection. Hide removes it from the list; Show dimmed keeps it in place, greyed out with a count of 0, so the list does not jump around.',
								'sieve'
							) }
							value={ settings.zero_counts ?? 'hide' }
							options={ [
								{ label: __( 'Hide', 'sieve' ), value: 'hide' },
								{
									label: __( 'Show dimmed', 'sieve' ),
									value: 'show',
								},
							] }
							onChange={ ( zeroCounts: string ) =>
								update( {
									zero_counts:
										zeroCounts as Settings[ 'zero_counts' ],
								} )
							}
						/>
					</div>
					<Flex gap={ 4 } align="flex-start">
						<FlexItem style={ { flex: 1 } }>
							<NumberControl
								label={ __( 'Products per page', 'sieve' ) }
								help={ __(
									'How many products to show before pagination appears.',
									'sieve'
								) }
								value={ settings.per_page }
								min={ 1 }
								onChange={ ( v?: string ) =>
									update( {
										per_page: parseInt( v || '12', 10 ),
									} )
								}
							/>
						</FlexItem>
						<FlexItem style={ { flex: 1 } }>
							<NumberControl
								label={ __( 'Columns', 'sieve' ) }
								help={ __(
									'Product grid columns on wide screens. Fewer columns are used automatically on tablets and phones.',
									'sieve'
								) }
								value={ settings.columns }
								min={ 1 }
								max={ 6 }
								onChange={ ( v?: string ) =>
									update( {
										columns: parseInt( v || '3', 10 ),
									} )
								}
							/>
						</FlexItem>
					</Flex>
				</PanelBody>
				<AppearancePanel
					appearance={ settings.appearance }
					onChange={ ( appearance: Appearance ) =>
						update( { appearance } )
					}
				/>
			</Panel>

			<h2 style={ { margin: '2rem 0 0.75rem', fontSize: '18px' } }>
				{ __( 'Facets', 'sieve' ) }
			</h2>
			{ settings.facets.length === 0 && (
				<Notice status="info" isDismissible={ false }>
					{ __(
						'No facets yet. Pick a source below, click Add facet, then Save settings. Shoppers will not see filters until at least one facet is configured.',
						'sieve'
					) }
				</Notice>
			) }
			{ settings.facets.map( ( facet, index ) => (
				<Card
					key={ facet.slug }
					size="small"
					style={ {
						marginBottom: '0.85rem',
						border: '1px solid #dcdcde',
						borderRadius: '8px',
						boxShadow: '0 1px 2px rgba(0,0,0,0.04)',
						overflow: 'hidden',
					} }
				>
					<CardHeader
						style={ {
							background: '#f8fafc',
							borderBottom: '1px solid #e2e8f0',
							padding: '8px 14px',
							display: 'flex',
							alignItems: 'center',
							justifyContent: 'space-between',
						} }
					>
						<div
							style={ {
								display: 'flex',
								alignItems: 'center',
								gap: '8px',
							} }
						>
							<Button
								icon="arrow-up-alt2"
								label={ __( 'Move up', 'sieve' ) }
								disabled={ index === 0 }
								onClick={ () => move( index, -1 ) }
								size="small"
							/>
							<Button
								icon="arrow-down-alt2"
								label={ __( 'Move down', 'sieve' ) }
								disabled={ index === settings.facets.length - 1 }
								onClick={ () => move( index, 1 ) }
								size="small"
							/>
							<strong
								style={ {
									fontSize: '13px',
									color: '#1e293b',
								} }
							>
								{ facet.label || facet.slug }
							</strong>
							<code
								style={ {
									fontSize: '11px',
									padding: '2px 6px',
									background: '#e2e8f0',
									color: '#475569',
									borderRadius: '4px',
								} }
								title={ __(
									'Internal source key (taxonomy, attribute or product field) used by Sieve.',
									'sieve'
								) }
							>
								{ facet.source }
							</code>
						</div>
						<Button
							icon="trash"
							isDestructive
							label={ __( 'Remove', 'sieve' ) }
							onClick={ () => remove( index ) }
							size="small"
						/>
					</CardHeader>
					<CardBody style={ { padding: '14px' } }>
						<div
							style={ {
								display: 'grid',
								gridTemplateColumns:
									'repeat(auto-fit, minmax(220px, 1fr))',
								gap: '16px',
								alignItems: 'start',
							} }
						>
							<div>
								<TextControl
									label={ __( 'Label', 'sieve' ) }
									value={ facet.label }
									onChange={ ( label: string ) =>
										updateFacet( index, { label } )
									}
								/>
							</div>
							<div>
								<SelectControl
									label={ __( 'Type', 'sieve' ) }
									value={ facet.type }
									options={ typesForSource(
										facet.source,
										typeOptions
									) }
									onChange={ ( type: string ) =>
										updateFacet( index, { type } )
									}
								/>
								{ typeHelp[ facet.type ] && (
									<p
										style={ {
											margin: '4px 0 0',
											fontSize: '12px',
											color: '#64748b',
											lineHeight: 1.4,
										} }
									>
										{ typeHelp[ facet.type ] }
									</p>
								) }
							</div>
						</div>
					</CardBody>
				</Card>
			) ) }

			<Card
				size="small"
				style={ {
					marginTop: '1.25rem',
					marginBottom: '1.5rem',
					border: '1px dashed #cbd5e1',
					borderRadius: '8px',
					background: '#f8fafc',
				} }
			>
				<CardBody style={ { padding: '14px' } }>
					<div
						style={ {
							fontWeight: 600,
							fontSize: '13px',
							marginBottom: '4px',
							color: '#1e293b',
						} }
					>
						{ __( 'Add a facet', 'sieve' ) }
					</div>
					<p
						style={ {
							margin: '0 0 10px',
							fontSize: '12px',
							color: '#64748b',
						} }
					>
						{ __(
							'Pick a product attribute, taxonomy or field to filter by. Sources are detected from your store automatically.',
							'sieve'
						) }
					</p>
					<div
						style={ {
							display: 'flex',
							gap: '10px',
							alignItems: 'center',
						} }
					>
						<div style={ { flex: 1 } }>
							<SelectControl
								value={ newSource }
								options={ groupedSourceOptions() }
								onChange={ setNewSource }
								aria-label={ __( 'Select a source', 'sieve' ) }
							/>
						</div>
						<Button
							variant="secondary"
							onClick={ addFacet }
							disabled={
								! newSource ||
								newSource.startsWith( '__group_' )
							}
						>
							{ __( 'Add facet', 'sieve' ) }
						</Button>
					</div>
				</CardBody>
			</Card>

			<div style={ { marginTop: '1.5rem' } }>
				<Button
					variant="primary"
					onClick={ save }
					isBusy={ saving }
					disabled={ saving }
				>
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
