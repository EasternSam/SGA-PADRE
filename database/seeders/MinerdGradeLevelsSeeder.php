<?php

namespace Database\Seeders;

use App\Models\GradeLevel;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class MinerdGradeLevelsSeeder extends Seeder
{
    public function run(): void
    {
        $order = 1;

        // ═══════════════════════════════════════════════════
        // NIVEL INICIAL (Pre-Primario)
        // ═══════════════════════════════════════════════════
        GradeLevel::create([
            'name' => 'Pre-Primario', 'short_name' => 'Pre-P',
            'level' => 'inicial', 'cycle' => 2, 'grade_number' => 1,
            'min_passing_score' => 65, 'order' => $order++,
        ]);

        // ═══════════════════════════════════════════════════
        // NIVEL PRIMARIO (1ro - 6to)
        // ═══════════════════════════════════════════════════
        $primarioGrades = [
            ['name' => '1ro Primaria', 'short_name' => '1ro P', 'cycle' => 1, 'grade_number' => 1],
            ['name' => '2do Primaria', 'short_name' => '2do P', 'cycle' => 1, 'grade_number' => 2],
            ['name' => '3ro Primaria', 'short_name' => '3ro P', 'cycle' => 1, 'grade_number' => 3],
            ['name' => '4to Primaria', 'short_name' => '4to P', 'cycle' => 2, 'grade_number' => 4],
            ['name' => '5to Primaria', 'short_name' => '5to P', 'cycle' => 2, 'grade_number' => 5],
            ['name' => '6to Primaria', 'short_name' => '6to P', 'cycle' => 2, 'grade_number' => 6],
        ];

        foreach ($primarioGrades as $grade) {
            GradeLevel::create(array_merge($grade, [
                'level' => 'primario',
                'min_passing_score' => 65,
                'order' => $order++,
            ]));
        }

        // ═══════════════════════════════════════════════════
        // NIVEL SECUNDARIO (1ro - 6to)
        // ═══════════════════════════════════════════════════

        // Primer Ciclo (1ro - 3ro) — Común
        $secundarioCiclo1 = [
            ['name' => '1ro Secundaria', 'short_name' => '1ro S', 'cycle' => 1, 'grade_number' => 1],
            ['name' => '2do Secundaria', 'short_name' => '2do S', 'cycle' => 1, 'grade_number' => 2],
            ['name' => '3ro Secundaria', 'short_name' => '3ro S', 'cycle' => 1, 'grade_number' => 3],
        ];

        foreach ($secundarioCiclo1 as $grade) {
            GradeLevel::create(array_merge($grade, [
                'level' => 'secundario',
                'min_passing_score' => 70,
                'order' => $order++,
            ]));
        }

        // Segundo Ciclo (4to - 6to) — Modalidad Académica (default)
        $secundarioCiclo2 = [
            ['name' => '4to Secundaria', 'short_name' => '4to S', 'cycle' => 2, 'grade_number' => 4],
            ['name' => '5to Secundaria', 'short_name' => '5to S', 'cycle' => 2, 'grade_number' => 5],
            ['name' => '6to Secundaria', 'short_name' => '6to S', 'cycle' => 2, 'grade_number' => 6],
        ];

        foreach ($secundarioCiclo2 as $grade) {
            GradeLevel::create(array_merge($grade, [
                'level' => 'secundario',
                'modality' => 'académica',
                'min_passing_score' => 70,
                'order' => $order++,
            ]));
        }

        $this->command->info('✅ Grados MINERD creados: Pre-Primario + 1ro-6to Primaria + 1ro-6to Secundaria');
    }
}
