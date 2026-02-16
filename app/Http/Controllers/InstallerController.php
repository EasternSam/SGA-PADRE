<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use PDO;

class InstallerController extends Controller
{
    /**
     * Muestra la vista del instalador.
     */
    public function index()
    {
        // Si ya está instalado, proteger la ruta bloqueando el acceso al instalador
        if (env('APP_INSTALLED', false)) {
            return redirect('/')->with('error', 'El sistema ya se encuentra instalado.');
        }

        $isSqlite = config('database.default') === 'sqlite';
        $sqlitePath = database_path('database.sqlite');
        $sqliteReady = $isSqlite && file_exists($sqlitePath);

        return view('installer.step-1', compact('isSqlite', 'sqliteReady'));
    }

    /**
     * Procesa la instalación, valida licencia, edita el .env y migra la BD.
     */
    public function install(Request $request)
    {
        if (env('APP_INSTALLED', false)) {
            return redirect('/');
        }

        // Determinar si estamos en modo SQLite
        $isSqlite = config('database.default') === 'sqlite';

        // Reglas de validación dinámicas
        $rules = [
            'license_key' => 'required|string'
        ];

        // Solo requerir datos de BD si NO es SQLite
        if (!$isSqlite) {
            $rules = array_merge($rules, [
                'db_host'     => 'required|string',
                'db_port'     => 'required|numeric',
                'db_name'     => 'required|string',
                'db_user'     => 'required|string',
            ]);
        }

        $request->validate($rules);

        // 1. Probar conexión a la Base de Datos local
        if (!$isSqlite) {
            try {
                $dsn = "mysql:host={$request->db_host};port={$request->db_port};dbname={$request->db_name}";
                // Intentamos conectar con PDO para verificar credenciales antes de tocar Laravel
                $pdo = new PDO($dsn, $request->db_user, $request->db_password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            } catch (\Exception $e) {
                return back()->with('error', 'Error conectando a la base de datos local. Verifique credenciales: ' . $e->getMessage())->withInput();
            }
        } else {
            // Verificar conexión SQLite
            try {
                if (!file_exists(database_path('database.sqlite'))) {
                    throw new \Exception("El archivo de base de datos SQLite no existe.");
                }
                // Probar conexión rápida
                DB::connection()->getPdo();
            } catch (\Exception $e) {
                return back()->with('error', 'Error conectando a SQLite: ' . $e->getMessage())->withInput();
            }
        }

        // 2. Validar Licencia con tu Servidor Maestro
        $domain = $request->getHost();
        
        // Limpiamos la URL por si quedó con un slash al final en el .env (ej: agency/)
        $masterUrl = rtrim(env('SAAS_MASTER_URL', 'https://gestion.90s.agency'), '/'); 
        
        try {
            // Se agregó withoutVerifying() para evitar el bloqueo por certificados SSL recientes en cPanel
            $response = Http::withoutVerifying()
                ->timeout(15)
                ->post("{$masterUrl}/api/v1/validate-license", [
                    'license_key' => $request->license_key,
                    'domain'      => $domain,
                ]);

            if (!$response->successful() || $response->json('status') !== 'success') {
                $msg = $response->json('message');
                
                // Si el maestro no devuelve JSON (ej. si da un error 404 o 500 HTML)
                if (!$msg) {
                    $msg = "El maestro no encontró la ruta o devolvió un error (HTTP {$response->status()}). Verifica las rutas del maestro.";
                }

                return back()->with('error', 'Validación fallida en servidor central: ' . $msg)->withInput();
            }
        } catch (\Exception $e) {
            // Ahora mostramos el error exacto que arroja el servidor/cURL para saber qué está pasando realmente
            return back()->with('error', 'Error de red al contactar al Maestro (' . $masterUrl . '): ' . $e->getMessage())->withInput();
        }

        // 3. Guardar en el archivo .env (Solo si es MySQL)
        if (!$isSqlite) {
            $this->setEnvVariable('DB_HOST', $request->db_host);
            $this->setEnvVariable('DB_PORT', $request->db_port);
            $this->setEnvVariable('DB_DATABASE', $request->db_name);
            $this->setEnvVariable('DB_USERNAME', $request->db_user);
            $this->setEnvVariable('DB_PASSWORD', $request->db_password ?? '');
        }

        // Guardar licencia y estado de instalación
        $this->setEnvVariable('APP_LICENSE_KEY', $request->license_key); // Ojo con el nombre de la variable, debe coincidir con LicenseService
        $this->setEnvVariable('LICENSE_KEY', $request->license_key); // Guardar en ambos por compatibilidad
        $this->setEnvVariable('APP_INSTALLED', 'true');
        $this->setEnvVariable('APP_URL', 'https://' . $domain);

        // 4. Limpiar caché y ejecutar migraciones de SGA-PADRE
        try {
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            
            // Usamos force para forzar migración en producción y seed para cargar tus roles/opciones
            Artisan::call('migrate:fresh', ['--force' => true, '--seed' => true]);
            
            // Crear archivo flag de instalación por si acaso
            file_put_contents(storage_path('installed'), 'INSTALLED ON ' . date('Y-m-d H:i:s'));
            
        } catch (\Exception $e) {
            return back()->with('error', 'Error al instalar la base de datos del SGA: ' . $e->getMessage());
        }

        return redirect('/')->with('success', '¡SGA-PADRE instalado exitosamente! La licencia ha sido vinculada a este dominio.');
    }

    /**
     * Utilidad para modificar líneas específicas del archivo .env de Laravel.
     */
    private function setEnvVariable($key, $value)
    {
        $path = base_path('.env');
        if (file_exists($path)) {
            // Escapar valores si tienen espacios
            if (str_contains($value, ' ')) {
                $value = '"' . $value . '"';
            }
            
            $oldEnv = file_get_contents($path);
            
            if (preg_match("/^{$key}=.*/m", $oldEnv)) {
                // Reemplazar existente
                $newEnv = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $oldEnv);
            } else {
                // Agregar al final si no existe
                $newEnv = rtrim($oldEnv) . "\n{$key}={$value}\n";
            }
            file_put_contents($path, $newEnv);
        }
    }
}