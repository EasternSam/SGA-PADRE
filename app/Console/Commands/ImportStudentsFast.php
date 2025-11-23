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
    protected $description = 'Importación de alta velocidad usando Bulk Inserts (SQL Directo).';

    public function handle()
    {
        $fileName = $this->argument('file');
        
        if (!file_exists($fileName)) {
            $this->error("El archivo '$fileName' no existe en la raíz del proyecto.");
            return;
        }

        $this->info('Iniciando importación optimizada...');
        $startTime = microtime(true);

        ini_set('memory_limit', '1024M');
        set_time_limit(0);

        $role = DB::table('roles')->where('name', 'Estudiante')->first();
        if (!$role) {
            $this->error('El rol "Estudiante" no existe.');
            return;
        }
        $roleId = $role->id;

        $handle = fopen($fileName, 'r');
        $headers = $this->getCleanHeaders(fgets($handle));
        
        $batchSize = 1000; 
        $usersBatch = [];
        $studentsBatch = [];
        $rolesBatch = [];
        
        $count = 0;
        
        $this->info('Procesando archivo... (Esto será rápido)');
        $bar = $this->output->createProgressBar($this->countRows($fileName));
        $bar->start();

        while (($line = fgets($handle)) !== false) {
            $row = str_getcsv(trim($line), ';');
            
            if (count($row) !== count($headers)) continue;
            
            $data = array_combine($headers, $row);
            $matricula = trim($data['Matricula'] ?? '');
            $cedula = trim($data['Cedula'] ?? '');
            
            if (empty($matricula)) continue;

            $email = $this->cleanEmail($data['Correo'] ?? '', $matricula);
            $nombre = Str::title(Str::lower($data['Nombre'] ?? ''));
            $apellido = Str::title(Str::lower($data['Apellido'] ?? ''));
            $fullName = trim("$nombre $apellido");
            $password = $cedula && $cedula !== 'ND' ? Hash::make(str_replace(['-',' '], '', $cedula)) : Hash::make($matricula);
            $now = now();

            // 1. Datos de Usuario
            $usersBatch[$email] = [
                'name' => $fullName,
                'email' => $email,
                'password' => $password,
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // 2. Datos de Estudiante
            $studentsBatch[$email] = [
                'student_code' => $matricula,
                'first_name' => $nombre,
                'last_name' => $apellido,
                'cedula' => $cedula,
                'email' => $email,
                'address' => $this->cleanText($data['Direccion'] ?? ''),
                'birth_date' => $this->parseDate($data['Fecha Nacimiento'] ?? ''),
                
                // --- CORRECCIÓN: Mapeo de Teléfonos ---
                'phone' => $this->cleanPhone($data['Telefono'] ?? null),
                'mobile' => $this->cleanPhone($data['Celular'] ?? null),
                // --------------------------------------

                'gender' => $this->cleanText($data['Sexo'] ?? ''),
                'nationality' => $this->cleanText($data['Nacionalidad'] ?? ''),
                'status' => 'Activa',
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $count++;

            if ($count >= $batchSize) {
                $this->processBatch($usersBatch, $studentsBatch, $rolesBatch, $roleId);
                $usersBatch = [];
                $studentsBatch = [];
                $rolesBatch = [];
                $count = 0;
                $bar->advance($batchSize);
            }
        }

        if ($count > 0) {
            $this->processBatch($usersBatch, $studentsBatch, $rolesBatch, $roleId);
            $bar->advance($count);
        }

        $bar->finish();
        fclose($handle);

        $duration = round(microtime(true) - $startTime, 2);
        $this->newLine();
        $this->info("¡Importación finalizada en $duration segundos!");
    }

    private function processBatch($usersData, $studentsData, &$rolesBatch, $roleId)
    {
        DB::table('users')->insertOrIgnore(array_values($usersData));

        $emails = array_keys($usersData);
        $usersMap = DB::table('users')->whereIn('email', $emails)->pluck('id', 'email'); 

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

        DB::table('students')->insertOrIgnore($finalStudents);
        DB::table('model_has_roles')->insertOrIgnore($finalRoles);
    }

    private function getCleanHeaders($line)
    {
        $d = (strpos($line, ';') !== false) ? ';' : ',';
        $h = str_getcsv(trim($line), $d);
        return array_map(function($v) { return trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $v)); }, $h);
    }

    private function cleanEmail($email, $matricula)
    {
        $email = trim($email);
        if (empty($email) || strtoupper($email) === 'ND' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return strtolower($matricula) . '@sga.local';
        }
        return $email;
    }

    private function cleanText($text)
    {
        $text = trim($text);
        return (strtoupper($text) === 'ND' || $text === '-       -') ? null : Str::title(Str::lower($text));
    }

    private function cleanPhone($text)
    {
        $text = trim($text);
        if (strtoupper($text) === 'ND' || $text === '-       -') return null;
        return $text;
    }

    private function parseDate($date)
    {
        if (empty($date)) return null;
        try {
            return Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');
        } catch (\Exception $e) { return null; }
    }

    private function countRows($file)
    {
        $lineCount = 0;
        $handle = fopen($file, "r");
        while(!feof($handle)){
            $line = fgets($handle);
            if($line !== false) $lineCount++;
        }
        fclose($handle);
        return $lineCount;
    }
}