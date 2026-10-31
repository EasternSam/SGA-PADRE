<?php

namespace Tests\Unit\UI;

use PHPUnit\Framework\TestCase;

class ButtonNoShadowTest extends TestCase
{
    public function test_shadcn_and_godui_buttons_do_not_contain_shadow_classes(): void
    {
        $buttonFile = file_get_contents(__DIR__ . '/../../../resources/js/components/ui/button.jsx');
        $this->assertDoesNotMatchRegularExpression('/\bshadow(?:-[a-z0-9]+)?\b/', $buttonFile);

        $progressFoldFile = file_get_contents(__DIR__ . '/../../../resources/js/components/godui/ProgressFoldButton.jsx');
        $this->assertDoesNotMatchRegularExpression('/\bshadow(?:-[a-z0-9\/]+)?\b/', $progressFoldFile);

        $holdConfirmFile = file_get_contents(__DIR__ . '/../../../resources/js/components/godui/HoldConfirmButton.jsx');
        $this->assertDoesNotMatchRegularExpression('/\bshadow(?:-[a-z0-9]+)?\b/', $holdConfirmFile);

        $slideConfirmFile = file_get_contents(__DIR__ . '/../../../resources/js/components/godui/SlideConfirmButton.jsx');
        $this->assertDoesNotMatchRegularExpression('/\bshadow(?:-[a-z0-9]+)?\b/', $slideConfirmFile);
    }

    public function test_global_stylesheets_enforce_no_box_shadow_on_all_buttons(): void
    {
        $appCss = file_get_contents(__DIR__ . '/../../../resources/css/app.css');
        $this->assertStringContainsString('Global Button No-Shadow Rule', $appCss);
        $this->assertStringContainsString('box-shadow: none !important;', $appCss);
        $this->assertStringContainsString('--tw-shadow: 0 0 #0000 !important;', $appCss);

        $gridbaseCss = file_get_contents(__DIR__ . '/../../../resources/css/gridbase.css');
        $this->assertDoesNotMatchRegularExpression(
            '/\.gb-btn-primary,\s*\.btn-primary\s*\{[^}]*box-shadow:\s*0px/s',
            $gridbaseCss
        );
    }
}
