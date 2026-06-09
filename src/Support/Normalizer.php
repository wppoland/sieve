<?php

declare(strict_types=1);

namespace Sieve\Support;

defined('ABSPATH') || exit;

/**
 * Diacritic-folding text normaliser used IDENTICALLY at index time and query
 * time so a search like "lozko" can match a title like "łóżko".
 *
 * Folding is deterministic and locale-independent: it prefers the intl
 * Transliterator when available and otherwise falls back to an explicit Polish
 * map plus WordPress's remove_accents() for the non-Polish long tail. We never
 * use iconv('//TRANSLIT'), which is locale-dependent and mangles characters
 * such as "ł".
 */
final class Normalizer
{
    /**
     * Explicit Polish diacritic map, applied before the generic fallback so the
     * Polish characters fold predictably regardless of the active locale.
     *
     * @var array<string, string>
     */
    private const POLISH_MAP = [
        'ł' => 'l', 'Ł' => 'l',
        'ó' => 'o', 'Ó' => 'o',
        'ą' => 'a', 'Ą' => 'a',
        'ę' => 'e', 'Ę' => 'e',
        'ś' => 's', 'Ś' => 's',
        'ż' => 'z', 'Ż' => 'z',
        'ź' => 'z', 'Ź' => 'z',
        'ć' => 'c', 'Ć' => 'c',
        'ń' => 'n', 'Ń' => 'n',
    ];

    /**
     * Fold text to a lowercased, diacritic-free ASCII form: "Łóżko" -> "lozko".
     */
    public static function fold(string $text): string
    {
        $folded = null;

        // Primary path: intl Transliterator (Any-Latin then Latin-ASCII then
        // lowercase) handles the full Unicode range deterministically.
        if (class_exists('Transliterator')) {
            $transliterator = \Transliterator::create('Any-Latin; Latin-ASCII; Lower');
            if (null !== $transliterator) {
                $result = $transliterator->transliterate($text);
                if (is_string($result)) {
                    $folded = $result;
                }
            }
        }

        // Fallback when intl/Transliterator is unavailable: explicit Polish map
        // first, then remove_accents() for the rest, then lowercase.
        if (null === $folded) {
            $folded = strtr($text, self::POLISH_MAP);
            if (function_exists('remove_accents')) {
                $folded = remove_accents($folded);
            }
            $folded = mb_strtolower($folded, 'UTF-8');
        }

        // Strip anything outside [a-z0-9 ], collapse runs of spaces, trim.
        $folded = preg_replace('/[^a-z0-9 ]+/u', ' ', $folded);
        $folded = is_string($folded) ? $folded : '';
        $folded = preg_replace('/\s+/u', ' ', $folded);
        $folded = is_string($folded) ? $folded : '';

        return trim($folded);
    }

    /**
     * Fold text and split into deduped tokens, dropping single-character noise.
     *
     * @return array<int, string>
     */
    public static function tokens(string $text): array
    {
        $folded = self::fold($text);
        if ('' === $folded) {
            return [];
        }

        $parts = explode(' ', $folded);
        $parts = array_filter($parts, static fn (string $t): bool => mb_strlen($t, 'UTF-8') >= 2);

        return array_values(array_unique($parts));
    }
}
