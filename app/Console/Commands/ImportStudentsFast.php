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

        // --- DIAGNÓSTICO CRÍTICO DE RUTA SQLITE ---
        $this->info('--- DIAGNÓSTICO DE CONEXIÓN ---');
        $dbConnection = DB::connection();
        $this->info('Driver: ' . $dbConnection->getDriverName());
        
        // Obtenemos la ruta real del archivo SQLite
        $dbName = $dbConnection->getDatabaseName();
        $this->info('Base de Datos (Archivo): ' . $dbName);
        
        // Verificamos si el archivo existe físicamente
        if (file_exists($dbName)) {
            $this->info('Estado del archivo: EXISTE (Permisos: ' . substr(sprintf('%o', fileperms($dbName)), -4) . ')');
            $this->info('Tamaño: ' . round(filesize($dbName) / 1024 / 1024, 2) . ' MB');
        } else {
            $this->warn('Estado del archivo: NO ENCONTRADO O ES EN MEMORIA (:memory:)');
        }

        $countBefore = DB::table('students')->count();
        $this->info("Estudiantes actuales en esta BD antes de importar: $countBefore");
        
        if (!$this->confirm('Verifica que la ruta de la BD sea la CORRECTA y coincida con la de tu servidor web. ¿Continuar?')) {
            return;
        }
        // -------------------------------------------

        $this->info('--- MODO TURBO ACTIVADO (SQLite Safe) ---');
        $this->info('Este script está hecho a medida para tu archivo CSV.');
        
        // Configuración fija para velocidad
        $pass = '12345678'; // Contraseña por defecto rápida
        $defaultPasswordHash = Hash::make($pass);
        
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
            // Reducimos drásticamente el batch size para SQLite (Límite de variables)
            $batchSize = 250; 
            $this->info("Modo SQLite detectado: Lotes reducidos a $batchSize para evitar errores de memoria.");
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            try {
                DB::statement('SET UNIQUE_CHECKS=0;');
            } catch (\Exception $e) {} 
            $batchSize = 1000;
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

            $city = $this->cleanText($data['Ciudad_Raw'] ?? ''); 
            
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
                'city' => $city,
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
                try {
                    $insertedCount += $this->processBatch($usersBatch, $studentsBatch, $roleId);
                } catch (\Exception $e) {
                    $this->error("Error en lote: " . $e->getMessage());
                }
                
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
            try {
                $insertedCount += $this->processBatch($usersBatch, $studentsBatch, $roleId);
            } catch (\Exception $e) {
                $this->error("Error final: " . $e->getMessage());
            }
            $bar->advance($count);
        }

        $bar->finish();
        fclose($handle);

        // Reactivar seguridad
        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            try {
                DB::statement('SET UNIQUE_CHECKS=1;');
            } catch (\Exception $e) {}
        }

        $duration = round(microtime(true) - $startTime, 2);
        $this->newLine(2);
        
        $countAfter = DB::table('students')->count();
        $this->info("¡FINALIZADO!");
        $this->info("Estudiantes en BD ahora: " . number_format($countAfter));
        $this->info("Insertados en esta ejecución: " . number_format($insertedCount));
        $this->warn("Ignorados (duplicados/errores): " . number_format($skippedCount));
        $this->line("Tiempo: $duration segundos");
    }

    private function processBatch($usersData, $studentsData, $roleId)
    {
        // 1. Insertar Usuarios
        DB::table('users')->insertOrIgnore($usersData);

        // 2. Obtener IDs
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
            $insertedStudents = DB::table('students')->insertOrIgnore($finalStudents);
        }
        if (count($finalRoles) > 0) {
            DB::table('model_has_roles')->insertOrIgnore($finalRoles);
        }

        return $insertedStudents;
    }
    
    // --- FUNCIONES AUXILIARES (Sin cambios) ---
    private function generateUniqueCedula($cedula, $matricula) {
        $c = trim($cedula);
        $invalid = ['ND', 'N/D', '', '-       -', '000-0000000-0', 'SIN CEDULA'];
        if (in_array(strtoupper($c), $invalid) || strlen($c) < 5) return 'GEN-' . $matricula; 
        return $c;
    }
    private function cleanEmail($email, $matricula) {
        $email = trim($email);
        if (empty($email) || strlen($email) < 5 || strpos($email, '@') === false || strtoupper($email) === 'ND') {
            return strtolower($matricula) . '@sga.local';
        }
        return $email;
    }
    private function cleanText($text) {
        if (empty($text)) return null;
        if (!mb_check_encoding($text, 'UTF-8')) $text = mb_convert_encoding($text, 'UTF-8', 'Windows-1252');
        $text = trim(preg_replace('/[\x00-\x1F\x7F\xEF\xBB\xBF]/', '', $text));
        $text = trim($text, "\"' ");
        $text = str_replace("\xEF\xBB\xBF", '', $text); 
        $text = preg_replace('/^[^a-zA-Z0-9\(\)]+/', '', $text); 
        if (strtoupper($text) === 'ND' || $text === '-       -') return null;
        return Str::title(Str::lower($text));
    }
    private function cleanPhone($text) {
        $text = trim($text);
        if (empty($text) || strtoupper($text) === 'ND' || $text === '-       -') return null;
        return $text;
    }
    private function parseDate($date) {
        if (empty($date)) return null;
        $parts = explode('/', $date);
        if (count($parts) === 3) return $parts[2] . '-' . str_pad($parts[1], 2, '0', STR_PAD_LEFT) . '-' . str_pad($parts[0], 2, '0', STR_PAD_LEFT);
        return null;
    }
    private function countRows($file) {
        $lineCount = 0;
        $handle = fopen($file, "r");
        while(!feof($handle)){ $line = fgets($handle); if($line !== false) $lineCount++; }
        fclose($handle);
        return max(0, $lineCount - 1);
    }
}