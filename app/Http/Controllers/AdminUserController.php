<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class AdminUserController extends Controller
{
    /**
     * Display users management listing.
     */
    public function index(Request $request): Response
    {
        $search = $request->query('search');
        $role = $request->query('role');

        $query = User::with('roles')->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role && $role !== 'all') {
            $query->whereHas('roles', fn($q) => $q->where('name', $role));
        }

        $users = $query->take(100)->get()->map(fn($u) => [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'roles' => $u->roles->pluck('name')->toArray(),
            'has_kiosk_pin' => !empty($u->kiosk_pin),
            'created_at' => $u->created_at?->format('d/m/Y H:i'),
        ]);

        $roles = Role::pluck('name')->toArray();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'roles' => $roles,
            'filters' => [
                'search' => $search,
                'role' => $role ?? 'all',
            ],
        ]);
    }

    /**
     * Create a new user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|string|exists:roles,name',
            'kiosk_pin' => 'nullable|string|min:4|max:6',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'kiosk_pin' => $validated['kiosk_pin'] ?? null,
        ]);

        $user->assignRole($validated['role']);

        return back()->with('success', "Usuario {$user->name} creado exitosamente.");
    }

    /**
     * Update user details and role.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|string|email|max:255|unique:users,email,{$user->id}",
            'password' => 'nullable|string|min:6',
            'role' => 'required|string|exists:roles,name',
            'kiosk_pin' => 'nullable|string|min:4|max:6',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        if (isset($validated['kiosk_pin'])) {
            $data['kiosk_pin'] = $validated['kiosk_pin'];
        }

        $user->update($data);
        $user->syncRoles([$validated['role']]);

        return back()->with('success', "Usuario {$user->name} actualizado exitosamente.");
    }

    /**
     * Remove user.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta en sesión.');
        }

        $name = $user->name;
        $user->delete();

        return back()->with('success', "Usuario {$name} eliminado.");
    }
}
