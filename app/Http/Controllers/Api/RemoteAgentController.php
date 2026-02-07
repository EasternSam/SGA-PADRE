<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\DeploymentService;

class RemoteAgentController extends Controller
{
    protected $deployer;

    public function __construct(DeploymentService $deployer)
    {
        $this->deployer = $deployer;
    }

    // GET /remote/status - Para que el panel sepa cómo está la escuela
    public function status()
    {
        return response()->json($this->deployer->getStatus());
    }

    // POST /remote/deploy - Para ejecutar la actualización
    public function deploy(Request $request)
    {
        $request->validate([
            'branch' => 'nullable|string'
        ]);

        $result = $this->deployer->deploy($request->branch);

        return response()->json($result, $result['success'] ? 200 : 500);
    }
}