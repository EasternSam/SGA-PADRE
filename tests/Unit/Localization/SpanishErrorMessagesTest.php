<?php

namespace Tests\Unit\Localization;

use PHPUnit\Framework\TestCase;

class SpanishErrorMessagesTest extends TestCase
{
    private function projectPath(string $relative = ''): string
    {
        $base = dirname(__DIR__, 3);

        return $relative === '' ? $base : $base . '/' . ltrim($relative, '/');
    }

    public function test_spanish_auth_language_file_contains_human_readable_messages(): void
    {
        $authFile = $this->projectPath('lang/es/auth.php');
        $this->assertFileExists($authFile, 'El archivo lang/es/auth.php debe existir.');

        $messages = require $authFile;
        $this->assertIsArray($messages);
        $this->assertArrayHasKey('failed', $messages);
        $this->assertArrayHasKey('password', $messages);
        $this->assertArrayHasKey('throttle', $messages);

        $this->assertNotSame('auth.failed', $messages['failed']);
        $this->assertStringNotContainsString('auth.', $messages['failed']);
        $this->assertGreaterThan(20, strlen($messages['failed']));
    }

    public function test_spanish_passwords_and_validation_files_exist_and_are_complete(): void
    {
        $passwordsFile = $this->projectPath('lang/es/passwords.php');
        $validationFile = $this->projectPath('lang/es/validation.php');
        $paginationFile = $this->projectPath('lang/es/pagination.php');

        $this->assertFileExists($passwordsFile);
        $this->assertFileExists($validationFile);
        $this->assertFileExists($paginationFile);

        $passwords = require $passwordsFile;
        foreach (['reset', 'sent', 'throttled', 'token', 'user'] as $key) {
            $this->assertArrayHasKey($key, $passwords);
            $this->assertStringNotContainsString('passwords.', $passwords[$key]);
        }

        $validation = require $validationFile;
        foreach (['required', 'email', 'unique', 'confirmed', 'min', 'max', 'attributes'] as $key) {
            $this->assertArrayHasKey($key, $validation);
        }
        $this->assertArrayHasKey('email', $validation['attributes']);
        $this->assertArrayHasKey('password', $validation['attributes']);
        $this->assertArrayHasKey('login', $validation['attributes']);
    }

    public function test_app_config_defaults_to_spanish_locale(): void
    {
        $configContent = file_get_contents($this->projectPath('config/app.php'));
        $this->assertStringContainsString("env('APP_LOCALE', 'es')", $configContent);
        $this->assertStringContainsString("env('APP_FALLBACK_LOCALE', 'es')", $configContent);
    }

    public function test_login_request_has_human_readable_fallback_for_invalid_credentials(): void
    {
        $loginRequest = file_get_contents($this->projectPath('app/Http/Requests/Auth/LoginRequest.php'));
        $this->assertStringContainsString('invalidCredentialsMessage', $loginRequest);
        $this->assertStringContainsString('incorrectos', $loginRequest);
    }

    public function test_seeder_includes_gridbase_support_admin_account(): void
    {
        $seeder = file_get_contents($this->projectPath('database/seeders/RolesAndPermissionsSeeder.php'));
        $this->assertStringContainsString('soporte@gridbase.com.do', $seeder);
    }
}
