<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Course;
use App\Models\Module;
use App\Models\PaymentConcept;
use App\Models\CourseMapping;
use App\Services\WordpressApiService;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ImportFinancialsFast extends Command
{
    protected $signature = 'app:import-financials-fast {file=BASE_DE_DATOS_FINANCIERA.csv}';
    protected $description = 'Importa historial financiero, estructura Cursos/Módulos/Secciones con datos dummy y vincula con WP.';

    protected $wpService;
    protected $wpCoursesCache = [];

    public function __construct(WordpressApiService $wpService)
    {
        parent::__construct();
        $this->wpService = $wpService;
    }

    public function handle()
    {
        $fileName = $this->argument('file');

        if (!file_exists($fileName)) {
            $this->error("El archivo '$fileName' no existe.");
            return;
        }

        $this->info('--- FASE 0: CARGA DE DATOS WP ---');
        try {
            $this->wpCoursesCache = $this->wpService->getCourses() ?? [];
            $this->info('✅ Se cargaron ' . count($this->wpCoursesCache) . ' cursos de WP para vinculación.');
        } catch (\Exception $e) {
            $this->warn('⚠️ Sin conexión a WP API. Se omitirá la vinculación automática.');
            $this->wpCoursesCache = [];
        }
        
        $this->newLine();

        $this->info('--- FASE 1: ANÁLISIS DE ESTRUCTURA ---');
        
        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        $delimiter = $this->detectDelimiter($fileName);
        $handle = fopen($fileName, 'r');
        
        $headerLine = fgetcsv($handle, 0, $delimiter);
        $headers = array_map(function($v) { 
            return trim(preg_replace('/[\x00-\x1F\x7F\xEF\xBB\xBF]/', '', $v)); 
        }, $headerLine);

        $idxCurso = array_search('Curso', $headers);
        $idxValor = array_search('Valor', $headers);
        $idxSeccion = array_search('Seccion', $headers);
        $idxAnio = array_search('Año', $headers); 

        if ($idxCurso === false) {
            $this->error('Columna "Curso" no encontrada.');
            return;
        }

        $rawItems = [];
        $this->info("Escaneando catálogo...");
        $bar = $this->output->createProgressBar($this->countRows($fileName));
        $bar->start();
        
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (count($row) !== count($headers)) { $bar->advance(); continue; }

            $cursoRaw = trim($row[$idxCurso] ?? '');
            $cursoName = $this->cleanText($cursoRaw);
            
            if (empty($cursoName)) { $bar->advance(); continue; }

            $valor = (float) str_replace([',', '$'], '', $row[$idxValor] ?? '0');

            if (!isset($rawItems[$cursoName])) {
                $rawItems[$cursoName] = ['count' => 0, 'prices' => []];
            }
            
            $rawItems[$cursoName]['count']++;
            $valKey = (string)$valor;
            if (!isset($rawItems[$cursoName]['prices'][$valKey])) $rawItems[$cursoName]['prices'][$valKey] = 0;
            $rawItems[$cursoName]['prices'][$valKey]++;
            $bar->advance();
        }
        $bar->finish();
        fclose($handle);

        $this->newLine(2);
        if (!$this->confirm('¿Proceder a crear estructura (con datos dummy) e importar pagos?')) return;

        // --- FASE 2: CREACIÓN DE CURSOS Y MÓDULOS ---
        $this->info("Creando estructura académica...");
        
        $conceptMap = []; 
        $coursesCreated = []; 
        $moduleMap = []; 

        foreach (DB::table('modules')->get() as $mod) {
            $moduleMap[strtoupper($mod->name)] = $mod->id;
        }

        foreach ($rawItems as $csvName => $data) {
            $prices = $data['prices'];
            arsort($prices);
            $defaultPrice = (float) array_key_first($prices);

            $structure = $this->analyzeCourseStructure($csvName);
            $parentName = $structure['parent'];
            $isModule = $structure['is_module'];

            $wpMatch = $this->findWpMatch($parentName);
            $finalParentName = $wpMatch ? ($wpMatch['title']['rendered'] ?? $wpMatch['title']) : $parentName;
            $wpId = $wpMatch ? $wpMatch['id'] : null;

            if (!isset($coursesCreated[$parentName])) {
                $course = Course::where('name', $finalParentName)->first();
                if (!$course) {
                    $course = Course::create([
                        'name' => $finalParentName,
                        'description' => $wpMatch ? 'Vinculado con WP' : 'Importado',
                        'status' => 'Activo',
                        'created_at' => now(), 'updated_at' => now()
                    ]);
                }
                if ($wpId) {
                    CourseMapping::firstOrCreate(['course_id' => $course->id], ['wp_post_id' => $wpId]);
                }
                $coursesCreated[$parentName] = $course->id;
            }
            $parentId = $coursesCreated[$parentName];

            $finalModuleName = $isModule ? $csvName : $finalParentName; 
            $moduleKey = strtoupper($finalModuleName);

            if (!isset($moduleMap[$moduleKey])) {
                $mod = DB::table('modules')->where('name', $finalModuleName)->where('course_id', $parentId)->first();
                if ($mod) {
                    $moduleId = $mod->id;
                } else {
                    // --- INTENTO DE CREACIÓN CON CÓDIGO ÚNICO ---
                    $moduleId = $this->createModuleSafe($parentId, $finalModuleName, $defaultPrice);
                }
                $moduleMap[$moduleKey] = $moduleId;
                $moduleMap[strtoupper($csvName)] = $moduleId; 
            }

            $concept = PaymentConcept::firstOrCreate(
                ['name' => $csvName],
                [
                    'description' => "Importado: $finalParentName",
                    'default_amount' => $defaultPrice,
                    'is_fixed_amount' => false
                ]
            );
            $conceptMap[$csvName] = $concept->id;
        }

        // --- FASE 3: PAGOS Y SECCIONES ---
        $this->info("--- FASE 3: PAGOS Y SECCIONES ---");
        
        $handle = fopen($fileName, 'r');
        fgetcsv($handle, 0, $delimiter); 

        DB::disableQueryLog();
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        $batchSize = 2000;
        $paymentsBatch = [];
        $enrollmentsBatch = [];
        $count = 0;
        $insertedCount = 0;
        $skippedCount = 0;

        $this->info("Cargando mapa de estudiantes...");
        $studentMap = DB::table('students')->pluck('id', 'student_code')->toArray();
        $sectionMap = []; 

        $bar = $this->output->createProgressBar($this->countRows($fileName));
        $bar->start();

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (count($row) !== count($headers)) { $skippedCount++; $bar->advance(); continue; }

            $data = array_combine($headers, $row);
            $matricula = trim($data['Matricula'] ?? '');
            
            if (empty($matricula) || !isset($studentMap[$matricula])) {
                $skippedCount++; $bar->advance(); continue;
            }
            $studentId = $studentMap[$matricula];

            $cursoName = $this->cleanText($data['Curso'] ?? '');
            if (empty($cursoName) || !isset($conceptMap[$cursoName])) {
                $bar->advance(); continue;
            }

            $seccionName = trim($data['Seccion'] ?? 'UNICA');
            if (empty($seccionName)) $seccionName = 'UNICA';
            
            $anio = intval($data['Año'] ?? date('Y'));
            if ($anio < 1990) $anio = date('Y');

            $moduleId = $moduleMap[strtoupper($cursoName)] ?? null;

            if ($moduleId) {
                $sectionKey = "{$moduleId}-{$seccionName}-{$anio}";

                if (!isset($sectionMap[$sectionKey])) {
                    $existingSection = DB::table('course_schedules')
                        ->where('module_id', $moduleId)
                        ->where('section_name', $seccionName)
                        ->whereYear('start_date', $anio)
                        ->first();

                    if ($existingSection) {
                        $sectionId = $existingSection->id;
                    } else {
                        $startDate = Carbon::create($anio, 1, 15)->format('Y-m-d');
                        $endDate = Carbon::create($anio, 5, 15)->format('Y-m-d');
                        
                        $daysPool = [
                            json_encode(['Lunes', 'Miércoles']),
                            json_encode(['Martes', 'Jueves']),
                            json_encode(['Sábado'])
                        ];
                        $dummyDays = $daysPool[array_rand($daysPool)];

                        $sectionId = DB::table('course_schedules')->insertGetId([
                            'module_id' => $moduleId,
                            'section_name' => $seccionName,
                            'professor_id' => null, 
                            'start_date' => $startDate,
                            'end_date' => $endDate,
                            'days_of_week' => $dummyDays,
                            'start_time' => '18:00:00',
                            'end_time' => '20:00:00',
                            'room' => 'Aula Virtual', 
                            'capacity' => 50,
                            'status' => 'Cerrada', 
                            'created_at' => now(), 'updated_at' => now()
                        ]);
                    }
                    $sectionMap[$sectionKey] = $sectionId;
                }
                $sectionId = $sectionMap[$sectionKey];

                $enrollKey = "{$studentId}-{$sectionId}";
                if (!isset($enrollmentsBatch[$enrollKey])) {
                    $enrollmentsBatch[$enrollKey] = [
                        'course_schedule_id' => $sectionId,
                        'student_id' => $studentId,
                        'status' => 'Completado',
                        'final_grade' => null,
                        'created_at' => now(), 'updated_at' => now()
                    ];
                }
            }

            $valor = (float) str_replace([',', '$'], '', $data['Valor'] ?? '0');
            $abonos = (float) str_replace([',', '$'], '', $data['Abonos'] ?? '0');
            $balance = (float) str_replace([',', '$'], '', $data['Balance'] ?? '0');
            
            $status = 'Pendiente';
            if ($balance <= 0 && $valor > 0) $status = 'Completado';
            elseif ($abonos > 0) $status = 'Parcial';

            $paymentsBatch[] = [
                'student_id' => $studentId,
                'payment_concept_id' => $conceptMap[$cursoName],
                'amount' => $valor,
                'status' => $status,
                'currency' => 'DOP',
                'gateway' => 'Importado',
                'transaction_id' => 'HIST-' . uniqid(),
                'created_at' => now(), 'updated_at' => now(),
            ];

            $count++;

            if ($count >= $batchSize) {
                DB::table('payments')->insert($paymentsBatch);
                if (!empty($enrollmentsBatch)) {
                    DB::table('enrollments')->insertOrIgnore(array_values($enrollmentsBatch));
                }
                $paymentsBatch = [];
                $enrollmentsBatch = [];
                $insertedCount += $count;
                $count = 0;
                $bar->advance($batchSize);
            }
        }

        if ($count > 0) {
            DB::table('payments')->insert($paymentsBatch);
            if (!empty($enrollmentsBatch)) {
                DB::table('enrollments')->insertOrIgnore(array_values($enrollmentsBatch));
            }
            $insertedCount += $count;
            $bar->advance($count);
        }

        $bar->finish();
        fclose($handle);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->newLine(2);
        $this->info("¡FINALIZADO!");
        $this->info("Pagos: " . number_format($insertedCount));
        $this->warn("Ignorados: " . number_format($skippedCount));
    }

    // --- MÉTODO SEGURO DE CREACIÓN DE MÓDULO ---
    private function createModuleSafe($parentId, $finalModuleName, $defaultPrice)
    {
        $attempts = 0;
        while ($attempts < 5) {
            try {
                return DB::table('modules')->insertGetId([
                    'course_id' => $parentId,
                    'name' => $finalModuleName,
                    'code' => $this->generateCode($finalModuleName), // Genera código nuevo en cada intento
                    'price' => $defaultPrice,
                    'status' => 'Activo',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } catch (\Exception $e) {
                // Si es error de duplicado (código 23000), reintentamos con otro código
                if (str_contains($e->getMessage(), 'Duplicate entry') || $e->getCode() == 23000) {
                    $attempts++;
                    continue;
                }
                throw $e; // Otro error, lanzarlo
            }
        }
        throw new \Exception("No se pudo generar un código único para el módulo '$finalModuleName' después de 5 intentos.");
    }

    private function findWpMatch($csvName) {
        if (empty($this->wpCoursesCache)) return null;
        $csvSlug = Str::slug($csvName);
        $bestMatch = null;
        $highestPercent = 0;
        foreach ($this->wpCoursesCache as $wpCourse) {
            $wpTitle = $wpCourse['title']['rendered'] ?? $wpCourse['title'] ?? '';
            $wpSlug = Str::slug($wpTitle);
            if ($csvSlug === $wpSlug) return $wpCourse;
            similar_text(strtoupper($csvName), strtoupper($wpTitle), $percent);
            if ($percent > 85 && $percent > $highestPercent) {
                $highestPercent = $percent;
                $bestMatch = $wpCourse;
            }
        }
        return $bestMatch;
    }

    private function analyzeCourseStructure($name) {
        $patterns = ['/^\(\d+\)\s*/', '/\s+(I{1,3}|IV|V|VI{0,3}|IX|X)$/i', '/\s+\d+$/', '/^M[óo]dulo\s+\d+\s*/i', '/\s+(B[áa]sico|Intermedio|Avanzado)$/i'];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $name)) {
                $parent = trim(preg_replace($pattern, '', $name));
                if (strlen($parent) < 2) continue; 
                return ['is_module' => true, 'parent' => Str::title($parent), 'module_name' => $name];
            }
        }
        return ['is_module' => false, 'parent' => $name, 'module_name' => $name];
    }

    // --- MEJORA: Código Único y Aleatorio ---
    private function generateCode($name)
    {
        // Genera un código con 3 letras + 5 caracteres aleatorios alfanuméricos para evitar colisiones
        $prefix = strtoupper(substr(Str::slug($name), 0, 3));
        return $prefix . '-' . strtoupper(Str::random(5));
    }

    private function detectDelimiter($file) {
        $handle = fopen($file, "r");
        $line = fgets($handle);
        fclose($handle);
        return (strpos($line, ';') !== false) ? ';' : ',';
    }
    private function cleanText($text) {
        if (empty($text)) return null;
        if (!mb_check_encoding($text, 'UTF-8')) $text = mb_convert_encoding($text, 'UTF-8', 'Windows-1252');
        $text = trim(preg_replace('/[\x00-\x1F\x7F\xEF\xBB\xBF]/', '', $text));
        return Str::title(Str::lower($text));
    }
    private function countRows($file) {
        $lineCount = 0;
        $handle = fopen($file, "r");
        while(!feof($handle)){ $line = fgets($handle); if($line !== false) $lineCount++; }
        fclose($handle);
        return max(0, $lineCount - 1);
    }
}