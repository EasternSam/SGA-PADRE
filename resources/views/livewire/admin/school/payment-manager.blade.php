<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">💰 Pagos y Cuotas Escolares</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Control de inscripciones, mensualidades y pagos</p>
        </div>
        <button wire:click="create" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">+ Nuevo Cobro</button>
    </div>

    @if(session()->has('message'))
        <div class="mb-4 rounded-lg bg-green-50 p-3 text-sm text-green-800 dark:bg-green-900/30 dark:text-green-400" x-data x-init="setTimeout(() => $el.remove(), 3000)">✅ {{ session('message') }}</div>
    @endif

    {{-- Financial Summary --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-4 text-center">
            <div class="text-2xl font-bold text-blue-700 dark:text-blue-400">RD$ {{ number_format($totalDue, 2) }}</div>
            <p class="text-xs text-blue-600">Total Facturado</p>
        </div>
        <div class="rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-4 text-center">
            <div class="text-2xl font-bold text-green-700 dark:text-green-400">RD$ {{ number_format($totalPaid, 2) }}</div>
            <p class="text-xs text-green-600">Total Cobrado</p>
        </div>
        <div class="rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4 text-center">
            <div class="text-2xl font-bold text-red-700 dark:text-red-400">RD$ {{ number_format($totalPending, 2) }}</div>
            <p class="text-xs text-red-600">Pendiente de Cobro</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap gap-3 mb-4">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="🔍 Buscar estudiante..." class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white w-60" />
        <select wire:model.live="filterStatus" class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            <option value="">Todos estados</option>
            @foreach($statuses as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
        </select>
        <select wire:model.live="filterType" class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            <option value="">Todos tipos</option>
            @foreach($types as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
        </select>
    </div>

    {{-- Table --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-900/50">
                    <th class="px-3 py-2 text-left text-xs font-bold uppercase text-gray-500">Estudiante</th>
                    <th class="px-3 py-2 text-left text-xs font-bold uppercase text-gray-500">Concepto</th>
                    <th class="px-3 py-2 text-center text-xs font-bold uppercase text-gray-500">Monto</th>
                    <th class="px-3 py-2 text-center text-xs font-bold uppercase text-gray-500">Pagado</th>
                    <th class="px-3 py-2 text-center text-xs font-bold uppercase text-gray-500">Balance</th>
                    <th class="px-3 py-2 text-center text-xs font-bold uppercase text-gray-500">Estado</th>
                    <th class="px-3 py-2 text-center text-xs font-bold uppercase text-gray-500">Vence</th>
                    <th class="px-3 py-2 text-right text-xs font-bold uppercase text-gray-500">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($payments as $p)
                    @php $balance = $p->amount - $p->paid; @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-3 py-2 text-sm font-medium text-gray-900 dark:text-white">{{ $p->student?->full_name ?? '—' }}</td>
                        <td class="px-3 py-2 text-sm">
                            <span class="text-xs">{{ $types[$p->type] ?? '' }}</span>
                            <br><span class="text-gray-600 dark:text-gray-400">{{ $p->concept }}</span>
                        </td>
                        <td class="px-3 py-2 text-center text-sm font-medium">{{ number_format($p->amount, 2) }}</td>
                        <td class="px-3 py-2 text-center text-sm text-green-600 font-bold">{{ number_format($p->paid, 2) }}</td>
                        <td class="px-3 py-2 text-center text-sm {{ $balance > 0 ? 'text-red-600 font-bold' : 'text-green-600' }}">{{ number_format($balance, 2) }}</td>
                        <td class="px-3 py-2 text-center">
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-bold
                                {{ $p->status === 'paid' ? 'bg-green-100 text-green-800' : ($p->status === 'partial' ? 'bg-yellow-100 text-yellow-800' : ($p->status === 'waived' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800')) }}">
                                {{ $statuses[$p->status] ?? $p->status }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-center text-xs text-gray-500">
                            {{ $p->due_date ? $p->due_date->format('d/m/Y') : '—' }}
                            @if($p->due_date && $p->due_date->isPast() && $p->status !== 'paid' && $p->status !== 'waived')
                                <span class="text-red-500 font-bold">⚠️</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-right space-x-1">
                            @if($p->status !== 'paid' && $p->status !== 'waived')
                                <button wire:click="openPay({{ $p->id }})" class="text-xs text-green-600 hover:text-green-800 font-medium">💵 Pagar</button>
                            @endif
                            <button wire:click="edit({{ $p->id }})" class="text-xs text-blue-600 hover:text-blue-800">✏️</button>
                            <button wire:click="delete({{ $p->id }})" wire:confirm="¿Eliminar?" class="text-xs text-red-400 hover:text-red-600">🗑️</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-sm text-gray-400">Sin registros de pagos</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $payments->links() }}</div>

    {{-- Create/Edit Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="$set('showModal', false)"></div>
            <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">{{ $editId ? 'Editar Cobro' : 'Nuevo Cobro' }}</h3>
                <form wire:submit="save" class="space-y-3">
                    @if(!$editId)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estudiante *</label>
                        <input type="text" wire:model.live.debounce.300ms="studentSearch" placeholder="Buscar..." class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                        @if($studentResults->count() > 0)
                            <select wire:model="student_id" class="w-full mt-1 rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                <option value="">Seleccionar...</option>
                                @foreach($studentResults as $sr)
                                    <option value="{{ $sr->id }}">{{ $sr->full_name }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                    @endif
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo</label>
                            <select wire:model="type" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                @foreach($types as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha Vencimiento</label>
                            <input type="date" wire:model="due_date" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Concepto *</label>
                        <input type="text" wire:model="concept" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Ej: Mensualidad Enero 2026" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Monto (RD$) *</label>
                            <input type="number" wire:model="amount" step="0.01" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pagado (RD$)</label>
                            <input type="number" wire:model="paid" step="0.01" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notas</label>
                        <textarea wire:model="notes" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-3 border-t dark:border-gray-700">
                        <button type="button" wire:click="$set('showModal', false)" class="rounded-lg px-4 py-2 text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300">Cancelar</button>
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Pay Modal --}}
    @if($showPayModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="$set('showPayModal', false)"></div>
            <div class="relative w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">💵 Registrar Pago</h3>
                <form wire:submit="applyPayment" class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Monto a Pagar (RD$)</label>
                        <input type="number" wire:model="payAmount" step="0.01" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Método</label>
                        <select wire:model="payMethod" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            @foreach($methods as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">No. Recibo (opcional)</label>
                        <input type="text" wire:model="payReceipt" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                    </div>
                    <div class="flex justify-end gap-3 pt-3 border-t dark:border-gray-700">
                        <button type="button" wire:click="$set('showPayModal', false)" class="rounded-lg px-4 py-2 text-sm text-gray-700 bg-gray-100 dark:bg-gray-700 dark:text-gray-300">Cancelar</button>
                        <button type="submit" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">Registrar Pago</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
