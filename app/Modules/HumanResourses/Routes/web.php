<?php

use Illuminate\Support\Facades\Route;
use App\Modules\HumanResources\Livewire\Dashboard;

Route::middleware(['web', 'auth', 'role:Admin', 'feature:hr'])->prefix('admin/hr')->name('admin.hr.')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    // Route::get('/employees', Employees::class)->name('employees');
});