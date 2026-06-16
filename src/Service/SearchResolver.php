<?php

declare(strict_types=1);

namespace Sieve\Service;

defined('ABSPATH') || exit;

use Sieve\Repository\IndexRepository;
use Sieve\Support\Normalizer;

/**
 * Shared term -> product-id resolver. Folds the query the same way the index was
 * built, then runs a prefix tier (AND across query tokens) plus a bounded
 * Levenshtein fuzzy tier on the last token. Both the predictive dropdown
 * (SuggestService) and the live grid (FilterService / FilterEngine) consume this
 * single path, so a suggestion shown in the dropdown is guaranteed to appear in
 * the grid and dependent counts can never disagree with the results.
 */
final class SearchResolver
{
    private const PREFIX_CAP = 200;
    private const VOCAB_CAP = 400;
    private const FUZZY_TOKEN_CAP = 8;
    // Hard cap on the returned id set, to bound post__in / array_intersect on
    // very large catalogs. Filterable so a store can raise or lower it.
    private const RESOLVE_CAP = 2000;

    public function __construct(private readonly IndexRepository $index)
    {
    }

    /**
     * Resolve a search term to product ids via folded prefix + bounded-Levenshtein
     * fuzzy matching.
     *
     * Return contract (kept deliberately strict so callers never confuse the
     * three states):
     * - null  => term empty or untokenizable (e.g. punctuation only): search is
     *            INACTIVE, apply no constraint.
     * - []    => term valid but zero index matches: constrain to NOTHING.
     * - int[] => matching ids, discovery-ordered, deduped, capped to RESOLVE_CAP.
     *
     * @return array<int, int>|null
     */
    public function resolve(string $term): ?array
    {
        $term = trim($term);
        if ('' === $term) {
            return null;
        }

        $tokens = Normalizer::tokens($term);
        if ([] === $tokens) {
            return null;
        }

        /** @var int $cap */
        $cap = (int) apply_filters('sieve_search_resolve_cap', self::RESOLVE_CAP);
        if ($cap < 1) {
            $cap = self::RESOLVE_CAP;
        }

        /**
         * Filters product IDs for a search term before the built-in index resolver runs.
         *
         * Return an array of product IDs to replace native resolution. Return null to
         * use the built-in folded prefix + fuzzy index search.
         *
         * @param array<int, int>|null $productIds Resolved product IDs, or null.
         * @param string               $term       Raw search term.
         * @param array<int, string>   $tokens     Normalized query tokens.
         */
        $external = apply_filters('sieve_search_product_ids', null, $term, $tokens);
        if (is_array($external)) {
            $ids = array_values(array_unique(array_map('intval', $external)));

            return array_slice($ids, 0, $cap);
        }

        $ids = $this->prefixTier($tokens);

        $lastToken = $tokens[count($tokens) - 1];
        if (mb_strlen($lastToken, 'UTF-8') >= 3) {
            $ids = $this->appendUnique($ids, $this->fuzzyTier($lastToken));
        }

        // Even an empty result is returned as [] (a genuine zero-result term must
        // stay constrained-to-nothing, never silently fall through to no filter).
        return array_slice(array_values(array_unique($ids)), 0, $cap);
    }

    /**
     * TIER 1: prefix-match each query token against the '_search' index and
     * intersect the id sets in PHP, so a multi-word query is an AND of prefixes.
     *
     * @param array<int, string> $tokens
     * @return array<int, int> discovery-ordered, deduped
     */
    private function prefixTier(array $tokens): array
    {
        $sets = [];
        foreach ($tokens as $token) {
            $sets[] = $this->index->searchPrefix($token, self::PREFIX_CAP);
        }
        if ([] === $sets) {
            return [];
        }

        // Preserve the discovery order of the first set, then intersect.
        $primary = array_shift($sets);
        foreach ($sets as $set) {
            $lookup = array_flip($set);
            $primary = array_values(array_filter($primary, static fn (int $id): bool => isset($lookup[$id])));
        }

        return array_values(array_unique($primary));
    }

    /**
     * TIER 2: bounded Levenshtein over the vocabulary sharing the last token's
     * first folded letter. Accepts candidates within a length-scaled distance,
     * sorts by closeness, and resolves the best tokens to object ids.
     *
     * @return array<int, int>
     */
    private function fuzzyTier(string $lastToken): array
    {
        $vocab = $this->index->vocabularyForPrefix(mb_substr($lastToken, 0, 1, 'UTF-8'), self::VOCAB_CAP);
        if ([] === $vocab) {
            return [];
        }

        $maxDistance = mb_strlen($lastToken, 'UTF-8') <= 4 ? 1 : 2;

        $accepted = [];
        foreach ($vocab as $candidate) {
            $distance = levenshtein($lastToken, $candidate);
            if ($distance <= $maxDistance) {
                $accepted[$candidate] = $distance;
            }
        }
        if ([] === $accepted) {
            return [];
        }

        asort($accepted);
        $tokens = array_slice(array_keys($accepted), 0, self::FUZZY_TOKEN_CAP);

        return $this->index->objectsForValues('_search', $tokens);
    }

    /**
     * Append ids not already present, preserving the existing order first.
     *
     * @param array<int, int> $base
     * @param array<int, int> $extra
     * @return array<int, int>
     */
    private function appendUnique(array $base, array $extra): array
    {
        $seen = array_flip($base);
        foreach ($extra as $id) {
            if (! isset($seen[$id])) {
                $base[] = $id;
                $seen[$id] = true;
            }
        }
        return $base;
    }
}
