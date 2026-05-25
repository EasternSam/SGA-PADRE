<div class="p-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">Ficha del Estudiante</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Perfil académico consolidado</p>

    {{-- Búsqueda --}}
    <div class="mb-6 max-w-md">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="🔍 Buscar por nombre o matrícula..." class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
        @if($searchResults->count() > 0 && !$student_id)
            <div class="mt-1 rounded-lg border border-gray-200 bg-white shadow-lg max-h-60 overflow-y-auto dark:bg-gray-700 dark:border-gray-600">
                @foreach($searchResults as $s)
                    <button wire:click="selectStudent({{ $s->id }})" class="block w-full px-4 py-2.5 text-left text-sm hover:bg-blue-50 dark:hover:bg-gray-600 border-b border-gray-100 dark:border-gray-600 last:border-0">
                        <span class="font-medium text-gray-900 dark:text-white">{{ $s->full_name }}</span>
                        <span class="text-xs text-gray-500 ml-2">{{ $s->student_id ?? '' }}</span>
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    @if($studentData)
        @php
            $st = (object) $studentData['student'];
            $sec = $studentData['section'] ? (object) $studentData['section'] : null;
            $att = $studentData['attendance'];
            $disc = $studentData['discipline'];
            $grades = $studentData['grades'];
        @endphp

        {{-- Student Header Card --}}
        <div class="rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 p-6 text-white shadow-lg mb-6">
            <div class="flex items-center gap-6">
                <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center text-2xl font-bold">
                    {{ strtoupper(substr($st->first_name ?? '', 0, 1)) }}{{ strtoupper(substr($st->last_name ?? '', 0, 1)) }}
                </div>
                <div class="flex-1">
                    <h2 class="text-2xl font-bold">{{ $st->first_name ?? '' }} {{ $st->last_name ?? '' }}</h2>
                    <div class="flex flex-wrap gap-4 mt-1 text-blue-100 text-sm">
                        <span>🎓 {{ $sec?->grade_level?->name ?? 'Sin grado' }} {{ $sec?->name ?? '' }}</span>
                        <span>🆔 {{ $st->student_id ?? 'N/A' }}</span>
                        @if($st->birth_date ?? null)
                            <span>🎂 {{ \Carbon\Carbon::parse($st->birth_date)->format('d/m/Y') }}</span>
                        @endif
                        <span>📊 {{ $st->status ?? 'Activo' }}</span>
                    </div>
                </div>
                <div class="text-right space-y-1">
                    <a href="{{ route('documents.constancia', $st->id) }}" target="_blank" class="block rounded-lg bg-white/20 px-3 py-1 text-xs hover:bg-white/30 transition">📄 Constancia</a>
                    <a href="{{ route('documents.conducta', $st->id) }}" target="_blank" class="block rounded-lg bg-white/20 px-3 py-1 text-xs hover:bg-white/30 transition">📋 Conducta</a>
                    <a href="{{ route('documents.record', $st->id) }}" target="_blank" class="block rounded-lg bg-white/20 px-3 py-1 text-xs hover:bg-white/30 transition">📝 Récord</a>
                    <a href="{{ route('documents.certificado', $st->id) }}" target="_blank" class="block rounded-lg bg-white/20 px-3 py-1 text-xs hover:bg-white/30 transition">🎓 Certificado</a>
                    <a href="{{ route('documents.ficha', $st->id) }}" target="_blank" class="block rounded-lg bg-white/20 px-3 py-1 text-xs hover:bg-white/30 transition">📎 Ficha</a>
                    <a href="{{ route('documents.transferencia', $st->id) }}" target="_blank" class="block rounded-lg bg-white/20 px-3 py-1 text-xs hover:bg-white/30 transition">🔄 Transferencia</a>
                    <a href="{{ route('documents.pagos', $st->id) }}" target="_blank" class="block rounded-lg bg-white/20 px-3 py-1 text-xs hover:bg-white/30 transition">💰 Pagos</a>
                    <a href="{{ route('reports.attendance.student', $st->id) }}" target="_blank" class="block rounded-lg bg-white/20 px-3 py-1 text-xs hover:bg-white/30 transition">✅ Asistencia</a>
                </div>
            </div>
        </div>

        {{-- Stats Row --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="rounded-xl bg-green-50 dark:bg-green-900/20 p-4 text-center border border-green-200 dark:border-green-800">
                <div class="text-2xl font-bold text-green-700 dark:text-green-400">
                    {{ $att['total'] > 0 ? number_format(($att['present'] / $att['total']) * 100, 0) : 0 }}%
                </div>
                <p class="text-xs text-green-600 mt-1">Asistencia</p>
            </div>
            <div class="rounded-xl bg-blue-50 dark:bg-blue-900/20 p-4 text-center border border-blue-200 dark:border-blue-800">
                <div class="text-2xl font-bold text-blue-700 dark:text-blue-400">{{ $att['present'] }}</div>
                <p class="text-xs text-blue-600 mt-1">Presencias</p>
            </div>
            <div class="rounded-xl bg-red-50 dark:bg-red-900/20 p-4 text-center border border-red-200 dark:border-red-800">
                <div class="text-2xl font-bold text-red-700 dark:text-red-400">{{ $att['absent'] }}</div>
                <p class="text-xs text-red-600 mt-1">Ausencias</p>
            </div>
            <div class="rounded-xl bg-orange-50 dark:bg-orange-900/20 p-4 text-center border border-orange-200 dark:border-orange-800">
                <div class="text-2xl font-bold text-orange-700 dark:text-orange-400">{{ count($disc) }}</div>
                <p class="text-xs text-orange-600 mt-1">Incidencias</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Calificaciones --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4">📝 Calificaciones por Período</h3>
                @foreach($grades as $gp)
                    <div class="mb-3">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $gp['period']['name'] ?? '' }}</span>
                            @if($gp['avg'])
                                <span class="text-sm font-bold {{ $gp['avg'] >= 80 ? 'text-green-600' : ($gp['avg'] >= 70 ? 'text-yellow-600' : 'text-red-600') }}">
                                    Prom: {{ $gp['avg'] }}
                                </span>
                            @else
                                <span class="text-xs text-gray-400">Sin notas</span>
                            @endif
                        </div>
                        @if(count($gp['grades']) > 0)
                            <div class="flex flex-wrap gap-1">
                                @foreach($gp['grades'] as $g)
                                    @php $score = $g['score'] ?? null; @endphp
                                    <span class="inline-flex rounded px-2 py-0.5 text-xs font-medium 
                                        {{ $score >= 90 ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-400' : 
                                           ($score >= 70 ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-400' : 
                                           'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-400') }}"
                                        title="{{ $g['section_subject']['subject']['name'] ?? '' }}">
                                        {{ Str::limit($g['section_subject']['subject']['name'] ?? '', 8) }}: {{ $score ?? '—' }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach

                @if(count($grades) === 0)
                    <p class="text-center text-gray-400 text-sm py-4">Sin calificaciones registradas</p>
                @endif
            </div>

            {{-- Disciplina --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4">⚠️ Historial Disciplinario</h3>
                @if(count($disc) > 0)
                    <div class="space-y-2">
                        @foreach($disc as $d)
                            <div class="flex items-start gap-3 p-2 rounded-lg {{ $d['severity'] === 'muy_grave' ? 'bg-red-50 dark:bg-red-900/20' : ($d['severity'] === 'grave' ? 'bg-orange-50 dark:bg-orange-900/20' : 'bg-yellow-50 dark:bg-yellow-900/20') }}">
                                <span class="text-lg mt-0.5">
                                    @switch($d['severity'])
                                        @case('leve') 🟡 @break
                                        @case('grave') 🟠 @break
                                        @case('muy_grave') 🔴 @break
                                    @endswitch
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ \App\Models\DisciplineRecord::CATEGORIES[$d['category']] ?? $d['category'] }}
                                    </p>
                                    <p class="text-xs text-gray-500 truncate">{{ $d['description'] }}</p>
                                    <p class="text-[10px] text-gray-400 mt-0.5">{{ \Carbon\Carbon::parse($d['date'])->format('d/m/Y') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-green-500 text-sm py-4">🎉 Sin incidencias disciplinarias</p>
                @endif
            </div>
        </div>
    @else
        <div class="rounded-xl border-2 border-dashed border-gray-300 p-12 text-center dark:border-gray-600">
            <p class="text-lg text-gray-400 mb-1">👆 Busca un estudiante para ver su ficha completa</p>
            <p class="text-sm text-gray-400">Incluye: calificaciones, asistencia, disciplina e inscripción</p>
        </div>
    @endif
</div>
