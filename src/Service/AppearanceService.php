<?php

declare(strict_types=1);

namespace Sieve\Service;

defined('ABSPATH') || exit;

/**
 * Resolves the store's chosen appearance (a style preset plus four colour
 * overrides) into the markup attribute and the scoped inline CSS that the
 * frontend ships. Every method is pure: nothing here reads or writes options,
 * which keeps it free of a Settings dependency (and therefore free of the
 * Settings <-> AppearanceService cycle a constructor injection would create).
 *
 * The colour overrides target the variables that the bundled stylesheets
 * actually declare: --sieve-* on .sieve-app and the separate --sieve-search-*
 * prefix on .sieve-search. A "default" preset with untouched colours emits no
 * attribute and no inline style, so an upgraded install is byte-identical.
 */
final class AppearanceService
{
    /**
     * The single source of truth for the preset allowlist. Anything not in this
     * list is coerced to "default".
     *
     * @var array<int, string>
     */
    public const PRESETS = ['default', 'minimal', 'bordered', 'soft', 'unstyled'];

    /**
     * Defaults equal the hardcoded values in frontend.css / search.css, so the
     * "default preset, untouched colours" state is visually identical to 0.7.0.
     *
     * @var array<string, string>
     */
    public const DEFAULT_COLORS = [
        'accent' => '#2563eb',
        'border' => '#e2e4e7',
        'muted' => '#6b7280',
        'background' => '#ffffff',
    ];

    /**
     * @return array{preset: string, colors: array<string, string>}
     */
    public function defaults(): array
    {
        return [
            'preset' => 'default',
            'colors' => self::DEFAULT_COLORS,
        ];
    }

    /**
     * Coerce arbitrary input (REST payload or stored option) into a valid
     * appearance: preset bound to the allowlist, each colour a sanitized hex or
     * its default, unknown colour keys dropped.
     *
     * @param array<string, mixed> $input
     * @return array{preset: string, colors: array<string, string>}
     */
    public function normalize(array $input): array
    {
        $preset = isset($input['preset']) && is_string($input['preset']) ? $input['preset'] : 'default';
        if (! in_array($preset, self::PRESETS, true)) {
            $preset = 'default';
        }

        $raw = isset($input['colors']) && is_array($input['colors']) ? $input['colors'] : [];
        $colors = [];
        foreach (self::DEFAULT_COLORS as $key => $default) {
            $value = isset($raw[$key]) ? sanitize_hex_color((string) $raw[$key]) : null;
            $colors[$key] = $value ?? $default;
        }

        return ['preset' => $preset, 'colors' => $colors];
    }

    /**
     * Resolve the effective appearance from a Settings::all() payload, reporting
     * whether it is the default (so callers can skip emitting any markup).
     *
     * @param array<string, mixed> $all
     * @return array{preset: string, colors: array<string, string>, isDefault: bool}
     */
    public function resolveFrom(array $all): array
    {
        $appearance = isset($all['appearance']) && is_array($all['appearance'])
            ? $this->normalize($all['appearance'])
            : $this->defaults();

        $isDefault = 'default' === $appearance['preset']
            && $appearance['colors'] === self::DEFAULT_COLORS;

        return [
            'preset' => $appearance['preset'],
            'colors' => $appearance['colors'],
            'isDefault' => $isDefault,
        ];
    }

    /**
     * The scoped inline CSS carrying only the colour declarations that differ
     * from the defaults. Returns '' when nothing differs, so the default path
     * ships no inline style. Search uses its own --sieve-search-* prefix; the
     * filter applies "background" as a real background-color (no such var
     * exists on .sieve-app).
     *
     * @param 'filter'|'search'   $scope
     * @param array<string,string> $colors
     */
    public function colorBlock(string $scope, array $colors): string
    {
        $decls = [];

        if ('search' === $scope) {
            $map = [
                'accent' => '--sieve-search-accent',
                'border' => '--sieve-search-border',
                'muted' => '--sieve-search-muted',
                'background' => '--sieve-search-bg',
            ];
            foreach ($map as $key => $var) {
                if (! $this->changed($colors, $key)) {
                    continue;
                }
                $decls[] = $var . ':' . esc_attr($colors[$key]);
            }

            return $decls === [] ? '' : '.sieve-search{' . implode(';', $decls) . '}';
        }

        $map = [
            'accent' => '--sieve-accent',
            'border' => '--sieve-border',
            'muted' => '--sieve-muted',
        ];
        foreach ($map as $key => $var) {
            if (! $this->changed($colors, $key)) {
                continue;
            }
            $decls[] = $var . ':' . esc_attr($colors[$key]);
        }
        if ($this->changed($colors, 'background')) {
            $decls[] = 'background-color:' . esc_attr($colors['background']);
        }

        return $decls === [] ? '' : '.sieve-app{' . implode(';', $decls) . '}';
    }

    /**
     * @param array<string, string> $colors
     */
    private function changed(array $colors, string $key): bool
    {
        return isset($colors[$key]) && $colors[$key] !== self::DEFAULT_COLORS[$key];
    }
}
