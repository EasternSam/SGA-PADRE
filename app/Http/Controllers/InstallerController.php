<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
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

        return view('installer.step-1');
    }

    /**
     * Procesa la instalación, valida licencia, edita el .env y migra la BD.
     */
    public function install(Request $request)
    {
        if (env('APP_INSTALLED', false)) {
            return redirect('/');
        }

        $request->validate([
            'db_host'     => 'required|string',
            'db_port'     => 'required|numeric',
            'db_name'     => 'required|string',
            'db_user'     => 'required|string',
            'license_key' => 'required|string'
        ]);

        // 1. Probar conexión a la Base de Datos local
        try {
            $dsn = "mysql:host={$request->db_host};port={$request->db_port};dbname={$request->db_name}";
            // Intentamos conectar con PDO para verificar credenciales antes de tocar Laravel
            $pdo = new PDO($dsn, $request->db_user, $request->db_password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        } catch (\Exception $e) {
            return back()->with('error', 'Error conectando a la base de datos local. Verifique credenciales: ' . $e->getMessage())->withInput();
        }

        // 2. Validar Licencia con tu Servidor Maestro
        $domain = $request->getHost();
        $masterUrl = env('SAAS_MASTER_URL', 'https://tu-panel-central.com'); 
        
        try {
            $response = Http::timeout(10)->post("{$masterUrl}/api/v1/validate-license", [
                'license_key' => $request->license_key,
                'domain'      => $domain,
            ]);

            if (!$response->successful() || $response->json('status') !== 'success') {
                $msg = $response->json('message') ?? 'La licencia es inválida o ya está registrada en otro dominio.';
                return back()->with('error', 'Validación fallida en servidor central: ' . $msg)->withInput();
            }
        } catch (\Exception $e) {
            return back()->with('error', 'No se pudo contactar al servidor central de licencias. Verifique la conexión a internet.')->withInput();
        }

        // 3. Guardar en el archivo .env
        $this->setEnvVariable('DB_HOST', $request->db_host);
        $this->setEnvVariable('DB_PORT', $request->db_port);
        $this->setEnvVariable('DB_DATABASE', $request->db_name);
        $this->setEnvVariable('DB_USERNAME', $request->db_user);
        $this->setEnvVariable('DB_PASSWORD', $request->db_password ?? '');
        $this->setEnvVariable('APP_LICENSE_KEY', $request->license_key);
        $this->setEnvVariable('APP_INSTALLED', 'true');
        $this->setEnvVariable('APP_URL', 'https://' . $domain);

        // 4. Limpiar caché y ejecutar migraciones de SGA-PADRE
        try {
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            
            // Usamos force para forzar migración en producción y seed para cargar tus roles/opciones
            Artisan::call('migrate:fresh', ['--force' => true, '--seed' => true]);
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