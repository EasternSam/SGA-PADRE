<?php

namespace Tests\Feature;

use App\Models\CourseSchedule;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\PaymentConcept;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Tests\CreatesTestFixtures;

class EnrollmentTest extends TestCase
{
    use RefreshDatabase, CreatesTestFixtures;

    protected User $user;
    protected Student $student;
    protected int $scheduleId;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('Estudiante', 'web');

        $this->user = User::factory()->create();
        $this->user->assignRole('Estudiante');

        $this->student = Student::create([
            'user_id' => $this->user->id,
            'first_name' => 'Carlos',
            'last_name' => 'López',
            'cedula' => '00100000003',
            'email' => 'carlos@test.com',
            'status' => 'Activo',
        ]);

        $this->scheduleId = $this->createCourseSchedule()->id;
    }

    // =========================================================
    // ENROLLMENT CREATION
    // =========================================================

    public function test_enrollment_can_be_created(): void
    {
        $enrollment = Enrollment::create([
            'student_id' => $this->student->id,
            'course_schedule_id' => $this->scheduleId,
            'status' => 'Pendiente',
        ]);

        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'student_id' => $this->student->id,
            'status' => 'Pendiente',
        ]);
    }

    // =========================================================
    // RELATIONSHIPS
    // =========================================================

    public function test_enrollment_belongs_to_student(): void
    {
        $enrollment = Enrollment::create([
            'student_id' => $this->student->id,
            'course_schedule_id' => $this->scheduleId,
            'status' => 'Cursando',
        ]);

        $this->assertInstanceOf(Student::class, $enrollment->student);
        $this->assertEquals('Carlos', $enrollment->student->first_name);
    }

    // =========================================================
    // CASCADE DELETE (Pending Payments)
    // =========================================================

    public function test_deleting_enrollment_removes_pending_payments(): void
    {
        $enrollment = Enrollment::create([
            'student_id' => $this->student->id,
            'course_schedule_id' => $this->scheduleId,
            'status' => 'Pendiente',
        ]);

        $pendingPayment = Payment::create([
            'student_id' => $this->student->id,
            'enrollment_id' => $enrollment->id,
            'amount' => 5000,
            'status' => 'Pendiente',
            'gateway' => 'efectivo',
        ]);

        $enrollment->delete();

        $this->assertDatabaseMissing('payments', ['id' => $pendingPayment->id]);
    }

    public function test_deleting_enrollment_keeps_completed_payments(): void
    {
        $enrollment = Enrollment::create([
            'student_id' => $this->student->id,
            'course_schedule_id' => $this->scheduleId,
            'status' => 'Completado',
        ]);

        $completedPayment = Payment::create([
            'student_id' => $this->student->id,
            'enrollment_id' => $enrollment->id,
            'amount' => 5000,
            'status' => 'Completado',
            'gateway' => 'efectivo',
            'transaction_id' => 'TX999',
        ]);

        $enrollment->delete();

        // Completed payment should NOT be deleted
        $this->assertDatabaseHas('payments', ['id' => $completedPayment->id]);
    }

    // =========================================================
    // IS_PAID ATTRIBUTE
    // =========================================================

    public function test_enrollment_is_not_paid_without_payment(): void
    {
        $enrollment = Enrollment::create([
            'student_id' => $this->student->id,
            'course_schedule_id' => $this->scheduleId,
            'status' => 'Pendiente',
        ]);

        $this->assertFalse($enrollment->is_paid);
    }

    public function test_enrollment_is_paid_with_completed_individual_payment(): void
    {
        $enrollment = Enrollment::create([
            'student_id' => $this->student->id,
            'course_schedule_id' => $this->scheduleId,
            'status' => 'Cursando',
        ]);

        Payment::create([
            'student_id' => $this->student->id,
            'enrollment_id' => $enrollment->id,
            'amount' => 5000,
            'status' => 'Completado',
            'gateway' => 'efectivo',
        ]);

        $this->assertTrue($enrollment->fresh()->is_paid);
    }

    public function test_enrollment_is_paid_with_grouped_payment(): void
    {
        $groupPayment = Payment::create([
            'student_id' => $this->student->id,
            'amount' => 15000,
            'status' => 'Completado',
            'gateway' => 'efectivo',
        ]);

        $enrollment = Enrollment::create([
            'student_id' => $this->student->id,
            'course_schedule_id' => $this->scheduleId,
            'payment_id' => $groupPayment->id,
            'status' => 'Cursando',
        ]);

        $this->assertTrue($enrollment->is_paid);
    }

    // =========================================================
    // STATUS TRANSITIONS
    // =========================================================

    public function test_enrollment_status_transitions(): void
    {
        $enrollment = Enrollment::create([
            'student_id' => $this->student->id,
            'course_schedule_id' => $this->scheduleId,
            'status' => 'Pendiente',
        ]);

        $enrollment->update(['status' => 'Cursando']);
        $this->assertEquals('Cursando', $enrollment->fresh()->status);

        $enrollment->update(['status' => 'Completado', 'final_grade' => 95]);
        $this->assertEquals('Completado', $enrollment->fresh()->status);
        $this->assertEquals(95, $enrollment->fresh()->final_grade);
    }
}
