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
                    'Nombre' => 'Nombre',
                    'Apellido' => 'Apellido',
                    'Cedula' => 'Cédula',
                    'Telefono' => 'Teléfono',
                    'Email' => 'Email',
                    'Grupo_ID' => 'Grupo ID (Sección)',
                    'Asignatura_ID' => 'Asignatura ID'
                ],
                'method' => 'importStudentFromCsvSpecific'
            ],
            'financial_csv' => [
                'label' => 'Importar Pagos (Formato Específico)',
                'fields' => [
                    'Cedula_Estudiante' => 'Cédula Estudiante',
                    'Monto' => 'Monto Pagado',
                    'Fecha' => 'Fecha de Pago',
                    'Concepto' => 'Concepto / Curso',
                    'Metodo_Pago' => 'Método de Pago',
                    'Estado' => 'Estado',
                    'NCF' => 'NCF (Opcional)'
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
        $cedula = $this->cleanField($data['Cedula'] ?? null);
        if (!$cedula) return; 

        $email = $this->cleanField($data['Email'] ?? null);
        
        $nombreRaw = $this->cleanField($data['Nombre'] ?? 'Estudiante');
        $nombre = $nombreRaw ? Str::title(Str::lower($nombreRaw)) : 'Estudiante';

        $apellidoRaw = $this->cleanField($data['Apellido'] ?? '');
        $apellido = $apellidoRaw ? Str::title(Str::lower($apellidoRaw)) : '';

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = strtolower(str_replace(['-', ' '], '', $cedula)) . '@sga.local';
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $passwordRaw = str_replace(['-', ' '], '', $cedula);
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

        $student = Student::updateOrCreate(
            ['cedula' => $cedula],
            [
                // Generar un código dummy si la base de datos requiere student_code (matricula)
                'student_code' => 'STU-' . Str::upper(Str::random(6)),
                'user_id' => $user->id,
                'first_name' => $nombre,
                'last_name' => $apellido,
                'email' => $email,
                'phone' => $this->cleanField($data['Telefono'] ?? null),
                'status' => 'Activa'
            ]
        );

        $grupoId = $this->cleanField($data['Grupo_ID'] ?? null);
        if ($grupoId) {
            \App\Models\Enrollment::firstOrCreate([
                'student_id' => $student->id,
                'course_schedule_id' => $grupoId,
            ], [
                'status' => 'Cursando'
            ]);
        }
    }

    // ... El resto de métodos (importFinancialHistory, cleanField, etc.) se quedan igual ...
    private function importFinancialHistory($data)
    {
        $cedula = $this->cleanField($data['Cedula_Estudiante'] ?? null);
        if (!$cedula) return;

        $student = Student::where('cedula', $cedula)->first();
        if (!$student) return; 

        $valor = (float) str_replace(',', '', $data['Monto'] ?? 0);
        
        $conceptoNombreRaw = $this->cleanField($data['Concepto']) ?? 'Concepto General';
        $conceptoNombre = ($conceptoNombreRaw !== 'Concepto General') 
            ? Str::title(Str::lower($conceptoNombreRaw)) 
            : 'Concepto General';
        
        $concept = PaymentConcept::firstOrCreate(
            ['name' => $conceptoNombre],
            [
                'description' => 'Importado mediante CSV',
                'default_amount' => $valor,
                'is_fixed_amount' => false
            ]
        );

        $estadoRaw = strtolower($this->cleanField($data['Estado']) ?? 'pagado');
        $estadoMap = [
            'pagado' => 'paid',
            'completado' => 'paid',
            'pendiente' => 'pending',
            'cancelado' => 'voided',
            'anulado' => 'voided',
        ];
        $estadoFinal = $estadoMap[$estadoRaw] ?? 'paid';
        
        $fecha = $this->cleanField($data['Fecha'] ?? null);
        $paymentDate = current(array_filter([
            $fecha ? (function() use ($fecha) { try { return \Carbon\Carbon::parse($fecha)->format('Y-m-d H:i:s'); } catch (\Exception $e) { return null; } })() : null,
            now()->format('Y-m-d H:i:s')
        ]));

        if ($valor > 0) {
            Payment::create([
                'student_id' => $student->id,
                'payment_concept_id' => $concept->id,
                'amount' => $valor,
                'paid_amount' => $estadoFinal === 'paid' ? $valor : 0,
                'status' => $estadoFinal,
                'payment_date' => $paymentDate,
                'currency' => 'DOP',
                'gateway' => Str::title(Str::lower($this->cleanField($data['Metodo_Pago'] ?? 'Transferencia'))),
                'ncf' => $this->cleanField($data['NCF'] ?? null),
            ]);
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