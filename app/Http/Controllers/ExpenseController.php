<?php

namespace App\Http\Controllers;

use App\Models\AccountingAccount;
use App\Models\AccountingJournal;
use App\Models\Expense;
use App\Models\Supplier;
use App\Services\AccountingEngine;
use App\Services\DgiiRncLookupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ExpenseController extends Controller
{
    protected array $expenseTypes606 = [
        '01' => 'Gastos en personal',
        '02' => 'Gastos por trabajos, suministros y servicios',
        '03' => 'Arrendamientos',
        '04' => 'Gastos de activos fijos',
        '05' => 'Gastos de representación',
        '06' => 'Otras deducciones admitidas',
        '07' => 'Gastos financieros',
        '08' => 'Gastos extraordinarios',
        '09' => 'Compras/Gastos parte del costo',
        '10' => 'Adquisiciones de activos',
        '11' => 'Gastos de seguros',
    ];

    public function index(Request $request): Response
    {
        $search = $request->input('search', '');
        $statusFilter = $request->input('status_filter', '');

        $query = Expense::query()->with(['supplier', 'expenseAccount', 'paymentAccount']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('supplier', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                       ->orWhere('rnc_cedula', 'like', "%{$search}%");
                })
                ->orWhere('reference_number', 'like', "%{$search}%")
                ->orWhere('ncf', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        // KPIs
        $kpis = [
            'totalExpenses' => (float) Expense::sum('total_amount'),
            'totalPaid' => (float) Expense::where('status', 'paid')->sum('total_amount'),
            'totalPending' => (float) Expense::where('status', 'pending')->sum('total_amount'),
            'pendingCount' => (int) Expense::where('status', 'pending')->count(),
        ];

        $expenses = $query->latest('expense_date')->paginate(15)->withQueryString()->through(function ($e) {
            return [
                'id' => $e->id,
                'supplier_id' => $e->supplier_id,
                'supplier_name' => $e->supplier ? $e->supplier->name : 'N/A',
                'supplier_rnc' => $e->supplier ? $e->supplier->rnc_cedula : '',
                'reference_number' => $e->reference_number,
                'ncf' => $e->ncf,
                'expense_type_606' => $e->expense_type_606,
                'expense_date' => $e->expense_date ? $e->expense_date->format('Y-m-d') : '',
                'expense_date_formatted' => $e->expense_date ? $e->expense_date->format('d/m/Y') : '',
                'due_date' => $e->due_date ? $e->due_date->format('Y-m-d') : '',
                'due_date_formatted' => $e->due_date ? $e->due_date->format('d/m/Y') : '',
                'expense_account_id' => $e->expense_account_id,
                'expense_account_name' => $e->expenseAccount ? ($e->expenseAccount->code . ' - ' . $e->expenseAccount->name) : 'N/A',
                'payment_account_id' => $e->payment_account_id,
                'payment_account_name' => $e->paymentAccount ? ($e->paymentAccount->code . ' - ' . $e->paymentAccount->name) : 'N/A',
                'subtotal' => (float) $e->subtotal,
                'itbis_amount' => (float) $e->itbis_amount,
                'itbis_retained' => (float) $e->itbis_retained,
                'isr_retained' => (float) $e->isr_retained,
                'total_amount' => (float) $e->total_amount,
                'status' => $e->status,
                'description' => $e->description,
            ];
        });

        $expenseAccounts = AccountingAccount::where('type', 'expense')
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

        $paymentAccounts = AccountingAccount::whereIn('type', ['asset', 'liability'])
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'type']);

        $assetAccounts = AccountingAccount::where('type', 'asset')
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

        $journals = AccountingJournal::orderBy('name')->get();

        return Inertia::render('Finance/Expenses/Index', [
            'expenses' => $expenses,
            'kpis' => $kpis,
            'filters' => [
                'search' => $search,
                'status_filter' => $statusFilter,
            ],
            'expenseAccounts' => $expenseAccounts,
            'paymentAccounts' => $paymentAccounts,
            'assetAccounts' => $assetAccounts,
            'journals' => $journals,
            'expenseTypes606' => $this->expenseTypes606,
        ]);
    }

    public function lookupRnc(Request $request, DgiiRncLookupService $rncService): JsonResponse
    {
        $cleanRnc = preg_replace('/[^0-9]/', '', $request->input('rnc', ''));
        if (strlen($cleanRnc) < 9) {
            return response()->json(['found' => false, 'message' => 'RNC o Cédula incompleto.'], 422);
        }

        $existing = Supplier::where('rnc_cedula', $cleanRnc)->first();
        if ($existing) {
            return response()->json([
                'found' => true,
                'supplier_id' => $existing->id,
                'name' => $existing->name,
                'source' => 'local',
            ]);
        }

        $result = $rncService->lookup($cleanRnc);
        if ($result && isset($result['nombre'])) {
            return response()->json([
                'found' => true,
                'supplier_id' => null,
                'name' => $result['nombre'],
                'source' => 'dgii',
            ]);
        }

        return response()->json(['found' => false, 'message' => 'RNC no encontrado en DGII.'], 404);
    }

    public function store(Request $request, AccountingEngine $engine): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_rnc' => 'required|string',
            'supplier_name' => 'required|string|max:255',
            'reference_number' => 'nullable|string|max:100',
            'ncf' => 'nullable|string|max:19',
            'expense_type_606' => 'required|string|size:2',
            'expense_date' => 'required|date',
            'due_date' => 'nullable|date',
            'expense_account_id' => 'required|exists:accounting_accounts,id',
            'payment_account_id' => 'required|exists:accounting_accounts,id',
            'subtotal' => 'required|numeric|min:0.01',
            'itbis_amount' => 'nullable|numeric|min:0',
            'itbis_retained' => 'nullable|numeric|min:0',
            'isr_retained' => 'nullable|numeric|min:0',
            'status' => 'required|in:paid,pending,void',
            'selected_journal_id' => 'required|exists:accounting_journals,id',
            'description' => 'nullable|string|max:1000',
        ]);

        $subtotal = (float) $validated['subtotal'];
        $itbis = (float) ($validated['itbis_amount'] ?? 0);
        $retItbis = (float) ($validated['itbis_retained'] ?? 0);
        $retIsr = (float) ($validated['isr_retained'] ?? 0);
        $totalAmount = $subtotal + $itbis - $retItbis - $retIsr;

        try {
            DB::beginTransaction();

            $cleanRnc = preg_replace('/[^0-9]/', '', $validated['supplier_rnc']);
            $supplier = Supplier::firstOrCreate(
                ['rnc_cedula' => $cleanRnc],
                [
                    'name' => mb_convert_case($validated['supplier_name'], MB_CASE_TITLE, "UTF-8"),
                    'type' => strlen($cleanRnc) === 9 ? 'Juridica' : 'Fisica',
                ]
            );

            $expense = Expense::create([
                'supplier_id' => $supplier->id,
                'reference_number' => $validated['reference_number'] ?? null,
                'ncf' => $validated['ncf'] ?? null,
                'expense_type_606' => $validated['expense_type_606'],
                'expense_date' => $validated['expense_date'],
                'due_date' => $validated['due_date'] ?? null,
                'expense_account_id' => $validated['expense_account_id'],
                'payment_account_id' => $validated['payment_account_id'],
                'subtotal' => $subtotal,
                'itbis_amount' => $itbis,
                'itbis_retained' => $retItbis,
                'isr_retained' => $retIsr,
                'total_amount' => $totalAmount,
                'status' => $validated['status'],
                'description' => $validated['description'] ?? null,
            ]);

            $engine->registerExpense($expense, (int) $validated['selected_journal_id']);

            DB::commit();

            return redirect()->back()->with('success', 'Gasto registrado y contabilizado exitosamente en el Libro Mayor.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al contabilizar el gasto: ' . $e->getMessage());
        }
    }

    public function pay(Request $request, AccountingEngine $engine): RedirectResponse
    {
        $validated = $request->validate([
            'expense_id' => 'required|exists:expenses,id',
            'pay_account_origin_id' => 'required|exists:accounting_accounts,id',
            'selected_journal_id' => 'required|exists:accounting_journals,id',
        ]);

        $expense = Expense::with(['supplier', 'paymentAccount'])->findOrFail($validated['expense_id']);

        if ($expense->status !== 'pending') {
            return redirect()->back()->with('error', 'Esta factura ya se encuentra saldada o no está pendiente.');
        }

        try {
            DB::beginTransaction();

            $monto = (float) $expense->total_amount;
            $lines = [
                [
                    'account_id' => $expense->payment_account_id,
                    'debit' => $monto,
                    'credit' => 0,
                    'desc' => "Saldando CxP Factura Suplidor: " . ($expense->supplier->name ?? 'N/A'),
                ],
                [
                    'account_id' => $validated['pay_account_origin_id'],
                    'debit' => 0,
                    'credit' => $monto,
                    'desc' => "Pago de Factura (Ref: {$expense->reference_number})",
                ],
            ];

            $desc = "Pago de Factura Suplidor: " . ($expense->supplier->name ?? 'N/A');
            $engine->makeEntry((int) $validated['selected_journal_id'], date('Y-m-d'), $desc, $expense, $lines);

            $expense->update(['status' => 'paid']);

            DB::commit();

            return redirect()->back()->with('success', 'Cuenta por Pagar saldada exitosamente. Asiento generado en el Libro Mayor.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al procesar el pago: ' . $e->getMessage());
        }
    }
}
