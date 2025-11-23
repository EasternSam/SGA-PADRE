<?php

namespace App\Services;

use App\Models\User;
use App\Models\Student;
use App\Models\Course;
use App\Models\PaymentConcept;
use App\Models\Payment;
use App\Models\CourseMapping;
use App\Services\WordpressApiService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CsvImportService
{
    protected $wpService;
    protected $wpCoursesCache = null;

    public function __construct(WordpressApiService $wpService)
    {
        $this->wpService = $wpService;
    }

    public function getEntityConfig()
    {
        return [
            'students_csv' => [
                'label' => 'Importar CSV Estudiantes (Formato Específico)',
                'fields' => [
                    'Matricula' => 'Matrícula (student_code)',
                    'Nombre' => 'Nombres',
                    'Apellido' => 'Apellidos',
                    'Direccion' => 'Dirección',
                    'Fecha Nacimiento' => 'Fecha Nacimiento (d/m/Y)',
                    'Telefono' => 'Teléfono',
                    'Celular' => 'Celular',
                    'Correo' => 'Email (Si es ND se genera auto)',
                    'Cedula' => 'Cédula',
                    'Sexo' => 'Género',
                    'Nacionalidad' => 'Nacionalidad'
                ],
                'method' => 'importStudentFromCsvSpecific'
            ],
            // ... resto de configuraciones igual ...
            'financial_csv' => [
                'label' => 'Importar Historial Financiero (Formato Específico)',
                'fields' => [
                    'Matricula' => 'Matrícula Estudiante',
                    'Curso' => 'Concepto / Curso',
                    'Valor' => 'Monto Total',
                    'Abonos' => 'Monto Pagado',
                    'Balance' => 'Deuda Pendiente',
                    'Año' => 'Año (Referencia)',
                    'Seccion' => 'Sección (Referencia)'
                ],
                'method' => 'importFinancialHistory'
            ],
            'generic_courses' => [
                'label' => 'Cursos (Genérico)',
                'fields' => [ 'name' => 'Nombre', 'description' => 'Descripción' ],
                'method' => 'importCourse'
            ]
        ];
    }

    // ... (Métodos de lectura getCsvHeaders, countRows, importBatch se mantienen igual) ...
    public function getCsvHeaders($filePath)
    {
        if (!file_exists($filePath)) return [];
        $handle = fopen($filePath, 'r');
        $firstLine = fgets($handle);
        fclose($handle);
        if (!$firstLine) return [];
        $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';
        $headers = str_getcsv(trim($firstLine), $delimiter);
        return array_map(function($h) { return trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h)); }, $headers);
    }

    public function countRows($filePath)
    {
        try {
            $lineCount = 0;
            $handle = fopen($filePath, "r");
            while(!feof($handle)){
                $line = fgets($handle);
                if ($line !== false) $lineCount++;
            }
            fclose($handle);
            return max(0, $lineCount - 1);
        } catch (\Exception $e) {
            $file = new \SplFileObject($filePath, 'r');
            $file->seek(PHP_INT_MAX);
            return $file->key();
        }
    }

    public function importBatch($entityKey, $filePath, $mapping, $startRow, $chunkSize)
    {
        $config = $this->getEntityConfig()[$entityKey] ?? null;
        if (!$config) return ['processed' => 0, 'errors' => ["Configuración no encontrada"]];

        if ($entityKey === 'financial_csv' && $this->wpCoursesCache === null) {
            try { $this->wpCoursesCache = $this->wpService->getCourses() ?? []; } catch (\Exception $e) { $this->wpCoursesCache = []; }
        }

        $headers = $this->getCsvHeaders($filePath);
        $file = new \SplFileObject($filePath, 'r');
        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::READ_AHEAD | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);
        $firstLine = file_get_contents($filePath, false, null, 0, 500);
        $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';
        $file->setCsvControl($delimiter);
        $file->seek($startRow + 1);

        $count = 0;
        $errors = [];
        $batchData = [];

        while (!$file->eof() && $count < $chunkSize) {
            $row = $file->current();
            if (!empty($row) && count($row) > 1) {
                if (count($row) !== count($headers)) $row = array_pad($row, count($headers), '');
                $rowData = @array_combine($headers, $row);
                if ($rowData) {
                    $mappedItem = [];
                    foreach ($mapping as $dbField => $csvHeader) {
                        if ($csvHeader && isset($rowData[$csvHeader])) {
                            $mappedItem[$dbField] = trim($rowData[$csvHeader]);
                        }
                    }
                    $batchData[] = $mappedItem;
                }
            }
            $file->next();
            $count++;
        }

        if (count($batchData) > 0) {
            DB::beginTransaction();
            try {
                foreach ($batchData as $data) {
                    if (isset($config['method']) && method_exists($this, $config['method'])) {
                        $this->{$config['method']}($data);
                    }
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $errors[] = "Error en bloque $startRow: " . $e->getMessage();
            }
        }

        return ['processed' => count($batchData), 'errors' => $errors];
    }

    // --- MÉTODOS DE IMPORTACIÓN ---
    
    private function importStudentFromCsvSpecific($data)
    {
        $matricula = $this->cleanField($data['Matricula'] ?? null);
        if (!$matricula) return; 

        $cedula = $this->cleanField($data['Cedula'] ?? null);
        $email = $this->cleanField($data['Correo'] ?? null);
        
        $nombreRaw = $this->cleanField($data['Nombre'] ?? 'Estudiante');
        $nombre = $nombreRaw ? Str::title(Str::lower($nombreRaw)) : 'Estudiante';

        $apellidoRaw = $this->cleanField($data['Apellido'] ?? '');
        $apellido = $apellidoRaw ? Str::title(Str::lower($apellidoRaw)) : '';

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = strtolower($matricula) . '@sga.local';
        }

        $birthDate = null;
        if (!empty($data['Fecha Nacimiento'])) {
            try {
                $birthDate = Carbon::createFromFormat('d/m/Y', $data['Fecha Nacimiento'])->format('Y-m-d');
            } catch (\Exception $e) { 
                try { $birthDate = Carbon::parse($data['Fecha Nacimiento'])->format('Y-m-d'); } catch (\Exception $e) {}
            }
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $passwordRaw = $cedula ? str_replace(['-', ' '], '', $cedula) : $matricula;
            $user = User::create([
                'email' => $email,
                'name' => "$nombre $apellido",
                'password' => Hash::make($passwordRaw),
                'email_verified_at' => now(),
            ]);
        }
        
        if (!$user->relationLoaded('roles') || !$user->hasRole('Estudiante')) {
            $user->assignRole('Estudiante');
        }

        $address = $this->cleanField($data['Direccion'] ?? null);
        if ($address) {
            $address = ucfirst(Str::lower($address));
        }

        Student::updateOrCreate(
            ['student_code' => $matricula],
            [
                'user_id' => $user->id,
                'first_name' => $nombre,
                'last_name' => $apellido,
                'cedula' => $cedula ?? 'SIN-CEDULA-'.$matricula,
                'email' => $email,
                'address' => $address,
                'birth_date' => $birthDate,
                
                // --- AGREGADO: Soporte para Teléfonos ---
                'phone' => $this->cleanField($data['Telefono'] ?? null),
                'mobile' => $this->cleanField($data['Celular'] ?? null),
                // ----------------------------------------

                'gender' => $this->cleanField($data['Sexo']),
                'nationality' => $this->cleanField($data['Nacionalidad']),
                'status' => 'Activa'
            ]
        );
    }

    // ... El resto de métodos (importFinancialHistory, cleanField, etc.) se quedan igual ...
    private function importFinancialHistory($data)
    {
        $matricula = $this->cleanField($data['Matricula'] ?? null);
        if (!$matricula) return;

        $student = Student::where('student_code', $matricula)->first();
        if (!$student) return; 

        $valor = (float) str_replace(',', '', $data['Valor'] ?? 0);
        $abonos = (float) str_replace(',', '', $data['Abonos'] ?? 0);
        
        $conceptoNombreRaw = $this->cleanField($data['Curso']) ?? 'Concepto General';
        $conceptoNombre = ($conceptoNombreRaw !== 'Concepto General') 
            ? Str::title(Str::lower($conceptoNombreRaw)) 
            : 'Concepto General';

        if ($conceptoNombre && $conceptoNombre !== 'Concepto General') {
            $course = Course::firstOrCreate(
                ['name' => $conceptoNombre],
                ['description' => 'Importado Auto', 'status' => 'Activo']
            );
            
            if ($course->wasRecentlyCreated) {
                $wpMatch = $this->findWpCourseMatch($conceptoNombre);
                if ($wpMatch && class_exists(CourseMapping::class)) {
                    CourseMapping::create(['course_id' => $course->id, 'wp_post_id' => $wpMatch['id']]);
                }
            }
        }
        
        $concept = PaymentConcept::firstOrCreate(
            ['name' => $conceptoNombre],
            [
                'description' => 'Importado. Sección: ' . ($data['Seccion'] ?? ''),
                'default_amount' => $valor,
                'is_fixed_amount' => false
            ]
        );

        if ($valor > 0) {
            Payment::firstOrCreate(
                [
                    'student_id' => $student->id,
                    'payment_concept_id' => $concept->id,
                    'amount' => $valor,
                    'notes' => "Sección: {$data['Seccion']}, Año: {$data['Año']}"
                ],
                [
                    'paid_amount' => $abonos,
                    'status' => ($abonos >= $valor) ? 'paid' : 'pending',
                    'payment_date' => now(),
                    'currency' => 'DOP',
                    'gateway' => 'Importación'
                ]
            );
        }
    }

    private function findWpCourseMatch($csvName)
    {
        if (empty($this->wpCoursesCache)) return null;
        $csvSlug = Str::slug($csvName);
        foreach ($this->wpCoursesCache as $wpCourse) {
            $wpTitle = $wpCourse['title']['rendered'] ?? $wpCourse['title'] ?? '';
            if ($csvSlug === Str::slug($wpTitle)) return $wpCourse;
            similar_text(strtoupper($csvName), strtoupper($wpTitle), $percent);
            if ($percent > 85) return $wpCourse;
        }
        return null;
    }

    private function cleanField($value)
    {
        if (!$value) return null;
        $value = trim($value);
        return (strtoupper($value) === 'ND' || $value === '-       -') ? null : $value;
    }

    private function importCourse($data) { 
        if (empty($data['name'])) return;
        $name = Str::title(Str::lower($data['name']));
        Course::updateOrCreate(['name' => $name], ['description' => $data['description'] ?? null]);
    }
}