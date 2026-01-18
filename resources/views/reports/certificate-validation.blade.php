<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validación de Certificado | SGA</title>
    <!-- Usamos Tailwind vía CDN para esta vista pública ligera -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

    @php
        // Lógica unificada para Nombre e Iniciales
        // Nota: Asumimos que $student se pasa desde el controlador. 
        // Si se pasara $enrollment en su lugar, se usaría $enrollment->student.
        // Aquí usamos $student directamente ya que es lo que el controlador CertificatePdfController envía.
        
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

    <div class="bg-white rounded-lg shadow-xl overflow-hidden max-w-md w-full">
        <!-- Encabezado de Éxito -->
        <div class="bg-green-600 p-6 text-center">
            <div class="mx-auto bg-white rounded-full h-16 w-16 flex items-center justify-center mb-4 shadow-sm">
                <svg class="h-10 w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-white">Certificado Válido</h2>
            <p class="text-green-100 mt-1 text-sm">Documento Auténtico Verificado</p>
        </div>

        <!-- Detalles del Certificado -->
        <div class="p-6">
            <div class="space-y-4">
                <div class="border-b border-gray-100 pb-3">
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Estudiante</p>
                    <p class="text-lg font-bold text-gray-800">{{ $studentName }}</p>
                    <p class="text-sm text-gray-500">{{ $student->email }}</p>
                </div>

                <div class="border-b border-gray-100 pb-3">
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Curso Aprobado</p>
                    <p class="text-lg font-bold text-blue-700">{{ $course->name }}</p>
                    <p class="text-sm text-gray-500">Código: {{ $course->code ?? 'N/A' }}</p>
                </div>

                <div class="bg-gray-50 p-3 rounded-md">
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Estado de Validación</p>
                    <div class="flex items-center text-sm text-green-700 font-medium">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                        Verificado en sistema: {{ $verified_at->format('d/m/Y H:i:s') }}
                    </div>
                </div>
            </div>

            <div class="mt-8 text-center">
                <p class="text-xs text-gray-400">Sistema de Gestión Académica (SGA)</p>
                <p class="text-xs text-gray-400">&copy; {{ date('Y') }} Todos los derechos reservados.</p>
            </div>
        </div>
    </div>

</body>
</html>