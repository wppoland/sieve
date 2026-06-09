<?php

declare(strict_types=1);

namespace Sieve\Rest;

defined('ABSPATH') || exit;

use Sieve\Service\FilterEngine;
use Sieve\Service\UrlService;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Public read endpoint that powers AJAX filtering. Returns the same fragments the
 * server renders initially, so the frontend swaps them in place with no reflow.
 */
final class FilterController
{
    public const NAMESPACE = 'sieve/v1';

    public function __construct(
        private readonly FilterEngine $engine,
        private readonly UrlService $url,
    ) {
    }

    public function registerRoutes(): void
    {
        register_rest_route(self::NAMESPACE, '/filter', [
            'methods' => 'POST',
            'callback' => [$this, 'handle'],
            'permission_callback' => '__return_true',
            'args' => [
                'query' => [
                    'type' => 'object',
                    'default' => [],
                    'required' => false,
                ],
            ],
        ]);
    }

    public function handle(WP_REST_Request $request): WP_REST_Response
    {
        $query = $request->get_param('query');
        $parsed = $this->url->parse(is_array($query) ? $query : []);

        return rest_ensure_response($this->engine->run($parsed));
    }
}
