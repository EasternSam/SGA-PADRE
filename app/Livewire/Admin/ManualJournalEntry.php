<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\AccountingAccount;
use App\Models\AccountingJournal;
use App\Services\AccountingEngine;
use Illuminate\Support\Facades\Log;

class ManualJournalEntry extends Component
{
    public $journal_id;
    public $date;
    public $description;
    public $reference_type = 'manual';
    
    // Array para almacenar las líneas del asiento
    public $lines = [];
    
    // Totales calculados en tiempo real
    public $total_debit = 0;
    public $total_credit = 0;

    public function mount()
    {
        $this->date = date('Y-m-d');
        // Inicializar con dos líneas mínimas obligatorias para la partida doble
        $this->addLine();
        $this->addLine();
    }

    public function addLine()
    {
        $this->lines[] = [
            'account_id' => '',
            'description' => '',
            'debit' => 0,
            'credit' => 0,
        ];
    }

    public function removeLine($index)
    {
        if (count($this->lines) > 2) {
            unset($this->lines[$index]);
            $this->lines = array_values($this->lines); // Reindexar
            $this->calculateTotals();
        } else {
            session()->flash('error', 'Un asiento contable requiere al menos dos líneas.');
        }
    }

    public function updatedLines()
    {
        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        $this->total_debit = 0;
        $this->total_credit = 0;

        foreach ($this->lines as $line) {
            $this->total_debit += (float) ($line['debit'] ?: 0);
            $this->total_credit += (float) ($line['credit'] ?: 0);
        }

        // Redondear a 2 decimales para evitar problemas de coma flotante
        $this->total_debit = round($this->total_debit, 2);
        $this->total_credit = round($this->total_credit, 2);
    }

    public function validateEntry()
    {
        $this->validate([
            'journal_id' => 'required|exists:accounting_journals,id',
            'date' => 'required|date',
            'description' => 'required|string|max:255',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:accounting_accounts,id',
            'lines.*.description' => 'required|string|max:255',
            'lines.*.debit' => 'required|numeric|min:0',
            'lines.*.credit' => 'required|numeric|min:0',
        ], [
            'lines.*.account_id.required' => 'La cuenta es requerida.',
            'lines.*.description.required' => 'La descripción es requerida.',
        ]);

        $this->calculateTotals();

        if ($this->total_debit <= 0 && $this->total_credit <= 0) {
            session()->flash('error', 'El asiento no puede estar en cero.');
            return false;
        }

        if ($this->total_debit !== $this->total_credit) {
            session()->flash('error', 'El asiento está descuadrado. Débitos (' . $this->total_debit . ') y Créditos (' . $this->total_credit . ') deben ser iguales.');
            return false;
        }

        // Validar que ninguna línea tenga débito y crédito al mismo tiempo mayor a cero
        foreach ($this->lines as $line) {
            if ($line['debit'] > 0 && $line['credit'] > 0) {
                session()->flash('error', 'Una misma línea no puede tener Débito y Crédito simultáneamente.');
                return false;
            }
        }

        return true;
    }

    public function saveEntry(AccountingEngine $engine)
    {
        if (!$this->validateEntry()) {
            return;
        }

        try {
            // Preparar las líneas para el motor
            $engineLines = [];
            foreach ($this->lines as $line) {
                if ($line['debit'] > 0 || $line['credit'] > 0) {
                    $engineLines[] = [
                        'account_id' => $line['account_id'],
                        'description' => $line['description'],
                        'debit' => $line['debit'] ?: 0,
                        'credit' => $line['credit'] ?: 0,
                    ];
                }
            }

            // Llamar al motor ("El Dios")
            $entry = $engine->makeEntry(
                $this->journal_id,
                $this->date,
                $this->description,
                null, // Sin referencia polimórfica estricta, es un asiento manual
                $engineLines,
                'posted'
            );

            session()->flash('success', 'Asiento Manual #' . $entry->id . ' creado y contabilizado exitosamente.');
            
            // Restablecer el formulario
            $this->reset(['date', 'description', 'journal_id']);
            $this->lines = [];
            $this->mount();
            $this->calculateTotals();

        } catch (\Exception $e) {
            Log::error('Error creando asiento manual: ' . $e->getMessage());
            session()->flash('error', 'Error del Motor Contable: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $accounts = AccountingAccount::where('is_active', true)->orderBy('code', 'asc')->get();
        $journals = AccountingJournal::all();

        return view('livewire.admin.manual-journal-entry', [
            'accounts' => $accounts,
            'journals' => $journals,
        ])->layout('layouts.dashboard');
    }
}
