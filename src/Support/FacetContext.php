<?php

declare(strict_types=1);

namespace Sieve\Support;

defined('ABSPATH') || exit;

use Sieve\Service\UrlService;

/**
 * Page and shopper context passed into {@see Settings::facetsForContext()} so PRO
 * extensions can show or hide facets without forking the filter engine.
 */
final class FacetContext
{
    /**
     * Build context from the current main query (initial server render).
     *
     * @return array{is_shop: bool, category_id: int, user_roles: array<int, string>}
     */
    public static function current(): array
    {
        $context = [
            'is_shop'     => function_exists('is_shop') && is_shop(),
            'category_id' => 0,
            'user_roles'  => self::currentUserRoles(),
        ];

        if (function_exists('is_product_category') && is_product_category()) {
            $term = get_queried_object();
            if ($term instanceof \WP_Term) {
                $context['category_id'] = (int) $term->term_id;
            }
        }

        return $context;
    }

    /**
     * @param array<string, mixed> $params Raw request params (GET or REST query bag).
     * @return array{is_shop: bool, category_id: int, user_roles: array<int, string>}
     */
    public static function fromParams(array $params): array
    {
        $categoryKey = UrlService::PREFIX . 'ctx_category';
        $shopKey     = UrlService::PREFIX . 'ctx_shop';

        $context = [
            'is_shop'     => ! empty($params[$shopKey]),
            'category_id' => isset($params[$categoryKey]) ? max(0, (int) $params[$categoryKey]) : 0,
            'user_roles'  => self::currentUserRoles(),
        ];

        if ($context['category_id'] > 0 || $context['is_shop']) {
            return $context;
        }

        return self::current();
    }

    /**
     * @param array<string, mixed> $context
     * @return array{is_shop: bool, category_id: int, user_roles: array<int, string>}
     */
    public static function normalize(array $context): array
    {
        $roles = $context['user_roles'] ?? self::currentUserRoles();
        if (! is_array($roles)) {
            $roles = [];
        }

        return [
            'is_shop'     => ! empty($context['is_shop']),
            'category_id' => isset($context['category_id']) ? max(0, (int) $context['category_id']) : 0,
            'user_roles'  => array_values(array_map('strval', $roles)),
        ];
    }

    /**
     * @return array<int, string>
     */
    private static function currentUserRoles(): array
    {
        if (! is_user_logged_in()) {
            return [];
        }

        $user = wp_get_current_user();
        if (! $user instanceof \WP_User) {
            return [];
        }

        return array_values(array_map('strval', (array) $user->roles));
    }
}
