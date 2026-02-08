<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Payment;
use App\Models\Student;

class FinancialPdfController extends Controller
{
    public function download(Request $request, $studentId = null)
    {
        // === MODO 1: ESTADO DE CUENTA INDIVIDUAL ===
        if ($studentId) {
            $dateFrom = $request->input('date_from', '2000-01-01');
            $dateTo = $request->input('date_to', Carbon::now()->endOfYear()->toDateString());
            $specificStudentId = $studentId;
            
            // Seguridad: Estudiante solo ve lo suyo
            if (auth()->user()->hasRole('Estudiante')) {
                 $loggedStudent = auth()->user()->student;
                 if (!$loggedStudent || $loggedStudent->id != $studentId) {
                     abort(403, 'No autorizado.');
                 }
            }
        } 
        // === MODO 2: REPORTE GENERAL ===
        else {
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');
            $specificStudentId = null;

            if (!$dateFrom || !$dateTo) {
                abort(400, 'Fechas requeridas.');
            }
        }

        $courseId = $request->input('course_id');
        $teacherId = $request->input('teacher_id');
        $paymentStatus = $request->input('status', 'all');

        // Construcción Query Builder
        $query = DB::table('enrollments')
            ->join('students', 'enrollments.student_id', '=', 'students.id')
            ->join('course_schedules', 'enrollments.course_schedule_id', '=', 'course_schedules.id')
            ->join('modules', 'course_schedules.module_id', '=', 'modules.id')
            ->join('courses', 'modules.course_id', '=', 'courses.id')
            ->leftJoin('payments', 'enrollments.id', '=', 'payments.enrollment_id') // Join simple para deuda directa
            ->select(
                'enrollments.id as enrollment_id',
                'students.first_name',
                'students.last_name',
                'students.student_code',
                'courses.name as course_name',
                'courses.registration_fee', 
                'modules.name as module_name',
                'modules.price as module_price', // Precio base del módulo
                'enrollments.status as enrollment_status',
                'enrollments.created_at as enrollment_date',
                
                // Suma de lo pagado (Estado 'Completado')
                DB::raw("SUM(CASE WHEN payments.status = 'Completado' THEN payments.amount ELSE 0 END) as total_paid"),
                
                // Deuda esperada: Si hay un pago vinculado (aunque sea pendiente), usamos su monto. 
                // Si no, usamos el precio del módulo.
                DB::raw("COALESCE(MAX(payments.amount), modules.price, 0) as expected_cost")
            );

        if ($specificStudentId) $query->where('students.id', $specificStudentId);
        if ($courseId) $query->where('courses.id', $courseId);
        if ($teacherId) $query->where('course_schedules.teacher_id', $teacherId);
        
        $query->whereBetween('enrollments.created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        // Group By masivo para compatibilidad SQL estricto
        $query->groupBy(
            'enrollments.id', 'students.id', 'students.first_name', 'students.last_name', 'students.student_code',
            'courses.name', 'courses.registration_fee', 'modules.name', 'modules.price',
            'enrollments.status', 'enrollments.created_at'
        );

        $financials = $query->get();

        // Calcular Balance
        $financials = $financials->map(function ($item) {
            $cost = (float) $item->expected_cost;
            $paid = (float) $item->total_paid;
            $item->balance = max(0, $cost - $paid);
            return $item;
        });

        // Filtrado en memoria
        if ($paymentStatus !== 'all') {
            $financials = $financials->filter(function($item) use ($paymentStatus) {
                if ($paymentStatus == 'paid') return $item->balance <= 0; 
                if ($paymentStatus == 'pending') return $item->balance > 0; 
                return true;
            });
        }

        $data = [
            'financials' => $financials,
            'filter_status' => $paymentStatus,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'is_individual' => (bool)$studentId
        ];

        $pdf = Pdf::loadView('reports.financial-report-pdf', ['data' => $data]);
        $pdf->setPaper('a4', $studentId ? 'portrait' : 'landscape');

        return $pdf->stream('Reporte_Financiero.pdf');
    }

    public function ticket(Payment $payment)
    {
        $payment->load('student', 'paymentConcept', 'enrollment.courseSchedule.module');
        return view('reports.thermal-invoice', compact('payment'));
    }
}