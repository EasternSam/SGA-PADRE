<?php

namespace Tests\Unit\UI;

use Tests\TestCase;

class TopbarSectionTitlesTest extends TestCase
{
    /**
     * All pages that render AuthenticatedLayout must provide an explicit, human-readable title prop
     * so the Topbar never displays a generic "SGA" placeholder.
     */
    public function test_all_authenticated_pages_pass_explicit_title_prop(): void
    {
        $basePath = dirname(__DIR__, 3);
        $pagesWithAuthenticatedLayout = [
            'resources/js/Pages/Academic/Careers/CurriculumTree.jsx',
            'resources/js/Pages/Academic/Careers/Index.jsx',
            'resources/js/Pages/Academic/Classrooms/Index.jsx',
            'resources/js/Pages/Academic/Courses/Index.jsx',
            'resources/js/Pages/Academic/Schedules/Builder.jsx',
            'resources/js/Pages/Academic/Sections/Index.jsx',
            'resources/js/Pages/Accounting/ChartOfAccounts.jsx',
            'resources/js/Pages/Accounting/JournalEntry.jsx',
            'resources/js/Pages/Accounting/Ledger.jsx',
            'resources/js/Pages/Accounting/PeriodClosing.jsx',
            'resources/js/Pages/Accounting/Statements.jsx',
            'resources/js/Pages/Admin/AuditLogs/Index.jsx',
            'resources/js/Pages/Admin/Calendar/Index.jsx',
            'resources/js/Pages/Admin/Certificates/Editor.jsx',
            'resources/js/Pages/Admin/Certificates/Index.jsx',
            'resources/js/Pages/Admin/Dashboard.jsx',
            'resources/js/Pages/Admin/Settings/Index.jsx',
            'resources/js/Pages/Admin/Users/Index.jsx',
            'resources/js/Pages/Admissions/Pipeline.jsx',
            'resources/js/Pages/Applicant/Portal.jsx',
            'resources/js/Pages/Attendance/Justifications.jsx',
            'resources/js/Pages/Attendance/Register.jsx',
            'resources/js/Pages/Finance/Concepts/Index.jsx',
            'resources/js/Pages/Finance/Dashboard.jsx',
            'resources/js/Pages/Finance/DgiiReports/Index.jsx',
            'resources/js/Pages/Finance/Expenses/Index.jsx',
            'resources/js/Pages/Grades/LockManager.jsx',
            'resources/js/Pages/Grades/MatrixEntry.jsx',
            'resources/js/Pages/Hr/AttendanceClock.jsx',
            'resources/js/Pages/Hr/Employees/Index.jsx',
            'resources/js/Pages/Hr/Employees/Profile.jsx',
            'resources/js/Pages/Hr/Payroll/Index.jsx',
            'resources/js/Pages/Pos/Index.jsx',
            'resources/js/Pages/Profile/Edit.jsx',
            'resources/js/Pages/Reports/Center.jsx',
            'resources/js/Pages/Requests/Index.jsx',
            'resources/js/Pages/StudentPortal/AcademicProjection.jsx',
            'resources/js/Pages/StudentPortal/Dashboard.jsx',
            'resources/js/Pages/StudentPortal/MyPayments.jsx',
            'resources/js/Pages/StudentPortal/SubjectSelection.jsx',
            'resources/js/Pages/Students/Index.jsx',
            'resources/js/Pages/Students/Profile.jsx',
            'resources/js/Pages/TeacherPortal/Dashboard.jsx',
            'resources/js/Pages/TeacherPortal/GradeBook.jsx',
            'resources/js/Pages/TeacherPortal/MyPayroll.jsx',
            'resources/js/Pages/TeacherPortal/RollCall.jsx',
            'resources/js/Pages/Teachers/Index.jsx',
            'resources/js/Pages/Teachers/Profile.jsx',
        ];

        foreach ($pagesWithAuthenticatedLayout as $relativePath) {
            $fullPath = $basePath . '/' . $relativePath;
            $this->assertFileExists($fullPath);
            $content = file_get_contents($fullPath);

            $this->assertMatchesRegularExpression(
                '/<AuthenticatedLayout[^>]*\btitle\s*=/s',
                $content,
                "Expected {$relativePath} to pass a title prop to <AuthenticatedLayout>."
            );
        }
    }

    public function test_authenticated_layout_has_url_based_title_fallback_instead_of_generic_sga(): void
    {
        $basePath = dirname(__DIR__, 3);
        $layoutContent = file_get_contents($basePath . '/resources/js/Layouts/AuthenticatedLayout.jsx');

        $this->assertStringNotContainsString(
            "{title || 'SGA'}",
            $layoutContent,
            'AuthenticatedLayout should not fallback to generic "SGA" in the topbar.'
        );
        $this->assertStringContainsString(
            'resolveSectionTitle',
            $layoutContent,
            'AuthenticatedLayout should include a URL-based section title resolver fallback.'
        );
    }
}
