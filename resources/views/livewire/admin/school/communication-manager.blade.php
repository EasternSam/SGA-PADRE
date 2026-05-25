<div class="p-4 lg:p-6">
    {{-- Page Header --}}
    <div class="gb-page-header">
        <div>
            <h1 class="gb-page-title">Comunicaciones</h1>
            <p class="gb-page-subtitle">Envío de circulares, notificaciones y mensajes masivos</p>
        </div>
        <div class="flex items-center gap-2">
            <button wire:click="openSend" class="gb-btn gb-btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                Nuevo Comunicado
            </button>
        </div>
    </div>

    @if(session()->has('message'))
        <div class="mb-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 p-3 text-sm text-emerald-800 dark:text-emerald-400 flex items-center gap-2" x-data x-init="setTimeout(() => $el.remove(), 3000)">
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
            {{ session('message') }}
        </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="gb-metric gb-lift">
            <div class="flex items-center gap-3">
                <div class="gb-metric-icon bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <div class="gb-metric-value !text-xl !mt-0">{{ $totalSent }}</div>
                    <div class="gb-metric-label !mt-0">Total Enviados</div>
                </div>
            </div>
        </div>
        <div class="gb-metric gb-lift">
            <div class="flex items-center gap-3">
                <div class="gb-metric-icon bg-blue-50 dark:bg-blue-900/20 text-blue-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <div class="gb-metric-value !text-xl !mt-0">{{ $thisMonth }}</div>
                    <div class="gb-metric-label !mt-0">Este Mes</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="gb-table-outer">
        <div class="gb-table-toolbar">
            <div class="flex items-center gap-3">
                <select wire:model.live="filterChannel" class="gb-input w-auto">
                    <option value="">Todos canales</option>
                    @foreach($channels as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
                </select>
            </div>
            <div class="text-xs text-gray-400 font-medium">{{ $logs->total() }} comunicados</div>
        </div>

        <div class="overflow-x-auto">
            <table class="gb-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th class="text-center">Canal</th>
                        <th>Asunto</th>
                        <th class="text-center">Tipo</th>
                        <th class="text-center">Destinos</th>
                        <th>Enviado por</th>
                        <th class="text-right">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="text-xs text-gray-500 whitespace-nowrap">{{ $log->sent_at?->format('d/m/Y H:i') ?? $log->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-center">
                                @php
                                    $chColors = ['email' => 'gb-badge-info', 'whatsapp' => 'gb-badge-success', 'internal' => 'gb-badge-warning', 'sms' => 'gb-badge-danger'];
                                @endphp
                                <span class="gb-badge {{ $chColors[$log->channel] ?? 'gb-badge-info' }}">{{ $channels[$log->channel] ?? $log->channel }}</span>
                            </td>
                            <td class="text-sm font-medium text-gray-900 dark:text-white max-w-xs truncate">{{ $log->subject }}</td>
                            <td class="text-center">
                                <span class="text-xs text-gray-500">{{ $types[$log->type] ?? $log->type }}</span>
                            </td>
                            <td class="text-center">
                                <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400">{{ $log->recipients_count }}</span>
                            </td>
                            <td>
                                <div class="user-cell">
                                    <div class="w-6 h-6 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-[9px] font-bold text-gray-500 flex-shrink-0">
                                        {{ strtoupper(substr($log->sender?->name ?? 'S', 0, 2)) }}
                                    </div>
                                    <span class="text-xs text-gray-600 dark:text-gray-400">{{ $log->sender?->name ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="text-right">
                                <button wire:click="delete({{ $log->id }})" wire:confirm="¿Eliminar este comunicado?" class="gb-btn-icon !w-7 !h-7 text-red-400 hover:text-red-600 hover:!border-red-200 hover:!bg-red-50" title="Eliminar">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="gb-empty-state">
                                    <div class="gb-empty-icon">📢</div>
                                    <div class="gb-empty-title">Sin comunicados enviados</div>
                                    <div class="gb-empty-desc">Envía tu primer comunicado para ver el historial</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="px-6 py-3 border-t border-gray-100 dark:border-gray-700/50">{{ $logs->links() }}</div>
        @endif
    </div>

    {{-- Send Modal --}}
    @if($showSendModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="$set('showSendModal', false)"></div>
            <div class="relative w-full max-w-lg rounded-2xl bg-white dark:bg-gray-800 shadow-2xl border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Nuevo Comunicado</h3>
                    <button wire:click="$set('showSendModal', false)" class="gb-btn-icon !w-8 !h-8">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form wire:submit="send" class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="gb-section-title mb-2 block">Canal</label>
                            <select wire:model="channel" class="gb-input">
                                @foreach($channels as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="gb-section-title mb-2 block">Destinatarios</label>
                            <select wire:model.live="sendType" class="gb-input">
                                @foreach($types as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
                            </select>
                        </div>
                    </div>
                    @if($sendType === 'section')
                        <div>
                            <label class="gb-section-title mb-2 block">Sección</label>
                            <select wire:model="section_id" class="gb-input">
                                <option value="">Seleccionar...</option>
                                @foreach($sections as $s) <option value="{{ $s->id }}">{{ $s->gradeLevel?->short_name }} {{ $s->name }}</option> @endforeach
                            </select>
                        </div>
                    @endif
                    <div>
                        <label class="gb-section-title mb-2 block">Asunto *</label>
                        <input type="text" wire:model="subject" class="gb-input" placeholder="Ej: Reunión de padres" />
                    </div>
                    <div>
                        <label class="gb-section-title mb-2 block">Mensaje *</label>
                        <textarea wire:model="body" rows="5" class="gb-input" placeholder="Escriba el contenido del comunicado..."></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" wire:click="$set('showSendModal', false)" class="gb-btn gb-btn-secondary">Cancelar</button>
                        <button type="submit" class="gb-btn gb-btn-primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            Enviar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
