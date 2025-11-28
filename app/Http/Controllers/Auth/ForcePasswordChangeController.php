<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ForcePasswordChangeController extends Controller
{
    public function show()
    {
        return view('auth.force-password-change');
    }

    public function update(Request $request)
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // SOLUCIÓN: Usamos forceFill() o asignación directa para saltar la protección $fillable
        // Si usas $user->update(), y 'must_change_password' no está en el array $fillable del modelo User,
        // Laravel lo ignora silenciosamente y el usuario se queda en un bucle infinito.
        $user->forceFill([
            'password' => Hash::make($request->password),
            'must_change_password' => false, // Desactivar la bandera
        ])->save();

        return redirect()->route('dashboard')->with('status', '¡Contraseña actualizada correctamente!');
    }
}