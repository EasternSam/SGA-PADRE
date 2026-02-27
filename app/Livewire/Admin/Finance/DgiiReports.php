<?php

namespace App\Livewire\Admin\Finance;

use App\Services\DgiiExportService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.dashboard')]
class DgiiReports extends Component
{
    public $yearMonth;

    public function mount()
    {
        // By default, select the previous month for tax reporting
        $this->yearMonth = date('Y-m', strtotime('-1 month'));
    }

    public function download606(DgiiExportService $exporter)
    {
        $this->validate(['yearMonth' => 'required|date_format:Y-m']);
        
        $result = $exporter->generate606($this->yearMonth);

        if ($result['count'] === 0) {
            session()->flash('warning_606', 'No hay gastos registrados con NCF válido para este período.');
            return;
        }

        // Return the file as a streamed download
        return response()->streamDownload(function () use ($result) {
            echo $result['content'];
        }, $result['filename'], ['Content-Type' => 'text/plain']);
    }

    public function download607(DgiiExportService $exporter)
    {
        $this->validate(['yearMonth' => 'required|date_format:Y-m']);
        
        $result = $exporter->generate607($this->yearMonth);

        if ($result['count'] === 0) {
            session()->flash('warning_607', 'No hay ingresos/pagos registrados con NCF válido para este período.');
            return;
        }

        return response()->streamDownload(function () use ($result) {
            echo $result['content'];
        }, $result['filename'], ['Content-Type' => 'text/plain']);
    }

    public function render()
    {
        return view('livewire.admin.finance.dgii-reports');
    }
}
