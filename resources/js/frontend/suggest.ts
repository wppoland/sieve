/**
 * Sieve predictive search - dependency-free typeahead.
 *
 * Enhances the server-rendered [sieve_search] form into an accessible combobox:
 * debounced fetches to the suggest endpoint, an aborted in-flight request on each
 * keystroke, full keyboard navigation, and an ARIA listbox of product matches.
 * No jQuery, no framework. The form still submits to the native product search
 * when JavaScript is unavailable.
 */
import '../../css/search.css';

interface SuggestResult {
	id: number;
	name: string;
	url: string;
	image: string;
	sku: string;
	price_html: string;
}

interface SuggestCategory {
	id: number;
	name: string;
	url: string;
	count: number;
	count_label: string;
}

interface SuggestResponse {
	results: SuggestResult[];
	categories: SuggestCategory[];
	search_url: string;
}

interface SieveSearchData {
	restUrl: string;
	nonce: string;
	i18n: {
		noResults: string;
		viewAll: string;
		searching: string;
		productsHeading: string;
		categoriesHeading: string;
		oneResult: string;
		manyResults: string;
	};
}

declare global {
	interface Window {
		sieveSearchData?: SieveSearchData;
	}
}

const data = window.sieveSearchData;
const DEBOUNCE = 200;

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
		.querySelectorAll< HTMLElement >( '[data-sieve-search]' )
		.forEach( ( widget ) => setup( widget ) );
} );

function escapeHtml( value: string ): string {
	const div = document.createElement( 'div' );
	div.textContent = value;
	// textContent/innerHTML escapes &, <, > but not quotes; escape them too so a
	// value is safe inside a double-quoted attribute (defence in depth).
	return div.innerHTML.replace( /"/g, '&quot;' ).replace( /'/g, '&#39;' );
}

function setup( widget: HTMLElement ): void {
	const input = widget.querySelector< HTMLInputElement >(
		'[data-sieve-search-input]'
	);
	const results = widget.querySelector< HTMLElement >(
		'[data-sieve-search-results]'
	);
	const status = widget.querySelector< HTMLElement >(
		'[data-sieve-search-status]'
	);
	if ( ! input || ! results || ! data ) {
		return;
	}

	const announce = ( message: string ): void => {
		if ( status ) {
			status.textContent = message;
		}
	};

	const limit = Number( widget.dataset.limit ) || 6;
	const minChars = Number( widget.dataset.minChars ) || 2;
	const inStockOnly = widget.dataset.inStockOnly !== '0';
	const listId = results.id;

	let timer: number | undefined;
	let controller: AbortController | undefined;
	let requestId = 0;
	let active = -1;
	let options: HTMLElement[] = [];

	const close = (): void => {
		results.hidden = true;
		results.innerHTML = '';
		input.setAttribute( 'aria-expanded', 'false' );
		input.removeAttribute( 'aria-activedescendant' );
		announce( '' );
		options = [];
		active = -1;
	};

	const open = (): void => {
		results.hidden = false;
		input.setAttribute( 'aria-expanded', 'true' );
	};

	const setActive = ( index: number ): void => {
		if ( options.length === 0 ) {
			return;
		}
		if ( index < 0 ) {
			index = options.length - 1;
		} else if ( index >= options.length ) {
			index = 0;
		}
		options.forEach( ( el, i ) => {
			const isActive = i === index;
			el.classList.toggle( 'is-active', isActive );
			el.setAttribute( 'aria-selected', isActive ? 'true' : 'false' );
		} );
		active = index;
		const current = options[ active ];
		input.setAttribute( 'aria-activedescendant', current.id );
		current.scrollIntoView( { block: 'nearest' } );
	};

	const render = ( payload: SuggestResponse ): void => {
		const { i18n } = data;
		const categories = payload.categories ?? [];
		const productResults = payload.results ?? [];

		if ( productResults.length === 0 && categories.length === 0 ) {
			// Visible message for sighted users, marked role="presentation" so
			// it is not an invalid (and screen-reader-pruned) listbox child; the
			// live region carries the same message to assistive tech.
			results.innerHTML = `<div class="sieve-search__empty" role="presentation">${ escapeHtml(
				i18n.noResults
			) }</div>`;
			options = [];
			active = -1;
			announce( i18n.noResults );
			open();
			return;
		}

		// Both group headings are always rendered as the single source of each
		// group's accessible name (via aria-labelledby), but are visually hidden
		// unless both kinds of result are present, keeping the dropdown uncluttered.
		const showHeadings = categories.length > 0 && productResults.length > 0;
		const catHeadingId = `${ listId }-cat-h`;
		const prodHeadingId = `${ listId }-prod-h`;
		let optIndex = 0;

		const heading = ( id: string, text: string ): string =>
			`<div id="${ id }" class="sieve-search__heading${
				showHeadings ? '' : ' screen-reader-text'
			}" role="presentation">${ escapeHtml( text ) }</div>`;

		const categoryGroup = categories.length
			? `<div class="sieve-search__group" role="group" aria-labelledby="${ catHeadingId }">${ heading(
					catHeadingId,
					i18n.categoriesHeading
			  ) }${ categories
					.map( ( category ) => {
						const optId = `${ listId }-opt-${ optIndex++ }`;
						return `<a id="${ optId }" class="sieve-search__item sieve-search__item--category" role="option" aria-selected="false" href="${ escapeHtml(
							category.url
						) }"><span class="sieve-search__meta"><span class="sieve-search__name">${ escapeHtml(
							category.name
						) }</span><span class="sieve-search__count">${ escapeHtml(
							category.count_label
						) }</span></span></a>`;
					} )
					.join( '' ) }</div>`
			: '';

		const productGroup = productResults.length
			? `<div class="sieve-search__group" role="group" aria-labelledby="${ prodHeadingId }">${ heading(
					prodHeadingId,
					i18n.productsHeading
			  ) }${ productResults
					.map( ( product ) => {
						const optId = `${ listId }-opt-${ optIndex++ }`;
						const thumb = product.image
							? `<span class="sieve-search__thumb"><img src="${ escapeHtml(
									product.image
							  ) }" alt="" loading="lazy" width="44" height="44"></span>`
							: '';
						const sku = product.sku
							? `<span class="sieve-search__sku">${ escapeHtml(
									product.sku
							  ) }</span>`
							: '';
						// price_html is trusted WooCommerce output.
						const price = product.price_html
							? `<span class="sieve-search__price">${ product.price_html }</span>`
							: '';
						return `<a id="${ optId }" class="sieve-search__item" role="option" aria-selected="false" href="${ escapeHtml(
							product.url
						) }">${ thumb }<span class="sieve-search__meta"><span class="sieve-search__name">${ escapeHtml(
							product.name
						) }</span>${ sku }${ price }</span></a>`;
					} )
					.join( '' ) }</div>`
			: '';

		// "View all" is a real role="option" so arrow keys reach it and Enter
		// activates it, instead of a role-less anchor stranded in the listbox.
		const viewAll = payload.search_url
			? `<a id="${ listId }-opt-${ optIndex++ }" class="sieve-search__item sieve-search__all" role="option" aria-selected="false" href="${ escapeHtml(
					payload.search_url
			  ) }">${ escapeHtml( i18n.viewAll ) }</a>`
			: '';

		results.innerHTML = categoryGroup + productGroup + viewAll;
		options = Array.from(
			results.querySelectorAll< HTMLElement >( '[role="option"]' )
		);
		active = -1;
		const matches = categories.length + productResults.length;
		announce(
			matches === 1
				? i18n.oneResult
				: i18n.manyResults.replace( '%d', String( matches ) )
		);
		open();
	};

	const run = async (): Promise< void > => {
		const term = input.value.trim();
		if ( term.length < minChars ) {
			close();
			return;
		}

		controller?.abort();
		controller = new AbortController();
		const current = ++requestId;
		results.setAttribute( 'aria-busy', 'true' );
		announce( data.i18n.searching );

		const url = new URL( data.restUrl );
		url.searchParams.set( 'q', term );
		url.searchParams.set( 'limit', String( limit ) );
		url.searchParams.set( 'in_stock_only', inStockOnly ? '1' : '0' );

		try {
			const response = await fetch( url.toString(), {
				headers: { 'X-WP-Nonce': data.nonce },
				credentials: 'same-origin',
				signal: controller.signal,
			} );
			const payload = ( await response.json() ) as SuggestResponse;
			if ( current !== requestId ) {
				return;
			}
			render( payload );
		} catch ( error ) {
			if ( ( error as Error ).name !== 'AbortError' ) {
				// eslint-disable-next-line no-console
				console.error( 'Sieve suggest request failed', error );
			}
		} finally {
			if ( current === requestId ) {
				results.removeAttribute( 'aria-busy' );
			}
		}
	};

	const schedule = (): void => {
		window.clearTimeout( timer );
		timer = window.setTimeout( run, DEBOUNCE );
	};

	input.addEventListener( 'input', schedule );

	input.addEventListener( 'focus', () => {
		if (
			results.innerHTML.trim() !== '' &&
			input.value.trim().length >= minChars
		) {
			open();
		}
	} );

	input.addEventListener( 'keydown', ( event ) => {
		if ( results.hidden ) {
			return;
		}
		switch ( event.key ) {
			case 'ArrowDown':
				event.preventDefault();
				setActive( active + 1 );
				break;
			case 'ArrowUp':
				event.preventDefault();
				setActive( active - 1 );
				break;
			case 'Enter':
				if ( active >= 0 && options[ active ] ) {
					event.preventDefault();
					window.location.assign(
						options[ active ].getAttribute( 'href' ) ?? '#'
					);
				}
				break;
			case 'Escape':
				event.preventDefault();
				close();
				break;
			default:
				break;
		}
	} );

	document.addEventListener( 'click', ( event ) => {
		if ( ! widget.contains( event.target as Node ) ) {
			close();
		}
	} );
}
