<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal de Padres — {{ $student->full_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="min-h-screen bg-gray-50">
    {{-- Header --}}
    <header class="bg-gradient-to-r from-blue-700 to-indigo-800 text-white shadow-lg">
        <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-3xl"></span>
                <div>
                    <h1 class="text-lg font-bold">{{ $student->first_name }} {{ $student->last_name }}</h1>
                    <p class="text-blue-200 text-xs">{{ $student->gradeLevel?->name ?? '' }} {{ $student->section?->name ?? '' }} · {{ $activeYear?->name ?? '' }}</p>
                </div>
            </div>
            <a href="{{ route('parent.logout') }}" class="rounded-lg bg-white/20 px-3 py-1.5 text-xs hover:bg-white/30 transition">Salir</a>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 py-6">
        {{-- KPIs --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="rounded-xl bg-white p-4 shadow-sm border border-gray-200 text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $attendance['pct'] }}%</div>
                <p class="text-xs text-gray-500 mt-1">Asistencia</p>
            </div>
            <div class="rounded-xl bg-white p-4 shadow-sm border border-gray-200 text-center">
                @php $lastAvg = !empty($gradeData) ? end($gradeData)['avg'] : null; @endphp
                <div class="text-2xl font-bold {{ ($lastAvg ?? 0) >= 70 ? 'text-green-600' : 'text-red-600' }}">{{ $lastAvg ?? '—' }}</div>
                <p class="text-xs text-gray-500 mt-1">Último Promedio</p>
            </div>
            <div class="rounded-xl bg-white p-4 shadow-sm border border-gray-200 text-center">
                <div class="text-2xl font-bold text-red-600">{{ $attendance['absent'] }}</div>
                <p class="text-xs text-gray-500 mt-1">Ausencias</p>
            </div>
            <div class="rounded-xl bg-white p-4 shadow-sm border border-gray-200 text-center">
                @php $balance = $totalDue - $totalPaid; @endphp
                <div class="text-2xl font-bold {{ $balance > 0 ? 'text-red-600' : 'text-green-600' }}">RD${{ number_format($balance, 0) }}</div>
                <p class="text-xs text-gray-500 mt-1">Balance</p>
            </div>
        </div>

        {{-- Grades --}}
        <div class="rounded-xl bg-white border border-gray-200 shadow-sm p-5 mb-6">
            <h2 class="text-base font-bold text-gray-900 mb-4">Calificaciones</h2>
            @forelse($gradeData as $gd)
                <div class="mb-4 {{ !$loop->last ? 'pb-4 border-b border-gray-100' : '' }}">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm font-bold text-gray-700">{{ $gd['period']->name }}</h3>
                        <span class="text-sm font-bold {{ ($gd['avg'] ?? 0) >= 70 ? 'text-green-600' : 'text-red-600' }}">Prom: {{ $gd['avg'] }}</span>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        @foreach($gd['grades'] as $g)
                            <div class="flex items-center justify-between px-3 py-1.5 rounded-lg bg-gray-50">
                                <span class="text-xs text-gray-600 truncate">{{ $g->sectionSubject?->subject?->name ?? '—' }}</span>
                                <span class="text-sm font-bold {{ ($g->score ?? 0) >= 70 ? 'text-green-600' : 'text-red-600' }}">{{ $g->score ?? '—' }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-center text-gray-400 text-sm py-4">Sin calificaciones cargadas</p>
            @endforelse
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Attendance --}}
            <div class="rounded-xl bg-white border border-gray-200 shadow-sm p-5">
                <h2 class="text-base font-bold text-gray-900 mb-4">Asistencia</h2>
                <div class="flex items-center justify-center mb-4">
                    <div class="relative w-24 h-24">
                        @php $pct = $attendance['pct']; @endphp
                        <svg class="w-24 h-24 transform -rotate-90" viewBox="0 0 120 120">
                            <circle cx="60" cy="60" r="50" stroke="#e5e7eb" stroke-width="10" fill="none"/>
                            <circle cx="60" cy="60" r="50" stroke="{{ $pct >= 80 ? '#10b981' : ($pct >= 60 ? '#f59e0b' : '#ef4444') }}" stroke-width="10" fill="none"
                                stroke-dasharray="{{ 2 * 3.14159 * 50 }}" stroke-dashoffset="{{ 2 * 3.14159 * 50 * (1 - $pct / 100) }}" stroke-linecap="round"/>
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-xl font-bold text-gray-900">{{ $pct }}%</span>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2 text-center text-sm">
                    <div><span class="font-bold text-green-600">{{ $attendance['present'] }}</span><br><span class="text-[10px] text-gray-400">Presencias</span></div>
                    <div><span class="font-bold text-red-600">{{ $attendance['absent'] }}</span><br><span class="text-[10px] text-gray-400">Ausencias</span></div>
                    <div><span class="font-bold text-yellow-600">{{ $attendance['late'] }}</span><br><span class="text-[10px] text-gray-400">Tardanzas</span></div>
                </div>
            </div>

            {{-- Payments --}}
            <div class="rounded-xl bg-white border border-gray-200 shadow-sm p-5">
                <h2 class="text-base font-bold text-gray-900 mb-4">Pagos</h2>
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="rounded-lg bg-blue-50 p-3 text-center">
                        <div class="text-lg font-bold text-blue-700">RD${{ number_format($totalDue, 0) }}</div>
                        <p class="text-[10px] text-blue-600">Total</p>
                    </div>
                    <div class="rounded-lg bg-green-50 p-3 text-center">
                        <div class="text-lg font-bold text-green-700">RD${{ number_format($totalPaid, 0) }}</div>
                        <p class="text-[10px] text-green-600">Pagado</p>
                    </div>
                </div>
                @foreach($payments->take(5) as $p)
                    <div class="flex items-center justify-between py-1.5 {{ !$loop->last ? 'border-b border-gray-50' : '' }}">
                        <span class="text-xs text-gray-600">{{ $p->concept }}</span>
                        <span class="text-xs font-bold {{ $p->status === 'paid' ? 'text-green-600' : ($p->status === 'partial' ? 'text-yellow-600' : 'text-red-600') }}">
                            {{ \App\Models\StudentPayment::STATUSES[$p->status] ?? $p->status }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </main>

    <footer class="text-center py-4 text-xs text-gray-400">
        Portal de Padres · {{ config('app.name') }}
    </footer>
</body>
</html>
