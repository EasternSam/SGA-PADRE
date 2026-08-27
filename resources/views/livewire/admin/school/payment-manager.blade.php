<div class="p-4 lg:p-6">
    {{-- Page Header (Gridbase style) --}}
    <div class="gb-page-header">
        <div>
            <h1 class="gb-page-title">Pagos y Cuotas</h1>
            <p class="gb-page-subtitle">Gestión financiera escolar — inscripciones, mensualidades y cobros</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('export.payments') }}" class="gb-btn gb-btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Exportar CSV
            </a>
            <div class="gb-divider-v"></div>
            <button wire:click="create" class="gb-btn gb-btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nuevo Cobro
            </button>
        </div>
    </div>

    @if(session()->has('message'))
        <div class="mb-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 p-3 text-sm text-emerald-800 dark:text-emerald-400 flex items-center gap-2" x-data x-init="setTimeout(() => $el.remove(), 3000)">
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
            {{ session('message') }}
        </div>
    @endif

    {{-- Metric Cards Row --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="gb-metric gb-lift">
            <div class="flex items-center justify-between">
                <div class="gb-metric-icon bg-blue-50 dark:bg-blue-900/20 text-blue-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
                <span class="gb-section-title">Total Facturado</span>
            </div>
            <div class="gb-metric-value">RD$ {{ number_format($totalDue, 0) }}</div>
            <div class="gb-metric-label">Año académico actual</div>
        </div>
        <div class="gb-metric gb-lift">
            <div class="flex items-center justify-between">
                <div class="gb-metric-icon bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="gb-section-title">Total Cobrado</span>
            </div>
            <div class="gb-metric-value text-emerald-600 dark:text-emerald-400">RD$ {{ number_format($totalPaid, 0) }}</div>
            @php $paidPct = $totalDue > 0 ? round(($totalPaid / $totalDue) * 100) : 0; @endphp
            <div class="mt-2 h-1.5 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                <div class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-emerald-500 transition-all duration-700" style="width: {{ $paidPct }}%"></div>
            </div>
            <div class="gb-metric-label mt-1">{{ $paidPct }}% cobrado</div>
        </div>
        <div class="gb-metric gb-lift">
            <div class="flex items-center justify-between">
                <div class="gb-metric-icon {{ $totalPending > 0 ? 'bg-red-50 dark:bg-red-900/20 text-red-600' : 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="gb-section-title">Pendiente</span>
            </div>
            <div class="gb-metric-value {{ $totalPending > 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600' }}">RD$ {{ number_format($totalPending, 0) }}</div>
            <div class="gb-metric-label">{{ $totalPending > 0 ? 'Por cobrar' : '¡Al día!' }}</div>
        </div>
    </div>

    {{-- Table Container --}}
    <div class="gb-table-outer">
        {{-- Toolbar --}}
        <div class="gb-table-toolbar">
            <div class="flex items-center gap-3 flex-1 flex-wrap">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar estudiante..." class="gb-input pl-9 w-56" />
                </div>
                <select wire:model.live="filterStatus" class="gb-input w-auto">
                    <option value="">Todos estados</option>
                    @foreach($statuses as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
                </select>
                <select wire:model.live="filterType" class="gb-input w-auto">
                    <option value="">Todos tipos</option>
                    @foreach($types as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
                </select>
            </div>
            <div class="text-xs text-gray-400 font-medium">{{ $payments->total() }} registros</div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="gb-table">
                <thead>
                    <tr>
                        <th>Estudiante</th>
                        <th>Concepto</th>
                        <th class="text-right">Monto</th>
                        <th class="text-right">Pagado</th>
                        <th class="text-right">Balance</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Vence</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $p)
                        @php $balance = $p->amount - $p->paid; @endphp
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-xs font-bold text-indigo-700 dark:text-indigo-400 flex-shrink-0">
                                        {{ strtoupper(substr($p->student?->first_name ?? '?', 0, 1)) }}{{ strtoupper(substr($p->student?->last_name ?? '?', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="user-name">{{ $p->student?->full_name ?? '—' }}</div>
                                        <div class="user-sub">{{ $p->student?->student_code ?? '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-[10px] text-gray-400 font-medium uppercase">{{ $types[$p->type] ?? '' }}</span>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $p->concept }}</div>
                            </td>
                            <td class="text-right font-medium tabular-nums">{{ number_format($p->amount, 2) }}</td>
                            <td class="text-right font-semibold text-emerald-600 tabular-nums">{{ number_format($p->paid, 2) }}</td>
                            <td class="text-right font-semibold tabular-nums {{ $balance > 0 ? 'text-red-600' : 'text-emerald-600' }}">{{ number_format($balance, 2) }}</td>
                            <td class="text-center">
                                <span class="gb-badge {{ $p->status === 'paid' ? 'gb-badge-success' : ($p->status === 'partial' ? 'gb-badge-warning' : ($p->status === 'waived' ? 'gb-badge-info' : 'gb-badge-danger')) }}">
                                    {{ $statuses[$p->status] ?? $p->status }}
                                </span>
                            </td>
                            <td class="text-center text-xs">
                                {{ $p->due_date ? $p->due_date->format('d/m/Y') : '—' }}
                                @if($p->due_date && $p->due_date->isPast() && !in_array($p->status, ['paid', 'waived']))
                                    <span class="inline-block ml-1 w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    @if(!in_array($p->status, ['paid', 'waived']))
                                        <button wire:click="openPay({{ $p->id }})" class="gb-btn-icon !w-8 !h-8 !border-emerald-200 text-emerald-600 hover:!bg-emerald-50" title="Registrar pago">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </button>
                                    @endif
                                    <button wire:click="edit({{ $p->id }})" class="gb-btn-icon !w-8 !h-8" title="Editar">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button wire:click="delete({{ $p->id }})" wire:confirm="¿Eliminar este cobro?" class="gb-btn-icon !w-8 !h-8 text-red-400 hover:text-red-600 hover:!border-red-200 hover:!bg-red-50" title="Eliminar">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="gb-empty-state">
                                    <div class="gb-empty-icon"></div>
                                    <div class="gb-empty-title">Sin registros de pagos</div>
                                    <div class="gb-empty-desc">Crea un nuevo cobro para comenzar</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($payments->hasPages())
        <div class="px-6 py-3 border-t border-gray-100 dark:border-gray-700/50">
            {{ $payments->links() }}
        </div>
        @endif
    </div>

    {{-- Create/Edit Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="$set('showModal', false)"></div>
            <div class="relative w-full max-w-lg rounded-2xl bg-white dark:bg-gray-800 shadow-2xl border border-gray-200 dark:border-gray-700" style="box-shadow: var(--gb-shadow-lg);">
                {{-- Modal Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $editId ? 'Editar Cobro' : 'Nuevo Cobro' }}</h3>
                    <button wire:click="$set('showModal', false)" class="gb-btn-icon !w-8 !h-8">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                {{-- Modal Body --}}
                <form wire:submit="save" class="p-6 space-y-4">
                    @if(!$editId)
                    <div>
                        <label class="gb-section-title mb-2 block">Estudiante *</label>
                        <input type="text" wire:model.live.debounce.300ms="studentSearch" placeholder="Buscar por nombre..." class="gb-input" />
                        @if($studentResults->count() > 0)
                            <select wire:model="student_id" class="gb-input mt-2">
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
                            <label class="gb-section-title mb-2 block">Tipo</label>
                            <select wire:model="type" class="gb-input">
                                @foreach($types as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="gb-section-title mb-2 block">Fecha Vencimiento</label>
                            <input type="date" wire:model="due_date" class="gb-input" />
                        </div>
                    </div>
                    <div>
                        <label class="gb-section-title mb-2 block">Concepto *</label>
                        <input type="text" wire:model="concept" class="gb-input" placeholder="Ej: Mensualidad Enero 2026" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="gb-section-title mb-2 block">Monto (RD$) *</label>
                            <input type="number" wire:model="amount" step="0.01" class="gb-input" />
                        </div>
                        <div>
                            <label class="gb-section-title mb-2 block">Pagado (RD$)</label>
                            <input type="number" wire:model="paid" step="0.01" class="gb-input" />
                        </div>
                    </div>
                    <div>
                        <label class="gb-section-title mb-2 block">Notas</label>
                        <textarea wire:model="notes" rows="2" class="gb-input"></textarea>
                    </div>
                    {{-- Modal Footer --}}
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" wire:click="$set('showModal', false)" class="gb-btn gb-btn-secondary">Cancelar</button>
                        <button type="submit" class="gb-btn gb-btn-primary">Guardar</button>
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
            <div class="relative w-full max-w-sm rounded-2xl bg-white dark:bg-gray-800 shadow-2xl border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Registrar Pago</h3>
                    <button wire:click="$set('showPayModal', false)" class="gb-btn-icon !w-8 !h-8">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form wire:submit="applyPayment" class="p-6 space-y-4">
                    <div>
                        <label class="gb-section-title mb-2 block">Monto a Pagar (RD$)</label>
                        <input type="number" wire:model="payAmount" step="0.01" class="gb-input text-lg font-bold" />
                    </div>
                    <div>
                        <label class="gb-section-title mb-2 block">Método</label>
                        <select wire:model="payMethod" class="gb-input">
                            @foreach($methods as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="gb-section-title mb-2 block">No. Recibo (opcional)</label>
                        <input type="text" wire:model="payReceipt" class="gb-input" />
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" wire:click="$set('showPayModal', false)" class="gb-btn gb-btn-secondary">Cancelar</button>
                        <button type="submit" class="gb-btn gb-btn-primary" style="background: linear-gradient(180deg, #059669, #047857); border-color: #047857;">Registrar Pago</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
