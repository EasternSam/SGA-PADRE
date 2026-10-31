<?php

namespace Tests\Unit\Branding;

use App\Support\BrandingDefaults;
use PHPUnit\Framework\TestCase;

class BrandingDefaultsTest extends TestCase
{
    private string $basePath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->basePath = dirname(__DIR__, 3);
    }

    public function test_academic_plus_default_constants_are_defined(): void
    {
        $this->assertSame('Academic+', BrandingDefaults::INSTITUTION_NAME);
        $this->assertSame('/branding/academic-logo.png', BrandingDefaults::LOGO);
        $this->assertSame('/branding/academic-favicon.png', BrandingDefaults::FAVICON);
        $this->assertSame('/branding/academic-favicon.png', BrandingDefaults::APP_ICON);
        $this->assertSame('#211170', BrandingDefaults::PRIMARY_COLOR);
        $this->assertSame('#1a0a53', BrandingDefaults::SECONDARY_COLOR);
        $this->assertSame('#f7931e', BrandingDefaults::ACCENT_COLOR);
        $this->assertSame('#64748b', BrandingDefaults::NEUTRAL_COLOR);
        $this->assertSame('33 17 112', BrandingDefaults::PRIMARY_RGB);
        $this->assertSame('26 10 83', BrandingDefaults::SECONDARY_RGB);
        $this->assertSame('247 147 30', BrandingDefaults::ACCENT_RGB);
        $this->assertSame('100 116 139', BrandingDefaults::NEUTRAL_RGB);
    }

    public function test_resolve_returns_academic_plus_defaults_when_settings_are_empty_or_null(): void
    {
        $resolved = BrandingDefaults::resolve([
            'institution_name' => '',
            'institution_logo' => null,
            'favicon' => '   ',
            'brand_primary_color' => '',
        ]);

        $this->assertSame('Academic+', $resolved['name']);
        $this->assertSame('/branding/academic-logo.png', $resolved['logo']);
        $this->assertSame('/branding/academic-favicon.png', $resolved['favicon']);
        $this->assertSame('/branding/academic-favicon.png', $resolved['app_icon']);
        $this->assertSame('#211170', $resolved['primary_color']);
        $this->assertSame('#1a0a53', $resolved['secondary_color']);
        $this->assertSame('#f7931e', $resolved['accent_color']);
        $this->assertSame('#64748b', $resolved['neutral_color']);
        $this->assertSame('#211170', $resolved['palette']['primary']);
        $this->assertSame('#1a0a53', $resolved['palette']['secondary']);
        $this->assertSame('#f7931e', $resolved['palette']['accent']);
        $this->assertSame('#64748b', $resolved['palette']['neutral']);
    }

    public function test_resolve_preserves_custom_institution_values_when_assigned(): void
    {
        $resolved = BrandingDefaults::resolve([
            'institution_name' => 'Instituto Demo',
            'institution_logo' => '/branding/custom-logo.png',
            'favicon' => '/branding/custom-favicon.png',
            'brand_primary_color' => '#0f766e',
            'brand_secondary_color' => '#064e3b',
            'brand_accent_color' => '#10b981',
            'brand_neutral_color' => '#6b7280',
        ]);

        $this->assertSame('Instituto Demo', $resolved['name']);
        $this->assertSame('/branding/custom-logo.png', $resolved['logo']);
        $this->assertSame('/branding/custom-favicon.png', $resolved['favicon']);
        $this->assertSame('#0f766e', $resolved['primary_color']);
        $this->assertSame('#064e3b', $resolved['secondary_color']);
        $this->assertSame('#10b981', $resolved['accent_color']);
        $this->assertSame('#6b7280', $resolved['neutral_color']);
    }

    public function test_default_academic_plus_branding_assets_exist_and_are_tracked_in_git(): void
    {
        $logoPath = $this->basePath . '/public/branding/academic-logo.png';
        $faviconPath = $this->basePath . '/public/branding/academic-favicon.png';
        $gitignorePath = $this->basePath . '/public/branding/.gitignore';

        $this->assertFileExists($logoPath, 'Default Academic+ logo must exist in public/branding/academic-logo.png');
        $this->assertFileExists($faviconPath, 'Default Academic+ favicon must exist in public/branding/academic-favicon.png');
        $this->assertGreaterThan(1000, filesize($logoPath));
        $this->assertGreaterThan(1000, filesize($faviconPath));

        $gitignore = file_get_contents($gitignorePath);
        $this->assertStringContainsString('!academic-logo.png', $gitignore);
        $this->assertStringContainsString('!academic-favicon.png', $gitignore);
    }

    public function test_core_files_do_not_fallback_to_other_institutions_by_default(): void
    {
        $filesToCheck = [
            '/app/Http/Middleware/HandleInertiaRequests.php',
            '/app/Http/Controllers/AdminSettingsController.php',
            '/resources/views/app.blade.php',
            '/resources/css/app.css',
            '/tailwind.config.js',
            '/resources/js/Layouts/AuthenticatedLayout.jsx',
            '/resources/js/Pages/Auth/Login.jsx',
        ];

        foreach ($filesToCheck as $relativePath) {
            $content = file_get_contents($this->basePath . $relativePath);
            $this->assertStringNotContainsString('logo_1787814439.png', $content, "File {$relativePath} must not fallback to logo_1787814439.png");
            $this->assertStringNotContainsString('favicon_1777868576.webp', $content, "File {$relativePath} must not fallback to favicon_1777868576.webp");
            $this->assertStringNotContainsString('centuu.png', $content, "File {$relativePath} must not fallback to centuu.png");
            $this->assertStringNotContainsString('Instituto Tecnológico de Las Américas', $content, "File {$relativePath} must not fallback to ITLA");
        }
    }
}
