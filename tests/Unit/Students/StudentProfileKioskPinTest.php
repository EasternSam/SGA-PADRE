<?php

namespace Tests\Unit\Students;

use PHPUnit\Framework\TestCase;

class StudentProfileKioskPinTest extends TestCase
{
    public function test_profile_passes_real_kiosk_pin_explicitly_without_unhiding_globally(): void
    {
        $basePath = dirname(__DIR__, 3);
        $controller = file_get_contents($basePath . '/app/Http/Controllers/StudentController.php');
        $userModel = file_get_contents($basePath . '/app/Models/User.php');
        $profileJsx = file_get_contents($basePath . '/resources/js/Pages/Students/Profile.jsx');

        // El PIN sigue oculto globalmente (credencial)
        $this->assertMatchesRegularExpression("/protected \\\$hidden = \\[[^\\]]*'kiosk_pin'/s", $userModel);

        // El perfil envía el PIN real de forma explícita
        $this->assertStringContainsString("'kioskPin' =>", $controller);
        $this->assertStringContainsString('resolveDisplayableKioskPin', $controller);

        // La vista ya no muestra un '0000' falso
        $this->assertStringNotContainsString("kiosk_pin || '0000'", $profileJsx);
        $this->assertStringContainsString('kioskPin', $profileJsx);
        $this->assertStringContainsString('Sin asignar', $profileJsx);
    }
}
