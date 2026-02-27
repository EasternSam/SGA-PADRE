<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\AccountingAccount;
use App\Models\AccountingEntryLine;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinancialStatements extends Component
{
    public $report_type = 'balance_sheet'; // 'balance_sheet' o 'income_statement'
    public $date_from;
    public $date_to;

    public function mount()
    {
        // Por defecto, mostrar el mes actual completo
        $this->date_from = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->date_to = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function getIncomeStatementProperty()
    {
        // Ingresos (Revenue) y Gastos (Expense)
        $revenues = $this->getAccountBalancesByType('revenue');
        $expenses = $this->getAccountBalancesByType('expense');

        $totalRevenue = $revenues->sum('balance');
        $totalExpense = $expenses->sum('balance');
        $netIncome = $totalRevenue - $totalExpense; // Utilidad / Pérdida

        return [
            'revenues' => $revenues,
            'expenses' => $expenses,
            'total_revenue' => $totalRevenue,
            'total_expense' => $totalExpense,
            'net_income' => $netIncome,
        ];
    }

    public function getBalanceSheetProperty()
    {
        // Activos (Assets), Pasivos (Liabilities), Capital (Equity)
        // Capital se ajusta con la Utilidad Neta del período + períodos anteriores no distribuidos
        $assets = $this->getAccountBalancesByType('asset');
        $liabilities = $this->getAccountBalancesByType('liability');
        $equity = $this->getAccountBalancesByType('equity');

        $totalAssets = $assets->sum('balance');
        $totalLiabilities = $liabilities->sum('balance');
        
        // Calcular la Utilidad Neta Histórica (Todo el P&L desde el inicio de los tiempos hasta date_to)
        $historicalNetIncome = $this->calculateHistoricalNetIncome();
        
        $totalEquityBase = $equity->sum('balance');
        $totalEquity = $totalEquityBase + $historicalNetIncome; // Capital + Utilidad Retenida

        return [
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

    private function getAccountBalancesByType($type)
    {
        // Query base para sumar débitos y créditos por cuenta
        $linesQuery = AccountingEntryLine::select(
            'accounting_accounts.id',
            'accounting_accounts.code',
            'accounting_accounts.name',
            'accounting_accounts.type',
            DB::raw('SUM(accounting_entry_lines.debit) as total_debits'),
            DB::raw('SUM(accounting_entry_lines.credit) as total_credits')
        )
        ->join('accounting_accounts', 'accounting_entry_lines.accounting_account_id', '=', 'accounting_accounts.id')
        ->join('accounting_entries', 'accounting_entry_lines.accounting_entry_id', '=', 'accounting_entries.id')
        ->where('accounting_entries.status', 'posted')
        ->where('accounting_accounts.type', $type);

        // Si es Estado de Resultados, filtramos estrictamente por rango de fechas (flujo)
        if ($this->report_type === 'income_statement') {
            if ($this->date_from) {
                $linesQuery->whereDate('accounting_entries.date', '>=', $this->date_from);
            }
            if ($this->date_to) {
                $linesQuery->whereDate('accounting_entries.date', '<=', $this->date_to);
            }
        } else {
            // Si es Balance General, filtramos acumulado HASTA date_to (snapshot financiero)
            if ($this->date_to) {
                $linesQuery->whereDate('accounting_entries.date', '<=', $this->date_to);
            }
        }

        $accounts = $linesQuery->groupBy('accounting_accounts.id', 'accounting_accounts.code', 'accounting_accounts.name', 'accounting_accounts.type')
                               ->orderBy('accounting_accounts.code')
                               ->get();

        // Calcular el balance real basado en su naturaleza
        return $accounts->map(function ($account) {
            $balance = 0;
            switch ($account->type) {
                case 'asset':
                case 'expense':
                    // Naturaleza Deudora: Aumenta con Débitos, disminuye con Créditos
                    $balance = $account->total_debits - $account->total_credits;
                    break;
                case 'liability':
                case 'equity':
                case 'revenue':
                    // Naturaleza Acreedora: Aumenta con Créditos, disminuye con Débitos
                    $balance = $account->total_credits - $account->total_debits;
                    break;
            }
            $account->balance = $balance;
            return $account;
        })->filter(function ($account) {
            return round($account->balance, 2) != 0; // Excluir cuentas con balance 0
        });
    }

    private function calculateHistoricalNetIncome()
    {
        // Para que el balance cuadre (Activo = Pasivo + Capital), el Capital debe incluir 
        // la Utilidad del Ejercicio. Esto se calcula sumando todos los Ingresos - Gastos históricos
        // hasta la fecha de corte ($this->date_to).
        
        $linesQuery = AccountingEntryLine::select(
            'accounting_accounts.type',
            DB::raw('SUM(accounting_entry_lines.debit) as total_debits'),
            DB::raw('SUM(accounting_entry_lines.credit) as total_credits')
        )
        ->join('accounting_accounts', 'accounting_entry_lines.accounting_account_id', '=', 'accounting_accounts.id')
        ->join('accounting_entries', 'accounting_entry_lines.accounting_entry_id', '=', 'accounting_entries.id')
        ->where('accounting_entries.status', 'posted')
        ->whereIn('accounting_accounts.type', ['revenue', 'expense']);
        
        if ($this->date_to) {
            $linesQuery->whereDate('accounting_entries.date', '<=', $this->date_to);
        }

        $totals = $linesQuery->groupBy('accounting_accounts.type')->get();

        $netIncome = 0;
        foreach ($totals as $total) {
            if ($total->type === 'revenue') {
                $netIncome += ($total->total_credits - $total->total_debits); // Ingresos aumentan con crédito
            } elseif ($total->type === 'expense') {
                $netIncome -= ($total->total_debits - $total->total_credits); // Gastos aumentan con débito, pero REducen utilidad
            }
        }

        return $netIncome;
    }

    public function render()
    {
        $data = [];
        if ($this->report_type === 'balance_sheet') {
            $data = ['report' => $this->balance_sheet];
        } else {
            $data = ['report' => $this->income_statement];
        }

        return view('livewire.admin.financial-statements', $data)
               ->layout('layouts.dashboard');
    }
}
