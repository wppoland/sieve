<?php

declare(strict_types=1);

namespace Sieve\Rest;

defined('ABSPATH') || exit;

use Sieve\Service\SuggestService;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Public read endpoint behind the predictive search dropdown. Returns a compact
 * list of matching products as JSON, with the same shape the frontend renders.
 */
final class SuggestController
{
    public const NAMESPACE = 'sieve/v1';

    public function __construct(
        private readonly SuggestService $suggest,
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
            ],
        ]);
    }

    public function handle(WP_REST_Request $request): WP_REST_Response
    {
        $term = (string) $request->get_param('q');
        $limit = (int) $request->get_param('limit');
        $inStockOnly = (bool) $request->get_param('in_stock_only');

        return rest_ensure_response(
            $this->suggest->suggest($term, $limit, ! $inStockOnly),
        );
    }
}
