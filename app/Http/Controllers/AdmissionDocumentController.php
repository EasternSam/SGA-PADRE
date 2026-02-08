<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AdmissionDocumentController extends Controller
{
    /**
     * Sirve un documento de admisión de forma segura.
     * @param Admission $admission La solicitud de admisión.
     * @param string $key La clave del documento (ej. 'birth_certificate', 'id_card').
     */
    public function show(Admission $admission, $key)
    {
        $user = Auth::user();

        // 1. SEGURIDAD: Verificar Permisos
        $isOwner = $user->id === $admission->user_id;
        $isAdmin = $user->hasAnyRole(['Admin', 'Registro', 'Dirección']);

        if (!$isOwner && !$isAdmin) {
            abort(403, 'No tienes permiso para ver este documento.');
        }

        // 2. Verificar que el documento existe en el registro
        $documents = $admission->documents;
        if (!isset($documents[$key]) || empty($documents[$key])) {
            abort(404, 'Documento no encontrado en la solicitud.');
        }

        $path = $documents[$key];

        // 3. Verificar existencia física en disco PRIVADO ('local')
        if (!Storage::disk('local')->exists($path)) {
            // Intentar fallback a 'public' por si son archivos viejos antes de la migración
            if (Storage::disk('public')->exists($path)) {
                return Storage::disk('public')->response($path);
            }
            abort(404, 'El archivo físico no se encuentra.');
        }

        // 4. Servir el archivo de forma segura
        return Storage::disk('local')->response($path);
    }
}