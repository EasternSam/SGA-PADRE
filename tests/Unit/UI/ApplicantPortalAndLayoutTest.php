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

    public function test_applicant_portal_supports_document_uploads_in_controller_and_view(): void
    {
        $basePath = dirname(__DIR__, 3);
        $controller = file_get_contents($basePath . '/app/Http/Controllers/ApplicantPortalController.php');
        $portalView = file_get_contents($basePath . '/resources/js/Pages/Applicant/Portal.jsx');

        $this->assertStringContainsString('admissions/birth_certificates', $controller);
        $this->assertStringContainsString('admissions/id_cards', $controller);
        $this->assertStringContainsString('admissions/records', $controller);
        $this->assertStringContainsString('admissions/photos', $controller);

        $this->assertStringContainsString('forceFormData: true', $portalView);
        $this->assertStringContainsString('type="file"', $portalView);
        $this->assertStringContainsString('birth_certificate', $portalView);
        $this->assertStringContainsString('high_school_record', $portalView);
    }

    public function test_applicant_portal_marks_uploaded_documents_as_deposited_and_supports_direct_upload(): void
    {
        $basePath = dirname(__DIR__, 3);
        $controller = file_get_contents($basePath . '/app/Http/Controllers/ApplicantPortalController.php');
        $portalView = file_get_contents($basePath . '/resources/js/Pages/Applicant/Portal.jsx');
        $routes = file_get_contents($basePath . '/routes/web.php');

        $this->assertStringContainsString('public function uploadDocument', $controller);
        $this->assertStringContainsString('/portal-aspirante/documento', $routes);
        $this->assertStringContainsString("label: 'Depositado'", $portalView);
        $this->assertStringContainsString('handleQuickUpload', $portalView);
    }
}
