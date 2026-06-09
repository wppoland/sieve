<?php

declare(strict_types=1);

namespace Sieve\Rest;

defined('ABSPATH') || exit;

use Sieve\Repository\IndexRepository;
use Sieve\Service\FacetCatalog;
use Sieve\Service\ProductIndexer;
use Sieve\Service\Settings;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Admin-only endpoints for the React facet builder: read/write settings, list the
 * auto-discovered facet sources, and trigger a re-index.
 */
final class AdminController
{
    public const NAMESPACE = 'sieve/v1';

    public function __construct(
        private readonly Settings $settings,
        private readonly FacetCatalog $catalog,
        private readonly ProductIndexer $indexer,
        private readonly IndexRepository $index,
    ) {
    }

    public function registerRoutes(): void
    {
        $permission = [$this, 'permission'];

        register_rest_route(self::NAMESPACE, '/settings', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'getSettings'],
                'permission_callback' => $permission,
            ],
            [
                'methods' => 'POST',
                'callback' => [$this, 'saveSettings'],
                'permission_callback' => $permission,
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/catalog', [
            'methods' => 'GET',
            'callback' => [$this, 'getCatalog'],
            'permission_callback' => $permission,
        ]);

        register_rest_route(self::NAMESPACE, '/reindex', [
            'methods' => 'POST',
            'callback' => [$this, 'reindex'],
            'permission_callback' => $permission,
        ]);
    }

    public function permission(): bool
    {
        return current_user_can('manage_woocommerce');
    }

    public function getSettings(): WP_REST_Response
    {
        return rest_ensure_response($this->settings->all());
    }

    public function saveSettings(WP_REST_Request $request): WP_REST_Response
    {
        $body = $request->get_json_params();
        $this->settings->save(is_array($body) ? $body : []);

        return rest_ensure_response($this->settings->all());
    }

    public function getCatalog(): WP_REST_Response
    {
        return rest_ensure_response([
            'sources' => $this->catalog->available(),
            'indexed_rows' => $this->index->rowCount(),
        ]);
    }

    public function reindex(): WP_REST_Response
    {
        $count = $this->indexer->indexAll();

        return rest_ensure_response([
            'indexed_products' => $count,
            'indexed_rows' => $this->index->rowCount(),
        ]);
    }
}
