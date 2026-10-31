<?php

namespace App\Http\Controllers;

use App\Models\PaymentConcept;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentConceptController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search', '');

        $query = PaymentConcept::withCount('payments')->orderBy('name');

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        $concepts = $query->paginate(15)->withQueryString()->through(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'description' => $c->description,
                'amount' => (float) $c->amount,
                'is_fixed_amount' => (bool) $c->is_fixed_amount,
                'payments_count' => (int) $c->payments_count,
            ];
        });

        return Inertia::render('Finance/Concepts/Index', [
            'concepts' => $concepts,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id' => 'nullable|integer|exists:payment_concepts,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'amount' => 'nullable|numeric|min:0',
            'is_fixed_amount' => 'nullable|boolean',
        ]);

        $id = $validated['id'] ?? null;
        $isFixed = (bool) ($validated['is_fixed_amount'] ?? false);
        $amount = $isFixed ? (float) ($validated['amount'] ?? 0) : 0.00;

        PaymentConcept::updateOrCreate(
            ['id' => $id],
            [
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'amount' => $amount,
                'is_fixed_amount' => $isFixed,
            ]
        );

        $action = $id ? 'actualizado' : 'creado';
        return redirect()->back()->with('success', "Concepto de pago {$action} correctamente.");
    }

    public function destroy($id): RedirectResponse
    {
        $concept = PaymentConcept::findOrFail($id);

        if ($concept->payments()->count() > 0) {
            return redirect()->back()->with('error', 'No se puede eliminar: Este concepto ya posee transacciones de pago vinculadas.');
        }

        $concept->delete();

        return redirect()->back()->with('success', 'Concepto eliminado correctamente.');
    }

    public function massDestroy(): RedirectResponse
    {
        $concepts = PaymentConcept::withCount('payments')->get();
        $count = 0;
        foreach ($concepts as $c) {
            if ($c->payments_count === 0) {
                $c->delete();
                $count++;
            }
        }

        if ($count > 0) {
            return redirect()->back()->with('success', "Se eliminaron {$count} conceptos sin uso.");
        }

        return redirect()->back()->with('error', 'No se encontraron conceptos sin transacciones para eliminar.');
    }
}
