<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ApplicantPortalController extends Controller
{
    private const STORAGE_FOLDERS = [
        'birth_certificate' => 'admissions/birth_certificates',
        'id_card' => 'admissions/id_cards',
        'high_school_record' => 'admissions/records',
        'photo' => 'admissions/photos',
    ];

    private const ALLOWED_EXTENSIONS = [
        'pdf', 'jpg', 'jpeg', 'png', 'webp', 'heic', 'heif', 'gif', 'bmp', 'doc', 'docx',
    ];

    /**
     * Store an uploaded admission document with clean extension validation.
     */
    private function storeUploadedDocument(UploadedFile $file, string $key): string
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'pdf');
        if (!in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            throw ValidationException::withMessages([
                $key => 'Formato de archivo no permitido. Usa PDF, JPG, PNG, WEBP o HEIC (máx. 25 MB).',
            ]);
        }

        $folder = self::STORAGE_FOLDERS[$key];
        $filename = Str::uuid()->toString() . '.' . $ext;

        return $file->storeAs($folder, $filename, 'local');
    }

    /**
     * Resolve the active admission record for the authenticated user.
     */
    private function resolveAdmission($user): ?Admission
    {
        $admission = Admission::where(function ($q) use ($user) {
            $q->where('user_id', $user->id);
            if (!empty($user->email)) {
                $q->orWhere('email', $user->email);
            }
        })
            ->with('course')
            ->latest()
            ->first();

        if (!$admission && ($user->hasRole('Admin') || $user->hasRole('SuperAdmin'))) {
            $admission = Admission::with('course')->latest()->first();
        }

        return $admission;
    }

    /**
     * Display applicant portal with status & document upload form.
     */
    public function index(): Response
    {
        $user = auth()->user();
        $admission = $this->resolveAdmission($user);

        $courses = Course::where('program_type', 'degree')
            ->where('status', 'Activo')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'monthly_fee', 'registration_fee', 'duration_periods'])
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'code' => $c->code,
                'price' => (float) ($c->monthly_fee ?: $c->registration_fee ?: 0),
                'duration_hours' => $c->duration_periods ? ($c->duration_periods . ' Períodos') : '2 Años',
            ]);

        return Inertia::render('Applicant/Portal', [
            'applicant' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
            'admission' => $admission ? [
                'id' => $admission->id,
                'first_name' => $admission->first_name,
                'last_name' => $admission->last_name,
                'identification_id' => $admission->identification_id,
                'phone' => $admission->phone,
                'birth_date' => $admission->birth_date?->format('Y-m-d'),
                'address' => $admission->address,
                'course' => $admission->course ? [
                    'id' => $admission->course->id,
                    'name' => $admission->course->name,
                    'code' => $admission->course->code,
                ] : null,
                'previous_school' => $admission->previous_school,
                'previous_gpa' => $admission->previous_gpa,
                'status' => $admission->status,
                'notes' => $admission->notes,
                'documents' => $admission->documents ?? [],
                'document_status' => $admission->document_status ?? [
                    'birth_certificate' => 'pending',
                    'id_card' => 'pending',
                    'high_school_record' => 'pending',
                    'photo' => 'pending',
                ],
                'created_at' => $admission->created_at?->format('d/m/Y H:i'),
            ] : null,
            'courses' => $courses,
        ]);
    }

    /**
     * Submit or update admission application and any attached documents.
     */
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'identification_id' => 'required|string|max:20',
            'phone' => 'required|string|max:20',
            'birth_date' => 'required|date',
            'address' => 'required|string|max:500',
            'course_id' => 'required|exists:courses,id',
            'previous_school' => 'required|string|max:255',
            'previous_gpa' => 'nullable|numeric|between:0,100',
            'birth_certificate' => 'nullable|file|max:25600',
            'id_card' => 'nullable|file|max:25600',
            'high_school_record' => 'nullable|file|max:25600',
            'photo' => 'nullable|file|max:25600',
        ]);

        $user = auth()->user();
        $existingAdmission = $this->resolveAdmission($user);

        $documents = is_array($existingAdmission?->documents) ? $existingAdmission->documents : [];
        $docStatus = is_array($existingAdmission?->document_status) ? $existingAdmission->document_status : [
            'birth_certificate' => 'pending',
            'id_card' => 'pending',
            'high_school_record' => 'pending',
            'photo' => 'pending',
        ];

        foreach (array_keys(self::STORAGE_FOLDERS) as $key) {
            if ($request->hasFile($key) && $request->file($key)->isValid()) {
                $documents[$key] = $this->storeUploadedDocument($request->file($key), $key);
                $docStatus[$key] = 'pending';
            }
        }

        $payload = collect($validated)->except(array_keys(self::STORAGE_FOLDERS))->toArray();

        if ($existingAdmission) {
            $existingAdmission->update(array_merge($payload, [
                'user_id' => $existingAdmission->user_id ?: $user->id,
                'email' => $existingAdmission->email ?: $user->email,
                'status' => $existingAdmission->status === 'approved' ? 'approved' : 'pending',
                'documents' => $documents,
                'document_status' => $docStatus,
            ]));
            $admission = $existingAdmission->fresh('course');
        } else {
            $admission = Admission::create(array_merge($payload, [
                'user_id' => $user->id,
                'email' => $user->email,
                'status' => 'pending',
                'documents' => $documents,
                'document_status' => $docStatus,
            ]))->load('course');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Solicitud de admisión y documentos registrados exitosamente.',
                'admission_id' => $admission->id,
                'documents' => $admission->documents ?? [],
                'document_status' => $admission->document_status ?? [],
            ]);
        }

        return back()->with('success', 'Solicitud de admisión y documentos registrados exitosamente.');
    }

    /**
     * Direct single-document upload or replacement from the status checklist or form.
     */
    public function uploadDocument(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|in:birth_certificate,id_card,high_school_record,photo',
            'file' => 'required|file|max:25600',
        ]);

        $user = auth()->user();
        $admission = $this->resolveAdmission($user);

        if (!$admission) {
            $nameParts = preg_split('/\s+/', trim((string) $user->name), 2);
            $firstName = $request->input('first_name') ?: ($nameParts[0] ?? 'Aspirante');
            $lastName = $request->input('last_name') ?: ($nameParts[1] ?? 'Pendiente');
            $courseId = $request->input('course_id')
                ?: Course::where('program_type', 'degree')->where('status', 'Activo')->value('id')
                ?: Course::value('id');

            if (!$courseId) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'ok' => false,
                        'message' => 'No hay programas académicos activos para asociar la solicitud.',
                    ], 422);
                }
                return back()->withErrors([
                    'file' => 'No hay programas académicos activos para asociar la solicitud.',
                ]);
            }

            $admission = Admission::create([
                'user_id' => $user->id,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $user->email,
                'course_id' => $courseId,
                'status' => 'pending',
                'documents' => [],
                'document_status' => [
                    'birth_certificate' => 'pending',
                    'id_card' => 'pending',
                    'high_school_record' => 'pending',
                    'photo' => 'pending',
                ],
            ]);
        }

        $key = $validated['key'];
        $documents = is_array($admission->documents) ? $admission->documents : [];
        $docStatus = is_array($admission->document_status) ? $admission->document_status : [
            'birth_certificate' => 'pending',
            'id_card' => 'pending',
            'high_school_record' => 'pending',
            'photo' => 'pending',
        ];

        $documents[$key] = $this->storeUploadedDocument($request->file('file'), $key);
        $docStatus[$key] = 'pending';

        $admission->update([
            'documents' => $documents,
            'document_status' => $docStatus,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Documento depositado correctamente.',
                'admission_id' => $admission->id,
                'documents' => $admission->documents,
                'document_status' => $admission->document_status,
            ]);
        }

        return back()->with('success', 'Documento depositado correctamente.');
    }
}
