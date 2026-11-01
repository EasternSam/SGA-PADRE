<?php

namespace Tests\Unit\Admissions;

use PHPUnit\Framework\TestCase;

class ApplicantDocumentsNotificationTest extends TestCase
{
    public function test_system_notification_writes_synchronously_to_database_channel(): void
    {
        $basePath = dirname(__DIR__, 3);
        $notificationFile = file_get_contents($basePath . '/app/Notifications/SystemNotification.php');

        $this->assertStringNotContainsString('implements ShouldQueue', $notificationFile);
        $this->assertStringContainsString("return ['database']", $notificationFile);
    }

    public function test_applicant_portal_controller_notifies_when_all_required_documents_are_submitted(): void
    {
        $basePath = dirname(__DIR__, 3);
        $controllerFile = file_get_contents($basePath . '/app/Http/Controllers/ApplicantPortalController.php');
        $portalJsx = file_get_contents($basePath . '/resources/js/Pages/Applicant/Portal.jsx');

        $this->assertStringContainsString('hasAllRequiredDocuments', $controllerFile);
        $this->assertStringContainsString('notifyAllDocumentsSubmitted', $controllerFile);
        $this->assertStringContainsString('Expediente de Admisión Completo', $controllerFile);
        $this->assertStringContainsString('Documentación Completa Enviada', $controllerFile);
        $this->assertStringContainsString("'notifications'", $portalJsx);
    }
}
