<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Emisión de Certificados y Diplomas</h2>
                </div>

                <!-- Filtros -->
                <div class="mb-6 flex flex-col md:flex-row gap-4 bg-gray-50 p-4 rounded-lg border border-gray-100">
                    <div class="w-full md:w-1/2">
                        <x-input-label for="search" :value="__('Buscar Estudiante')" />
                        <x-text-input id="search" class="block mt-1 w-full" type="text" wire:model.live.debounce.300ms="search" placeholder="Nombre, apellido o correo..." />
                    </div>
                    <div class="w-full md:w-1/4">
                        <x-input-label for="minGrade" :value="__('Nota Mínima Aprobación')" />
                        <x-text-input id="minGrade" class="block mt-1 w-full" type="number" wire:model.live="minGrade" />
                    </div>
                </div>

                <!-- Tabla -->
                <div class="overflow-x-auto relative shadow-md sm:rounded-lg border border-gray-200">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th scope="col" class="py-3 px-6">Estudiante</th>
                                <th scope="col" class="py-3 px-6">Curso / Módulo</th>
                                <th scope="col" class="py-3 px-6 text-center">Calificación</th>
                                <th scope="col" class="py-3 px-6 text-center">Estado</th>
                                <th scope="col" class="py-3 px-6 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($enrollments as $enrollment)
                                {{-- Verificación de seguridad en cascada: Schedule -> Module -> Course --}}
                                @if($enrollment->courseSchedule && $enrollment->courseSchedule->module && $enrollment->courseSchedule->module->course)
                                    @php
                                        // Lógica unificada para Nombre en la Tabla
                                        $student = $enrollment->student ?? null;
                                        $user = $student->user ?? null;
                                        $studentName = 'N/A';
                                        
                                        if ($student) {
                                            $first = $student->first_name ?? $student->name ?? $student->nombres ?? $student->firstname ?? $user->first_name ?? $user->name ?? '';
                                            $last = $student->last_name ?? $student->apellidos ?? $student->lastname ?? $user->last_name ?? $user->lastname ?? '';
                                            $studentName = trim($first . ' ' . $last);
                                            
                                            if (empty($studentName)) {
                                                $studentName = $student->full_name ?? $student->fullname ?? $user->full_name ?? $user->fullname ?? '';
                                            }
                                            
                                            if (empty($studentName) && $student) {
                                                 $studentName = $student->email ?? $user->email ?? 'Sin Nombre';
                                            }
                                        }
                                    @endphp
                                <tr class="bg-white border-b hover:bg-gray-50 transition">
                                    <td class="py-4 px-6 font-medium text-gray-900">
                                        {{-- Usamos la variable calculada $studentName --}}
                                        <div class="text-base font-semibold">{{ $studentName }}</div>
                                        <div class="text-xs text-gray-500">{{ $enrollment->student->email }}</div>
                                    </td>
                                    <td class="py-4 px-6">
                                        {{-- Acceso corregido a través del módulo --}}
                                        {{ $enrollment->courseSchedule->module->course->name }}
                                        <div class="text-xs text-gray-400">
                                            Módulo: {{ $enrollment->courseSchedule->module->name ?? 'N/A' }} <br>
                                            Sección: {{ $enrollment->courseSchedule->section_name ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <span class="text-lg font-bold {{ $enrollment->final_grade >= $minGrade ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $enrollment->final_grade }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        @if($enrollment->final_grade >= $minGrade)
                                            <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded border border-green-400">Aprobado</span>
                                        @else
                                            <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded border border-red-400">Reprobado</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        @if($enrollment->final_grade >= $minGrade)
                                            {{-- Ruta corregida: Pasamos el ID del curso obtenido desde el módulo --}}
                                            <a href="{{ route('certificates.download', ['student' => $enrollment->student_id, 'course' => $enrollment->courseSchedule->module->course_id]) }}" 
                                               target="_blank"
                                               class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 focus:outline-none inline-flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                Generar Diploma
                                            </a>
                                        @else
                                            <button disabled class="text-white bg-gray-400 font-medium rounded-lg text-sm px-4 py-2 cursor-not-allowed inline-flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                                No disponible
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 px-6 text-center text-gray-500">
                                        No se encontraron registros de calificaciones que coincidan con la búsqueda.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $enrollments->links() }}
                </div>
            </div>
        </div>
    </div>
</div>