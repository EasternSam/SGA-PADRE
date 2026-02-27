<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AccountingEntry;
use App\Models\AccountingJournal;

class AccountingLedger extends Component
{
    use WithPagination;

    public $journal_id = '';
    public $date_from = '';
    public $date_to = '';
    public $status = 'posted';

    public function updating($name, $value)
    {
        $this->resetPage();
    }

    public function render()
    {
        $journals = AccountingJournal::all();

        $entriesQuery = AccountingEntry::with(['journal', 'lines.account', 'reference'])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc');

        if ($this->journal_id) {
            $entriesQuery->where('accounting_journal_id', $this->journal_id);
        }

        if ($this->date_from) {
            $entriesQuery->whereDate('date', '>=', $this->date_from);
        }

        if ($this->date_to) {
            $entriesQuery->whereDate('date', '<=', $this->date_to);
        }

        if ($this->status !== 'all') {
            $entriesQuery->where('status', $this->status);
        }

        $entries = $entriesQuery->paginate(15);

        return view('livewire.admin.accounting-ledger', [
            'entries' => $entries,
            'journals' => $journals,
        ])->layout('layouts.dashboard');
    }
}
