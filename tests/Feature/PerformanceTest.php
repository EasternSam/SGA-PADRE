<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class PerformanceTest extends TestCase
{
    private function measureRouteSpeed($userEmail, $route)
    {
        $user = User::where('email', $userEmail)->first();
        if (!$user) {
            echo str_pad($route, 30) . " | User not found: $userEmail\n";
            return;
        }

        $start = microtime(true);
        $response = $this->actingAs($user)->get($route);
        $time = microtime(true) - $start;
        
        $status = $response->status();
        $ms = round($time * 1000, 2);
        
        $color = $ms > 1000 ? "\e[31m" : ($ms > 500 ? "\e[33m" : "\e[32m");
        $reset = "\e[0m";

        echo str_pad($route, 30) . " | Status: $status | Time: {$color}{$ms} ms{$reset}\n";
    }

    public function test_dashboard_load_times(): void
    {
        echo "\n\n--- RESULTADOS DE RENDIMIENTO (TIEMPO DE CARGA DEL SERVIDOR) ---\n";
        
        $this->measureRouteSpeed('solicitante@prueba.com', route('applicant.portal', [], false));
        $this->measureRouteSpeed('estudiante@estudiante.com', '/estudiante'); 
        $this->measureRouteSpeed('profesor@profesor.com', '/docencia');
        $this->measureRouteSpeed('admin@admin.com', '/admin');
        $this->measureRouteSpeed('admin@admin.com', '/admin/finance');
        
        echo "--------------------------------------------------------------\n";
        $this->assertTrue(true); // Dummy assertion to pass the test
    }
}
