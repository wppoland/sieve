/**
 * Sieve admin - the Appearance panel.
 *
 * Lets the merchant pick a style preset and four colours, with a live preview,
 * a reset, and a non-blocking WCAG contrast warning. The chosen appearance is
 * part of the settings payload the parent already POSTs, so there is no extra
 * endpoint here.
 */
import {
	Button,
	Card,
	CardBody,
	ColorPicker,
	Flex,
	FlexBlock,
	FlexItem,
	Notice,
	PanelBody,
	SelectControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export interface Appearance {
	preset: 'default' | 'minimal' | 'bordered' | 'soft' | 'unstyled';
	colors: {
		accent: string;
		border: string;
		muted: string;
		background: string;
	};
}

export const DEFAULT_APPEARANCE: Appearance = {
	preset: 'default',
	colors: {
		accent: '#2563eb',
		border: '#e2e4e7',
		muted: '#6b7280',
		background: '#ffffff',
	},
};

const PRESET_OPTIONS = [
	{ label: __( 'Default', 'sieve' ), value: 'default' },
	{ label: __( 'Minimal', 'sieve' ), value: 'minimal' },
	{ label: __( 'Bordered', 'sieve' ), value: 'bordered' },
	{ label: __( 'Soft', 'sieve' ), value: 'soft' },
	{ label: __( 'Unstyled', 'sieve' ), value: 'unstyled' },
];

const COLOR_FIELDS: Array< { key: keyof Appearance[ 'colors' ]; label: string } > = [
	{ key: 'accent', label: __( 'Accent', 'sieve' ) },
	{ key: 'border', label: __( 'Border', 'sieve' ) },
	{ key: 'muted', label: __( 'Muted text', 'sieve' ) },
	{ key: 'background', label: __( 'Background', 'sieve' ) },
];

/**
 * Normalise a colour string to #rrggbb, expanding 3-digit shorthand and
 * dropping an 8-digit alpha suffix. Returns null if it cannot be parsed.
 */
function normaliseHex( value: string ): string | null {
	const hex = value.trim().replace( /^#/, '' ).toLowerCase();
	if ( /^[0-9a-f]{3}$/.test( hex ) ) {
		return `#${ hex[ 0 ] }${ hex[ 0 ] }${ hex[ 1 ] }${ hex[ 1 ] }${ hex[ 2 ] }${ hex[ 2 ] }`;
	}
	if ( /^[0-9a-f]{6}$/.test( hex ) || /^[0-9a-f]{8}$/.test( hex ) ) {
		return `#${ hex.slice( 0, 6 ) }`;
	}
	return null;
}

function luminance( hex: string ): number | null {
	const norm = normaliseHex( hex );
	if ( ! norm ) {
		return null;
	}
	const channels = [ 1, 3, 5 ].map( ( i ) => {
		const c = parseInt( norm.slice( i, i + 2 ), 16 ) / 255;
		return c <= 0.03928 ? c / 12.92 : ( ( c + 0.055 ) / 1.055 ) ** 2.4;
	} );
	return 0.2126 * channels[ 0 ] + 0.7152 * channels[ 1 ] + 0.0722 * channels[ 2 ];
}

/** WCAG contrast ratio. Returns null if either colour is unparseable. */
function contrastRatio( a: string, b: string ): number | null {
	const la = luminance( a );
	const lb = luminance( b );
	if ( la === null || lb === null ) {
		return null;
	}
	const lighter = Math.max( la, lb );
	const darker = Math.min( la, lb );
	return ( lighter + 0.05 ) / ( darker + 0.05 );
}

interface Props {
	appearance: Appearance;
	onChange: ( appearance: Appearance ) => void;
}

export default function AppearancePanel( { appearance, onChange }: Props ) {
	const { preset, colors } = appearance;
	const isUnstyled = preset === 'unstyled';

	const setColor = ( key: keyof Appearance[ 'colors' ], value: string ) => {
		const hex = normaliseHex( value ) ?? value;
		onChange( { ...appearance, colors: { ...colors, [ key ]: hex } } );
	};

	const reset = () => onChange( DEFAULT_APPEARANCE );

	// Active/selected cues paint white text on the accent, so check accent vs
	// white; also surface a softer note for muted-on-background.
	const accentRatio = contrastRatio( colors.accent, '#ffffff' );
	const mutedRatio = contrastRatio( colors.muted, colors.background );

	return (
		<PanelBody title={ __( 'Appearance', 'sieve' ) } initialOpen={ false }>
			<SelectControl
				label={ __( 'Style preset', 'sieve' ) }
				help={ __(
					'Visual style for the filter panel. Unstyled removes plugin CSS so your theme controls everything through .sieve-* classes.',
					'sieve'
				) }
				value={ preset }
				options={ PRESET_OPTIONS }
				onChange={ ( value: string ) =>
					onChange( {
						...appearance,
						preset: value as Appearance[ 'preset' ],
					} )
				}
			/>

			{ isUnstyled && (
				<Card style={ { marginTop: '1rem' } }>
					<CardBody>
						<p style={ { margin: 0 } }>
							{ __(
								'Unstyled mode ships no plugin CSS. Your theme styles everything through the semantic .sieve-* classes (.sieve-app, .sieve-facet, .sieve-chip, .sieve-search, …). Keyboard focus and high-contrast cues are still emitted.',
								'sieve'
							) }
						</p>
					</CardBody>
				</Card>
			) }

			{ ! isUnstyled && (
				<>
					<Flex
						gap={ 4 }
						align="flex-start"
						wrap
						style={ { marginTop: '1rem' } }
					>
						{ COLOR_FIELDS.map( ( field ) => (
							<FlexItem key={ field.key }>
								<div
									style={ {
										fontSize: '11px',
										fontWeight: 600,
										textTransform: 'uppercase',
										marginBottom: '0.25rem',
									} }
								>
									{ field.label }{ ' ' }
									<code>{ colors[ field.key ] }</code>
								</div>
								<ColorPicker
									color={ colors[ field.key ] }
									onChange={ ( value: string ) =>
										setColor( field.key, value )
									}
									enableAlpha={ false }
								/>
							</FlexItem>
						) ) }
					</Flex>

					{ accentRatio !== null && accentRatio < 4.5 && (
						<Notice
							status="warning"
							isDismissible={ false }
							className="sieve-contrast-notice"
						>
							{ __(
								'Low contrast: the active/selected state uses white text on the accent colour and may be hard to read.',
								'sieve'
							) }
						</Notice>
					) }
					{ mutedRatio !== null && mutedRatio < 4.5 && (
						<Notice
							status="warning"
							isDismissible={ false }
							className="sieve-contrast-notice"
						>
							{ __(
								'Muted text may be hard to read against the chosen background colour.',
								'sieve'
							) }
						</Notice>
					) }

					<PreviewBox appearance={ appearance } />
				</>
			) }

			<div style={ { marginTop: '1rem' } }>
				<Button variant="secondary" onClick={ reset }>
					{ __( 'Reset to defaults', 'sieve' ) }
				</Button>
			</div>
		</PanelBody>
	);
}

/**
 * A small, self-contained preview that mirrors the four colour variables so the
 * owner sees their choices before saving. Structural preset differences are not
 * reproduced here (those live in the frontend bundle); this previews colours.
 */
function PreviewBox( { appearance }: { appearance: Appearance } ) {
	const { colors } = appearance;
	return (
		<div style={ { marginTop: '1rem' } }>
			<div
				style={ {
					fontSize: '11px',
					fontWeight: 600,
					textTransform: 'uppercase',
					marginBottom: '0.25rem',
				} }
			>
				{ __( 'Preview', 'sieve' ) }
			</div>
			<div
				style={ {
					border: `1px solid ${ colors.border }`,
					background: colors.background,
					borderRadius: '8px',
					padding: '1rem',
					maxWidth: '320px',
				} }
			>
				<strong>{ __( 'Category', 'sieve' ) }</strong>
				<Flex
					align="center"
					gap={ 2 }
					style={ { margin: '0.5rem 0' } }
				>
					<FlexItem>
						<input type="checkbox" checked readOnly />
					</FlexItem>
					<FlexBlock>{ __( 'Accessories', 'sieve' ) }</FlexBlock>
					<FlexItem>
						<span style={ { color: colors.muted } }>12</span>
					</FlexItem>
				</Flex>
				<button
					type="button"
					style={ {
						background: colors.accent,
						color: '#fff',
						border: 'none',
						borderRadius: '4px',
						padding: '0.35rem 0.75rem',
						cursor: 'default',
					} }
				>
					{ __( 'Accessories', 'sieve' ) } &times;
				</button>
				<input
					type="search"
					readOnly
					placeholder={ __( 'Search products', 'sieve' ) }
					style={ {
						display: 'block',
						marginTop: '0.75rem',
						width: '100%',
						border: `1px solid ${ colors.border }`,
						borderRadius: '4px',
						padding: '0.4rem 0.6rem',
					} }
				/>
			</div>
		</div>
	);
}
