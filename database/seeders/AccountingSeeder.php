<?php

namespace Database\Seeders;

use App\Models\AccountingAccount;
use App\Models\AccountingJournal;
use App\Models\NcfSequence;
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

        // Base Chart of Accounts (Powered by Gridbase Bills Core)
        // 1. Activos
        $activo = AccountingAccount::firstOrCreate(['code' => '1.0.0.0'], ['name' => 'Activos', 'type' => 'asset']);
        AccountingAccount::firstOrCreate(['code' => '1.1.0.0'], ['parent_id' => $activo->id, 'name' => 'Caja y Bancos', 'type' => 'asset']);
        AccountingAccount::firstOrCreate(['code' => '1.1.4.0'], ['parent_id' => $activo->id, 'name' => 'ITBIS Pagado por Adelantado', 'type' => 'asset']);
        AccountingAccount::firstOrCreate(['code' => '1.2.0.0'], ['parent_id' => $activo->id, 'name' => 'Cuentas por Cobrar Estudiantes', 'type' => 'asset']);

        // 2. Pasivos
        $pasivo = AccountingAccount::firstOrCreate(['code' => '2.0.0.0'], ['name' => 'Pasivos', 'type' => 'liability']);
        AccountingAccount::firstOrCreate(['code' => '2.1.0.0'], ['parent_id' => $pasivo->id, 'name' => 'Ingresos Diferidos', 'type' => 'liability']);
        AccountingAccount::firstOrCreate(['code' => '2.1.2.0'], ['parent_id' => $pasivo->id, 'name' => 'Cuentas por Pagar Suplidores', 'type' => 'liability']);
        AccountingAccount::firstOrCreate(['code' => '2.1.3.0'], ['parent_id' => $pasivo->id, 'name' => 'ITBIS por Pagar', 'type' => 'liability']);
        AccountingAccount::firstOrCreate(['code' => '2.1.4.0'], ['parent_id' => $pasivo->id, 'name' => 'ITBIS Retenido por Pagar (DGII)', 'type' => 'liability']);
        AccountingAccount::firstOrCreate(['code' => '2.1.5.0'], ['parent_id' => $pasivo->id, 'name' => 'ISR Retenido por Pagar (DGII)', 'type' => 'liability']);

        // 3. Capital
        AccountingAccount::firstOrCreate(['code' => '3.0.0.0'], ['name' => 'Capital', 'type' => 'equity']);

        // 4. Ingresos
        $ingreso = AccountingAccount::firstOrCreate(['code' => '4.0.0.0'], ['name' => 'Ingresos', 'type' => 'revenue']);
        AccountingAccount::firstOrCreate(['code' => '4.1.0.0'], ['parent_id' => $ingreso->id, 'name' => 'Ingresos Académicos (Colegiaturas)', 'type' => 'revenue']);

        // 5. Costos
        AccountingAccount::firstOrCreate(['code' => '5.0.0.0'], ['name' => 'Costos', 'type' => 'cost']);

        // 6. Gastos
        $gasto = AccountingAccount::firstOrCreate(['code' => '6.0.0.0'], ['name' => 'Gastos', 'type' => 'expense']);
        AccountingAccount::firstOrCreate(['code' => '6.1.0.0'], ['parent_id' => $gasto->id, 'name' => 'Gastos Operativos y Administrativos', 'type' => 'expense']);

        // Default e-CF Sequences for Gridbase Bills Core
        NcfSequence::firstOrCreate(
            ['type_code' => '32', 'series' => 'E'],
            [
                'name' => 'Factura de Consumo Electrónica (e-CF)',
                'current_sequence' => 0,
                'limit_sequence' => 99999999,
                'expiration_date' => '2027-12-31',
                'is_active' => true,
            ]
        );

        NcfSequence::firstOrCreate(
            ['type_code' => '31', 'series' => 'E'],
            [
                'name' => 'Factura de Crédito Fiscal Electrónica (e-CF)',
                'current_sequence' => 0,
                'limit_sequence' => 99999999,
                'expiration_date' => '2027-12-31',
                'is_active' => true,
            ]
        );
    }
}
