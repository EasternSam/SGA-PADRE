<?php

namespace App\Http\Controllers;

use App\Http\Requests\Pos\ProcessPosPaymentRequest;
use App\Models\Payment;
use App\Models\PaymentConcept;
use App\Models\Student;
use App\Services\Pos\PosCheckoutService;
use App\Services\Pos\PosReceiptPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class PosController extends Controller
{
    public function __construct(private readonly PosReceiptPresenter $presenter)
    {
    }

    /**
     * Terminal de caja.
     */
    public function index(Request $request): Response
    {
        $openConceptName = config('pos.open_concept_name');

        $concepts = PaymentConcept::orderBy('name')
            ->get(['id', 'name', 'description', 'amount', 'default_amount', 'is_fixed_amount'])
            ->map(fn (PaymentConcept $concept) => [
                'id' => $concept->id,
                'name' => $concept->name,
                'description' => $concept->description,
                'amount' => (float) ($concept->default_amount ?: $concept->amount),
                'is_fixed' => (bool) $concept->is_fixed_amount,
                'is_open' => $concept->name === $openConceptName,
            ]);

        $todayPayments = Payment::with(['student', 'paymentConcept', 'user'])
            ->whereDate('created_at', today())
            ->where('status', 'Completado')
            ->latest()
            ->get();

        $initialStudent = $request->integer('student_id')
            ? Student::excludingWalkIn()->find($request->integer('student_id'))
            : null;

        return Inertia::render('Pos/Index', [
            'concepts' => $concepts,
            'walkInAvailable' => Student::where('student_code', config('pos.walk_in_student_code'))->exists(),
            'initialStudent' => $initialStudent ? $this->studentOption($initialStudent) : null,
            'todayPayments' => $todayPayments->map(fn (Payment $p) => $this->presenter->present($p))->values(),
            'todayTotal' => (float) $todayPayments->sum('amount'),
            'issuer' => $this->presenter->issuer(),
            'gateways' => config('pos.gateways'),
            'maxQuantity' => config('pos.max_quantity'),
        ]);
    }

    /**
     * Búsqueda de estudiantes (excluye la ficha interna de cliente general).
     */
    public function searchStudents(Request $request): JsonResponse
    {
        $query = trim((string) $request->input('q', ''));
        if (mb_strlen($query) < 2) {
            return response()->json([]);
        }

        $tokens = preg_split('/\s+/u', $query, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $students = Student::excludingWalkIn()
            ->where('status', '!=', 'Inactivo')
            ->where(function ($q) use ($tokens) {
                foreach ($tokens as $token) {
                    $q->where(function ($sub) use ($token) {
                        $sub->where('first_name', 'like', "%{$token}%")
                            ->orWhere('last_name', 'like', "%{$token}%")
                            ->orWhere('student_code', 'like', "%{$token}%")
                            ->orWhere('cedula', 'like', "%{$token}%")
                            ->orWhere('email', 'like', "%{$token}%");
                    });
                }
            })
            ->limit(15)
            ->get()
            ->map(fn (Student $s) => $this->studentOption($s));

        return response()->json($students);
    }

    /**
     * Registra el cobro o genera la deuda en cuenta. Respuesta siempre JSON: 201 con el recibo/cargo, 422 con errores por campo.
     */
    public function process(ProcessPosPaymentRequest $request, PosCheckoutService $checkout): JsonResponse
    {
        try {
            $payment = $checkout->checkout($request->validated(), $request->user());
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('POS: error registrando operación', ['user_id' => $request->user()?->id, 'exception' => $e]);

            return response()->json([
                'message' => 'La operación no quedó registrada en el sistema. Intente de nuevo o contacte a soporte.',
            ], 500);
        }

        $isDebt = in_array($payment->status, ['Pendiente', 'pending'], true);
        $updatedStudent = ($payment->student && !$payment->student->isWalkIn())
            ? $this->studentOption($payment->student->fresh())
            : null;

        return response()->json([
            'message' => $isDebt
                ? "Deuda #{$payment->id} generada en la cuenta del estudiante."
                : "Cobro #{$payment->id} registrado.",
            'receipt' => $this->presenter->present($payment),
            'student' => $updatedStudent,
        ], 201);
    }

    /**
     * Genera el comprobante térmico de 80mm (o A4 si ?template=normal) en PDF vía DomPDF
     * usando las plantillas oficiales de Gridbase Bills con código QR DGII inyectado.
     */
    public function receiptPdf(Request $request, Payment $payment, \Gridbase\BillsCore\Contracts\IssuerConfigInterface $issuer): HttpResponse
    {
        if (!$request->has('template')) {
            $request->merge(['template' => 'thermal']);
        }

        return app(FinanceInvoiceController::class)->downloadPdf($request, $payment, $issuer);
    }

    private function studentOption(Student $s): array
    {
        $pendingPayments = Payment::with('paymentConcept')
            ->where('student_id', $s->id)
            ->whereIn('status', ['Pendiente', 'pending', 'Vencido', 'Parcial'])
            ->orderBy('due_date')
            ->get()
            ->map(fn (Payment $p) => [
                'id' => $p->id,
                'payment_concept_id' => $p->payment_concept_id,
                'concept' => $p->concept_label ?: ($p->paymentConcept?->name ?? 'Cargo Académico'),
                'concept_label' => $p->concept_label,
                'quantity' => (int) ($p->quantity ?: 1),
                'amount' => (float) $p->amount,
                'original_amount' => (float) ($p->original_amount ?: $p->amount),
                'discount_amount' => (float) ($p->discount_amount ?? 0),
                'due_date' => $p->due_date?->format('d/m/Y') ?? 'Sin vencimiento',
                'status' => $p->status,
                'notes' => $p->notes,
            ])
            ->values()
            ->all();

        return [
            'id' => $s->id,
            'name' => $s->full_name,
            'code' => $s->student_code,
            'cedula' => $s->cedula,
            'email' => $s->email,
            'phone' => $s->mobile_phone ?: $s->home_phone,
            'balance' => (float) collect($pendingPayments)->sum('amount'),
            'pending_payments' => $pendingPayments,
        ];
    }
}
