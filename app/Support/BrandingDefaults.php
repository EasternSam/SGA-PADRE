<?php

namespace App\Support;

use App\Models\Setting;

class BrandingDefaults
{
    public const INSTITUTION_NAME = 'Academic+';
    public const LOGO = '/branding/academic-logo.png';
    public const FAVICON = '/branding/academic-favicon.png';
    public const APP_ICON = '/branding/academic-favicon.png';
    public const LOGIN_BANNER = '';

    public const PRIMARY_COLOR = '#211170';
    public const SECONDARY_COLOR = '#1a0a53';
    public const ACCENT_COLOR = '#f7931e';
    public const NEUTRAL_COLOR = '#64748b';

    public const PRIMARY_RGB = '33 17 112';
    public const SECONDARY_RGB = '26 10 83';
    public const ACCENT_RGB = '247 147 30';
    public const NEUTRAL_RGB = '100 116 139';

    public const NAVBAR_TYPE = 'gradient';
    public const NAVBAR_GRADIENT_START = '#211170';
    public const NAVBAR_GRADIENT_END = '#1a0a53';
    public const NAVBAR_GRADIENT_DIRECTION = 'to bottom right';

    /**
     * Return trimmed non-empty setting value or fallback to Academic+ default.
     */
    public static function value(mixed $raw, string $default): string
    {
        if (!is_string($raw)) {
            return $default;
        }

        $trimmed = trim($raw);

        return $trimmed !== '' ? $trimmed : $default;
    }

    /**
     * Convert hex color (#RRGGBB or #RGB) to space-separated RGB triplet.
     */
    public static function hexToRgb(?string $hex, string $defaultRgb = self::PRIMARY_RGB): string
    {
        if (!$hex) {
            return $defaultRgb;
        }

        $clean = ltrim(trim($hex), '#');
        if (strlen($clean) === 3) {
            $clean = $clean[0] . $clean[0] . $clean[1] . $clean[1] . $clean[2] . $clean[2];
        }

        if (strlen($clean) !== 6 || !ctype_xdigit($clean)) {
            return $defaultRgb;
        }

        return hexdec(substr($clean, 0, 2))
            . ' ' . hexdec(substr($clean, 2, 2))
            . ' ' . hexdec(substr($clean, 4, 2));
    }

    /**
     * Resolve full branding array from raw key-value settings.
     *
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    public static function resolve(array $raw = []): array
    {
        $primary = self::value($raw['brand_primary_color'] ?? null, self::PRIMARY_COLOR);
        $secondary = self::value($raw['brand_secondary_color'] ?? null, self::SECONDARY_COLOR);
        $accent = self::value($raw['brand_accent_color'] ?? null, self::ACCENT_COLOR);
        $neutral = self::value($raw['brand_neutral_color'] ?? null, self::NEUTRAL_COLOR);

        $logo = self::value($raw['institution_logo'] ?? null, self::LOGO);
        $favicon = self::value($raw['favicon'] ?? null, self::FAVICON);
        $appIcon = self::value($raw['app_icon'] ?? null, $favicon ?: self::APP_ICON);
        $loginBanner = self::value($raw['login_banner'] ?? null, self::LOGIN_BANNER);

        $navbarType = self::value($raw['navbar_type'] ?? null, self::NAVBAR_TYPE);
        $gradientStart = self::value($raw['navbar_gradient_start'] ?? null, $primary);
        $gradientEnd = self::value($raw['navbar_gradient_end'] ?? null, self::NAVBAR_GRADIENT_END);
        $gradientDirection = self::value($raw['navbar_gradient_direction'] ?? null, self::NAVBAR_GRADIENT_DIRECTION);

        return [
            'name' => self::value($raw['institution_name'] ?? null, self::INSTITUTION_NAME),
            'logo' => $logo,
            'favicon' => $favicon,
            'app_icon' => $appIcon,
            'primary_color' => $primary,
            'secondary_color' => $secondary,
            'accent_color' => $accent,
            'neutral_color' => $neutral,
            'primary_rgb' => self::hexToRgb($primary, self::PRIMARY_RGB),
            'secondary_rgb' => self::hexToRgb($secondary, self::SECONDARY_RGB),
            'accent_rgb' => self::hexToRgb($accent, self::ACCENT_RGB),
            'neutral_rgb' => self::hexToRgb($neutral, self::NEUTRAL_RGB),
            'palette' => [
                'primary' => $primary,
                'secondary' => $secondary,
                'accent' => $accent,
                'neutral' => $neutral,
            ],
            'navbar_type' => $navbarType,
            'navbar_gradient_start' => $gradientStart,
            'navbar_gradient_end' => $gradientEnd,
            'navbar_gradient_direction' => $gradientDirection,
            'login_banner' => $loginBanner,
        ];
    }

    /**
     * Resolve branding directly from the Setting model with safe fallback.
     *
     * @return array<string, mixed>
     */
    public static function fromDatabase(): array
    {
        try {
            return self::resolve([
                'institution_name' => Setting::get('institution_name'),
                'institution_logo' => Setting::get('institution_logo'),
                'favicon' => Setting::get('favicon'),
                'app_icon' => Setting::get('app_icon'),
                'login_banner' => Setting::get('login_banner'),
                'brand_primary_color' => Setting::get('brand_primary_color'),
                'brand_secondary_color' => Setting::get('brand_secondary_color'),
                'brand_accent_color' => Setting::get('brand_accent_color'),
                'brand_neutral_color' => Setting::get('brand_neutral_color'),
                'navbar_type' => Setting::get('navbar_type'),
                'navbar_gradient_start' => Setting::get('navbar_gradient_start'),
                'navbar_gradient_end' => Setting::get('navbar_gradient_end'),
                'navbar_gradient_direction' => Setting::get('navbar_gradient_direction'),
            ]);
        } catch (\Throwable) {
            return self::resolve([]);
        }
    }
}
