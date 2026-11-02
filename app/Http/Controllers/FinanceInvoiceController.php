<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Gridbase\BillsCore\Contracts\IssuerConfigInterface;
use Gridbase\BillsCore\Services\Dgii\EcfManagerService;
use Gridbase\BillsCore\Services\Dgii\XmlBuilderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class FinanceInvoiceController extends Controller
{
    public function index(Request $request, IssuerConfigInterface $issuer): Response
    {
        $tab = $request->input('tab', 'all');
        $search = trim((string) $request->input('search', ''));
        $ncfType = trim((string) $request->input('ncf_type', ''));

        $query = Payment::query()->with(['student', 'paymentConcept', 'user']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('ncf', 'like', "%{$search}%")
                  ->orWhere('transaction_id', 'like', "%{$search}%")
                  ->orWhere('rnc_client', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('concept_label', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('student', function ($sq) use ($search) {
                      $sq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('cedula', 'like', "%{$search}%")
                         ->orWhere('matricula', 'like', "%{$search}%");
                  });
            });
        }

        if ($ncfType !== '') {
            $query->where(function ($q) use ($ncfType) {
                $q->where('ncf_type', $ncfType)
                  ->orWhere('ncf', 'like', "E{$ncfType}%")
                  ->orWhere('ncf', 'like', "B{$ncfType}%");
            });
        }

        if ($tab === 'paid') {
            $query->whereIn('status', ['paid', 'Completado', 'Pagado'])
                  ->where(function ($q) {
                      $q->whereNull('ncf_type')->orWhereNotIn('ncf_type', ['34', '04']);
                  });
        } elseif ($tab === 'pending') {
            $query->whereIn('status', ['pending', 'Pendiente', 'En Revisión']);
        } elseif ($tab === 'credit_notes') {
            $query->where(function ($q) {
                $q->whereIn('ncf_type', ['34', '04'])
                  ->orWhere('ncf', 'like', 'E34%')
                  ->orWhere('ncf', 'like', 'B04%')
                  ->orWhere('status', 'Nota de Crédito');
            });
        } elseif ($tab === 'voided') {
            $query->whereIn('status', ['Anulado', 'void', 'cancelled', 'cancelado']);
        } elseif ($tab === 'dgii_audit') {
            $query->whereNotNull('ncf')->where('ncf', '!=', '');
        }

        $invoices = $query->latest('created_at')->paginate(18)->withQueryString()->through(function (Payment $p) {
            $studentName = $p->student
                ? trim($p->student->first_name . ' ' . $p->student->last_name)
                : ($p->company_name ?: 'Consumidor Final');
            $studentRnc = $p->rnc_client ?: ($p->student->cedula ?? '—');
            $concept = $p->concept_label
                ?: ($p->paymentConcept->name ?? null)
                ?: ($p->notes ?: 'Servicio Educativo');

            $ncf = (string) ($p->ncf ?? '');
            $isCreditNote = in_array((string) $p->ncf_type, ['34', '04'], true)
                || str_starts_with($ncf, 'E34')
                || str_starts_with($ncf, 'B04')
                || $p->status === 'Nota de Crédito';
            $isVoided = in_array(strtolower((string) $p->status), ['anulado', 'void', 'cancelled', 'cancelado'], true);

            $signedXmlPath = $ncf !== '' ? storage_path('app/signed_ecf/' . $ncf . '.xml') : null;

            return [
                'id' => $p->id,
                'invoice_number' => $p->transaction_id ?: ('FAC-' . str_pad((string) $p->id, 6, '0', STR_PAD_LEFT)),
                'student_id' => $p->student_id,
                'client_name' => $studentName,
                'client_rnc' => $studentRnc,
                'company_name' => $p->company_name,
                'concept' => $concept,
                'amount' => (float) $p->amount,
                'discount_amount' => (float) ($p->discount_amount ?? 0),
                'currency' => $p->currency ?: 'DOP',
                'status' => $p->status,
                'is_credit_note' => $isCreditNote,
                'is_voided' => $isVoided,
                'gateway' => $p->gateway ?: 'Caja',
                'ncf' => $ncf !== '' ? $ncf : null,
                'ncf_type' => $p->ncf_type ?: ($ncf !== '' ? substr($ncf, 1, 2) : null),
                'security_code' => $p->security_code,
                'dgii_status' => $p->dgii_status ?: ($ncf !== '' ? 'issued_local' : null),
                'dgii_track_id' => $p->dgii_track_id,
                'qr_url' => $p->dgii_qr_url,
                'has_signed_xml' => $signedXmlPath && file_exists($signedXmlPath),
                'notes' => $p->notes,
                'date' => $p->created_at ? $p->created_at->format('Y-m-d') : '',
                'date_formatted' => $p->created_at ? $p->created_at->format('d/m/Y') : '',
                'datetime_formatted' => $p->created_at ? $p->created_at->format('d/m/Y h:i A') : '',
                'due_date_formatted' => $p->due_date
                    ? $p->due_date->format('d/m/Y')
                    : ($p->created_at ? $p->created_at->format('d/m/Y') : '—'),
            ];
        });

        $stats = [
            'totalInvoiced' => (float) Payment::whereIn('status', ['paid', 'Completado', 'Pagado'])
                ->where(function ($q) {
                    $q->whereNull('ncf_type')->orWhereNotIn('ncf_type', ['34', '04']);
                })->sum('amount'),
            'totalPaidCount' => (int) Payment::whereIn('status', ['paid', 'Completado', 'Pagado'])->count(),
            'totalPendingAmount' => (float) Payment::whereIn('status', ['pending', 'Pendiente', 'En Revisión'])->sum('amount'),
            'totalPendingCount' => (int) Payment::whereIn('status', ['pending', 'Pendiente', 'En Revisión'])->count(),
            'creditNotesCount' => (int) Payment::where(function ($q) {
                $q->whereIn('ncf_type', ['34', '04'])
                  ->orWhere('ncf', 'like', 'E34%')
                  ->orWhere('ncf', 'like', 'B04%')
                  ->orWhere('status', 'Nota de Crédito');
            })->count(),
            'creditNotesAmount' => (float) Payment::where(function ($q) {
                $q->whereIn('ncf_type', ['34', '04'])
                  ->orWhere('ncf', 'like', 'E34%')
                  ->orWhere('ncf', 'like', 'B04%')
                  ->orWhere('status', 'Nota de Crédito');
            })->sum('amount'),
            'voidedCount' => (int) Payment::whereIn('status', ['Anulado', 'void', 'cancelled', 'cancelado'])->count(),
            'ecfIssuedCount' => (int) Payment::whereNotNull('ncf')->where('ncf', '!=', '')->count(),
        ];

        $students = \App\Models\Student::query()
            ->select(['id', 'first_name', 'last_name', 'cedula', 'matricula'])
            ->latest('id')
            ->limit(250)
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'name' => trim($s->first_name . ' ' . $s->last_name),
                'rnc' => $s->cedula ?: $s->matricula ?: '',
            ]);

        $concepts = \App\Models\PaymentConcept::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'default_amount', 'description'])
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'price' => (float) $c->default_amount,
                'description' => $c->description,
            ]);

        return Inertia::render('Finance/Invoices/Index', [
            'invoices' => $invoices,
            'stats' => $stats,
            'students' => $students,
            'concepts' => $concepts,
            'filters' => [
                'module' => $request->input('module', 'invoices'),
                'tab' => $tab,
                'search' => $search,
                'ncf_type' => $ncfType,
            ],
            'dgiiConfig' => [
                'enabled' => $issuer->isElectronicBillingEnabled(),
                'environment' => $issuer->getEnvironment(),
                'emitter_rnc' => $issuer->getEmitterRnc(),
                'emitter_name' => $issuer->getEmitterName(),
                'has_certificate' => $issuer->hasActiveCertificate(),
            ],
        ]);
    }

    /**
     * Crea una nueva factura / comprobante fiscal desde el formulario 1:1 de Bills.
     */
    public function store(
        Request $request,
        EcfManagerService $ecfManager,
        IssuerConfigInterface $issuer
    ): RedirectResponse {
        $validated = $request->validate([
            'student_id' => 'nullable|integer|exists:students,id',
            'client_name' => 'nullable|string|max:255',
            'client_rnc' => 'nullable|string|max:30',
            'issue_date' => 'required|date',
            'due_date' => 'nullable|date',
            'status' => 'required|string|in:paid,pending,draft',
            'ecf_type' => 'nullable|string',
            'notes' => 'nullable|string|max:1000',
            'discount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $subtotal = 0.0;
            $taxAmount = 0.0;
            $descriptions = [];
            $totalQty = 0;

            foreach ($validated['items'] as $item) {
                $qty = (float) $item['quantity'];
                $price = (float) $item['unit_price'];
                $rate = (float) ($item['tax_rate'] ?? 0);
                $lineSub = $qty * $price;
                $lineTax = $lineSub * ($rate / 100.0);
                $subtotal += $lineSub;
                $taxAmount += $lineTax;
                $totalQty += (int) max(1, round($qty));
                $descriptions[] = trim($item['description']);
            }

            $discount = (float) ($validated['discount'] ?? 0);
            $total = max(0.01, ($subtotal - $discount) + $taxAmount);
            $conceptLabel = implode(', ', array_slice($descriptions, 0, 3));

            $student = !empty($validated['student_id'])
                ? \App\Models\Student::find($validated['student_id'])
                : null;

            $buyerName = $validated['client_name']
                ?: ($student ? trim($student->first_name . ' ' . $student->last_name) : 'Consumidor Final');
            $buyerRnc = $validated['client_rnc']
                ?: ($student ? ($student->cedula ?? '') : '');

            $statusMap = [
                'paid' => 'Pagado',
                'pending' => 'Pendiente',
                'draft' => 'Pendiente',
            ];

            $ncf = null;
            $ncfType = null;
            $securityCode = null;
            $ncfExpiration = null;
            $dgiiStatus = null;

            if (!empty($validated['ecf_type'])) {
                $typeInt = (int) preg_replace('/\D/', '', (string) $validated['ecf_type']) ?: 32;
                $issued = $ecfManager->issueDocument([
                    'ecf_type' => $typeInt,
                    'issue_date' => $validated['issue_date'],
                    'subtotal' => $subtotal,
                    'discount_amount' => $discount,
                    'tax_amount' => $taxAmount,
                    'total' => $total,
                    'buyer_rnc' => (string) $buyerRnc,
                    'buyer_name' => (string) $buyerName,
                    'items' => array_map(fn ($it) => [
                        'description' => $it['description'],
                        'quantity' => (float) $it['quantity'],
                        'unit_price' => (float) $it['unit_price'],
                        'amount' => (float) $it['quantity'] * (float) $it['unit_price'],
                        'is_exempt' => ((float) ($it['tax_rate'] ?? 0)) <= 0,
                        'tax_rate' => (float) ($it['tax_rate'] ?? 0),
                    ], $validated['items']),
                ], $issuer, false);

                $ncf = $issued['ncf'];
                $ncfType = $issued['ncf_type'];
                $securityCode = $issued['security_code'];
                $ncfExpiration = $issued['ncf_expiration'];
                $dgiiStatus = $issued['dgii_status'] ?? 'issued_local';
            }

            $payment = new Payment([
                'student_id' => $student?->id,
                'user_id' => auth()->id(),
                'amount' => $total,
                'original_amount' => $subtotal,
                'discount_amount' => $discount,
                'currency' => 'DOP',
                'status' => $statusMap[$validated['status']] ?? 'Pagado',
                'gateway' => 'Facturación Bills',
                'transaction_id' => $ncf ?: ('FAC-' . strtoupper(substr(uniqid(), -6))),
                'ncf' => $ncf,
                'ncf_type' => $ncfType,
                'security_code' => $securityCode,
                'ncf_expiration' => $ncfExpiration,
                'dgii_status' => $dgiiStatus,
                'rnc_client' => $buyerRnc,
                'company_name' => $buyerName,
                'quantity' => max(1, $totalQty),
                'concept_label' => $conceptLabel,
                'notes' => $validated['notes'] ?? null,
                'due_date' => $validated['due_date'] ?? $validated['issue_date'],
            ]);
            $payment->saveQuietly();

            DB::commit();

            return redirect('/admin/finance/invoices')->with('success', 'Factura ' . ($ncf ?: $payment->transaction_id) . ' creada exitosamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al crear la factura: ' . $e->getMessage());
        }
    }

    /**
     * Emite una Nota de Crédito Electrónica (E34 / B04) referenciando una factura/cobro existente.
     */
    public function issueCreditNote(
        Request $request,
        Payment $payment,
        EcfManagerService $ecfManager,
        IssuerConfigInterface $issuer
    ): RedirectResponse {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
            'modification_code' => 'required|in:1,2,3',
            'amount' => 'nullable|numeric|min:0.01',
        ]);

        if (empty($payment->ncf)) {
            return redirect()->back()->with('error', 'La factura seleccionada no posee un NCF activo para aplicar Nota de Crédito.');
        }

        $creditAmount = isset($validated['amount']) && (float) $validated['amount'] > 0
            ? min((float) $validated['amount'], (float) $payment->amount)
            : (float) $payment->amount;

        try {
            DB::beginTransaction();

            $buyerRnc = $payment->rnc_client ?: ($payment->student->cedula ?? '');
            $buyerName = $payment->company_name
                ?: ($payment->student ? trim($payment->student->first_name . ' ' . $payment->student->last_name) : 'Consumidor Final');
            $conceptName = 'Nota de Crédito (Modifica ' . $payment->ncf . '): ' . $validated['reason'];

            $issued = $ecfManager->issueDocument([
                'ecf_type' => 34,
                'issue_date' => now()->format('Y-m-d'),
                'subtotal' => $creditAmount,
                'discount_amount' => 0.00,
                'tax_amount' => 0.00,
                'total' => $creditAmount,
                'buyer_rnc' => (string) $buyerRnc,
                'buyer_name' => (string) $buyerName,
                'ncf_modificado' => (string) $payment->ncf,
                'codigo_modificacion' => (int) $validated['modification_code'],
                'razon_modificacion' => $validated['reason'],
                'items' => [
                    [
                        'description' => $conceptName,
                        'quantity' => 1,
                        'unit_price' => $creditAmount,
                        'amount' => $creditAmount,
                        'is_exempt' => true,
                        'tax_rate' => 0.0,
                    ],
                ],
            ], $issuer, false);

            // Crear registro de Nota de Crédito en Payments
            $creditNote = new Payment([
                'student_id' => $payment->student_id,
                'enrollment_id' => $payment->enrollment_id,
                'payment_concept_id' => $payment->payment_concept_id,
                'user_id' => auth()->id(),
                'amount' => $creditAmount,
                'original_amount' => $creditAmount,
                'discount_amount' => 0,
                'currency' => $payment->currency ?: 'DOP',
                'status' => 'Nota de Crédito',
                'gateway' => $payment->gateway ?: 'Nota de Crédito DGII',
                'transaction_id' => 'NC-' . $issued['ncf'],
                'ncf' => $issued['ncf'],
                'ncf_type' => '34',
                'security_code' => $issued['security_code'],
                'ncf_expiration' => $issued['ncf_expiration'],
                'dgii_status' => $issued['dgii_status'] ?? 'issued_local',
                'dgii_track_id' => $issued['dgii_track_id'] ?? null,
                'rnc_client' => $buyerRnc,
                'company_name' => $buyerName,
                'ncf_type_requested' => '34',
                'quantity' => 1,
                'concept_label' => $conceptName,
                'notes' => "NCF Modificado: {$payment->ncf} | Código DGII: {$validated['modification_code']} | Motivo: {$validated['reason']}",
            ]);
            $creditNote->saveQuietly();

            // Si el código de modificación es 1 (Anula el NCF Modificado), dejamos constancia en la factura original
            $updatedNotes = trim(($payment->notes ? $payment->notes . ' | ' : '') . "Nota de Crédito aplicada: {$issued['ncf']}");
            $payment->notes = $updatedNotes;
            $payment->saveQuietly();

            DB::commit();

            return redirect()->back()->with('success', "Nota de Crédito {$issued['ncf']} emitida exitosamente contra el comprobante {$payment->ncf}.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al emitir Nota de Crédito: ' . $e->getMessage());
        }
    }

    /**
     * Anula un comprobante para el Formato 608 de la DGII.
     */
    public function voidInvoice(Request $request, Payment $payment): RedirectResponse
    {
        $validated = $request->validate([
            'void_type_608' => 'required|string|size:2',
            'reason' => 'required|string|max:500',
        ]);

        $typeLabels = [
            '01' => '01 - Deterioro de Factura Pre-impresa',
            '02' => '02 - Errores de Impresión (Factura Pre-impresa)',
            '03' => '03 - Impresión Defectuosa',
            '04' => '04 - Corrección de la Información',
            '05' => '05 - Cambio de Productos',
            '06' => '06 - Devolución de Productos',
            '07' => '07 - Omisión de Productos',
            '08' => '08 - Errores en Secuencia de NCF',
            '09' => '09 - Por Cese de Operaciones',
            '10' => '10 - Pérdida o Hurto de Talonarios',
        ];

        $typeDesc = $typeLabels[$validated['void_type_608']] ?? $validated['void_type_608'];

        $payment->status = 'Anulado';
        $payment->dgii_status = 'voided_608';
        $payment->notes = trim(($payment->notes ? $payment->notes . ' | ' : '') . "[ANULADO 608 Tipo {$typeDesc}] Motivo: {$validated['reason']}");
        $payment->saveQuietly();

        return redirect()->back()->with('success', "Comprobante " . ($payment->ncf ?: "#{$payment->id}") . " anulado y registrado para el Formato 608.");
    }

    /**
     * Emite e-CF (E31/E32) manualmente para un cobro pagado que no tenía comprobante asignado.
     */
    public function emitFiscalNow(
        Request $request,
        Payment $payment,
        EcfManagerService $ecfManager,
        IssuerConfigInterface $issuer
    ): RedirectResponse {
        if (!empty($payment->ncf)) {
            return redirect()->back()->with('error', "Este documento ya posee el comprobante {$payment->ncf}.");
        }

        $requestedType = (int) $request->input('ecf_type', 32);
        if (!in_array($requestedType, [31, 32, 44, 45], true)) {
            $requestedType = 32;
        }

        try {
            $amount = (float) ($payment->amount ?? 0);
            $buyerRnc = $payment->rnc_client ?: ($payment->student->cedula ?? '');
            $buyerName = $payment->company_name
                ?: ($payment->student ? trim($payment->student->first_name . ' ' . $payment->student->last_name) : 'Consumidor Final');
            $conceptName = $payment->concept_label
                ?: ($payment->paymentConcept->name ?? null)
                ?: ($payment->notes ?: 'Servicio Educativo');

            $issued = $ecfManager->issueDocument([
                'ecf_type' => $requestedType,
                'issue_date' => now()->format('Y-m-d'),
                'subtotal' => $amount,
                'discount_amount' => (float) ($payment->discount_amount ?? 0),
                'tax_amount' => 0.00,
                'total' => $amount,
                'buyer_rnc' => (string) $buyerRnc,
                'buyer_name' => (string) $buyerName,
                'items' => [
                    [
                        'description' => $conceptName,
                        'quantity' => max(1, (int) ($payment->quantity ?? 1)),
                        'unit_price' => $amount / max(1, (int) ($payment->quantity ?? 1)),
                        'amount' => $amount,
                        'is_exempt' => true,
                        'tax_rate' => 0.0,
                    ],
                ],
            ], $issuer, false);

            $payment->ncf = $issued['ncf'];
            $payment->ncf_type = $issued['ncf_type'];
            $payment->security_code = $issued['security_code'];
            $payment->ncf_expiration = $issued['ncf_expiration'];
            $payment->dgii_status = $issued['dgii_status'] ?? 'issued_local';
            $payment->saveQuietly();

            return redirect()->back()->with('success', "Comprobante Fiscal {$issued['ncf']} generado exitosamente.");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'No se pudo emitir el comprobante: ' . $e->getMessage());
        }
    }

    /**
     * Descarga el XML estándar DGII e-CF (firmado si existe en disco, o construido al vuelo por XmlBuilderService).
     */
    public function downloadXml(
        Payment $payment,
        XmlBuilderService $xmlBuilder,
        IssuerConfigInterface $issuer
    ): HttpResponse {
        $ncf = (string) ($payment->ncf ?: ('E32' . str_pad((string) $payment->id, 10, '0', STR_PAD_LEFT)));
        $signedPath = storage_path('app/signed_ecf/' . $ncf . '.xml');

        if (file_exists($signedPath)) {
            $xmlContent = (string) file_get_contents($signedPath);
        } else {
            $amount = (float) ($payment->amount ?? 0);
            $buyerRnc = $payment->rnc_client ?: ($payment->student->cedula ?? '');
            $buyerName = $payment->company_name
                ?: ($payment->student ? trim($payment->student->first_name . ' ' . $payment->student->last_name) : 'Consumidor Final');
            $conceptName = $payment->concept_label
                ?: ($payment->paymentConcept->name ?? null)
                ?: ($payment->notes ?: 'Servicio Educativo');
            $typeCode = (int) ($payment->ncf_type ?: (strlen($ncf) >= 3 ? substr($ncf, 1, 2) : 32));

            $xmlContent = $xmlBuilder->buildXml([
                'ncf' => $ncf,
                'ecf_type' => $typeCode ?: 32,
                'issue_date' => $payment->created_at ? $payment->created_at->format('Y-m-d') : date('Y-m-d'),
                'expiration_date' => $payment->ncf_expiration ? $payment->ncf_expiration->format('Y-m-d') : date('Y-12-31'),
                'subtotal' => $amount,
                'discount_amount' => (float) ($payment->discount_amount ?? 0),
                'tax_amount' => 0.00,
                'total' => $amount,
                'buyer_rnc' => (string) $buyerRnc,
                'buyer_name' => (string) $buyerName,
                'items' => [
                    [
                        'description' => $conceptName,
                        'quantity' => max(1, (int) ($payment->quantity ?? 1)),
                        'unit_price' => $amount / max(1, (int) ($payment->quantity ?? 1)),
                        'amount' => $amount,
                        'is_exempt' => true,
                        'tax_rate' => 0.0,
                    ],
                ],
            ], $issuer);
        }

        return response($xmlContent, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $ncf . '.xml"',
        ]);
    }
}
