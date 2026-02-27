<?php

namespace Database\Seeders;

use App\Models\AccountingAccount;
use App\Models\AccountingJournal;
use Illuminate\Database\Seeder;

class AccountingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Journals
        AccountingJournal::firstOrCreate(['prefix' => 'ING-'], ['name' => 'Diario de Ingresos']);
        AccountingJournal::firstOrCreate(['prefix' => 'EGR-'], ['name' => 'Diario de Egresos']);
        AccountingJournal::firstOrCreate(['prefix' => 'GEN-'], ['name' => 'Diario General']);

        // Base Chart of Accounts
        // 1. Activos
        $activo = AccountingAccount::firstOrCreate(['code' => '1.0.0.0'], ['name' => 'Activos', 'type' => 'asset']);
        AccountingAccount::firstOrCreate(['code' => '1.1.0.0', 'parent_id' => $activo->id], ['name' => 'Caja y Bancos', 'type' => 'asset']);
        AccountingAccount::firstOrCreate(['code' => '1.2.0.0', 'parent_id' => $activo->id], ['name' => 'Cuentas por Cobrar Estudiantes', 'type' => 'asset']);

        // 2. Pasivos
        $pasivo = AccountingAccount::firstOrCreate(['code' => '2.0.0.0'], ['name' => 'Pasivos', 'type' => 'liability']);
        AccountingAccount::firstOrCreate(['code' => '2.1.0.0', 'parent_id' => $pasivo->id], ['name' => 'Ingresos Diferidos', 'type' => 'liability']);

        // 3. Capital
        AccountingAccount::firstOrCreate(['code' => '3.0.0.0'], ['name' => 'Capital', 'type' => 'equity']);

        // 4. Ingresos
        $ingreso = AccountingAccount::firstOrCreate(['code' => '4.0.0.0'], ['name' => 'Ingresos', 'type' => 'revenue']);
        AccountingAccount::firstOrCreate(['code' => '4.1.0.0', 'parent_id' => $ingreso->id], ['name' => 'Ingresos Académicos (Colegiaturas)', 'type' => 'revenue']);

        // 5. Gastos
        AccountingAccount::firstOrCreate(['code' => '5.0.0.0'], ['name' => 'Gastos', 'type' => 'expense']);
    }
}
