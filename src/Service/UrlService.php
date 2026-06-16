<?php

declare(strict_types=1);

namespace Sieve\Service;

defined('ABSPATH') || exit;

use Sieve\Support\FacetContext;

/**
 * Owns the URL state contract shared by the server (initial render) and the
 * frontend script (AJAX + History API). Every facet maps to a "sf_{slug}" query
 * var so filter state is bookmarkable and back-button safe.
 */
final class UrlService
{
    public const PREFIX = 'sf_';

    /**
     * Normalise a raw param bag (typically $_GET or a REST payload) into the
     * request the engine understands.
     *
     * @param array<string, mixed> $params
     * @return array{filters: array<string, string>, orderby: string, paged: int, search: string, context: array{is_shop: bool, category_id: int, user_roles: array<int, string>}}
     */
    public function parse(array $params): array
    {
        $filters = [];
        $orderby = '';
        $paged = 1;
        $search = '';
        $contextParams = $params;

        foreach ($params as $key => $value) {
            $key = (string) $key;
            if (! str_starts_with($key, self::PREFIX)) {
                continue;
            }

            $name = substr($key, strlen(self::PREFIX));
            if (str_starts_with($name, 'ctx_')) {
                continue;
            }

            $clean = is_array($value)
                ? implode(',', array_map([$this, 'cleanScalar'], $value))
                : $this->cleanScalar($value);

            if ('' === $clean) {
                continue;
            }

            if ('orderby' === $name) {
                $orderby = sanitize_key($clean);
            } elseif ('paged' === $name) {
                $paged = max(1, (int) $clean);
            } elseif ('q' === $name) {
                $search = sanitize_text_field($clean);
            } else {
                $filters[sanitize_key($name)] = $clean;
            }
        }

        return [
            'filters' => $filters,
            'orderby' => $orderby,
            'paged' => $paged,
            'search' => $search,
            'context' => FacetContext::fromParams($contextParams),
        ];
    }

    private function cleanScalar(mixed $value): string
    {
        return sanitize_text_field((string) $value);
    }
}
