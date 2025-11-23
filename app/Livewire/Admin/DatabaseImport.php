<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\CsvImportService;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class DatabaseImport extends Component
{
    use WithFileUploads;

    public $file;
    public $entity = ''; 
    public $step = 1;
    
    public $csvHeaders = [];
    public $previewRows = [];
    public $dbFields = [];
    public $columnMapping = []; 
    
    public $isProcessing = false;
    public $totalRows = 0;
    public $processedRows = 0;
    
    // --- CORRECCIÓN DE RENDIMIENTO ---
    // Bajamos a 200 para evitar el timeout de 120s por el hashing de contraseñas.
    // Es más rápido hacer muchas peticiones cortas que una larga que falla.
    public $chunkSize = 200; 
    
    public $importErrors = [];
    public $startTime;

    public $serverLimits = [];

    protected function getImporter()
    {
        return app(CsvImportService::class);
    }

    public function mount()
    {
        $this->serverLimits = [
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'memory_limit' => ini_get('memory_limit'),
        ];
        $configs = $this->getImporter()->getEntityConfig();
        $this->entity = array_key_first($configs);
    }

    public function render()
    {
        return view('livewire.admin.database-import', [
            'availableEntities' => $this->getImporter()->getEntityConfig()
        ])->layout('layouts.dashboard'); 
    }

    public function updatedFile()
    {
        $this->validate([
            'file' => ['required', 'file', 'extensions:csv,txt', 'max:102400'], 
        ]);
    }

    public function analyzeFile()
    {
        $this->validate(['file' => 'required', 'entity' => 'required']);
        $importer = $this->getImporter();
        $path = $this->file->getRealPath();
        
        $this->csvHeaders = $importer->getCsvHeaders($path);
        
        $fileObj = new \SplFileObject($path);
        $fileObj->setFlags(\SplFileObject::READ_CSV);
        $fileObj->seek(1);
        $this->previewRows = [];
        for($i=0; $i<5; $i++) {
            if(!$fileObj->eof()) {
                $this->previewRows[] = $fileObj->current();
                $fileObj->next();
            }
        }

        $config = $importer->getEntityConfig()[$this->entity] ?? null;
        $this->dbFields = $config['fields'] ?? [];
        
        $this->columnMapping = [];
        foreach ($this->dbFields as $fieldKey => $label) {
            $match = collect($this->csvHeaders)->first(function($h) use ($fieldKey) {
                return \Illuminate\Support\Str::lower(trim($h)) === \Illuminate\Support\Str::lower(trim($fieldKey));
            });
            $this->columnMapping[$fieldKey] = $match ?? '';
        }

        $this->step = 2;
    }

    public function startImport()
    {
        Log::info(">>> IMPORTADOR: Botón Start presionado.");
        
        try {
            $importer = $this->getImporter();
            $path = $this->file->getRealPath();

            $this->totalRows = $importer->countRows($path);
            Log::info(">>> IMPORTADOR: Filas contadas: {$this->totalRows}");

            $this->processedRows = 0;
            $this->importErrors = [];
            $this->isProcessing = true;
            $this->startTime = now();

            $this->dispatch('start-batch-process');

        } catch (\Exception $e) {
            Log::error(">>> IMPORTADOR: Error al iniciar: " . $e->getMessage());
            $this->addError('general', 'No se pudo iniciar: ' . $e->getMessage());
        }
    }

    public function importBatch()
    {
        if ($this->processedRows >= $this->totalRows) {
            $this->isProcessing = false;
            $this->step = 3;
            return;
        }

        $importer = $this->getImporter();
        $path = $this->file->getRealPath();

        // Intentamos extender el tiempo límite para este lote específico a 5 minutos
        set_time_limit(300); 

        $result = $importer->importBatch(
            $this->entity,
            $path,
            $this->columnMapping,
            $this->processedRows,
            $this->chunkSize
        );

        $this->processedRows += $result['processed'];
        
        if (!empty($result['errors'])) {
            if (count($this->importErrors) < 100) {
                $this->importErrors = array_merge($this->importErrors, $result['errors']);
            }
        }

        $percentage = ($this->totalRows > 0) ? round(($this->processedRows / $this->totalRows) * 100) : 0;
        
        if ($this->processedRows < $this->totalRows) {
            $this->dispatch('batch-processed', progress: $percentage);
        } else {
            $this->isProcessing = false;
            $this->step = 3;
            Log::info("Importación finalizada.");
        }
    }

    public function resetImport()
    {
        $this->reset(['file', 'step', 'csvHeaders', 'previewRows', 'columnMapping', 'isProcessing', 'totalRows', 'processedRows', 'importErrors']);
    }
}