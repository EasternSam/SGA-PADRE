<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinancialPdfController extends Controller
{
    /**
     * Genera el reporte financiero.
     * Puede funcionar en dos modos:
     * 1. Reporte General (Admin): Requiere filtros de fecha.
     * 2. Estado de Cuenta Individual (Estudiante): Se basa en el ID pasado por URL.
     */
    public function download(Request $request, $studentId = null)
    {
        // === MODO 1: ESTADO DE CUENTA INDIVIDUAL (Estudiante) ===
        if ($studentId) {
            // Filtros por defecto para el estudiante (todo el historial o año actual)
            $dateFrom = $request->input('date_from', '2000-01-01'); // Desde el inicio
            $dateTo = $request->input('date_to', Carbon::now()->endOfYear()->toDateString());
            
            // Forzar el filtro por estudiante
            $specificStudentId = $studentId;
            
            // Opcional: Validar que el usuario logueado sea el estudiante o un admin
            if (auth()->user()->hasRole('Estudiante')) {
                 $loggedStudent = auth()->user()->student;
                 if (!$loggedStudent || $loggedStudent->id != $studentId) {
                     abort(403, 'No autorizado para ver este reporte.');
                 }
            }
        } 
        // === MODO 2: REPORTE GENERAL (Admin) ===
        else {
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');
            $specificStudentId = null;

            // Validar fechas solo si es reporte general
            if (!$dateFrom || !$dateTo) {
                abort(400, 'Las fechas de inicio y fin son obligatorias para el reporte general.');
            }
        }

        $courseId = $request->input('course_id');
        $teacherId = $request->input('teacher_id');
        $paymentStatus = $request->input('status', 'all');

        // --- CONSTRUCCIÓN DE LA CONSULTA ---
        
        $query = DB::table('enrollments')
            ->join('students', 'enrollments.student_id', '=', 'students.id')
            ->join('course_schedules', 'enrollments.course_schedule_id', '=', 'course_schedules.id')
            ->join('modules', 'course_schedules.module_id', '=', 'modules.id')
            ->join('courses', 'modules.course_id', '=', 'courses.id')
            ->leftJoin('payments', 'enrollments.id', '=', 'payments.enrollment_id')
            ->select(
                'enrollments.id as enrollment_id',
                'students.first_name',
                'students.last_name',
                'students.phone as student_phone',
                'students.mobile_phone',
                'students.student_code', // Importante para mostrar matrícula
                'courses.name as course_name',
                'courses.registration_fee', // Precio inscripción
                'courses.monthly_fee',      // Mensualidad
                'modules.name as module_name',
                'modules.price as module_price', // Precio extra módulo (si aplica)
                'course_schedules.section_name',
                'enrollments.status as enrollment_status',
                'enrollments.created_at as enrollment_date',
                
                // Sumar pagos aprobados
                DB::raw("SUM(CASE WHEN LOWER(payments.status) IN ('paid', 'pagado', 'completado', 'succeeded', 'aprobado', 'completado') THEN payments.amount ELSE 0 END) as total_paid"),
                // Sumar deuda original (esto es un aproximado, idealmente se usa payment->amount si existe)
                DB::raw("MAX(payments.amount) as payment_amount_ref") 
            );

        // Filtros
        if ($specificStudentId) {
            $query->where('students.id', $specificStudentId);
        }
        
        if ($courseId) {
            $query->where('courses.id', $courseId);
        }
        if ($teacherId) {
            $query->where('course_schedules.teacher_id', $teacherId);
        }
        
        // Filtro de fecha (aplicar siempre, pero con rangos amplios para estudiantes)
        $query->whereBetween('enrollments.created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        // Agrupación
        $query->groupBy(
            'enrollments.id', 'students.id', 'students.first_name', 'students.last_name', 
            'students.phone', 'students.mobile_phone', 'students.student_code',
            'courses.name', 'courses.registration_fee', 'courses.monthly_fee',
            'modules.name', 'modules.price', 'course_schedules.section_name', 
            'enrollments.status', 'enrollments.created_at'
        );

        $financials = $query->get();

        // Procesamiento y Cálculo de Deuda Real
        $financials = $financials->map(function ($item) {
            $paid = (float) $item->total_paid;
            
            // Lógica de costo:
            // Si hay un pago registrado (incluso pendiente), ese es el monto real de la deuda.
            // Si no, asumimos inscripción (registration_fee) para el primer módulo/pago.
            // Esta lógica puede refinarse según tu modelo de negocio exacto.
            
            $cost = (float) $item->payment_amount_ref; 
            
            if ($cost == 0) {
                 // Fallback si no hay pago generado aún: Precio Inscripción + Precio Módulo
                 $cost = (float)$item->registration_fee + (float)$item->module_price;
            }

            $item->total_cost = $cost;
            $item->balance = max(0, $cost - $paid);
            
            return $item;
        });

        // Filtrado por estado de pago en memoria
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
            'is_individual' => (bool)$studentId // Bandera para la vista PDF
        ];

        // Generar PDF
        $pdf = Pdf::loadView('reports.financial-report-pdf', ['data' => $data]);
        
        // Si es individual, vertical es mejor. Si es general, horizontal.
        if ($studentId) {
             $pdf->setPaper('a4', 'portrait');
        } else {
             $pdf->setPaper('a4', 'landscape');
        }

        return $pdf->stream('Reporte_Financiero.pdf');
    }
}