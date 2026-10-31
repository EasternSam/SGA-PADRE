<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\PaymentConcept;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use App\Services\Pos\PosCheckoutService;
use App\Services\Pos\PosReceiptPresenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Terminal de caja (POS): el servidor es la única fuente de verdad para montos,
 * cambio, comprobante fiscal y cliente final.
 */
class PosCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;
    protected Student $student;
    protected Student $walkIn;
    protected PaymentConcept $fixed;
    protected PaymentConcept $variable;
    protected PaymentConcept $open;

    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();
        Http::preventStrayRequests();
        Setting::updateOrCreate(['key' => 'enable_bills_invoicing'], ['value' => 'false']);

        Role::findOrCreate('Admin', 'web');
        $this->cashier = User::factory()->create();
        $this->cashier->assignRole('Admin');

        [$this->student, $this->walkIn] = Student::withoutEvents(fn () => [
            Student::create([
                'first_name' => 'Ana', 'last_name' => 'Pérez', 'cedula' => '00100000001',
                'student_code' => '2026-0001', 'status' => 'Activo',
            ]),
            Student::create([
                'first_name' => 'Cliente', 'last_name' => 'General', 'cedula' => 'CLIENTE-GRAL',
                'student_code' => config('pos.walk_in_student_code'), 'status' => 'Activo',
            ]),
        ]);

        $this->fixed = PaymentConcept::create(['name' => 'Carnet', 'amount' => 400, 'default_amount' => 400, 'is_fixed_amount' => true]);
        $this->variable = PaymentConcept::create(['name' => 'Laboratorio', 'amount' => 1500, 'default_amount' => 1500, 'is_fixed_amount' => false]);
        $this->open = PaymentConcept::create(['name' => config('pos.open_concept_name'), 'amount' => 0, 'default_amount' => 0, 'is_fixed_amount' => false]);
    }

    private function checkout(array $overrides = []): Payment
    {
        return app(PosCheckoutService::class)->checkout(array_merge([
            'student_id' => $this->student->id,
            'payment_concept_id' => $this->fixed->id,
            'quantity' => 1,
            'gateway' => 'Tarjeta',
            'ncf_type' => 'B02',
        ], $overrides), $this->cashier);
    }

    private function assertRejects(string $field, array $overrides): void
    {
        try {
            $this->checkout($overrides);
            $this->fail("Se esperaba un error de validación en '{$field}'.");
        } catch (ValidationException $e) {
            $this->assertArrayHasKey($field, $e->errors());
        }
        $this->assertSame(0, Payment::count());
    }

    public function test_fixed_concept_price_comes_from_catalog_not_from_client(): void
    {
        $payment = $this->checkout(['unit_amount' => 1, 'quantity' => 2]);

        $this->assertEquals(800, (float) $payment->original_amount);
        $this->assertEquals(800, (float) $payment->amount);
        $this->assertSame(2, (int) $payment->quantity);
    }

    public function test_variable_concept_accepts_cashier_price(): void
    {
        $payment = $this->checkout(['payment_concept_id' => $this->variable->id, 'unit_amount' => 1250.50]);

        $this->assertEquals(1250.50, (float) $payment->amount);
    }

    public function test_discount_is_subtracted_and_cannot_exceed_subtotal(): void
    {
        $payment = $this->checkout(['discount_amount' => 100]);
        $this->assertEquals(400, (float) $payment->original_amount);
        $this->assertEquals(100, (float) $payment->discount_amount);
        $this->assertEquals(300, (float) $payment->amount);

        Payment::query()->delete();
        $this->assertRejects('discount_amount', ['discount_amount' => 401]);
    }

    public function test_cash_payment_requires_enough_cash_and_change_is_computed_server_side(): void
    {
        $this->assertRejects('cash_received', ['gateway' => 'Efectivo', 'cash_received' => 399.99]);

        $payment = $this->checkout(['gateway' => 'Efectivo', 'cash_received' => 1000, 'change_amount' => 9999]);

        $this->assertEquals(1000, (float) $payment->cash_received);
        $this->assertEquals(600, (float) $payment->change_amount);
    }

    public function test_non_cash_payment_does_not_store_cash_fields(): void
    {
        $payment = $this->checkout(['cash_received' => 5000]);

        $this->assertNull($payment->cash_received);
        $this->assertNull($payment->change_amount);
    }

    public function test_open_concept_requires_a_label_and_a_price(): void
    {
        $this->assertRejects('concept_label', ['payment_concept_id' => $this->open->id, 'unit_amount' => 250]);
        $this->assertRejects('unit_amount', ['payment_concept_id' => $this->open->id, 'concept_label' => 'Fotocopias']);

        $payment = $this->checkout(['payment_concept_id' => $this->open->id, 'concept_label' => 'Fotocopias', 'unit_amount' => 25, 'quantity' => 4]);

        $this->assertSame('Fotocopias', $payment->concept_label);
        $this->assertSame($this->open->id, $payment->payment_concept_id);
        $this->assertEquals(100, (float) $payment->amount);
    }

    public function test_walk_in_flag_resolves_the_configured_record_not_a_hardcoded_id(): void
    {
        $payment = $this->checkout(['student_id' => null, 'walk_in' => true]);

        $this->assertSame($this->walkIn->id, $payment->student_id);
        $this->assertTrue($payment->student->isWalkIn());
    }

    public function test_credit_fiscal_requires_rnc_and_is_requested_with_dgii_code(): void
    {
        $this->assertRejects('rnc_client', ['ncf_type' => 'B01']);

        $payment = $this->checkout(['ncf_type' => 'B01', 'rnc_client' => '131000001', 'company_name' => 'Acme SRL']);

        $this->assertSame('01', $payment->ncf_type_requested);
        $this->assertSame('131000001', $payment->rnc_client);

        $consumer = $this->checkout();
        $this->assertSame('02', $consumer->ncf_type_requested);
        $this->assertNull($consumer->rnc_client);
    }

    public function test_receipt_never_invents_an_ncf(): void
    {
        $payment = $this->checkout();
        $payment->forceFill(['ncf' => null])->saveQuietly();

        $receipt = app(PosReceiptPresenter::class)->present($payment->fresh());

        $this->assertNull($receipt['ncf']);
        $this->assertSame('pending', $receipt['ncf_status']);
        $this->assertSame('Carnet', $receipt['concept']);
    }

    public function test_process_endpoint_returns_201_with_receipt_and_422_on_invalid_input(): void
    {
        $this->actingAs($this->cashier)
            ->postJson(route('admin.pos.process'), [
                'walk_in' => true,
                'payment_concept_id' => $this->fixed->id,
                'quantity' => 1,
                'gateway' => 'Efectivo',
                'cash_received' => 500,
                'ncf_type' => 'B02',
            ])
            ->assertCreated()
            ->assertJsonPath('receipt.total', 400)
            ->assertJsonPath('receipt.change_amount', 100);

        $this->actingAs($this->cashier)
            ->postJson(route('admin.pos.process'), ['payment_concept_id' => $this->fixed->id, 'gateway' => 'Bitcoin'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['student_id', 'gateway']);
    }

    public function test_student_search_hides_the_walk_in_record(): void
    {
        $this->actingAs($this->cashier)
            ->getJson(route('admin.pos.search', ['q' => 'Cliente']))
            ->assertOk()
            ->assertJsonCount(0);
    }
}
