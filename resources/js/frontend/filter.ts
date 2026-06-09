/**
 * Sieve frontend - dependency-free faceted filtering.
 *
 * Enhances the server-rendered widget: collects filter state from the DOM,
 * fetches fresh fragments from the REST endpoint, and swaps them in place. No
 * jQuery, no full reload. Designed for Core Web Vitals: the results container's
 * height is reserved during a fetch (zero CLS), input is debounced, and the swap
 * yields to the main thread to protect INP.
 */
import '../../css/frontend.css';

interface SieveData {
	restUrl: string;
	nonce: string;
	prefix: string;
	i18n?: { optionsCount: string; noOptions: string };
}

interface FilterResponse {
	facets_html: string;
	toolbar_html: string;
	results_html: string;
	pagination_html: string;
	found: number;
	count_text: string;
}

declare global {
	interface Window {
		sieveData?: SieveData;
		scheduler?: { yield?: () => Promise< void > };
	}
}

const data = window.sieveData;
const PREFIX = data?.prefix ?? 'sf_';
const DEBOUNCE = 250;

function ready( fn: () => void ): void {
	if ( document.readyState !== 'loading' ) {
		fn();
	} else {
		document.addEventListener( 'DOMContentLoaded', fn );
	}
}

ready( () => {
	if ( ! data ) {
		return;
	}
	document
		.querySelectorAll< HTMLElement >( '[data-sieve-app]' )
		.forEach( ( app ) => setup( app ) );
} );

function setup( app: HTMLElement ): void {
	let timer: number | undefined;

	const schedule = ( resetPage: boolean ): void => {
		if ( resetPage ) {
			app.dataset.paged = '1';
		}
		window.clearTimeout( timer );
		timer = window.setTimeout( () => run( app ), DEBOUNCE );
	};

	app.addEventListener( 'change', ( event ) => {
		const target = event.target as HTMLElement;
		// The autocomplete option-filter box is visual only; never treat it as a filter value.
		if ( target.hasAttribute( 'data-sieve-filter-options' ) ) {
			return;
		}
		if ( target.closest( '[data-sieve-facets]' ) || target.closest( '[data-sieve-sort]' ) ) {
			syncRanges( app );
			schedule( true );
		}
	} );

	app.addEventListener( 'input', ( event ) => {
		const target = event.target as HTMLElement;
		if ( target.hasAttribute( 'data-sieve-filter-options' ) ) {
			filterOptions( target as HTMLInputElement );
			return;
		}
		if ( target.classList.contains( 'sieve-range__min' ) || target.classList.contains( 'sieve-range__max' ) || target.classList.contains( 'sieve-search' ) ) {
			syncRanges( app );
			schedule( true );
		}
	} );

	app.addEventListener( 'submit', ( event ) => event.preventDefault() );

	app.addEventListener( 'click', ( event ) => onClick( app, event ) );

	window.addEventListener( 'popstate', () => {
		hydrateFromUrl( app );
		run( app, false );
	} );
}

function onClick( app: HTMLElement, event: Event ): void {
	const el = event.target as HTMLElement;

	const letter = el.closest< HTMLElement >( '.sieve-az__letter' );
	if ( letter ) {
		event.preventDefault();
		filterByLetter( letter );
		return;
	}

	const chip = el.closest< HTMLElement >( '[data-sieve-chip]' );
	if ( chip ) {
		event.preventDefault();
		removeFilter( app, chip.dataset.facet ?? '', chip.dataset.value ?? '' );
		app.dataset.paged = '1';
		run( app );
		return;
	}

	if ( el.closest( '[data-sieve-reset]' ) ) {
		event.preventDefault();
		resetAll( app );
		app.dataset.paged = '1';
		run( app );
		return;
	}

	const page = el.closest< HTMLElement >( '[data-sieve-page]' );
	if ( page ) {
		event.preventDefault();
		app.dataset.paged = page.dataset.sievePage ?? '1';
		run( app );
		app.querySelector( '[data-sieve-results]' )?.scrollIntoView( { behavior: 'smooth', block: 'start' } );
		return;
	}

	if ( el.closest( '[data-sieve-open]' ) ) {
		app.classList.add( 'is-drawer-open' );
		el.closest( '[data-sieve-open]' )?.setAttribute( 'aria-expanded', 'true' );
		( app.querySelector( '[data-sieve-apply]' ) as HTMLElement | null )?.removeAttribute( 'hidden' );
		return;
	}

	if ( el.closest( '[data-sieve-close]' ) ) {
		closeDrawer( app );
	}
}

/**
 * Autocomplete facet: hide options whose label does not contain the typed text.
 * Purely client-side; the checkbox values are untouched so an active filter
 * stays applied even if its option is hidden by the search.
 */
function filterOptions( input: HTMLInputElement ): void {
	const q = input.value.trim().toLowerCase();
	const wrap = input.closest( '.sieve-autocomplete' );
	if ( ! wrap ) {
		return;
	}
	let visible = 0;
	wrap.querySelectorAll< HTMLElement >( '.sieve-choice' ).forEach( ( item ) => {
		const label = item.querySelector( '.sieve-choice__label' )?.textContent?.toLowerCase() ?? '';
		const hide = q !== '' && ! label.includes( q );
		item.hidden = hide;
		if ( ! hide ) {
			visible++;
		}
	} );
	announceCount( wrap, visible );
}

/**
 * A-Z facet: show only options whose label starts with the chosen letter ("all"
 * shows everything). Client-side display filter; checkbox values are untouched.
 */
function filterByLetter( btn: HTMLElement ): void {
	const wrap = btn.closest( '.sieve-az' );
	if ( ! wrap ) {
		return;
	}
	const letter = btn.dataset.letter ?? 'all';
	wrap.querySelectorAll( '.sieve-az__letter' ).forEach( ( b ) => {
		const on = b === btn;
		b.classList.toggle( 'is-active', on );
		b.setAttribute( 'aria-pressed', String( on ) );
	} );
	let visible = 0;
	wrap.querySelectorAll< HTMLElement >( '.sieve-choice' ).forEach( ( item ) => {
		const label = ( item.querySelector( '.sieve-choice__label' )?.textContent ?? '' ).trim();
		// First code point (not UTF-16 unit), to match the PHP mb_substr letters.
		const first = ( [ ...label ][ 0 ] ?? '' ).toUpperCase();
		item.hidden = letter !== 'all' && first !== letter;
		if ( ! item.hidden ) {
			visible++;
		}
	} );
	announceCount( wrap, visible );
}

/**
 * Update a facet's visually-hidden live region with the remaining option count,
 * so screen-reader users hear the effect of typing / picking a letter.
 */
function announceCount( wrap: Element, visible: number ): void {
	const status = wrap.querySelector( '[data-sieve-filter-status]' );
	if ( ! status ) {
		return;
	}
	status.textContent = visible === 0
		? ( data?.i18n?.noOptions ?? 'No matching options' )
		: ( data?.i18n?.optionsCount ?? '%d options' ).replace( '%d', String( visible ) );
}

function closeDrawer( app: HTMLElement ): void {
	app.classList.remove( 'is-drawer-open' );
	app.querySelector( '[data-sieve-open]' )?.setAttribute( 'aria-expanded', 'false' );
	( app.querySelector( '[data-sieve-apply]' ) as HTMLElement | null )?.setAttribute( 'hidden', '' );
}

/**
 * Mirror the two price number inputs into the hidden facet value, clearing it
 * when the range spans the full bounds so an untouched slider adds no filter.
 */
function syncRanges( app: HTMLElement ): void {
	app.querySelectorAll< HTMLElement >( '.sieve-range' ).forEach( ( range ) => {
		const minEl = range.querySelector< HTMLInputElement >( '.sieve-range__min' );
		const maxEl = range.querySelector< HTMLInputElement >( '.sieve-range__max' );
		const hidden = range.querySelector< HTMLInputElement >( '.sieve-range__value' );
		if ( ! minEl || ! maxEl || ! hidden ) {
			return;
		}
		const lo = parseFloat( minEl.value );
		const hi = parseFloat( maxEl.value );
		const bMin = parseFloat( range.dataset.min ?? '0' );
		const bMax = parseFloat( range.dataset.max ?? '0' );
		hidden.value = lo <= bMin && hi >= bMax ? '' : `${ lo }-${ hi }`;
	} );
}

function removeFilter( app: HTMLElement, slug: string, value: string ): void {
	const facet = app.querySelector< HTMLElement >( `[data-sieve-facet="${ slug }"]` );

	if ( 'q' === slug ) {
		const search = app.querySelector< HTMLInputElement >( '.sieve-search' );
		if ( search ) {
			search.value = '';
		}
		return;
	}

	if ( ! facet ) {
		return;
	}

	if ( facet.querySelector( '.sieve-range' ) ) {
		const minEl = facet.querySelector< HTMLInputElement >( '.sieve-range__min' );
		const maxEl = facet.querySelector< HTMLInputElement >( '.sieve-range__max' );
		const range = facet.querySelector< HTMLElement >( '.sieve-range' );
		const hidden = facet.querySelector< HTMLInputElement >( '.sieve-range__value' );
		if ( minEl && maxEl && range ) {
			minEl.value = range.dataset.min ?? '';
			maxEl.value = range.dataset.max ?? '';
		}
		if ( hidden ) {
			hidden.value = '';
		}
		return;
	}

	facet.querySelectorAll< HTMLInputElement >( 'input' ).forEach( ( input ) => {
		if ( '' === value || input.value === value ) {
			input.checked = false;
		}
	} );
	facet.querySelectorAll< HTMLSelectElement >( 'select' ).forEach( ( select ) => {
		select.value = '';
	} );
}

function resetAll( app: HTMLElement ): void {
	app.querySelectorAll< HTMLInputElement >( '[data-sieve-facets] input' ).forEach( ( input ) => {
		if ( 'checkbox' === input.type || 'radio' === input.type ) {
			input.checked = false;
		} else {
			input.value = '';
		}
	} );
	app.querySelectorAll< HTMLSelectElement >( '[data-sieve-facets] select' ).forEach( ( select ) => {
		select.value = '';
	} );
	app.querySelectorAll< HTMLInputElement >( '.sieve-range__value' ).forEach( ( hidden ) => {
		hidden.value = '';
	} );
	const sort = app.querySelector< HTMLSelectElement >( '[data-sieve-sort]' );
	if ( sort ) {
		sort.value = '';
	}
}

/**
 * Read the current state from the DOM into the query object the server expects.
 */
function collectQuery( app: HTMLElement ): Record< string, string > {
	const query: Record< string, string > = {};
	const multi: Record< string, string[] > = {};

	app
		.querySelectorAll< HTMLInputElement >( '[data-sieve-facets] input' )
		.forEach( ( input ) => {
			if ( ( 'checkbox' === input.type || 'radio' === input.type ) && ! input.checked ) {
				return;
			}
			if ( '' === input.value || ! input.name ) {
				return;
			}
			const base = input.name.replace( /\[\]$/, '' );
			( multi[ base ] ??= [] ).push( input.value );
		} );

	app
		.querySelectorAll< HTMLSelectElement >( '[data-sieve-facets] select' )
		.forEach( ( select ) => {
			if ( select.value && select.name ) {
				( multi[ select.name ] ??= [] ).push( select.value );
			}
		} );

	Object.entries( multi ).forEach( ( [ name, values ] ) => {
		query[ name ] = values.join( ',' );
	} );

	const sort = app.querySelector< HTMLSelectElement >( '[data-sieve-sort]' );
	if ( sort?.value ) {
		query[ `${ PREFIX }orderby` ] = sort.value;
	}

	const paged = app.dataset.paged ?? '1';
	if ( '1' !== paged ) {
		query[ `${ PREFIX }paged` ] = paged;
	}

	return query;
}

/**
 * Re-apply state from the URL (back/forward navigation).
 */
function hydrateFromUrl( app: HTMLElement ): void {
	const params = new URLSearchParams( window.location.search );
	resetAll( app );
	app.dataset.paged = '1';

	params.forEach( ( value, key ) => {
		if ( ! key.startsWith( PREFIX ) ) {
			return;
		}
		const name = key.slice( PREFIX.length );
		if ( 'paged' === name ) {
			app.dataset.paged = value;
			return;
		}
		if ( 'orderby' === name ) {
			const sort = app.querySelector< HTMLSelectElement >( '[data-sieve-sort]' );
			if ( sort ) {
				sort.value = value;
			}
			return;
		}
		applyValueToFacet( app, name, value );
	} );
	syncRanges( app );
}

function applyValueToFacet( app: HTMLElement, name: string, value: string ): void {
	if ( 'q' === name ) {
		const search = app.querySelector< HTMLInputElement >( '.sieve-search' );
		if ( search ) {
			search.value = value;
		}
		return;
	}
	const facet = app.querySelector< HTMLElement >( `[data-sieve-facet="${ name }"]` );
	if ( ! facet ) {
		return;
	}
	if ( facet.querySelector( '.sieve-range' ) ) {
		const [ lo, hi ] = value.split( '-' );
		const minEl = facet.querySelector< HTMLInputElement >( '.sieve-range__min' );
		const maxEl = facet.querySelector< HTMLInputElement >( '.sieve-range__max' );
		if ( minEl && lo ) {
			minEl.value = lo;
		}
		if ( maxEl && hi ) {
			maxEl.value = hi;
		}
		return;
	}
	const wanted = value.split( ',' );
	facet.querySelectorAll< HTMLInputElement >( 'input' ).forEach( ( input ) => {
		if ( wanted.includes( input.value ) ) {
			input.checked = true;
		}
	} );
	facet.querySelectorAll< HTMLSelectElement >( 'select' ).forEach( ( select ) => {
		if ( wanted.includes( select.value ) || wanted[ 0 ] ) {
			select.value = wanted[ 0 ];
		}
	} );
}

async function yieldToMain(): Promise< void > {
	if ( window.scheduler?.yield ) {
		await window.scheduler.yield();
		return;
	}
	await new Promise< void >( ( resolve ) => requestAnimationFrame( () => resolve() ) );
}

async function run( app: HTMLElement, pushHistory = true ): Promise< void > {
	if ( ! data ) {
		return;
	}

	const search = app.querySelector< HTMLInputElement >( '.sieve-search' );
	const query = collectQuery( app );
	if ( search?.value ) {
		query[ `${ PREFIX }q` ] = search.value;
	}

	const results = app.querySelector< HTMLElement >( '[data-sieve-results]' );
	if ( results ) {
		results.style.minHeight = `${ results.offsetHeight }px`;
	}
	app.classList.add( 'is-loading' );

	try {
		const response = await fetch( data.restUrl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': data.nonce,
			},
			body: JSON.stringify( { query } ),
		} );
		const payload = ( await response.json() ) as FilterResponse;

		await yieldToMain();
		applyFragments( app, payload );

		if ( pushHistory ) {
			updateHistory( query );
		}
	} catch ( error ) {
		// Network/parse failure: leave the current results in place.
		// eslint-disable-next-line no-console
		console.error( 'Sieve filter request failed', error );
	} finally {
		app.classList.remove( 'is-loading' );
		if ( results ) {
			results.style.minHeight = '';
		}
		closeDrawer( app );
	}
}

function applyFragments( app: HTMLElement, payload: FilterResponse ): void {
	const facets = app.querySelector< HTMLElement >( '[data-sieve-facets]' );
	// The server re-renders facets with fresh counts but cannot know the client
	// -only display state (autocomplete text, active A-Z letter); capture it,
	// then re-apply it to the new nodes so it survives the swap.
	const display = snapshotDisplayFilters( facets );
	setHtml( app, '[data-sieve-facets]', payload.facets_html );
	restoreDisplayFilters( facets, display );
	setHtml( app, '[data-sieve-toolbar]', payload.toolbar_html );
	setHtml( app, '[data-sieve-results]', payload.results_html );
	setHtml( app, '[data-sieve-pagination]', payload.pagination_html );
}

interface DisplayFilters {
	autocomplete: Map< string, string >; // facet slug -> typed text
	az: Map< string, string >; // facet slug -> active letter
}

function snapshotDisplayFilters( facets: HTMLElement | null ): DisplayFilters {
	const autocomplete = new Map< string, string >();
	const az = new Map< string, string >();
	facets?.querySelectorAll< HTMLElement >( '.sieve-facet--autocomplete' ).forEach( ( f ) => {
		const value = f.querySelector< HTMLInputElement >( '[data-sieve-filter-options]' )?.value ?? '';
		if ( value && f.dataset.sieveFacet ) {
			autocomplete.set( f.dataset.sieveFacet, value );
		}
	} );
	facets?.querySelectorAll< HTMLElement >( '.sieve-facet--az_index' ).forEach( ( f ) => {
		const letter = f.querySelector< HTMLElement >( '.sieve-az__letter.is-active' )?.dataset.letter ?? 'all';
		if ( letter !== 'all' && f.dataset.sieveFacet ) {
			az.set( f.dataset.sieveFacet, letter );
		}
	} );
	return { autocomplete, az };
}

function restoreDisplayFilters( facets: HTMLElement | null, state: DisplayFilters ): void {
	if ( ! facets ) {
		return;
	}
	state.autocomplete.forEach( ( value, slug ) => {
		const input = facets.querySelector< HTMLInputElement >(
			`.sieve-facet--autocomplete[data-sieve-facet="${ slug }"] [data-sieve-filter-options]`
		);
		if ( input ) {
			input.value = value;
			filterOptions( input );
		}
	} );
	state.az.forEach( ( letter, slug ) => {
		const btn = facets.querySelector< HTMLElement >(
			`.sieve-facet--az_index[data-sieve-facet="${ slug }"] .sieve-az__letter[data-letter="${ letter }"]`
		);
		if ( btn ) {
			filterByLetter( btn );
		}
	} );
}

function setHtml( app: HTMLElement, selector: string, html: string ): void {
	const el = app.querySelector< HTMLElement >( selector );
	if ( el ) {
		el.innerHTML = html;
	}
}

function updateHistory( query: Record< string, string > ): void {
	const params = new URLSearchParams( window.location.search );
	[ ...params.keys() ].forEach( ( key ) => {
		if ( key.startsWith( PREFIX ) ) {
			params.delete( key );
		}
	} );
	Object.entries( query ).forEach( ( [ key, value ] ) => {
		if ( value ) {
			params.set( key, value );
		}
	} );
	const qs = params.toString();
	const url = qs ? `${ window.location.pathname }?${ qs }` : window.location.pathname;
	window.history.pushState( {}, '', url );
}
