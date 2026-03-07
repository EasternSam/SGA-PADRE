<?php

namespace Tests\Feature;

use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\PaymentConcept;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaymentModelTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Student $student;
    protected PaymentConcept $concept;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('Estudiante', 'web');

        $this->user = User::factory()->create();
        $this->user->assignRole('Estudiante');

        $this->student = Student::create([
            'user_id' => $this->user->id,
            'first_name' => 'María',
            'last_name' => 'García',
            'cedula' => '00100000002',
            'email' => 'maria@test.com',
            'status' => 'Activo',
        ]);

        $this->concept = PaymentConcept::create([
            'name' => 'Inscripción',
            'amount' => 10000,
        ]);
    }

    // =========================================================
    // PAYMENT CREATION
    // =========================================================

    public function test_payment_can_be_created_with_required_fields(): void
    {
        $payment = Payment::create([
            'student_id' => $this->student->id,
            'user_id' => $this->user->id,
            'payment_concept_id' => $this->concept->id,
            'amount' => 5000.00,
            'status' => 'Pendiente',
            'gateway' => 'efectivo',
        ]);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'student_id' => $this->student->id,
            'amount' => 5000.00,
            'status' => 'Pendiente',
        ]);
    }

    // =========================================================
    // RELATIONSHIPS
    // =========================================================

    public function test_payment_belongs_to_student(): void
    {
        $payment = Payment::create([
            'student_id' => $this->student->id,
            'amount' => 1000,
            'status' => 'Pendiente',
            'gateway' => 'efectivo',
        ]);

        $this->assertInstanceOf(Student::class, $payment->student);
        $this->assertEquals($this->student->id, $payment->student->id);
    }

    public function test_payment_belongs_to_user(): void
    {
        $payment = Payment::create([
            'student_id' => $this->student->id,
            'user_id' => $this->user->id,
            'amount' => 1000,
            'status' => 'Pendiente',
            'gateway' => 'efectivo',
        ]);

        $this->assertInstanceOf(User::class, $payment->user);
        $this->assertEquals($this->user->id, $payment->user->id);
    }

    public function test_payment_belongs_to_concept(): void
    {
        $payment = Payment::create([
            'student_id' => $this->student->id,
            'payment_concept_id' => $this->concept->id,
            'amount' => 1000,
            'status' => 'Pendiente',
            'gateway' => 'efectivo',
        ]);

        $this->assertInstanceOf(PaymentConcept::class, $payment->paymentConcept);
        $this->assertEquals('Inscripción', $payment->paymentConcept->name);
    }

    public function test_payment_belongs_to_enrollment(): void
    {
        $enrollment = Enrollment::create([
            'student_id' => $this->student->id,
            'course_schedule_id' => \App\Models\CourseSchedule::first()?->id ?? (new class { use \Tests\CreatesTestFixtures; public function make() { return $this->createCourseSchedule(); } })->make()->id,
            'status' => 'Pendiente',
        ]);

        $payment = Payment::create([
            'student_id' => $this->student->id,
            'enrollment_id' => $enrollment->id,
            'amount' => 1000,
            'status' => 'Pendiente',
            'gateway' => 'efectivo',
        ]);

        $this->assertInstanceOf(Enrollment::class, $payment->enrollment);
    }

    // =========================================================
    // STATUS TRANSITIONS
    // =========================================================

    public function test_payment_status_can_be_updated(): void
    {
        $payment = Payment::create([
            'student_id' => $this->student->id,
            'amount' => 5000,
            'status' => 'Pendiente',
            'gateway' => 'efectivo',
        ]);

        $payment->update(['status' => 'Completado', 'transaction_id' => 'TX123']);

        $this->assertEquals('Completado', $payment->fresh()->status);
        $this->assertEquals('TX123', $payment->fresh()->transaction_id);
    }

    // =========================================================
    // AMOUNT CASTING
    // =========================================================

    public function test_amount_is_cast_to_decimal(): void
    {
        $payment = Payment::create([
            'student_id' => $this->student->id,
            'amount' => 5000.5,
            'status' => 'Pendiente',
            'gateway' => 'efectivo',
        ]);

        $this->assertIsNumeric($payment->amount);
        $this->assertEquals('5000.50', $payment->amount);
    }

    // =========================================================
    // DGII QR URL
    // =========================================================

    public function test_dgii_qr_url_returns_null_without_ncf(): void
    {
        // Pendiente payments get no NCF
        $payment = Payment::create([
            'student_id' => $this->student->id,
            'amount' => 1000,
            'status' => 'Pendiente',
            'gateway' => 'efectivo',
        ]);

        $this->assertNull($payment->dgii_qr_url);
    }

    public function test_dgii_qr_url_returns_valid_url_with_ncf(): void
    {
        $payment = Payment::create([
            'student_id' => $this->student->id,
            'amount' => 1000,
            'status' => 'Pendiente',
            'gateway' => 'efectivo',
        ]);

        // Manually set NCF to test the accessor
        $payment->ncf = 'E320000000001';
        $payment->security_code = 'ABC123';
        $payment->save();

        $url = $payment->dgii_qr_url;
        $this->assertNotNull($url);
        $this->assertStringContainsString('ecf.dgii.gov.do', $url);
        $this->assertStringContainsString('E320000000001', $url);
        $this->assertStringContainsString('ABC123', $url);
    }
}

