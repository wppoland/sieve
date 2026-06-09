<?php

declare(strict_types=1);

namespace Sieve\Model;

defined('ABSPATH') || exit;

use Sieve\Enum\FacetType;

/**
 * Immutable value object describing one configured facet.
 *
 * A facet has a URL-facing slug (also the query var suffix), a display label, a
 * presentation type (FacetType), and a data source string that tells the indexer
 * and resolver where its values come from. Source formats:
 *   - "tax:{taxonomy}"  product_cat, product_tag, pa_* attribute taxonomies
 *   - "price"           numeric price range
 *   - "stock"           stock status (instock / outofstock / onbackorder)
 *   - "on_sale"         products currently on sale
 *   - "rating"          rounded average rating
 *   - "search"          free-text product search (handled by the query, not the index)
 */
final class Facet
{
    public function __construct(
        public readonly string $slug,
        public readonly string $label,
        public readonly FacetType $type,
        public readonly string $source,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $slug = isset($data['slug']) ? sanitize_key((string) $data['slug']) : '';
        $label = isset($data['label']) ? sanitize_text_field((string) $data['label']) : $slug;
        $source = isset($data['source']) ? self::sanitizeSource((string) $data['source']) : '';
        $type = FacetType::tryFrom(isset($data['type']) ? (string) $data['type'] : '') ?? FacetType::Checkbox;

        return new self($slug, $label, $type, $source);
    }

    /**
     * @return array{slug: string, label: string, type: string, source: string}
     */
    public function toArray(): array
    {
        return [
            'slug' => $this->slug,
            'label' => $this->label,
            'type' => $this->type->value,
            'source' => $this->source,
        ];
    }

    /**
     * The key this facet's values are stored under in the index table.
     */
    public function indexKey(): string
    {
        if (str_starts_with($this->source, 'tax:')) {
            return substr($this->source, 4);
        }
        return $this->source;
    }

    /**
     * The taxonomy backing this facet, or null when it is not taxonomy-based.
     */
    public function taxonomy(): ?string
    {
        return str_starts_with($this->source, 'tax:') ? substr($this->source, 4) : null;
    }

    public function isPrice(): bool
    {
        return 'price' === $this->source;
    }

    public function isSearch(): bool
    {
        return 'search' === $this->source;
    }

    private static function sanitizeSource(string $source): string
    {
        if (str_starts_with($source, 'tax:')) {
            return 'tax:' . sanitize_key(substr($source, 4));
        }
        return sanitize_key($source);
    }
}
