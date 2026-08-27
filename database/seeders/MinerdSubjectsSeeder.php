<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\GradeLevel;
use Illuminate\Database\Seeder;

class MinerdSubjectsSeeder extends Seeder
{
    public function run(): void
    {
        // ═══════════════════════════════════════════════════
        // ASIGNATURAS BASE (Comunes a Primario y Secundario)
        // ═══════════════════════════════════════════════════

        $baseSubjects = [
            ['name' => 'Lengua Española',                        'code' => 'LE',   'area' => 'lengua_espanola',     'weekly_hours' => 5],
            ['name' => 'Matemáticas',                            'code' => 'MAT',  'area' => 'matematicas',         'weekly_hours' => 5],
            ['name' => 'Ciencias de la Naturaleza',              'code' => 'CN',   'area' => 'ciencias_naturaleza', 'weekly_hours' => 4],
            ['name' => 'Ciencias Sociales',                      'code' => 'CS',   'area' => 'ciencias_sociales',   'weekly_hours' => 4],
            ['name' => 'Educación Artística',                    'code' => 'EA',   'area' => 'educacion_artistica', 'weekly_hours' => 2],
            ['name' => 'Educación Física',                       'code' => 'EF',   'area' => 'educacion_fisica',    'weekly_hours' => 2],
            ['name' => 'Formación Integral Humana y Religiosa',  'code' => 'FIHR', 'area' => 'formacion_humana',    'weekly_hours' => 2],
            ['name' => 'Inglés',                                 'code' => 'ING',  'area' => 'lenguas_extranjeras', 'weekly_hours' => 3],
            ['name' => 'Francés',                                'code' => 'FRA',  'area' => 'lenguas_extranjeras', 'weekly_hours' => 2, 'is_core' => false],
        ];

        foreach ($baseSubjects as $subjectData) {
            Subject::firstOrCreate([
                'code' => $subjectData['code'],
            ], array_merge([
                'is_core'   => true,
                'is_active' => true,
            ], $subjectData));
        }

        // ═══════════════════════════════════════════════════
        // ASIGNATURAS ESPECÍFICAS DE SECUNDARIA
        // ═══════════════════════════════════════════════════

        $secundariaSubjects = [
            ['name' => 'Biología',   'code' => 'BIO', 'area' => 'ciencias_naturaleza', 'weekly_hours' => 3],
            ['name' => 'Química',    'code' => 'QUI', 'area' => 'ciencias_naturaleza', 'weekly_hours' => 3],
            ['name' => 'Física',     'code' => 'FIS', 'area' => 'ciencias_naturaleza', 'weekly_hours' => 3],
            ['name' => 'Historia',   'code' => 'HIS', 'area' => 'ciencias_sociales',   'weekly_hours' => 3],
            ['name' => 'Geografía',  'code' => 'GEO', 'area' => 'ciencias_sociales',   'weekly_hours' => 2],
            ['name' => 'Informática','code' => 'INF', 'area' => 'lenguas_extranjeras', 'weekly_hours' => 2],
        ];

        foreach ($secundariaSubjects as $subjectData) {
            Subject::firstOrCreate([
                'code' => $subjectData['code'],
            ], array_merge([
                'is_core'   => true,
                'is_active' => true,
            ], $subjectData));
        }

        // ═══════════════════════════════════════════════════
        // ASIGNAR ASIGNATURAS A GRADOS (Pivot)
        // ═══════════════════════════════════════════════════

        $baseCodes = ['LE', 'MAT', 'CN', 'CS', 'EA', 'EF', 'FIHR', 'ING'];
        $baseSubjectIds = Subject::whereIn('code', $baseCodes)->pluck('id', 'code');

        // Asignar asignaturas base a TODOS los grados de Primaria
        $primarioGrades = GradeLevel::where('level', 'primario')->get();
        foreach ($primarioGrades as $grade) {
            foreach ($baseSubjectIds as $code => $subjectId) {
                $grade->subjects()->syncWithoutDetaching([$subjectId]);
            }
        }

        // Asignar asignaturas a Secundaria Primer Ciclo (1ro-3ro)
        $secCiclo1 = GradeLevel::where('level', 'secundario')->where('cycle', 1)->get();
        foreach ($secCiclo1 as $grade) {
            foreach ($baseSubjectIds as $code => $subjectId) {
                $grade->subjects()->syncWithoutDetaching([$subjectId]);
            }
        }

        // Asignar asignaturas específicas a Secundaria Segundo Ciclo (4to-6to)
        $secCiclo2 = GradeLevel::where('level', 'secundario')->where('cycle', 2)->get();
        $secSpecific = Subject::whereIn('code', ['LE', 'MAT', 'BIO', 'QUI', 'FIS', 'HIS', 'GEO', 'EA', 'EF', 'FIHR', 'ING'])->pluck('id');

        foreach ($secCiclo2 as $grade) {
            $grade->subjects()->syncWithoutDetaching($secSpecific);
        }

        $this->command->info('Asignaturas MINERD creadas y asignadas a grados');
    }
}
