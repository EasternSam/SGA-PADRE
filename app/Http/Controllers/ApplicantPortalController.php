<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ApplicantPortalController extends Controller
{
    /**
     * Display applicant portal with status & document upload form.
     */
    public function index(): Response
    {
        $user = auth()->user();
        $admission = Admission::where('user_id', $user->id)->with('course')->latest()->first();

        // If no admission for current user, fallback to latest admission for Admin preview
        if (!$admission && ($user->hasRole('Admin') || $user->hasRole('SuperAdmin'))) {
            $admission = Admission::with('course')->first();
        }

        $courses = Course::where('program_type', 'degree')
            ->where('status', 'Activo')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'monthly_fee', 'registration_fee', 'duration_periods'])
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'code' => $c->code,
                'price' => (float)($c->monthly_fee ?: $c->registration_fee ?: 0),
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
     * Submit or update admission application.
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
        ]);

        $user = auth()->user();

        $admission = Admission::updateOrCreate(
            ['user_id' => $user->id],
            array_merge($validated, [
                'email' => $user->email,
                'status' => 'pending',
                'document_status' => [
                    'birth_certificate' => 'approved',
                    'id_card' => 'approved',
                    'high_school_record' => 'approved',
                    'photo' => 'approved',
                ]
            ])
        );

        return back()->with('success', 'Solicitud de admisión registrada exitosamente. Tus documentos están en proceso de validación.');
    }
}
