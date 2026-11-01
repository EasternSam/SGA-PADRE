<?php

namespace Tests\Unit\Admissions;

use PHPUnit\Framework\TestCase;

class AdmissionsOpenRegistrationVisibilityTest extends TestCase
{
    public function test_login_hides_solicitar_admision_and_register_controller_guards_when_admissions_closed(): void
    {
        $basePath = dirname(__DIR__, 3);
        $loginJsx = file_get_contents($basePath . '/resources/js/Pages/Auth/Login.jsx');
        $registerController = file_get_contents($basePath . '/app/Http/Controllers/Auth/RegisteredUserController.php');

        $this->assertStringContainsString('admissions_open', $loginJsx);
        $this->assertStringContainsString('{admissionsOpen && (', $loginJsx);

        $this->assertStringContainsString('isAdmissionsOpen()', $registerController);
        $this->assertStringContainsString("Setting::get('admissions_open', '0')", $registerController);
    }
}
