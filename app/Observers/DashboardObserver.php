<?php
namespace App\Observers;
use Illuminate\Support\Facades\Cache;

class DashboardObserver
{
    public function saved($model) { $this->updateVersion(); }
    public function deleted($model) { $this->updateVersion(); }

    private function updateVersion() {
        Cache::put('dashboard_version', now()->timestamp);
    }
}