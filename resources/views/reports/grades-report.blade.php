{{-- Reporte de Calificaciones --}}
<div id="printable-area" class="bg-white text-black p-4 md:p-8 font-sans">
    <div class="border-b-2 border-gray-800 pb-4 mb-6 flex justify-between items-start">
        <div>
            <h1 class="text-2xl font-bold uppercase tracking-wider">Reporte de Calificaciones</h1>
            <p class="text-sm text-gray-600">Periodo Académico Vigente</p>
        </div>
        <div class="text-right text-xs">
            <p><strong>Generado:</strong> {{ now()->format('d/m/Y') }}</p>
        </div>
    </div>

    <div class="mb-6 grid grid-cols-2 gap-4 text-sm bg-gray-50 p-4 rounded border border-gray-200">
        <div>
            <p><strong class="block text-gray-500 text-xs uppercase">Curso</strong> {{ $data['schedule']->module->course->name ?? 'N/A' }}</p>
            <p class="mt-2"><strong class="block text-gray-500 text-xs uppercase">Sección</strong> {{ $data['schedule']->section_name ?? 'Única' }}</p>
        </div>
        <div class="text-right">
            <p><strong class="block text-gray-500 text-xs uppercase">Profesor</strong> {{ $data['schedule']->teacher->name ?? 'N/A' }}</p>
            <p class="mt-2"><strong class="block text-gray-500 text-xs uppercase">Módulo</strong> {{ $data['schedule']->module->name ?? 'General' }}</p>
        </div>
    </div>

    <table class="w-full border-collapse border border-gray-300 text-sm">
        <thead class="bg-gray-100">
            <tr>
                <th class="border border-gray-300 px-3 py-2 text-left w-12">#</th>
                <th class="border border-gray-300 px-3 py-2 text-left">Estudiante</th>
                <th class="border border-gray-300 px-3 py-2 text-center w-32">Estado</th>
                <th class="border border-gray-300 px-3 py-2 text-center w-24">Nota Final</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['enrollments'] as $index => $enrollment)
                <tr class="{{ $loop->even ? 'bg-gray-50' : '' }}">
                    <td class="border border-gray-300 px-3 py-2 text-center">{{ $index + 1 }}</td>
                    <td class="border border-gray-300 px-3 py-2 uppercase">
                        {{ $enrollment->student->last_name }}, {{ $enrollment->student->first_name }}
                    </td>
                    <td class="border border-gray-300 px-3 py-2 text-center text-xs">
                        {{ $enrollment->status }}
                    </td>
                    <td class="border border-gray-300 px-3 py-2 text-center font-bold {{ ($enrollment->final_grade ?? 0) < 70 ? 'text-red-600' : 'text-blue-600' }}">
                        {{ $enrollment->final_grade ?? '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="mt-12 flex justify-end">
        <div class="border-t border-gray-400 w-64 text-center pt-2 text-xs">
            Firma del Docente
        </div>
    </div>
</div>