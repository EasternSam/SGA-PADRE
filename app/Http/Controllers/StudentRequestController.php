<?php

namespace App\Http\Controllers;

use App\Mail\PaymentReceiptMail;
use App\Mail\RequestApprovedMail;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\PaymentConcept;
use App\Models\RequestType;
use App\Models\Student;
use App\Models\StudentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class StudentRequestController extends Controller
{
    /**
     * Muestra el panel de gestión de solicitudes y trámites institucionales.
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $status = $request->input('status', 'all');

        $query = StudentRequest::with(['student.user', 'course', 'payment.paymentConcept', 'requestType']);

        if (!empty($search)) {
            $query->whereHas('student.user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhereHas('student', function ($q) use ($search) {
                $q->where('student_code', 'like', "%{$search}%")
                  ->orWhere('cedula', 'like', "%{$search}%");
            });
        }

        if ($status !== 'all' && !empty($status)) {
            $query->where('status', $status);
        }

        $requests = $query->orderByDesc('created_at')->paginate(12)->withQueryString();

        $stats = [
            'total' => StudentRequest::count(),
            'pending' => StudentRequest::where('status', 'pendiente')->count(),
            'approved' => StudentRequest::where('status', 'aprobado')->count(),
            'rejected' => StudentRequest::where('status', 'rechazado')->count(),
        ];

        $requestTypes = RequestType::orderBy('name')->get();
        $courses = Course::orderBy('name')->get(['id', 'name', 'code']);

        return Inertia::render('Requests/Index', [
            'requests' => $requests,
            'requestTypes' => $requestTypes,
            'courses' => $courses,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
            'stats' => $stats,
        ]);
    }

    /**
     * Actualiza el estado y observaciones de una solicitud estudiantil.
     */
    public function updateStatus(Request $request, StudentRequest $studentRequest)
    {
        $validated = $request->validate([
            'status' => 'required|in:pendiente,en_proceso,aprobado,rechazado',
            'admin_notes' => 'nullable|string',
        ]);

        $oldStatus = $studentRequest->status;
        $newStatus = $validated['status'];

        try {
            DB::beginTransaction();

            $studentRequest->update([
                'status' => $newStatus,
                'admin_notes' => $validated['admin_notes'] ?? $studentRequest->admin_notes,
            ]);

            // Si se aprueba y el tipo de solicitud requiere cobro
            if ($newStatus === 'aprobado' && $oldStatus !== 'aprobado') {
                $type = $studentRequest->requestType;

                if ($type && $type->requires_payment && !$studentRequest->payment_id) {
                    $concept = PaymentConcept::firstOrCreate(
                        ['name' => $type->name],
                        [
                            'description' => 'Pago por trámite institucional: ' . $type->name,
                            'amount' => $type->payment_amount,
                        ]
                    );

                    $payment = Payment::create([
                        'student_id' => $studentRequest->student_id,
                        'payment_concept_id' => $concept->id,
                        'amount' => $type->payment_amount,
                        'currency' => 'DOP',
                        'status' => 'Pendiente',
                        'gateway' => 'Sistema',
                        'due_date' => now()->addDays(7),
                        'description' => $type->name . ($studentRequest->course ? ' - ' . $studentRequest->course->name : ''),
                    ]);

                    $studentRequest->payment_id = $payment->id;
                    $studentRequest->save();

                    // Notificar deuda por correo
                    if ($studentRequest->student && $studentRequest->student->email) {
                        try {
                            Mail::to($studentRequest->student->email)->send(new PaymentReceiptMail($payment, ''));
                        } catch (\Exception $e) {}
                    }
                }

                // Enviar correo de aprobación
                if ($studentRequest->student && $studentRequest->student->email) {
                    try {
                        Mail::to($studentRequest->student->email)->send(new RequestApprovedMail($studentRequest));
                    } catch (\Exception $e) {
                        Log::error('Error enviando correo de aprobación: ' . $e->getMessage());
                    }
                }
            }

            DB::commit();

            return redirect()->back()->with('success', 'Solicitud actualizada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar solicitud: ' . $e->getMessage());
            return redirect()->back()->withErrors(['general' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Registra manualmente una solicitud para un estudiante.
     */
    public function storeRequest(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'request_type_id' => 'required|exists:request_types,id',
            'course_id' => 'nullable|exists:courses,id',
            'details' => 'required|string',
        ], [
            'student_id.required' => 'Debe seleccionar un estudiante.',
            'request_type_id.required' => 'Debe seleccionar un tipo de trámite.',
            'details.required' => 'El detalle o justificación de la solicitud es obligatorio.',
        ]);

        $type = RequestType::find($validated['request_type_id']);

        if (($type->requires_enrolled_course || $type->requires_completed_course) && empty($validated['course_id'])) {
            return redirect()->back()->withErrors([
                'course_id' => 'Este tipo de solicitud requiere seleccionar la materia o carrera vinculada.',
            ]);
        }

        StudentRequest::create([
            'student_id' => $validated['student_id'],
            'request_type_id' => $validated['request_type_id'],
            'type' => $type->name,
            'course_id' => $validated['course_id'] ?: null,
            'details' => $validated['details'],
            'status' => 'pendiente',
        ]);

        return redirect()->back()->with('success', 'Trámite creado exitosamente.');
    }

    /**
     * Crea un nuevo tipo de solicitud / trámite institucional.
     */
    public function storeType(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'requires_payment' => 'boolean',
            'payment_amount' => 'required_if:requires_payment,true|numeric|min:0',
            'requires_enrolled_course' => 'boolean',
            'requires_completed_course' => 'boolean',
            'is_active' => 'boolean',
        ]);

        RequestType::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'requires_payment' => $request->boolean('requires_payment'),
            'payment_amount' => $request->boolean('requires_payment') ? ($validated['payment_amount'] ?? 0) : 0,
            'requires_enrolled_course' => $request->boolean('requires_enrolled_course'),
            'requires_completed_course' => $request->boolean('requires_completed_course'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->back()->with('success', 'Tipo de trámite creado exitosamente.');
    }

    /**
     * Actualiza un tipo de solicitud institucional.
     */
    public function updateType(Request $request, RequestType $requestType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'requires_payment' => 'boolean',
            'payment_amount' => 'required_if:requires_payment,true|numeric|min:0',
            'requires_enrolled_course' => 'boolean',
            'requires_completed_course' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $requestType->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'requires_payment' => $request->boolean('requires_payment'),
            'payment_amount' => $request->boolean('requires_payment') ? ($validated['payment_amount'] ?? 0) : 0,
            'requires_enrolled_course' => $request->boolean('requires_enrolled_course'),
            'requires_completed_course' => $request->boolean('requires_completed_course'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->back()->with('success', 'Tipo de trámite actualizado.');
    }

    /**
     * Elimina un tipo de trámite.
     */
    public function destroyType(RequestType $requestType)
    {
        $requestType->delete();
        return redirect()->back()->with('success', 'Tipo de trámite eliminado.');
    }

    /**
     * Búsqueda rápida de estudiantes para el autocompletado en creación manual.
     */
    public function searchStudents(Request $request)
    {
        $q = trim($request->input('q', ''));
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $students = Student::with('user')
            ->where(function ($query) use ($q) {
                $query->where('student_code', 'like', "%{$q}%")
                      ->orWhere('cedula', 'like', "%{$q}%")
                      ->orWhereHas('user', function ($uq) use ($q) {
                          $uq->where('name', 'like', "%{$q}%")
                             ->orWhere('email', 'like', "%{$q}%");
                      });
            })
            ->take(10)
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'name' => $s->user?->name ?? "{$s->first_name} {$s->last_name}",
                    'email' => $s->email,
                    'code' => $s->student_code,
                    'cedula' => $s->cedula,
                ];
            });

        return response()->json($students);
    }
}
