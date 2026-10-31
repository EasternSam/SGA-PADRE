<?php

namespace App\Http\Controllers;

use App\Models\CertificateTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminCertificateController extends Controller
{
    /**
     * List certificate templates.
     */
    public function index(): Response
    {
        // Seed initial default template if database is empty
        if (CertificateTemplate::count() === 0) {
            CertificateTemplate::create([
                'name' => 'Título de Graduación Institucional',
                'is_active' => true,
                'layout_data' => [
                    'orientation' => 'landscape',
                    'title' => 'REPÚBLICA DOMINICANA',
                    'subtitle' => 'INSTITUTO TECNOLÓGICO DE LAS AMÉRICAS',
                    'headline' => 'CONFIERE EL PRESENTE TÍTULO A',
                    'student_placeholder' => '{student_name}',
                    'body' => 'Por haber completado satisfactoriamente los requisitos del programa académico correspondiente a la carrera técnica de:',
                    'course_placeholder' => '{course_name}',
                    'date_text' => 'Dado en Santo Domingo Este, a los {day} días del mes de {month} del año {year}.',
                    'signatures' => [
                        ['name' => 'Rector / Director General', 'role' => 'Firma y Sello Oficial'],
                        ['name' => 'Director de Registro Académico', 'role' => 'Certificación Académica'],
                    ],
                ]
            ]);

            CertificateTemplate::create([
                'name' => 'Certificado de Aprobación & Participación',
                'is_active' => true,
                'layout_data' => [
                    'orientation' => 'landscape',
                    'title' => 'CERTIFICADO DE APROBACIÓN',
                    'subtitle' => 'DIRECCIÓN DE EDUCACIÓN CONTINUA',
                    'headline' => 'Otorgado formalmente a:',
                    'student_placeholder' => '{student_name}',
                    'body' => 'Por su destacada participación y culminación con éxito del curso intensivo de:',
                    'course_placeholder' => '{course_name}',
                    'date_text' => 'Emitido el {issue_date}.',
                    'signatures' => [
                        ['name' => 'Coordinador Académico', 'role' => 'Firma Autorizada'],
                    ],
                ]
            ]);
        }

        $templates = CertificateTemplate::latest()->get()->map(fn($t) => [
            'id' => $t->id,
            'name' => $t->name,
            'is_active' => (bool)$t->is_active,
            'updated_at' => $t->updated_at?->format('d/m/Y H:i'),
        ]);

        return Inertia::render('Admin/Certificates/Index', [
            'templates' => $templates,
        ]);
    }

    /**
     * Visual drag & drop canvas editor.
     */
    public function editor(?int $templateId = null): Response
    {
        $template = null;
        if ($templateId) {
            $template = CertificateTemplate::find($templateId);
        }

        if (!$template) {
            $template = CertificateTemplate::first();
        }

        return Inertia::render('Admin/Certificates/Editor', [
            'template' => $template ? [
                'id' => $template->id,
                'name' => $template->name,
                'is_active' => (bool)$template->is_active,
                'layout_data' => $template->layout_data ?? [
                    'orientation' => 'landscape',
                    'title' => 'INSTITUTO TECNOLÓGICO DE LAS AMÉRICAS',
                    'subtitle' => 'CERTIFICACIÓN ACADÉMICA',
                    'headline' => 'CONFIERE EL PRESENTE CERTIFICADO A:',
                    'student_placeholder' => '{student_name}',
                    'body' => 'Por haber cumplido con todos los requisitos curriculares de la carrera de:',
                    'course_placeholder' => '{course_name}',
                    'date_text' => 'Dado en Santo Domingo, República Dominicana.',
                    'signatures' => [
                        ['name' => 'Rector', 'role' => 'Firma Oficial'],
                        ['name' => 'Director de Registro', 'role' => 'Certificación'],
                    ],
                ],
            ] : null,
        ]);
    }

    /**
     * Save template from editor.
     */
    public function saveTemplate(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
            'name' => 'required|string|max:255',
            'is_active' => 'required|boolean',
            'layout_data' => 'required|array',
        ]);

        $template = CertificateTemplate::updateOrCreate(
            ['id' => $validated['id'] ?? null],
            [
                'name' => $validated['name'],
                'is_active' => $validated['is_active'],
                'layout_data' => $validated['layout_data'],
            ]
        );

        return redirect()->route('admin.certificates.index')->with('success', "Plantilla '{$template->name}' guardada exitosamente.");
    }

    /**
     * Delete template.
     */
    public function destroy(CertificateTemplate $template)
    {
        $name = $template->name;
        $template->delete();
        return back()->with('success', "Plantilla '{$name}' eliminada.");
    }
}
