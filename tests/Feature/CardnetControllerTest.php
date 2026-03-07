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

class CardnetControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $studentUser;
    protected Student $student;
    protected Payment $payment;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::findOrCreate('Estudiante', 'web');
        Role::findOrCreate('Admin', 'web');

        // Create student user
        $this->studentUser = User::factory()->create();
        $this->studentUser->assignRole('Estudiante');

        // Create student profile
        $this->student = Student::create([
            'user_id' => $this->studentUser->id,
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'cedula' => '00100000001',
            'email' => $this->studentUser->email,
            'status' => 'Activo',
        ]);

        // Create payment concept
        $concept = PaymentConcept::create([
            'name' => 'Mensualidad',
            'amount' => 5000,
        ]);

        // Create pending payment
        $this->payment = Payment::create([
            'student_id' => $this->student->id,
            'user_id' => $this->studentUser->id,
            'payment_concept_id' => $concept->id,
            'amount' => 5000,
            'status' => 'Pendiente',
            'gateway' => 'cardnet',
        ]);
    }

    // =========================================================
    // CARDNET RESPONSE (Successful Payment)
    // =========================================================

    public function test_successful_payment_marks_as_completed(): void
    {
        $response = $this->post('/cardnet/response', [
            'OrdenId' => $this->payment->id,
            'ResponseCode' => '00',
            'AuthorizationCode' => 'AUTH123',
            'TransactionId' => 'TX456',
        ]);

        $response->assertRedirect();

        $this->payment->refresh();
        $this->assertEquals('Completado', $this->payment->status);
        $this->assertEquals('AUTH123', $this->payment->transaction_id);
        $this->assertStringContainsString('Aprobado Cardnet', $this->payment->notes);
    }

    public function test_successful_payment_redirects_student_to_payments(): void
    {
        $this->actingAs($this->studentUser);

        $response = $this->post('/cardnet/response', [
            'OrdenId' => $this->payment->id,
            'ResponseCode' => '00',
            'AuthorizationCode' => 'AUTH123',
            'TransactionId' => 'TX456',
        ]);

        $response->assertRedirectToRoute('student.payments');
        $response->assertSessionHas('message');
    }

    public function test_successful_payment_activates_enrollment(): void
    {
        $enrollment = Enrollment::create([
            'student_id' => $this->student->id,
            'course_schedule_id' => \App\Models\CourseSchedule::first()?->id ?? (new class { use \Tests\CreatesTestFixtures; public function make() { return $this->createCourseSchedule(); } })->make()->id,
            'status' => 'Pendiente',
        ]);

        $this->payment->update(['enrollment_id' => $enrollment->id]);

        $this->post('/cardnet/response', [
            'OrdenId' => $this->payment->id,
            'ResponseCode' => '00',
            'AuthorizationCode' => 'AUTH123',
            'TransactionId' => 'TX456',
        ]);

        $enrollment->refresh();
        $this->assertEquals('Cursando', $enrollment->status);
    }

    // =========================================================
    // CARDNET RESPONSE (Failed Payment)
    // =========================================================

    public function test_failed_payment_stays_pending(): void
    {
        $response = $this->post('/cardnet/response', [
            'OrdenId' => $this->payment->id,
            'ResponseCode' => '51',
            'ResponseMessage' => 'Fondos insuficientes',
        ]);

        $response->assertRedirect();

        $this->payment->refresh();
        $this->assertEquals('Pendiente', $this->payment->status);
        $this->assertStringContainsString('Intento fallido', $this->payment->notes);
        $this->assertStringContainsString('51', $this->payment->notes);
    }

    public function test_nonexistent_payment_redirects_to_home(): void
    {
        $response = $this->post('/cardnet/response', [
            'OrdenId' => 99999,
            'ResponseCode' => '00',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('error');
    }

    // =========================================================
    // CARDNET RESPONSE (Session Restoration)
    // =========================================================

    public function test_restores_session_for_unauthenticated_user(): void
    {
        // Hit the endpoint without being logged in
        $this->post('/cardnet/response', [
            'OrdenId' => $this->payment->id,
            'ResponseCode' => '00',
            'AuthorizationCode' => 'AUTH123',
            'TransactionId' => 'TX456',
        ]);

        // The controller should have logged in the user via Auth::loginUsingId
        $this->assertAuthenticatedAs($this->studentUser);
    }

    // =========================================================
    // CARDNET CANCEL
    // =========================================================

    public function test_cancel_adds_cancel_note(): void
    {
        $this->post('/cardnet/cancel', [
            'OrdenId' => $this->payment->id,
        ]);

        $this->payment->refresh();
        $this->assertStringContainsString('Cancelado por usuario', $this->payment->notes);
    }

    public function test_cancel_redirects_authenticated_student_to_payments(): void
    {
        $this->actingAs($this->studentUser);

        $response = $this->post('/cardnet/cancel', [
            'OrdenId' => $this->payment->id,
        ]);

        $response->assertRedirectToRoute('student.payments');
        $response->assertSessionHas('error');
    }

    public function test_cancel_redirects_guest_to_home(): void
    {
        // Payment without user_id so session can't be restored
        $guestPayment = Payment::create([
            'student_id' => $this->student->id,
            'amount' => 1000,
            'status' => 'Pendiente',
            'gateway' => 'cardnet',
        ]);

        $response = $this->post('/cardnet/cancel', [
            'OrdenId' => $guestPayment->id,
        ]);

        $response->assertRedirect('/');
    }

    // =========================================================
    // KIOSK CANCEL
    // =========================================================

    public function test_kiosk_cancel_adds_kiosk_note(): void
    {
        $this->post('/kiosk/cardnet/cancel', [
            'OrdenId' => $this->payment->id,
        ]);

        $this->payment->refresh();
        $this->assertStringContainsString('Cancelado por usuario en Kiosco', $this->payment->notes);
    }

    public function test_kiosk_cancel_redirects_to_kiosk_finances(): void
    {
        $response = $this->post('/kiosk/cardnet/cancel', [
            'OrdenId' => $this->payment->id,
        ]);

        $response->assertRedirectToRoute('kiosk.finances');
        $response->assertSessionHas('notify');
    }

    // =========================================================
    // CARDNET RESPONSE (Alternative Input Casing)
    // =========================================================

    public function test_handles_alternative_input_key_casing(): void
    {
        $response = $this->post('/cardnet/response', [
            'OrdenID' => $this->payment->id, // <-- OrdenID instead of OrdenId
            'ResponseCode' => '00',
            'AuthorizationCode' => 'AUTH789',
            'TransactionId' => 'TX101',
        ]);

        $response->assertRedirect();

        $this->payment->refresh();
        $this->assertEquals('Completado', $this->payment->status);
    }
}
