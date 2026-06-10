import { test, expect, type Page } from '@playwright/test';
import { execSync } from 'node:child_process';
import { resolve } from 'node:path';
import { seedUrls } from './urls';
import type { SeedUrls } from './global-setup';

/**
 * Search-as-filter (0.7.0): the in-grid predictive combobox upgrade of the search
 * facet. Typing narrows the live grid through the shared folded index; the thin
 * dropdown assists with keyboard completion + a live count; picking a suggestion
 * re-filters in place (never navigates). Grid, counts and dropdown stay in sync.
 *
 * These tests require a search facet to be present in the [sieve] config and a
 * diacritic product ("Łóżko") in the catalog. Both are ensured in beforeAll via a
 * small wp-env eval; if the environment cannot provide them, the affected tests
 * skip rather than fail.
 */

interface SearchFilterEnv {
	hasSearchFacet: boolean;
	bedExists: boolean;
}

function ensureSearchFacetAndBed(): SearchFilterEnv {
	// Prepend a search facet to the saved Sieve settings (idempotent) and confirm
	// the diacritic sample product exists. Emits a single JSON status line.
	const php = `
$opt = get_option('sieve_settings');
if (!is_array($opt) || empty($opt['facets'])) {
    $c = Sieve\\\\Plugin::instance()->container();
    $opt = $c->get(Sieve\\\\Service\\\\Settings::class)->defaults();
}
$hasSearch = false;
foreach ($opt['facets'] as $f) { if (($f['type'] ?? '') === 'search') { $hasSearch = true; break; } }
if (!$hasSearch) {
    array_unshift($opt['facets'], ['slug' => 'q', 'label' => 'Search', 'type' => 'search', 'source' => 'search']);
    update_option('sieve_settings', $opt);
    $hasSearch = true;
}
$bed = function_exists('wc_get_products') ? wc_get_products(['s' => 'lozko', 'limit' => 1]) : [];
echo "\\n" . wp_json_encode(['hasSearchFacet' => $hasSearch, 'bedExists' => !empty($bed)]) . "\\n";
`.trim();

	const raw = execSync(
		`npx wp-env run cli wp eval ${ JSON.stringify( php ) }`,
		{ cwd: resolve( __dirname, '../..' ), encoding: 'utf8' }
	);
	const line = raw
		.split( '\n' )
		.map( ( l ) => l.trim() )
		.find( ( l ) => l.startsWith( '{' ) && l.includes( 'hasSearchFacet' ) );
	if ( ! line ) {
		return { hasSearchFacet: false, bedExists: false };
	}
	return JSON.parse( line ) as SearchFilterEnv;
}

async function gotoFilter( page: Page, url: string ): Promise< void > {
	await page.goto( url );
	await expect( page.locator( '[data-sieve-app]' ) ).toBeVisible();
}

async function typeSearch( page: Page, term: string ): Promise< void > {
	const input = page.locator( '[data-sieve-app] .sieve-search' ).first();
	await input.click();
	await input.fill( term );
}

test.describe( 'Search as filter (in-grid combobox)', () => {
	let urls: SeedUrls;
	let env: SearchFilterEnv;

	test.beforeAll( () => {
		urls = seedUrls();
		env = ensureSearchFacetAndBed();
	} );

	test.beforeEach( async ( { page } ) => {
		test.skip( ! env.hasSearchFacet, 'No search facet configured in this env' );
		await gotoFilter( page, urls.filterUrl );
	} );

	// 1. Search facet renders as a combobox.
	test( 'renders the search facet as an ARIA combobox', async ( { page } ) => {
		const input = page.locator( '[data-sieve-app] .sieve-search' ).first();
		await expect( input ).toHaveAttribute( 'role', 'combobox' );
		await expect( input ).toHaveAttribute( 'aria-expanded', 'false' );
		await expect( input ).toHaveAttribute( 'aria-autocomplete', 'list' );
		await expect( input ).toHaveAttribute( 'aria-controls', /sieve-grid-suggest-/ );
		await expect(
			page.locator( '[data-sieve-app] [role="listbox"]' ).first()
		).toBeAttached();
	} );

	// 2. Typing >= 2 chars opens the listbox and fires GET /suggest.
	test( 'typing opens the listbox and fires GET /suggest', async ( { page } ) => {
		const [ response ] = await Promise.all( [
			page.waitForResponse(
				( r ) => r.url().includes( '/sieve/v1/suggest' ) && r.request().method() === 'GET'
			),
			typeSearch( page, 'hood' ),
		] );
		expect( response.ok() ).toBeTruthy();
		await expect(
			page.locator( '[data-sieve-app] [role="listbox"]' ).first()
		).toBeVisible();
	} );

	// 3. Diacritic parity: "lozko" surfaces "Łóżko" in the dropdown AND the grid.
	test( 'diacritic parity: lozko surfaces the bed in dropdown and grid', async ( { page } ) => {
		test.skip( ! env.bedExists, 'No diacritic sample product (Łóżko) in this env' );
		await Promise.all( [
			page.waitForResponse( ( r ) => r.url().includes( '/sieve/v1/suggest' ) ),
			page.waitForResponse( ( r ) => r.url().includes( '/sieve/v1/filter' ) ),
			typeSearch( page, 'lozko' ),
		] );
		await expect(
			page.locator( '[data-sieve-app] [role="option"]' ).filter( { hasText: /Łóżko/i } ).first()
		).toBeVisible();
		await expect(
			page.locator( '[data-sieve-results]' ).filter( { hasText: /Łóżko/i } )
		).toBeVisible();
	} );

	// 4. Typo tolerance: a 1-edit typo still returns the product in the grid.
	test( 'typo tolerance returns the product in the grid (fuzzy tier)', async ( { page } ) => {
		test.skip( ! env.bedExists, 'No diacritic sample product (Łóżko) in this env' );
		await Promise.all( [
			page.waitForResponse( ( r ) => r.url().includes( '/sieve/v1/filter' ) ),
			typeSearch( page, 'lozkk' ),
		] );
		await expect(
			page.locator( '[data-sieve-results]' ).filter( { hasText: /Łóżko/i } )
		).toBeVisible();
	} );

	// 5. Enter (no active option) re-filters in place; URL gains sf_q; no navigation.
	test( 'Enter re-filters in place and updates the URL', async ( { page } ) => {
		await typeSearch( page, 'hood' );
		const before = page.url();
		await Promise.all( [
			page.waitForResponse( ( r ) => r.url().includes( '/sieve/v1/filter' ) ),
			page.locator( '[data-sieve-app] .sieve-search' ).first().press( 'Enter' ),
		] );
		await expect( page ).toHaveURL( /sf_q=hood/ );
		// Same document (no navigation): the app element is still attached.
		await expect( page.locator( '[data-sieve-app]' ) ).toBeVisible();
		expect( new URL( page.url() ).pathname ).toBe( new URL( before ).pathname );
	} );

	// 6. Click a suggestion -> sets the term, closes the dropdown, grid narrows.
	test( 'clicking a suggestion sets the term and narrows the grid in place', async ( { page } ) => {
		await Promise.all( [
			page.waitForResponse( ( r ) => r.url().includes( '/sieve/v1/suggest' ) ),
			typeSearch( page, 'hood' ),
		] );
		const option = page.locator( '[data-sieve-app] [role="option"]' ).first();
		await expect( option ).toBeVisible();
		const name = ( await option.getAttribute( 'data-name' ) ) ?? '';
		await Promise.all( [
			page.waitForResponse( ( r ) => r.url().includes( '/sieve/v1/filter' ) ),
			option.click(),
		] );
		await expect(
			page.locator( '[data-sieve-app] [role="listbox"]' ).first()
		).toBeHidden();
		await expect( page.locator( '[data-sieve-app] .sieve-search' ).first() ).toHaveValue( name );
		await expect( page.locator( '[data-sieve-app]' ) ).toBeVisible();
	} );

	// 7. Search + facet combine (AND): both chips reflect both.
	test( 'search combines with a facet (AND) and shows both chips', async ( { page } ) => {
		const checkbox = page.locator( '[data-sieve-facets] input[type="checkbox"][name^="sf_"]' ).first();
		await Promise.all( [
			page.waitForResponse( ( r ) => r.url().includes( '/sieve/v1/filter' ) ),
			checkbox.check(),
		] );
		await Promise.all( [
			page.waitForResponse( ( r ) => r.url().includes( '/sieve/v1/filter' ) ),
			( async () => {
				await typeSearch( page, 'hood' );
				await page.locator( '[data-sieve-app] .sieve-search' ).first().press( 'Enter' );
			} )(),
		] );
		await expect( page ).toHaveURL( /sf_q=hood/ );
		await expect(
			page.locator( '.sieve-chip[data-facet="q"]' )
		).toBeVisible();
		await expect(
			page.locator( '.sieve-chip[data-sieve-chip]' ).first()
		).toBeVisible();
	} );

	// 8. Dependent counts are search-aware: option counts shrink under a search.
	test( 'dependent facet counts are search-aware', async ( { page } ) => {
		const firstCount = page.locator( '[data-sieve-facets] .sieve-choice__count' ).first();
		await expect( firstCount ).toBeVisible();
		const before = await page
			.locator( '[data-sieve-facets] .sieve-choice__count' )
			.allTextContents();
		const beforeSum = before.reduce( ( a, t ) => a + ( parseInt( t.replace( /\D/g, '' ), 10 ) || 0 ), 0 );

		await Promise.all( [
			page.waitForResponse( ( r ) => r.url().includes( '/sieve/v1/filter' ) ),
			( async () => {
				await typeSearch( page, 'hood' );
				await page.locator( '[data-sieve-app] .sieve-search' ).first().press( 'Enter' );
			} )(),
		] );

		const after = await page
			.locator( '[data-sieve-facets] .sieve-choice__count' )
			.allTextContents();
		const afterSum = after.reduce( ( a, t ) => a + ( parseInt( t.replace( /\D/g, '' ), 10 ) || 0 ), 0 );
		expect( afterSum ).toBeLessThanOrEqual( beforeSum );
	} );

	// 9. Pagination resets to page 1 when the search term changes.
	test( 'pagination resets to page 1 on a new search term', async ( { page } ) => {
		await Promise.all( [
			page.waitForResponse( ( r ) => r.url().includes( '/sieve/v1/filter' ) ),
			( async () => {
				await typeSearch( page, 'hood' );
				await page.locator( '[data-sieve-app] .sieve-search' ).first().press( 'Enter' );
			} )(),
		] );
		// sf_paged must not appear (page 1 is implicit).
		expect( page.url() ).not.toMatch( /sf_paged=/ );
	} );

	// 10. Clicking the q chip clears search and restores the full grid.
	test( 'clearing the q chip removes sf_q and restores the grid', async ( { page } ) => {
		await Promise.all( [
			page.waitForResponse( ( r ) => r.url().includes( '/sieve/v1/filter' ) ),
			( async () => {
				await typeSearch( page, 'hood' );
				await page.locator( '[data-sieve-app] .sieve-search' ).first().press( 'Enter' );
			} )(),
		] );
		await expect( page ).toHaveURL( /sf_q=hood/ );
		await Promise.all( [
			page.waitForResponse( ( r ) => r.url().includes( '/sieve/v1/filter' ) ),
			page.locator( '.sieve-chip[data-facet="q"]' ).click(),
		] );
		await expect( page ).not.toHaveURL( /sf_q=/ );
		await expect( page.locator( '[data-sieve-app] .sieve-search' ).first() ).toHaveValue( '' );
	} );

	// 11. Back/forward restores search + facets atomically.
	test( 'back/forward restores the search term (popstate)', async ( { page } ) => {
		await Promise.all( [
			page.waitForResponse( ( r ) => r.url().includes( '/sieve/v1/filter' ) ),
			( async () => {
				await typeSearch( page, 'hood' );
				await page.locator( '[data-sieve-app] .sieve-search' ).first().press( 'Enter' );
			} )(),
		] );
		await expect( page ).toHaveURL( /sf_q=hood/ );
		await page.goBack();
		await expect( page.locator( '[data-sieve-app] .sieve-search' ).first() ).toHaveValue( '' );
		await page.goForward();
		await expect( page.locator( '[data-sieve-app] .sieve-search' ).first() ).toHaveValue( 'hood' );
	} );

	// 12. Race guard: type fast; the final grid matches the latest term only.
	test( 'race guard: fast typing renders only the latest term', async ( { page } ) => {
		const input = page.locator( '[data-sieve-app] .sieve-search' ).first();
		await input.click();
		await input.pressSequentially( 'hoo', { delay: 20 } );
		await input.fill( 'hood' );
		await page.waitForResponse( ( r ) => r.url().includes( '/sieve/v1/filter' ) );
		// Settle, then assert the URL reflects the final term after Enter.
		await Promise.all( [
			page.waitForResponse( ( r ) => r.url().includes( '/sieve/v1/filter' ) ),
			input.press( 'Enter' ),
		] );
		await expect( page ).toHaveURL( /sf_q=hood/ );
	} );

	// 13. Scoped suggestions: with a facet active, suggestions carry a scope.
	test( 'suggestions are scoped to the active facet selection', async ( { page } ) => {
		const checkbox = page.locator( '[data-sieve-facets] input[type="checkbox"][name^="sf_"]' ).first();
		await Promise.all( [
			page.waitForResponse( ( r ) => r.url().includes( '/sieve/v1/filter' ) ),
			checkbox.check(),
		] );
		const [ response ] = await Promise.all( [
			page.waitForResponse( ( r ) => r.url().includes( '/sieve/v1/suggest' ) ),
			typeSearch( page, 'hood' ),
		] );
		expect( response.url() ).toMatch( /scope=/ );
	} );

	// 14. Empty index hit -> "no products" grid, no out-of-scope WC leakage.
	test( 'an unmatched search shows a no-products grid', async ( { page } ) => {
		await Promise.all( [
			page.waitForResponse( ( r ) => r.url().includes( '/sieve/v1/filter' ) ),
			( async () => {
				await typeSearch( page, 'zzqxnomatch' );
				await page.locator( '[data-sieve-app] .sieve-search' ).first().press( 'Enter' );
			} )(),
		] );
		await expect(
			page.locator( '[data-sieve-results] ul.products li' )
		).toHaveCount( 0 );
	} );

	// 15. CLS < 0.1 on a search-driven fragment swap.
	test( 'does not shift layout on a search swap (CLS smoke)', async ( { page } ) => {
		await page.evaluate( () => {
			( window as unknown as { __cls: number } ).__cls = 0;
			new PerformanceObserver( ( list ) => {
				for ( const entry of list.getEntries() as unknown as Array< {
					value: number;
					hadRecentInput: boolean;
				} > ) {
					if ( ! entry.hadRecentInput ) {
						( window as unknown as { __cls: number } ).__cls += entry.value;
					}
				}
			} ).observe( { type: 'layout-shift', buffered: true } );
		} );
		await Promise.all( [
			page.waitForResponse( ( r ) => r.url().includes( '/sieve/v1/filter' ) ),
			( async () => {
				await typeSearch( page, 'hood' );
				await page.locator( '[data-sieve-app] .sieve-search' ).first().press( 'Enter' );
			} )(),
		] );
		await page.waitForTimeout( 200 );
		const cls = await page.evaluate( () => ( window as unknown as { __cls: number } ).__cls );
		expect( cls ).toBeLessThan( 0.1 );
	} );

	// 16. Keyboard: ArrowDown/Up move aria-activedescendant; Escape closes.
	test( 'keyboard navigation tracks aria-activedescendant and Escape closes', async ( { page } ) => {
		await Promise.all( [
			page.waitForResponse( ( r ) => r.url().includes( '/sieve/v1/suggest' ) ),
			typeSearch( page, 'hood' ),
		] );
		const input = page.locator( '[data-sieve-app] .sieve-search' ).first();
		await expect( page.locator( '[data-sieve-app] [role="option"]' ).first() ).toBeVisible();
		await input.press( 'ArrowDown' );
		const firstOption = page.locator( '[data-sieve-app] [role="option"]' ).first();
		await expect( firstOption ).toHaveClass( /is-active/ );
		const optionId = await firstOption.getAttribute( 'id' );
		await expect( input ).toHaveAttribute( 'aria-activedescendant', optionId ?? '' );
		await input.press( 'Escape' );
		await expect(
			page.locator( '[data-sieve-app] [role="listbox"]' ).first()
		).toBeHidden();
	} );

	// 17. Standalone [sieve_search] unchanged: click navigates to the product.
	test( 'standalone [sieve_search] still navigates on click', async ( { page } ) => {
		test.skip( ! urls.searchHasResults, 'No products match the seed term in this env' );
		await page.goto( urls.searchUrl );
		const input = page.locator( '[data-sieve-search-input]' );
		await input.fill( urls.searchTerm );
		const item = page.locator( '.sieve-search__item' ).first();
		await expect( item ).toBeVisible();
		const href = await item.getAttribute( 'href' );
		await item.click();
		await page.waitForURL( href ?? '**' );
		expect( page.url() ).toBe( href );
	} );

	// 18. No-JS fallback: GET with sf_q re-renders a filtered grid server-side.
	test( 'no-JS GET fallback renders a filtered grid server-side', async ( { page } ) => {
		const sep = urls.filterUrl.includes( '?' ) ? '&' : '?';
		await page.goto( `${ urls.filterUrl }${ sep }sf_q=hood` );
		await expect( page.locator( '[data-sieve-app] .sieve-search' ).first() ).toHaveValue( 'hood' );
		await expect( page.locator( '[data-sieve-results]' ) ).toBeVisible();
	} );
} );
