<?php

namespace App\Services;

use Gridbase\BillsCore\Services\DgiiRncLookupService as BillsCoreRncLookupService;
use Illuminate\Support\Facades\Cache;

/**
 * Servicio de verificación de RNC/Cédula en la DGII — Powered by Gridbase Bills Core.
 */
class DgiiRncLookupService
{
    public function __construct(
        protected ?BillsCoreRncLookupService $coreLookup = null
    ) {
        $this->coreLookup = $coreLookup ?? app(BillsCoreRncLookupService::class);
    }

    /**
     * Busca un RNC (9 dígitos) o Cédula (11 dígitos) usando el motor de Gridbase Bills Core.
     */
    public function lookup(string $rnc): ?array
    {
        $cleanRnc = preg_replace('/[^0-9]/', '', $rnc);

        if (empty($cleanRnc) || (strlen($cleanRnc) !== 9 && strlen($cleanRnc) !== 11)) {
            return null;
        }

        $cacheKey = "dgii_lookup_{$cleanRnc}";
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $coreResult = $this->coreLookup->lookup($cleanRnc);
        if (!$coreResult || empty($coreResult['name'])) {
            return null;
        }

        $formattedName = mb_convert_case(mb_strtolower((string) $coreResult['name']), MB_CASE_TITLE, 'UTF-8');

        $normalized = [
            'nombre' => $formattedName,
            'name' => $formattedName,
            'commercial_name' => $coreResult['commercial_name'] ?? $formattedName,
            'tipo' => strlen($cleanRnc) === 9 ? 'Juridica' : 'Fisica',
            'rnc' => $cleanRnc,
            'status' => $coreResult['status'] ?? 'ACTIVO',
            'is_active' => $coreResult['is_active'] ?? true,
            'source' => 'gridbase_bills_core',
        ];

        Cache::put($cacheKey, $normalized, now()->addHours(24));

        return $normalized;
    }
}
