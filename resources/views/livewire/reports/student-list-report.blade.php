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
                <th class="border border-gray-300 p-2 text-left">Apellidos y Nombres</th>
                <th class="border border-gray-300 p-2 text-left">Correo Electrónico</th>
                <th class="border border-gray-300 p-2 text-left">Teléfono</th>
                <th class="border border-gray-300 p-2 text-center">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['enrollments'] as $index => $enrollment)
                <tr>
                    <td class="border border-gray-300 p-2 text-center">{{ $index + 1 }}</td>
                    <td class="border border-gray-300 p-2 font-bold uppercase text-gray-800">{{ $enrollment->student->last_name }}, {{ $enrollment->student->first_name }}</td>
                    <td class="border border-gray-300 p-2">{{ $enrollment->student->email }}</td>
                    <td class="border border-gray-300 p-2">{{ $enrollment->student->phone ?? '-' }}</td>
                    <td class="border border-gray-300 p-2 text-center">{{ $enrollment->status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>