<?php

declare(strict_types=1);

namespace Sieve\Rest;

defined('ABSPATH') || exit;

use Sieve\Service\FilterService;
use Sieve\Service\Settings;
use Sieve\Service\SuggestService;
use Sieve\Service\UrlService;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Public read endpoint behind the predictive search dropdown and the in-grid
 * search combobox. Returns a compact list of matching products as JSON, with the
 * same shape the frontend renders. An optional `scope` constrains the suggestions
 * to the active facet selection so the in-grid combobox stays in sync with the grid.
 */
final class SuggestController
{
    public const NAMESPACE = 'sieve/v1';

    public function __construct(
        private readonly SuggestService $suggest,
        private readonly FilterService $filter,
        private readonly Settings $settings,
    ) {
    }

    public function registerRoutes(): void
    {
        register_rest_route(self::NAMESPACE, '/suggest', [
            'methods' => 'GET',
            'callback' => [$this, 'handle'],
            'permission_callback' => '__return_true',
            'args' => [
                'q' => [
                    'type' => 'string',
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'limit' => [
                    'type' => 'integer',
                    'required' => false,
                    'default' => 6,
                    'minimum' => 1,
                    'maximum' => 20,
                    'sanitize_callback' => 'absint',
                ],
                'in_stock_only' => [
                    'type' => 'boolean',
                    'required' => false,
                    'default' => true,
                ],
                'scope' => [
                    'type' => 'string',
                    'required' => false,
                    // Serialized sf_* query string of the OTHER active facets
                    // (never sf_q), used to constrain suggestions to the grid.
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);
    }

    public function handle(WP_REST_Request $request): WP_REST_Response
    {
        $term = (string) $request->get_param('q');
        $limit = (int) $request->get_param('limit');
        $inStockOnly = (bool) $request->get_param('in_stock_only');
        $scope = (string) $request->get_param('scope');

        $constrainIds = $this->resolveScope($scope);

        return rest_ensure_response(
            $this->suggest->suggest($term, $limit, ! $inStockOnly, $constrainIds),
        );
    }

    /**
     * Resolve the scope query string to the active grid id set. Returns null when
     * no scope is supplied (unconstrained, standalone-widget behaviour).
     *
     * @return array<int, int>|null
     */
    private function resolveScope(string $scope): ?array
    {
        if ('' === $scope) {
            return null;
        }

        $params = [];
        parse_str($scope, $params);
        $parsed = (new UrlService())->parse($params);

        // Scope excludes the in-progress search term by contract, so resolve the
        // facet selection only (search ids stay null here).
        $resolved = $this->filter->resolve($this->settings->facets(), $parsed['filters']);

        // null => no facet constraint at all: nothing to scope to. Treat as an
        // empty constraint so an empty scope query does not silently widen the
        // search to the whole catalog.
        return $resolved ?? [];
    }
}
