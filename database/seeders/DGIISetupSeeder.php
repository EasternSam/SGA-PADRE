<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AccountingAccount;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\NcfSequence;

class DGIISetupSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Cuentas Contables Fiscales (Si no existen)
        $itbisAdvance = AccountingAccount::firstOrCreate(
            ['code' => '1.1.4.0'],
            ['name' => 'ITBIS Pagado por Adelantado (Compras)', 'type' => 'asset', 'is_active' => true]
        );

        $itbisRetenido = AccountingAccount::firstOrCreate(
            ['code' => '2.1.4.0'],
            ['name' => 'ITBIS Retenido por Pagar (Pasivo)', 'type' => 'liability', 'is_active' => true]
        );

        $isrRetenido = AccountingAccount::firstOrCreate(
            ['code' => '2.1.5.0'],
            ['name' => 'ISR Retenido por Pagar (Pasivo)', 'type' => 'liability', 'is_active' => true]
        );

        // 2. Configurar el Motor Contable Global (Settings)
        Setting::set('account_cash_default', '1.1.0.0', 'accounting');
        Setting::set('account_cxc_default', '1.2.0.0', 'accounting');
        Setting::set('account_income_default', '4.1.0.0', 'accounting');
        Setting::set('account_deferred_income', '2.1.0.0', 'accounting');
        Setting::set('account_itbis_advance', '1.1.4.0', 'accounting');
        Setting::set('account_itbis_retained', '2.1.4.0', 'accounting');
        Setting::set('account_isr_retained', '2.1.5.0', 'accounting');

        // 3. Crear Suplidor de Prueba para QA
        Supplier::firstOrCreate(
            ['rnc_cedula' => '130123456'],
            [
                'name' => 'Papelería El Cuadre Perfecto SRL',
                'type' => 'Juridica',
                'phone' => '809-555-0101',
                'email' => 'ventas@elcuadre.do'
            ]
        );
        Supplier::firstOrCreate(
            ['rnc_cedula' => '00100000001'],
            [
                'name' => 'Juan Pérez (Honorarios Profesionales)',
                'type' => 'Fisica',
                'phone' => '809-555-0202'
            ]
        );

        // 4. Secuencias NCF Básicas (Si no están)
        NcfSequence::firstOrCreate(
            ['type_code' => '31', 'series' => 'E'],
            [
                'name' => 'Crédito Fiscal (e-CF)',
                'current_sequence' => 0,
                'limit_sequence' => 1000,
                'expiration_date' => '2026-12-31',
                'is_active' => true
            ]
        );
        NcfSequence::firstOrCreate(
            ['type_code' => '32', 'series' => 'E'],
            [
                'name' => 'Consumo (e-CF)',
                'current_sequence' => 0,
                'limit_sequence' => 5000,
                'expiration_date' => '2026-12-31',
                'is_active' => true
            ]
        );
        NcfSequence::firstOrCreate(
            ['type_code' => '41', 'series' => 'E'],
            [
                'name' => 'Compras (e-CF)',
                'current_sequence' => 0,
                'limit_sequence' => 500,
                'expiration_date' => '2026-12-31',
                'is_active' => true
            ]
        );
    }
}
