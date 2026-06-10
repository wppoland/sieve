<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

use Sieve\Container;
use Sieve\Hook\AdminHooks;
use Sieve\Hook\BlockHooks;
use Sieve\Hook\FrontendHooks;
use Sieve\Hook\IndexerHooks;
use Sieve\Hook\RestHooks;
use Sieve\Hook\SearchBlockHooks;
use Sieve\Migrator;
use Sieve\Repository\IndexRepository;
use Sieve\Rest\AdminController;
use Sieve\Rest\FilterController;
use Sieve\Rest\SuggestController;
use Sieve\Service\AppearanceService;
use Sieve\Service\FacetCatalog;
use Sieve\Service\FacetCountService;
use Sieve\Service\FacetRenderer;
use Sieve\Service\FilterEngine;
use Sieve\Service\FilterService;
use Sieve\Service\ProductIndexer;
use Sieve\Service\ResultsRenderer;
use Sieve\Service\SearchRenderer;
use Sieve\Service\SearchResolver;
use Sieve\Service\Settings;
use Sieve\Service\SuggestService;
use Sieve\Service\UrlService;
use Sieve\Shortcode\FilterShortcode;
use Sieve\Shortcode\SearchShortcode;

/**
 * Service registration. Returns a callable that binds every service into the
 * container. Bindings are lazy: nothing is constructed until first resolved.
 */
return static function (Container $c): void {
    // Infrastructure.
    $c->singleton(Migrator::class, static fn (): Migrator => new Migrator());
    $c->singleton(IndexRepository::class, static fn (): IndexRepository => new IndexRepository());

    // Domain services.
    $c->singleton(AppearanceService::class, static fn (): AppearanceService => new AppearanceService());
    $c->singleton(
        Settings::class,
        static fn (): Settings => new Settings($c->get(AppearanceService::class)),
    );
    $c->singleton(FacetCatalog::class, static fn (): FacetCatalog => new FacetCatalog());
    $c->singleton(UrlService::class, static fn (): UrlService => new UrlService());
    $c->singleton(FacetRenderer::class, static fn (): FacetRenderer => new FacetRenderer());
    $c->singleton(ResultsRenderer::class, static fn (): ResultsRenderer => new ResultsRenderer());
    $c->singleton(
        SearchResolver::class,
        static fn (): SearchResolver => new SearchResolver($c->get(IndexRepository::class)),
    );
    $c->singleton(
        SuggestService::class,
        static fn (): SuggestService => new SuggestService(
            $c->get(SearchResolver::class),
        ),
    );
    $c->singleton(
        SearchRenderer::class,
        static fn (): SearchRenderer => new SearchRenderer(
            $c->get(AppearanceService::class),
            $c->get(Settings::class),
        ),
    );

    $c->singleton(
        ProductIndexer::class,
        static fn (): ProductIndexer => new ProductIndexer($c->get(IndexRepository::class)),
    );
    $c->singleton(
        FilterService::class,
        static fn (): FilterService => new FilterService($c->get(IndexRepository::class)),
    );
    $c->singleton(
        FacetCountService::class,
        static fn (): FacetCountService => new FacetCountService(
            $c->get(IndexRepository::class),
            $c->get(FilterService::class),
        ),
    );
    $c->singleton(
        FilterEngine::class,
        static fn (): FilterEngine => new FilterEngine(
            $c->get(Settings::class),
            $c->get(FilterService::class),
            $c->get(FacetCountService::class),
            $c->get(FacetRenderer::class),
            $c->get(ResultsRenderer::class),
            $c->get(SearchResolver::class),
            $c->get(AppearanceService::class),
        ),
    );

    // REST controllers.
    $c->singleton(
        FilterController::class,
        static fn (): FilterController => new FilterController(
            $c->get(FilterEngine::class),
            $c->get(UrlService::class),
        ),
    );
    $c->singleton(
        AdminController::class,
        static fn (): AdminController => new AdminController(
            $c->get(Settings::class),
            $c->get(FacetCatalog::class),
            $c->get(ProductIndexer::class),
            $c->get(IndexRepository::class),
        ),
    );
    $c->singleton(
        SuggestController::class,
        static fn (): SuggestController => new SuggestController(
            $c->get(SuggestService::class),
            $c->get(FilterService::class),
            $c->get(Settings::class),
        ),
    );

    // Hook subscribers.
    $c->singleton(AdminHooks::class, static fn (): AdminHooks => new AdminHooks());
    $c->singleton(
        FrontendHooks::class,
        static fn (): FrontendHooks => new FrontendHooks(
            $c->get(Settings::class),
            $c->get(AppearanceService::class),
        ),
    );
    $c->singleton(
        RestHooks::class,
        static fn (): RestHooks => new RestHooks(
            $c->get(FilterController::class),
            $c->get(AdminController::class),
            $c->get(SuggestController::class),
        ),
    );
    $c->singleton(
        IndexerHooks::class,
        static fn (): IndexerHooks => new IndexerHooks($c->get(ProductIndexer::class)),
    );
    $c->singleton(
        FilterShortcode::class,
        static fn (): FilterShortcode => new FilterShortcode(
            $c->get(FilterEngine::class),
            $c->get(UrlService::class),
            $c->get(FrontendHooks::class),
        ),
    );
    $c->singleton(
        BlockHooks::class,
        static fn (): BlockHooks => new BlockHooks(
            $c->get(FilterEngine::class),
            $c->get(UrlService::class),
            $c->get(FrontendHooks::class),
        ),
    );
    $c->singleton(
        SearchShortcode::class,
        static fn (): SearchShortcode => new SearchShortcode(
            $c->get(SearchRenderer::class),
            $c->get(FrontendHooks::class),
        ),
    );
    $c->singleton(
        SearchBlockHooks::class,
        static fn (): SearchBlockHooks => new SearchBlockHooks(
            $c->get(SearchRenderer::class),
            $c->get(FrontendHooks::class),
        ),
    );
};
