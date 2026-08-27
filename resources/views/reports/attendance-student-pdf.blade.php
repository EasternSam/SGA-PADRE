@extends('reports.layouts.pdf')
@section('title', 'Reporte Individual de Asistencia')
@section('subtitle', $student->full_name . ' | ' . ($section?->gradeLevel?->name ?? '') . ' ' . ($section?->name ?? ''))

@section('content')
    <table style="width: 100%; margin-bottom: 15px;">
        <tr>
            <td style="width: 50%;"><strong>Estudiante:</strong> {{ $student->full_name }}</td>
            <td style="width: 50%;"><strong>Año Escolar:</strong> {{ $activeYear?->name ?? '—' }}</td>
        </tr>
        <tr>
            <td><strong>Grado/Sección:</strong> {{ $section?->gradeLevel?->name ?? '' }} {{ $section?->name ?? '' }}</td>
            <td><strong>Matrícula:</strong> {{ $student->student_id ?? $student->id }}</td>
        </tr>
    </table>

    {{-- Resumen General --}}
    <h3 style="color: #1e3a8a; border-bottom: 2px solid #1e3a8a; padding-bottom: 4px; margin-bottom: 10px;">Resumen General</h3>
    <table class="data-table" style="width: 60%; margin: 0 auto 15px;">
        <tr>
            <th class="text-center">Días Registrados</th>
            <th class="text-center" style="color: #166534;">Presencias</th>
            <th class="text-center" style="color: #991b1b;">Ausencias</th>
            <th class="text-center" style="color: #92400e;">Tardanzas</th>
            <th class="text-center" style="color: #1e40af;">Excusas</th>
            <th class="text-center">% Asistencia</th>
        </tr>
        <tr>
            <td class="text-center font-bold">{{ $overall['total'] }}</td>
            <td class="text-center font-bold" style="color: #166534;">{{ $overall['present'] }}</td>
            <td class="text-center font-bold" style="color: #991b1b;">{{ $overall['absent'] }}</td>
            <td class="text-center font-bold" style="color: #92400e;">{{ $overall['late'] }}</td>
            <td class="text-center font-bold" style="color: #1e40af;">{{ $overall['excused'] }}</td>
            <td class="text-center font-bold">
                {{ $overall['total'] > 0 ? number_format(($overall['present'] / $overall['total']) * 100, 1) : 0 }}%
            </td>
        </tr>
    </table>

    {{-- Resumen Mensual --}}
    <h3 style="color: #1e3a8a; border-bottom: 2px solid #1e3a8a; padding-bottom: 4px; margin-bottom: 10px;">Desglose Mensual</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>Mes</th>
                <th class="text-center">Presencias</th>
                <th class="text-center">Ausencias</th>
                <th class="text-center">Tardanzas</th>
                <th class="text-center">Excusas</th>
                <th class="text-center">Total</th>
                <th class="text-center">% Asistencia</th>
            </tr>
        </thead>
        <tbody>
            @foreach($monthlySummary as $month => $data)
                <tr>
                    <td class="font-bold">{{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}</td>
                    <td class="text-center" style="color: #166534;">{{ $data['present'] }}</td>
                    <td class="text-center" style="color: #991b1b;">{{ $data['absent'] }}</td>
                    <td class="text-center" style="color: #92400e;">{{ $data['late'] }}</td>
                    <td class="text-center" style="color: #1e40af;">{{ $data['excused'] }}</td>
                    <td class="text-center">{{ $data['total'] }}</td>
                    <td class="text-center font-bold">
                        {{ $data['total'] > 0 ? number_format(($data['present'] / $data['total']) * 100, 1) : 0 }}%
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Detalle de Ausencias --}}
    @php $absences = $records->whereIn('status', ['absent', 'excused']); @endphp
    @if($absences->count() > 0)
        <h3 style="color: #1e3a8a; border-bottom: 2px solid #1e3a8a; padding-bottom: 4px; margin: 15px 0 10px;">Detalle de Ausencias</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Día</th>
                    <th class="text-center">Estado</th>
                    <th>Motivo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($absences as $absence)
                    <tr>
                        <td>{{ $absence->date->format('d/m/Y') }}</td>
                        <td>{{ $absence->date->translatedFormat('l') }}</td>
                        <td class="text-center">
                            @if($absence->status === 'absent')
                                <span style="color: #991b1b; font-weight: bold;">Ausente</span>
                            @else
                                <span style="color: #1e40af;">Excusa</span>
                            @endif
                        </td>
                        <td>{{ $absence->notes ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Firmas --}}
    <table style="width: 100%; margin-top: 30px;">
        <tr>
            <td style="text-align: center; width: 50%; padding-top: 30px;">
                <div style="border-top: 1px solid #374151; display: inline-block; min-width: 200px; padding-top: 5px; font-size: 8pt; color: #6b7280;">
                    Docente Titular
                </div>
            </td>
            <td style="text-align: center; width: 50%; padding-top: 30px;">
                <div style="border-top: 1px solid #374151; display: inline-block; min-width: 200px; padding-top: 5px; font-size: 8pt; color: #6b7280;">
                    Director/a
                </div>
            </td>
        </tr>
    </table>
@endsection
