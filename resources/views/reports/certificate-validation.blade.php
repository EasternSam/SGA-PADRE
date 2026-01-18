<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Documento Oficial | SGA</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Fuente Inter para un look más profesional -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        /* Patrón de fondo sutil para dar textura */
        .bg-security {
            background-color: #f8fafc;
            background-image: radial-gradient(#e2e8f0 1px, transparent 1px);
            background-size: 24px 24px;
        }
    </style>
</head>
<body class="bg-security min-h-screen flex items-center justify-center p-4 text-slate-800">

    @php
        // Lógica unificada para Nombre e Iniciales
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

    <div class="max-w-md w-full bg-white shadow-2xl rounded-2xl overflow-hidden border border-slate-200 relative">
        
        <!-- Barra superior de color institucional -->
        <div class="h-2 w-full bg-gradient-to-r from-blue-600 via-blue-500 to-indigo-600"></div>

        <!-- Encabezado con Logo y Estado -->
        <div class="bg-slate-50 p-8 pb-6 text-center border-b border-slate-100">
            <div class="inline-block p-3 bg-white rounded-xl shadow-sm border border-slate-100 mb-6">
                <!-- Usamos asset() para cargar el logo que tienes en public -->
                <img src="{{ asset('centuu.png') }}" alt="Logo Institución" class="h-16 w-auto object-contain">
            </div>
            
            <div class="flex items-center justify-center gap-2 mb-2">
                <span class="flex h-3 w-3 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                </span>
                <span class="text-green-700 font-bold tracking-wide uppercase text-sm">Certificado Auténtico</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 leading-tight">Verificación Exitosa</h1>
            <p class="text-slate-500 text-sm mt-2">El documento digital consultado es válido y fue emitido por nuestra institución.</p>
        </div>

        <!-- Cuerpo de Detalles -->
        <div class="p-6 bg-white">
            <div class="space-y-6">
                
                <!-- Estudiante -->
                <div class="flex gap-4 items-start">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Estudiante Certificado</p>
                        <p class="text-lg font-bold text-slate-800">{{ $studentName }}</p>
                        <p class="text-sm text-slate-500">{{ $student->email }}</p>
                    </div>
                </div>

                <!-- Línea separadora sutil -->
                <div class="h-px bg-slate-100 w-full"></div>

                <!-- Curso -->
                <div class="flex gap-4 items-start">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Programa Académico</p>
                        <p class="text-lg font-bold text-indigo-900 leading-snug">{{ $course->name }}</p>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                COD: {{ $course->code ?? $course->id }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Info de Verificación -->
                <div class="bg-green-50 rounded-lg p-4 border border-green-100 mt-2">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            <span class="text-sm font-semibold text-green-900">Validado en Sistema</span>
                        </div>
                        <span class="text-xs font-mono text-green-700 bg-white px-2 py-1 rounded shadow-sm border border-green-100">
                            {{ $verified_at->format('d/m/Y H:i') }}
                        </span>
                    </div>
                </div>

            </div>
        </div>

        <!-- Footer -->
        <div class="bg-slate-50 p-4 text-center border-t border-slate-100">
            <p class="text-[10px] text-slate-400 uppercase tracking-widest font-medium">Sistema de Gestión Académica Segura</p>
            <p class="text-[10px] text-slate-300 mt-1">&copy; {{ date('Y') }} Todos los derechos reservados.</p>
        </div>
    </div>

</body>
</html>