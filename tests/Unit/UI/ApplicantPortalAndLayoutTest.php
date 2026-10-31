<?php

namespace Tests\Unit\UI;

use Tests\TestCase;

class ApplicantPortalAndLayoutTest extends TestCase
{
    public function test_authenticated_content_uses_defined_csrf_token_and_valid_applicant_portal_route(): void
    {
        $basePath = dirname(__DIR__, 3);
        $layout = file_get_contents($basePath . '/resources/js/Layouts/AuthenticatedLayout.jsx');
        $controller = file_get_contents($basePath . '/app/Http/Controllers/ApplicantPortalController.php');

        $this->assertStringNotContainsString('props?.csrf_token', $layout);
        $this->assertStringContainsString('csrf_token', $layout);
        $this->assertStringNotContainsString('href="/applicant/portal"', $layout);
        $this->assertStringContainsString('href="/portal-aspirante"', $layout);
        $this->assertStringContainsString("->format('Y-m-d')", $controller);
    }
}
