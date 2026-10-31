<?php

namespace Tests\Unit\Deployment;

use PHPUnit\Framework\TestCase;

class BetaWorkflowConfigTest extends TestCase
{
    private string $basePath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->basePath = dirname(__DIR__, 3);
    }

    public function test_github_actions_workflow_file_exists_and_targets_beta_branch(): void
    {
        $workflowPath = $this->basePath . '/.github/workflows/deploy-beta.yml';
        $this->assertFileExists($workflowPath, 'GitHub Actions workflow deploy-beta.yml must exist');

        $content = file_get_contents($workflowPath);
        $this->assertStringContainsString('beta', $content);
        $this->assertStringContainsString('workflow_dispatch', $content);
        $this->assertStringContainsString('DEPLOY_SSH_HOST', $content);
        $this->assertStringContainsString('DEPLOY_SSH_KEY', $content);
        $this->assertStringContainsString('scripts/deploy-beta.sh', $content);
    }

    public function test_deploy_beta_script_exists_and_contains_required_lifecycle_steps(): void
    {
        $scriptPath = $this->basePath . '/scripts/deploy-beta.sh';
        $this->assertFileExists($scriptPath, 'Deployment script scripts/deploy-beta.sh must exist');

        $content = file_get_contents($scriptPath);
        $this->assertStringContainsString('git fetch origin', $content);
        $this->assertStringContainsString('composer install', $content);
        $this->assertStringContainsString('npm run build', $content);
        $this->assertStringContainsString('php artisan migrate --force', $content);
        $this->assertStringContainsString('php artisan optimize:clear', $content);
        $this->assertStringContainsString('tec.90s.agency', $content);
    }
}
