<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AccountingEntry;
use App\Models\AccountingEntryLine;
use App\Models\AccountingAccount;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class DiagnoseAccounting extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:diagnose-accounting';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Diagnose the health of the accounting engine data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Accounting Engine Diagnosis...');
        $this->newLine();

        $errorsFound = 0;

        // 1. Check for unbalanced entries
        $this->info('1. Checking for unbalanced entries...');
        $entries = AccountingEntry::with('lines')->get();
        $unbalancedCount = 0;
        foreach ($entries as $entry) {
            $totalDebit = $entry->lines->sum('debit');
            $totalCredit = $entry->lines->sum('credit');
            
            if (round($totalDebit, 2) !== round($totalCredit, 2)) {
                $this->error("Entry #{$entry->id} is unbalanced! Debit: {$totalDebit}, Credit: {$totalCredit}");
                $unbalancedCount++;
                $errorsFound++;
            }
        }
        if ($unbalancedCount === 0) {
            $this->info('   - All entries are perfectly balanced.');
        }

        $this->newLine();

        // 2. Check for missing or inactive accounts in lines
        $this->info('2. Checking for invalid accounts in entry lines...');
        $invalidLines = AccountingEntryLine::whereDoesntHave('account')->count();
        if ($invalidLines > 0) {
            $this->error("   - Found {$invalidLines} entry lines pointing to non-existent accounts.");
            $errorsFound++;
        } else {
            $this->info('   - All entry lines point to valid accounts.');
        }

        $inactiveLines = AccountingEntryLine::whereHas('account', function($query) {
            $query->where('is_active', false);
        })->count();
        if ($inactiveLines > 0) {
            $this->warn("   - Found {$inactiveLines} entry lines pointing to INACTIVE accounts.");
            $errorsFound++;
        }

        $this->newLine();

        // 3. Check for settings and default accounts
        $this->info('3. Checking Accounting Settings and Default Accounts...');
        $settingsToCheck = [
            'account_cash_default',
            'account_cxc_default',
            'account_income_default',
            'account_deferred_income',
            'account_itbis_advance',
            'account_itbis_retained',
            'account_isr_retained',
        ];

        foreach ($settingsToCheck as $key) {
            $code = Setting::val($key);
            if (!$code) {
                $this->warn("   - Setting '{$key}' is missing or empty.");
                $errorsFound++;
                continue;
            }

            $accountExists = AccountingAccount::where('code', $code)->exists();
            if (!$accountExists) {
                $this->error("   - Setting '{$key}' points to account '{$code}', but this account DOES NOT EXIST in the database.");
                $errorsFound++;
            } else {
                $this->line("   - Setting '{$key}' points to valid account -> {$code}");
            }
        }

        $this->newLine();

        // 4. Summarize
        if ($errorsFound === 0) {
            $this->info('Diagnosis Complete: SUCCESS. 0 critical errors found.');
        } else {
            $this->error("Diagnosis Complete: WARNING. {$errorsFound} potential issues found.");
        }

    }
}
