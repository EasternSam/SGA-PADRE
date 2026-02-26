{{-- Reporte de Listado de Estudiantes --}}
<div id="printable-area" class="bg-white text-black p-4 md:p-8 font-sans">
    <div class="border-b-2 border-gray-800 pb-4 mb-6">
        <h1 class="text-2xl font-bold uppercase">Nomina de Estudiantes</h1>
        <p class="text-sm text-gray-600">Curso: {{ $data['schedule']->module->course->name ?? 'N/A' }} ({{ $data['schedule']->section_name }})</p>
    </div>

    <table class="w-full border-collapse border border-gray-300 text-xs">
        <thead class="bg-gray-100">
            <tr>
                <th class="border border-gray-300 p-2 w-10">No.</th>
                <th class="border border-gray-300 p-2 text-left w-24">Matrícula</th>
                <th class="border border-gray-300 p-2 text-left">Nombre del Estudiante</th>
                <th class="border border-gray-300 p-2 text-left w-24">Teléfono</th>
                <th class="border border-gray-300 p-2 text-center w-24">Inscripción</th>
                <th class="border border-gray-300 p-2 text-center w-24">Estado Pago</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['enrollments'] as $index => $enrollment)
                <tr>
                    <td class="border border-gray-300 p-2 text-center">{{ $index + 1 }}</td>
                    <td class="border border-gray-300 p-2 text-left font-mono">
                        {{ $enrollment->student->student_code ?? $enrollment->student->cedula ?? $enrollment->student->id }}
                    </td>
                    <td class="border border-gray-300 p-2 font-bold uppercase text-gray-800">
                        {{ $enrollment->student->last_name }}, {{ $enrollment->student->first_name }}
                    </td>
                    <td class="border border-gray-300 p-2">
                        {{ $enrollment->student->mobile_phone ?? $enrollment->student->phone ?? '-' }}
                    </td>
                    <td class="border border-gray-300 p-2 text-center">
                        {{ \Carbon\Carbon::parse($enrollment->created_at)->format('d/m/Y') }}
                    </td>
                    <td class="border border-gray-300 p-2 text-center">
                        @if($enrollment->is_paid)
                            <span class="text-green-600 font-bold text-xs uppercase">Pagado</span>
                        @else
                            <span class="text-red-600 font-bold text-xs uppercase">Pendiente</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>