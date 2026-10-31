<?php

namespace App\Http\Controllers;

use App\Models\AccountingAccount;
use App\Models\AccountingEntry;
use App\Models\AccountingEntryLine;
use App\Models\AccountingJournal;
use App\Models\Setting;
use App\Services\AccountingEngine;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class AccountingController extends Controller
{
    // ── 1. CATÁLOGO DE CUENTAS ──
    public function chartOfAccounts(Request $request): Response
    {
        $search = $request->input('search', '');
        $typeFilter = $request->input('type_filter', '');

        $query = AccountingAccount::with('parent')->orderBy('code', 'asc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($typeFilter) {
            $query->where('type', $typeFilter);
        }

        $accounts = $query->paginate(25)->withQueryString()->through(function ($a) {
            return [
                'id' => $a->id,
                'parent_id' => $a->parent_id,
                'parent_code' => $a->parent ? $a->parent->code : null,
                'parent_name' => $a->parent ? $a->parent->name : null,
                'code' => $a->code,
                'name' => $a->name,
                'type' => $a->type,
                'is_active' => (bool) $a->is_active,
                'has_children' => $a->children()->count() > 0,
                'has_entries' => $a->entryLines()->count() > 0,
            ];
        });

        // Cuentas elegibles como padres
        $parentCandidates = AccountingAccount::where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'type']);

        return Inertia::render('Accounting/ChartOfAccounts', [
            'accounts' => $accounts,
            'parentCandidates' => $parentCandidates,
            'filters' => [
                'search' => $search,
                'type_filter' => $typeFilter,
            ],
        ]);
    }

    public function storeAccount(Request $request): RedirectResponse
    {
        $id = $request->input('id');

        $validated = $request->validate([
            'id' => 'nullable|integer|exists:accounting_accounts,id',
            'code' => 'required|string|max:50|unique:accounting_accounts,code,' . $id,
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,revenue,expense,cost',
            'parent_id' => 'nullable|exists:accounting_accounts,id',
            'is_active' => 'nullable|boolean',
        ]);

        if (!empty($validated['parent_id'])) {
            $parent = AccountingAccount::find($validated['parent_id']);
            if ($parent) {
                $validated['type'] = $parent->type;
            }
        }

        $validated['is_active'] = (bool) ($validated['is_active'] ?? true);

        AccountingAccount::updateOrCreate(
            ['id' => $id],
            [
                'parent_id' => $validated['parent_id'] ?: null,
                'code' => $validated['code'],
                'name' => $validated['name'],
                'type' => $validated['type'],
                'is_active' => $validated['is_active'],
            ]
        );

        $action = $id ? 'actualizada' : 'creada';
        return redirect()->back()->with('success', "Cuenta contable {$action} exitosamente.");
    }

    public function destroyAccount($id): RedirectResponse
    {
        $account = AccountingAccount::findOrFail($id);

        if ($account->children()->count() > 0) {
            return redirect()->back()->with('error', 'No se puede eliminar una cuenta que tiene subcuentas asociadas.');
        }

        if ($account->entryLines()->count() > 0) {
            return redirect()->back()->with('error', 'No se puede eliminar una cuenta que tiene transacciones en el Libro Mayor. Considere desactivarla.');
        }

        $account->delete();
        return redirect()->back()->with('success', 'Cuenta contable eliminada correctamente.');
    }

    // ── 2. ASIENTO CONTABLE MANUAL ──
    public function manualEntry(Request $request): Response
    {
        $journals = AccountingJournal::orderBy('name')->get();
        $accounts = AccountingAccount::where('is_active', true)->orderBy('code')->get(['id', 'code', 'name', 'type']);

        return Inertia::render('Accounting/JournalEntry', [
            'journals' => $journals,
            'accounts' => $accounts,
            'defaultDate' => date('Y-m-d'),
        ]);
    }

    public function storeManualEntry(Request $request, AccountingEngine $engine): RedirectResponse
    {
        $validated = $request->validate([
            'journal_id' => 'required|exists:accounting_journals,id',
            'date' => 'required|date',
            'description' => 'required|string|max:255',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:accounting_accounts,id',
            'lines.*.description' => 'required|string|max:255',
            'lines.*.debit' => 'required|numeric|min:0',
            'lines.*.credit' => 'required|numeric|min:0',
        ]);

        $totalDebit = 0;
        $totalCredit = 0;
        $engineLines = [];

        foreach ($validated['lines'] as $line) {
            $deb = (float) ($line['debit'] ?? 0);
            $cred = (float) ($line['credit'] ?? 0);

            if ($deb > 0 && $cred > 0) {
                return redirect()->back()->with('error', 'Una línea individual no puede tener débito y crédito simultáneamente.');
            }

            if ($deb > 0 || $cred > 0) {
                $totalDebit += $deb;
                $totalCredit += $cred;
                $engineLines[] = [
                    'account_id' => $line['account_id'],
                    'description' => $line['description'],
                    'debit' => $deb,
                    'credit' => $cred,
                ];
            }
        }

        if (round($totalDebit, 2) <= 0 || round($totalCredit, 2) <= 0) {
            return redirect()->back()->with('error', 'El asiento debe tener montos válidos mayores a cero.');
        }

        if (round($totalDebit, 2) !== round($totalCredit, 2)) {
            return redirect()->back()->with('error', "El asiento está descuadrado. Débitos (RD$ {$totalDebit}) y Créditos (RD$ {$totalCredit}) deben ser iguales.");
        }

        try {
            $entry = $engine->makeEntry(
                (int) $validated['journal_id'],
                $validated['date'],
                $validated['description'],
                null,
                $engineLines,
                'posted'
            );

            return redirect()->route('admin.finance.ledger')->with('success', "Asiento Manual #{$entry->id} registrado y contabilizado en el Libro Mayor.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al contabilizar asiento: ' . $e->getMessage());
        }
    }

    // ── 3. LIBRO MAYOR ──
    public function ledger(Request $request): Response
    {
        $journalId = $request->input('journal_id', '');
        $dateFrom = $request->input('date_from', '');
        $dateTo = $request->input('date_to', '');
        $status = $request->input('status', 'posted');

        $journals = AccountingJournal::orderBy('name')->get();

        $query = AccountingEntry::with(['journal', 'lines.account', 'reference'])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc');

        if ($journalId) {
            $query->where('accounting_journal_id', $journalId);
        }

        if ($dateFrom) {
            $query->whereDate('date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('date', '<=', $dateTo);
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $entries = $query->paginate(15)->withQueryString()->through(function ($e) {
            $totalDebit = 0;
            $totalCredit = 0;
            $lines = $e->lines->map(function ($l) use (&$totalDebit, &$totalCredit) {
                $deb = (float) $l->debit;
                $cred = (float) $l->credit;
                $totalDebit += $deb;
                $totalCredit += $cred;
                return [
                    'id' => $l->id,
                    'account_code' => $l->account ? $l->account->code : 'N/A',
                    'account_name' => $l->account ? $l->account->name : 'N/A',
                    'description' => $l->description,
                    'debit' => $deb,
                    'credit' => $cred,
                ];
            });

            return [
                'id' => $e->id,
                'number' => str_pad($e->id, 5, '0', STR_PAD_LEFT),
                'date' => $e->date ? $e->date->format('Y-m-d') : '',
                'date_formatted' => $e->date ? $e->date->format('d/m/Y') : '',
                'journal_name' => $e->journal ? $e->journal->name : 'General',
                'description' => $e->description,
                'reference_type' => $e->reference_type ? class_basename($e->reference_type) : null,
                'reference_id' => $e->reference_id,
                'status' => $e->status,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'lines' => $lines,
            ];
        });

        return Inertia::render('Accounting/Ledger', [
            'entries' => $entries,
            'journals' => $journals,
            'filters' => [
                'journal_id' => $journalId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'status' => $status,
            ],
        ]);
    }

    // ── 4. ESTADOS FINANCIEROS ──
    protected function buildStatementsPayload(Request $request): array
    {
        $reportType = $request->input('report_type', 'balance_sheet');
        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::now()->endOfMonth()->format('Y-m-d'));

        if ($reportType === 'income_statement') {
            $revenues = $this->getAccountBalancesByType('revenue', $dateFrom, $dateTo, true);
            $expenses = $this->getAccountBalancesByType('expense', $dateFrom, $dateTo, true);

            $totalRevenue = (float) $revenues->sum('balance');
            $totalExpense = (float) $expenses->sum('balance');
            $netIncome = $totalRevenue - $totalExpense;

            $reportData = [
                'revenues' => $revenues,
                'expenses' => $expenses,
                'total_revenue' => $totalRevenue,
                'total_expense' => $totalExpense,
                'net_income' => $netIncome,
            ];
        } else {
            $assets = $this->getAccountBalancesByType('asset', null, $dateTo, false);
            $liabilities = $this->getAccountBalancesByType('liability', null, $dateTo, false);
            $equity = $this->getAccountBalancesByType('equity', null, $dateTo, false);

            $totalAssets = (float) $assets->sum('balance');
            $totalLiabilities = (float) $liabilities->sum('balance');
            $totalEquityBase = (float) $equity->sum('balance');
            $historicalNetIncome = $this->calculateHistoricalNetIncome($dateTo);
            $totalEquity = $totalEquityBase + $historicalNetIncome;

            $reportData = [
                'assets' => $assets,
                'liabilities' => $liabilities,
                'equity' => $equity,
                'total_assets' => $totalAssets,
                'total_liabilities' => $totalLiabilities,
                'total_equity_base' => $totalEquityBase,
                'historical_net_income' => $historicalNetIncome,
                'total_equity' => $totalEquity,
                'total_liabilities_and_equity' => $totalLiabilities + $totalEquity,
            ];
        }

        return [
            'reportType' => $reportType,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'report' => $reportData,
        ];
    }

    public function statements(Request $request): Response
    {
        return Inertia::render('Accounting/Statements', $this->buildStatementsPayload($request));
    }

    public function exportStatementsPdf(Request $request): HttpResponse
    {
        $payload = $this->buildStatementsPayload($request);
        $reportType = $payload['reportType'];

        $pdf = Pdf::loadView('reports.statements-pdf', $payload)
            ->setPaper('a4', 'portrait');

        $fileName = "estado_financiero_{$reportType}_" . now()->format('Ymd_His') . '.pdf';

        if ($request->boolean('download')) {
            return $pdf->download($fileName);
        }

        return $pdf->stream($fileName);
    }

    private function getAccountBalancesByType(string $type, ?string $dateFrom, ?string $dateTo, bool $filterRange)
    {
        $query = AccountingEntryLine::select(
            'accounting_accounts.id',
            'accounting_accounts.code',
            'accounting_accounts.name',
            'accounting_accounts.type',
            DB::raw('COALESCE(SUM(accounting_entry_lines.debit), 0) as total_debits'),
            DB::raw('COALESCE(SUM(accounting_entry_lines.credit), 0) as total_credits')
        )
        ->join('accounting_accounts', 'accounting_entry_lines.accounting_account_id', '=', 'accounting_accounts.id')
        ->join('accounting_entries', 'accounting_entry_lines.accounting_entry_id', '=', 'accounting_entries.id')
        ->where('accounting_entries.status', 'posted')
        ->where('accounting_accounts.type', $type);

        if ($filterRange) {
            if ($dateFrom) {
                $query->whereDate('accounting_entries.date', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->whereDate('accounting_entries.date', '<=', $dateTo);
            }
        } else {
            if ($dateTo) {
                $query->whereDate('accounting_entries.date', '<=', $dateTo);
            }
        }

        $accounts = $query->groupBy('accounting_accounts.id', 'accounting_accounts.code', 'accounting_accounts.name', 'accounting_accounts.type')
            ->orderBy('accounting_accounts.code')
            ->get();

        return $accounts->map(function ($acc) {
            $debits = (float) $acc->total_debits;
            $credits = (float) $acc->total_credits;
            $balance = 0.0;

            if (in_array($acc->type, ['asset', 'expense', 'cost'])) {
                $balance = $debits - $credits;
            } else {
                $balance = $credits - $debits;
            }

            return [
                'id' => $acc->id,
                'code' => $acc->code,
                'name' => $acc->name,
                'type' => $acc->type,
                'total_debits' => $debits,
                'total_credits' => $credits,
                'balance' => $balance,
            ];
        })->filter(function ($acc) {
            return round($acc['balance'], 2) != 0;
        })->values();
    }

    private function calculateHistoricalNetIncome(?string $dateTo): float
    {
        $query = AccountingEntryLine::select(
            'accounting_accounts.type',
            DB::raw('COALESCE(SUM(accounting_entry_lines.debit), 0) as total_debits'),
            DB::raw('COALESCE(SUM(accounting_entry_lines.credit), 0) as total_credits')
        )
        ->join('accounting_accounts', 'accounting_entry_lines.accounting_account_id', '=', 'accounting_accounts.id')
        ->join('accounting_entries', 'accounting_entry_lines.accounting_entry_id', '=', 'accounting_entries.id')
        ->where('accounting_entries.status', 'posted')
        ->whereIn('accounting_accounts.type', ['revenue', 'expense']);

        if ($dateTo) {
            $query->whereDate('accounting_entries.date', '<=', $dateTo);
        }

        $totals = $query->groupBy('accounting_accounts.type')->get();

        $net = 0.0;
        foreach ($totals as $t) {
            $deb = (float) $t->total_debits;
            $cred = (float) $t->total_credits;
            if ($t->type === 'revenue') {
                $net += ($cred - $deb);
            } elseif ($t->type === 'expense') {
                $net -= ($deb - $cred);
            }
        }

        return $net;
    }

    // ── 5. CIERRE DE PERÍODO ──
    public function periodClosing(Request $request): Response
    {
        $lockDate = Setting::where('key', 'accounting_lock_date')->value('value');

        return Inertia::render('Accounting/PeriodClosing', [
            'lockDate' => $lockDate,
        ]);
    }

    public function storePeriodClosing(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'lock_date' => 'nullable|date',
        ]);

        Setting::updateOrCreate(
            ['key' => 'accounting_lock_date'],
            [
                'value' => $validated['lock_date'] ?? null,
                'type' => 'date',
                'description' => 'Fecha de Cierre Contable. No se permitirán asientos con fecha igual o anterior a esta.',
            ]
        );

        $msg = $validated['lock_date']
            ? "El período contable ha sido cerrado exitosamente hasta el {$validated['lock_date']}."
            : 'El bloqueo de período contable ha sido liberado.';

        return redirect()->back()->with('success', $msg);
    }
}
