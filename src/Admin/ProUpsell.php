<?php

declare(strict_types=1);

namespace Sieve\Admin;

defined('ABSPATH') || exit;

use const Sieve\PLUGIN_DIR;

/**
 * PRO upgrade promotion, shown ONLY on the Sieve admin screen: a dismissible top
 * banner and a "what PRO adds" locked-card list below the facet builder.
 *
 * Pure advertising: no disabled fields, nothing blocks the free workflow, scoped
 * to this one screen and dismissible per user, so it stays inside the
 * WordPress.org guidelines. Content comes from config/pro-upsell.php.
 */
final class ProUpsell
{
    private const META   = 'sieve_pro_banner_dismissed';
    public const ACTION  = 'sieve_dismiss_pro';

    /** @var array<string, mixed>|null */
    private ?array $data = null;

    /** @return array<string, mixed> */
    private function data(): array
    {
        if ($this->data === null) {
            $file = PLUGIN_DIR . '/config/pro-upsell.php';
            $this->data = is_readable($file) ? (array) require $file : [];
        }
        return $this->data;
    }

    /** Whether to render the promo at all (filterable for white-label builds). */
    public function enabled(): bool
    {
        /**
         * Filters whether the Sieve PRO promo is shown on the admin screen.
         *
         * @param bool $show Default true.
         */
        return (bool) apply_filters('sieve/show_pro_cta', true) && $this->features() !== [];
    }

    private function url(): string
    {
        $default = (string) ($this->data()['url'] ?? 'https://plogins.com/sieve-pro/pricing/');
        /**
         * Filters the URL the "Upgrade to PRO" buttons point at.
         *
         * @param string $url Default the Sieve PRO pricing page.
         */
        return (string) apply_filters('sieve/pro_url', $default);
    }

    private function isPolish(): bool
    {
        return str_starts_with((string) get_locale(), 'pl');
    }

    private function priceLabel(): string
    {
        $d = $this->data();
        if ($this->isPolish() && ! empty($d['price_pln'])) {
            /* translators: %d: yearly price in PLN */
            return sprintf(__('od %d zł/rok', 'sieve'), (int) $d['price_pln']);
        }
        if (! empty($d['price_from'])) {
            $cur = ($d['currency'] ?? 'EUR') === 'EUR' ? '€' : (string) $d['currency'] . ' ';
            /* translators: 1: currency symbol, 2: yearly price */
            return sprintf(__('from %1$s%2$d/yr', 'sieve'), $cur, (int) $d['price_from']);
        }
        return '';
    }

    /** @return array<int, array{title: string, desc: string}> */
    private function features(): array
    {
        $lang = $this->isPolish() ? 'pl' : 'en';
        $out  = [];
        foreach ((array) ($this->data()['features'] ?? []) as $f) {
            $x = is_array($f) ? ($f[$lang] ?? $f['en'] ?? null) : null;
            if (is_array($x) && ! empty($x['title'])) {
                $out[] = ['title' => (string) $x['title'], 'desc' => (string) ($x['desc'] ?? '')];
            }
        }
        return $out;
    }

    public function bannerDismissed(): bool
    {
        return (bool) get_user_meta(get_current_user_id(), self::META, true);
    }

    private function dismissUrl(): string
    {
        return wp_nonce_url(admin_url('admin-post.php?action=' . self::ACTION), self::ACTION);
    }

    public function handleDismiss(): void
    {
        if (! current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('Permission denied.', 'sieve'));
        }
        check_admin_referer(self::ACTION);
        update_user_meta(get_current_user_id(), self::META, 1);
        wp_safe_redirect(wp_get_referer() ?: admin_url('admin.php?page=sieve'));
        exit;
    }

    /** Dismissible strip at the top of the admin screen. */
    public function banner(): void
    {
        if (! $this->enabled() || $this->bannerDismissed()) {
            return;
        }
        $name     = (string) ($this->data()['name'] ?? 'Sieve PRO');
        $price    = $this->priceLabel();
        $subtitle = implode(', ', array_slice(array_map(
            static fn (array $f): string => $f['title'],
            $this->features(),
        ), 0, 3));
        ?>
        <div class="sieve-pro-banner" role="note">
            <span class="sieve-pro-banner__tag">PRO</span>
            <p class="sieve-pro-banner__text">
                <strong><?php
                /* translators: %s: PRO edition name */
                printf(esc_html__('Do more with %s', 'sieve'), esc_html($name)); ?></strong>
                <?php if ($subtitle !== '') : ?><span class="sieve-pro-banner__sub"><?php echo esc_html($subtitle); ?></span><?php endif; ?>
                <?php if ($price !== '') : ?><span class="sieve-pro-banner__price"><?php echo esc_html($price); ?></span><?php endif; ?>
            </p>
            <a class="button button-primary sieve-pro-banner__cta" href="<?php echo esc_url($this->url()); ?>" target="_blank" rel="noopener noreferrer">
                <?php esc_html_e('Upgrade to PRO', 'sieve'); ?>
            </a>
            <a class="sieve-pro-banner__dismiss" href="<?php echo esc_url($this->dismissUrl()); ?>" aria-label="<?php esc_attr_e('Dismiss this notice', 'sieve'); ?>">&times;</a>
        </div>
        <?php
    }

    /** "What PRO adds" locked-card grid, appended after the facet builder. */
    public function cards(): void
    {
        if (! $this->enabled()) {
            return;
        }
        $features = $this->features();
        $name     = (string) ($this->data()['name'] ?? 'Sieve PRO');
        ?>
        <section class="sieve-pro-cards" aria-labelledby="sieve-pro-cards-h">
            <h2 id="sieve-pro-cards-h" class="sieve-pro-cards__title">
                <?php
                /* translators: %s: PRO edition name */
                printf(esc_html__('What %s adds', 'sieve'), esc_html($name)); ?>
            </h2>
            <div class="sieve-pro-cards__grid">
                <?php foreach ($features as $f) : ?>
                    <article class="sieve-pro-card">
                        <span class="sieve-pro-card__badge">PRO</span>
                        <h3 class="sieve-pro-card__title"><?php echo esc_html($f['title']); ?></h3>
                        <?php if ($f['desc'] !== '') : ?>
                            <p class="sieve-pro-card__desc"><?php echo esc_html($f['desc']); ?></p>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
        <?php
    }
}
