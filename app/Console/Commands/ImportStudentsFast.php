<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ImportStudentsFast extends Command
{
    protected $signature = 'app:import-students-fast {file=BASE_DE_DATOS_ESTUDIANTES.csv}';
    protected $description = 'Importación optimizada y adaptada específicamente para el CSV de Estudiantes.';

    public function handle()
    {
        $fileName = $this->argument('file');
        
        if (!file_exists($fileName)) {
            $this->error("El archivo '$fileName' no existe.");
            return;
        }

        $this->info('--- MODO TURBO ACTIVADO ---');
        $this->info('Este script está hecho a medida para tu archivo CSV.');
        
        // Configuración fija para velocidad
        $pass = '12345678'; // Contraseña por defecto rápida
        $defaultPasswordHash = Hash::make($pass);
        $mustChangePassword = true; 

        $this->info("Contraseña temporal para todos: $pass");
        $this->info('Iniciando proceso...');
        
        $startTime = microtime(true);

        // Configuración de servidor
        ini_set('memory_limit', '2048M');
        set_time_limit(0);
        
        // Desactivar frenos de seguridad para velocidad máxima
        DB::disableQueryLog();
        
        // --- DETECCIÓN DE DRIVER PARA DESACTIVAR LLAVES FORÁNEAS ---
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::statement('SET UNIQUE_CHECKS=0;'); // Solo en MySQL
        }

        // Obtener Rol
        $role = DB::table('roles')->where('name', 'Estudiante')->first();
        if (!$role) {
            $this->error('Error: No existe el rol "Estudiante".');
            return;
        }
        $roleId = $role->id;

        // Abrir archivo
        $handle = fopen($fileName, 'r');
        
        // Detectar delimitador (probablemente punto y coma ;)
        $firstLine = fgets($handle);
        rewind($handle); // Volver al inicio
        $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';
        
        // Leer y limpiar cabeceras
        $headerLine = fgetcsv($handle, 0, $delimiter);
        $headers = array_map(function($v) { 
            // Limpiar BOM y espacios invisibles
            return trim(preg_replace('/[\x00-\x1F\x7F\xEF\xBB\xBF]/', '', $v)); 
        }, $headerLine);

        $batchSize = 2000; // Procesar de 2000 en 2000
        $usersBatch = [];
        $studentsBatch = [];
        $emailsInBatch = [];
        $cedulasInBatch = [];
        
        $count = 0;
        $insertedCount = 0;
        $skippedCount = 0;
        
        $totalRows = $this->countRows($fileName);
        $bar = $this->output->createProgressBar($totalRows);
        $bar->start();

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            
            // Si la fila está rota, intentamos saltarla o arreglarla simple
            if (count($row) !== count($headers)) {
                $skippedCount++;
                $bar->advance();
                continue;
            }
            
            $data = array_combine($headers, $row);
            
            // Extracción directa de tus columnas
            $matricula = trim($data['Matricula'] ?? '');
            
            if (empty($matricula)) {
                $skippedCount++;
                $bar->advance();
                continue;
            }

            // --- 1. LIMPIEZA DE CÉDULA ---
            $cedulaRaw = trim($data['Cedula'] ?? '');
            $cedula = $this->generateUniqueCedula($cedulaRaw, $matricula);
            
            // Evitar duplicados de cédula en el mismo lote
            if (isset($cedulasInBatch[$cedula])) {
                $cedula = 'DUP-' . Str::random(4) . '-' . $matricula;
            }
            $cedulasInBatch[$cedula] = true;

            // --- 2. LIMPIEZA DE EMAIL ---
            $email = $this->cleanEmail($data['Correo'] ?? '', $matricula);
            
            // Evitar duplicados de email en el mismo lote
            if (isset($emailsInBatch[$email])) {
                $email = strtolower($matricula) . '@sga.local';
            }
            $emailsInBatch[$email] = true;

            // --- 3. LIMPIEZA DE NOMBRES ---
            $nombre = $this->cleanText($data['Nombre'] ?? '');
            $apellido = $this->cleanText($data['Apellido'] ?? '');
            
            if (empty($nombre)) $nombre = 'Estudiante';
            if (empty($apellido)) $apellido = $matricula;

            $fullName = trim("$nombre $apellido");
            $now = now()->toDateTimeString();

            // Preparar Usuario (User)
            $usersBatch[] = [
                'name' => $fullName,
                'email' => $email,
                'password' => $defaultPasswordHash,
                'must_change_password' => 1, // Siempre forzar cambio
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // --- 4. VALORES POR DEFECTO INTELIGENTES ---
            $address = $this->cleanText($data['Direccion'] ?? '');
            if (empty($address)) $address = 'Sin Dirección Registrada';

            $city = $this->cleanText($data['Ciudad_Raw'] ?? ''); // Nueva columna
            
            $nationality = $this->cleanText($data['Nacionalidad'] ?? '');
            if (empty($nationality)) $nationality = 'Dominicana';

            $gender = $this->cleanText($data['Sexo'] ?? '');
            if (empty($gender) || !in_array(strtoupper($gender), ['M', 'F', 'MASCULINO', 'FEMENINO'])) {
                $gender = null;
            }

            // Preparar Estudiante (Student)
            $studentsBatch[$email] = [
                'student_code' => $matricula,
                'first_name' => $nombre,
                'last_name' => $apellido,
                'cedula' => $cedula, 
                'email' => $email,
                'address' => $address,
                'city' => $city, // Guardamos la ciudad
                'birth_date' => $this->parseDate($data['Fecha Nacimiento'] ?? ''),
                'phone' => $this->cleanPhone($data['Telefono'] ?? null),
                'mobile' => $this->cleanPhone($data['Celular'] ?? null),
                'gender' => $gender,
                'nationality' => $nationality,
                'status' => 'Activa',
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $count++;

            // Ejecutar lote cuando se llena
            if ($count >= $batchSize) {
                $insertedCount += $this->processBatch($usersBatch, $studentsBatch, $roleId);
                
                // Limpiar memoria
                $usersBatch = [];
                $studentsBatch = [];
                $emailsInBatch = [];
                $cedulasInBatch = [];
                $count = 0;
                $bar->advance($batchSize);
            }
        }

        // Procesar remanentes
        if ($count > 0) {
            $insertedCount += $this->processBatch($usersBatch, $studentsBatch, $roleId);
            $bar->advance($count);
        }

        $bar->finish();
        fclose($handle);

        // Reactivar seguridad
        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            DB::statement('SET UNIQUE_CHECKS=1;');
        }

        $duration = round(microtime(true) - $startTime, 2);
        $this->newLine(2);
        $this->info("¡FINALIZADO!");
        $this->line("Total CSV: " . number_format($totalRows));
        $this->info("Insertados: " . number_format($insertedCount));
        $this->warn("Ignorados: " . number_format($skippedCount));
        $this->line("Tiempo: $duration segundos");
    }

    private function processBatch($usersData, $studentsData, $roleId)
    {
        // 1. Insertar Usuarios (ignorando si el email ya existe)
        DB::table('users')->insertOrIgnore($usersData);

        // 2. Obtener IDs de los usuarios recién insertados (o existentes)
        $emails = array_keys($studentsData);
        $usersMap = DB::table('users')
            ->whereIn('email', $emails)
            ->pluck('id', 'email'); 

        $finalStudents = [];
        $finalRoles = [];

        foreach ($studentsData as $email => $studentRow) {
            if (isset($usersMap[$email])) {
                $userId = $usersMap[$email];
                $studentRow['user_id'] = $userId;
                
                // Quitamos el email del array de estudiante si ya está en la tabla users 
                // (aunque tu tabla students lo tiene duplicado, así que lo dejamos).
                
                $finalStudents[] = $studentRow;

                $finalRoles[] = [
                    'role_id' => $roleId,
                    'model_type' => 'App\Models\User',
                    'model_id' => $userId
                ];
            }
        }

        // 3. Insertar Estudiantes y Roles
        $insertedStudents = 0;
        if (count($finalStudents) > 0) {
            // Usamos insertOrIgnore por si la matrícula o cédula ya existen
            $insertedStudents = DB::table('students')->insertOrIgnore($finalStudents);
        }
        if (count($finalRoles) > 0) {
            DB::table('model_has_roles')->insertOrIgnore($finalRoles);
        }

        return $insertedStudents;
    }

    // --- FUNCIONES DE LIMPIEZA A MEDIDA ---

    private function generateUniqueCedula($cedula, $matricula)
    {
        $c = trim($cedula);
        $invalid = ['ND', 'N/D', '', '-       -', '000-0000000-0', 'SIN CEDULA'];
        // Si la cédula es inválida o muy corta, generamos una basada en la matrícula
        if (in_array(strtoupper($c), $invalid) || strlen($c) < 5) {
            return 'GEN-' . $matricula; 
        }
        return $c;
    }

    private function cleanEmail($email, $matricula) {
        $email = trim($email);
        // Validación simple: si no parece un email o es "ND", creamos uno falso
        if (empty($email) || strlen($email) < 5 || strpos($email, '@') === false || strtoupper($email) === 'ND') {
            return strtolower($matricula) . '@sga.local';
        }
        return $email;
    }

    private function cleanText($text) {
        if (empty($text)) return null;
        
        // 1. Fix Encoding: Convertir de Windows-1252 a UTF-8 si es necesario
        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'Windows-1252');
        }

        // 2. Quitar basura al inicio (BOM, comillas, puntos raros)
        $text = trim($text, "\"' ");
        $text = str_replace("\xEF\xBB\xBF", '', $text); 
        $text = preg_replace('/^[^a-zA-Z0-9\(\)]+/', '', $text); // Quita símbolos no alfanuméricos del inicio

        if (strtoupper($text) === 'ND' || $text === '-       -') return null;

        // 3. Formato Título (Juan Perez)
        return Str::title(Str::lower($text));
    }

    private function cleanPhone($text) {
        $text = trim($text);
        if (empty($text) || strtoupper($text) === 'ND' || $text === '-       -') return null;
        return $text;
    }

    private function parseDate($date) {
        if (empty($date)) return null;
        // Parsea formato d/m/Y (ej: 25/8/2002) -> Y-m-d
        $parts = explode('/', $date);
        if (count($parts) === 3) {
            return $parts[2] . '-' . str_pad($parts[1], 2, '0', STR_PAD_LEFT) . '-' . str_pad($parts[0], 2, '0', STR_PAD_LEFT);
        }
        return null;
    }

    private function countRows($file) {
        $lineCount = 0;
        $handle = fopen($file, "r");
        while(!feof($handle)){
            $line = fgets($handle);
            if($line !== false) $lineCount++;
        }
        fclose($handle);
        return max(0, $lineCount - 1);
    }
}