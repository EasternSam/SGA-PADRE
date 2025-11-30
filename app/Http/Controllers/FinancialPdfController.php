<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinancialPdfController extends Controller
{
    public function download(Request $request)
    {
        // Recibir filtros desde la URL (query parameters)
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $courseId = $request->input('course_id');
        $teacherId = $request->input('teacher_id');
        $paymentStatus = $request->input('status', 'all');

        // Validar fechas básicas
        if (!$dateFrom || !$dateTo) {
            abort(400, 'Las fechas de inicio y fin son obligatorias.');
        }

        // --- LÓGICA DE DATOS (Misma que en Livewire) ---
        
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
                // Intentar obtener celular si phone es null (ajuste robusto)
                'students.mobile_phone',
                'courses.name as course_name',
                'modules.name as module_name',
                'modules.price as module_price', 
                'course_schedules.section_name',
                'enrollments.status as enrollment_status',
                
                DB::raw("SUM(CASE WHEN LOWER(payments.status) IN ('paid', 'pagado', 'completado', 'succeeded', 'aprobado') THEN payments.amount ELSE 0 END) as total_paid"),
                DB::raw("SUM(CASE WHEN LOWER(payments.status) IN ('pending', 'pendiente', 'created') THEN payments.amount ELSE 0 END) as raw_pending_sum")
            );

        // Filtros
        if ($courseId) {
            $query->where('courses.id', $courseId);
        }
        if ($teacherId) {
            $query->where('course_schedules.teacher_id', $teacherId);
        }
        // Filtro de fecha sobre inscripciones
        $query->whereBetween('enrollments.created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        // Agrupación
        $query->groupBy(
            'enrollments.id', 'students.id', 'students.first_name', 'students.last_name', 
            'students.phone', 'students.mobile_phone', 'courses.name', 'modules.name', 
            'modules.price', 'course_schedules.section_name', 'enrollments.status'
        );

        $financials = $query->get();

        // Procesamiento
        $financials = $financials->map(function ($item) {
            $paid = (float) $item->total_paid;
            $modulePrice = (float) $item->module_price;
            $item->total_cost = $modulePrice;
            if ($paid > $item->total_cost) {
                $item->total_cost = $paid;
            }
            return $item;
        });

        // Filtrado por estado de pago
        if ($paymentStatus !== 'all') {
            $financials = $financials->filter(function($item) use ($paymentStatus) {
                $paid = (float) $item->total_paid;
                $cost = (float) $item->total_cost;
                $balance = round($cost - $paid, 2);

                if ($paymentStatus == 'paid') return $balance <= 0 && $paid > 0; 
                if ($paymentStatus == 'pending') return $balance > 0; 
                return true;
            });
        }

        $data = [
            'financials' => $financials,
            'filter_status' => $paymentStatus,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];

        // Generar PDF
        $pdf = Pdf::loadView('reports.financial-report-pdf', ['data' => $data]);
        $pdf->setPaper('a4', 'landscape'); // Horizontal para reporte financiero (muchas columnas)

        return $pdf->stream('Reporte_Financiero_' . $dateFrom . '_al_' . $dateTo . '.pdf');
    }
}