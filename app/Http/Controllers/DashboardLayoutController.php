<?php

namespace App\Http\Controllers;

use App\Http\Requests\Dashboard\UpdateDashboardLayoutRequest;
use App\Services\Dashboard\DashboardLayoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endpoints JSON del modo edición del dashboard. Siempre responden con el layout
 * ya saneado para que el cliente refleje exactamente lo que quedó guardado.
 */
class DashboardLayoutController extends Controller
{
    public function __construct(private readonly DashboardLayoutService $layouts)
    {
    }

    public function update(UpdateDashboardLayoutRequest $request): JsonResponse
    {
        return response()->json([
            'layout' => $this->layouts->saveForUser($request->user(), $request->validated('widgets')),
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        return response()->json(['layout' => $this->layouts->resetForUser($request->user())]);
    }

    public function updateRoleDefault(UpdateDashboardLayoutRequest $request, string $role): JsonResponse
    {
        return response()->json([
            'role' => $role,
            'layout' => $this->layouts->saveRoleDefault($role, $request->validated('widgets')),
        ]);
    }

    public function destroyRoleDefault(string $role): JsonResponse
    {
        return response()->json(['role' => $role, 'layout' => $this->layouts->clearRoleDefault($role)]);
    }
}
