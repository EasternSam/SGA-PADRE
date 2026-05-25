<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\EvaluationPeriod;
use App\Models\ParentAccessToken;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentGrade;
use App\Models\StudentPayment;
use Illuminate\Http\Request;

class ParentPortalController extends Controller
{
    /**
     * Login page
     */
    public function login()
    {
        return view('parent-portal.login');
    }

    /**
     * Authenticate with token
     */
    public function authenticate(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'pin'   => 'required|string|size:6',
        ]);

        $access = ParentAccessToken::where('token', $request->token)
            ->where('pin', $request->pin)
            ->where('is_active', true)
            ->first();

        if (!$access) {
            return back()->withErrors(['token' => 'Token o PIN inválido.']);
        }

        $access->update(['last_accessed_at' => now()]);

        session(['parent_token_id' => $access->id, 'parent_student_id' => $access->student_id]);

        return redirect()->route('parent.dashboard');
    }

    /**
     * Dashboard
     */
    public function dashboard()
    {
        $studentId = session('parent_student_id');
        if (!$studentId) return redirect()->route('parent.login');

        $student = Student::with(['gradeLevel', 'section.gradeLevel'])->findOrFail($studentId);
        $activeYear = AcademicYear::where('status', 'active')->first();

        // Grades
        $periods = $activeYear
            ? EvaluationPeriod::where('academic_year_id', $activeYear->id)->orderBy('number')->get()
            : collect();

        $gradeData = [];
        foreach ($periods as $period) {
            $grades = StudentGrade::where('student_id', $student->id)
                ->where('evaluation_period_id', $period->id)
                ->with('sectionSubject.subject')
                ->get();

            if ($grades->isNotEmpty()) {
                $gradeData[] = [
                    'period' => $period,
                    'grades' => $grades,
                    'avg'    => round($grades->whereNotNull('score')->avg('score'), 1),
                ];
            }
        }

        // Attendance
        $att = StudentAttendance::where('student_id', $student->id)
            ->when($activeYear, fn($q) => $q->whereDate('date', '>=', $activeYear->start_date))
            ->get();
        $attendance = [
            'present' => $att->where('status', 'present')->count(),
            'absent'  => $att->where('status', 'absent')->count(),
            'late'    => $att->where('status', 'late')->count(),
            'total'   => $att->count(),
            'pct'     => $att->count() > 0 ? round(($att->where('status', 'present')->count() / $att->count()) * 100, 1) : 0,
        ];

        // Payments
        $payments = StudentPayment::where('student_id', $student->id)
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->orderByDesc('created_at')
            ->get();
        $totalDue = $payments->sum('amount');
        $totalPaid = $payments->sum('paid');

        return view('parent-portal.dashboard', compact(
            'student', 'activeYear', 'gradeData', 'attendance', 'payments', 'totalDue', 'totalPaid'
        ));
    }

    /**
     * Logout
     */
    public function logout()
    {
        session()->forget(['parent_token_id', 'parent_student_id']);
        return redirect()->route('parent.login');
    }
}
