<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pensum Académico - {{ $career->code }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Montserrat:ital,wght@0,400;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        h1, h2, h3, .font-heading {
            font-family: 'Montserrat', sans-serif;
        }
        
        /* Custom Dotted Leader - More refined */
        .dotted-leader {
            background-image: radial-gradient(circle, #cbd5e1 1.5px, transparent 1.5px); /* Smaller dots, lighter color */
            background-size: 8px 100%; /* More spacing between dots */
            background-repeat: repeat-x;
            background-position: bottom 6px left 0; /* Align with text baseline */
            height: 1.5em;
        }

        .bg-itla-purple {
            background-color: #7b1fa2;
        }
        .text-itla-purple {
            color: #7b1fa2;
        }
        .bg-itla-dark {
            background-color: #4a148c;
        }

        /* Subtle Pattern Background */
        .bg-pattern {
            background-image: 
                linear-gradient(30deg, #7b1fa2 12%, transparent 12.5%, transparent 87%, #7b1fa2 87.5%, #7b1fa2),
                linear-gradient(150deg, #7b1fa2 12%, transparent 12.5%, transparent 87%, #7b1fa2 87.5%, #7b1fa2),
                linear-gradient(30deg, #7b1fa2 12%, transparent 12.5%, transparent 87%, #7b1fa2 87.5%, #7b1fa2),
                linear-gradient(150deg, #7b1fa2 12%, transparent 12.5%, transparent 87%, #7b1fa2 87.5%, #7b1fa2),
                linear-gradient(60deg, #7b1fa277 25%, transparent 25.5%, transparent 75%, #7b1fa277 75%, #7b1fa277),
                linear-gradient(60deg, #7b1fa277 25%, transparent 25.5%, transparent 75%, #7b1fa277 75%, #7b1fa277);
            background-position: 0 0, 0 0, 50px 90px, 50px 90px, 0 0, 50px 90px;
            background-size: 100px 180px;
            opacity: 0.03;
        }

        @media print {
            body { background: white; }
            .shadow-2xl { box-shadow: none; }
            .bg-pattern { display: none; }
            .max-w-5xl { max-width: 100%; margin: 0; padding: 0; }
        }
    </style>
</head>
<body class="bg-gray-50 text-slate-800 min-h-screen relative overflow-x-hidden">

    <!-- Decorative Background -->
    <div class="fixed inset-0 bg-pattern z-0 pointer-events-none"></div>

    <div class="relative z-10 max-w-5xl mx-auto md:my-8 bg-white shadow-2xl overflow-hidden print:shadow-none print:my-0">
        
        <!-- Header Section -->
        <header class="flex flex-col md:flex-row h-auto md:h-64 border-b-4 border-purple-800">
            <!-- Logo Area -->
            <div class="bg-itla-purple w-full md:w-1/3 p-8 text-white flex flex-col justify-center relative overflow-hidden">
                <!-- Subtle circle decoration behind logo -->
                <div class="absolute -top-10 -left-10 w-40 h-40 bg-white opacity-10 rounded-full blur-2xl"></div>
                
                <div class="relative z-10">
                    <!-- Siglas de la institución (usamos el nombre de la app configurado) -->
                    <h1 class="text-6xl font-heading font-black italic tracking-tighter mb-1 leading-none">
                        {{ strtoupper(substr(config('app.name', 'SGA'), 0, 3)) }}
                    </h1>
                    <div class="h-1 w-12 bg-purple-300 mb-4 rounded-full"></div>
                    <!-- Nombre completo de la institución -->
                    <div class="text-xs font-medium uppercase leading-relaxed tracking-wide border-l-[3px] border-purple-300 pl-3">
                        Centro Educativo<br>Universitario
                    </div>
                    <!-- Subtítulo -->
                    <p class="mt-3 text-[10px] text-purple-200 italic font-light tracking-wider">{{ config('app.name', 'Sistema de Gestión') }}</p>
                </div>
            </div>

            <!-- Title Area -->
            <div class="w-full md:w-2/3 relative flex flex-col justify-end bg-gray-900">
                <img src="https://images.unsplash.com/photo-1571171637578-41bc2dd41cd2?ixlib=rb-1.2.1&auto=format&fit=crop&w=1600&q=80" 
                     alt="Background" 
                     class="absolute inset-0 w-full h-full object-cover opacity-30 mix-blend-luminosity">
                
                <div class="absolute inset-0 bg-gradient-to-t from-purple-900/90 via-purple-900/40 to-transparent"></div>

                <div class="relative z-10 p-8 text-white text-right">
                    <p class="text-xs font-bold tracking-[0.2em] uppercase text-purple-300 mb-2">Plan de Estudios Oficial</p>
                    <h2 class="text-3xl md:text-4xl font-black uppercase leading-tight mb-4 drop-shadow-lg">
                        {{ $career->name }}<br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-white to-purple-200">
                            {{ $career->program_type === 'degree' ? 'GRADO ACADÉMICO' : 'CARRERA TÉCNICA' }}
                        </span>
                    </h2>
                    
                    <div class="flex justify-end gap-6 text-xs font-medium bg-black/20 inline-flex p-2 px-4 rounded-lg backdrop-blur-sm border border-white/10">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-green-400"></span>
                            <span class="opacity-80">Clave: <strong class="text-white">{{ $career->code }}</strong></span>
                        </div>
                        <div class="w-px h-4 bg-white/20"></div>
                        <div class="flex items-center gap-2">
                            <span class="text-purple-300">📅</span>
                            <span class="opacity-80">Generado: <strong class="text-white">{{ $generatedAt }}</strong></span>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Slogan Bar -->
        <div class="bg-gray-100 py-3 text-center border-b border-gray-200 shadow-inner">
            <p class="text-itla-purple font-heading font-semibold text-lg italic tracking-wide">"Excelencia académica para el futuro"</p>
        </div>

        <!-- Main Content Container -->
        <div class="p-6 md:p-10">
            
            <!-- Table Headers -->
            <div class="hidden md:grid grid-cols-12 gap-4 text-xs font-bold text-gray-500 uppercase tracking-wider mb-6 pb-2 border-b-2 border-purple-100 px-2">
                <div class="col-span-2">Código</div>
                <div class="col-span-5">Descripción del Curso</div>
                <div class="col-span-2 text-center">Créditos</div>
                <div class="col-span-3 text-right">Prerrequisitos</div>
            </div>

            <!-- Content Grid -->
            <div class="space-y-10">

                @php $totalAccumulated = 0; @endphp

                @foreach($modulesByPeriod as $period => $modules)
                    <section class="relative page-break-inside-avoid"> <!-- Evitar cortar secciones -->
                        <div class="flex items-center gap-3 mb-4">
                            <div class="h-8 w-8 rounded bg-purple-100 text-itla-purple flex items-center justify-center font-bold text-sm">{{ $period }}</div>
                            <h3 class="text-itla-purple font-heading font-bold text-lg uppercase tracking-tight">Cuatrimestre {{ $period }}</h3>
                            <div class="flex-grow h-px bg-purple-100"></div>
                        </div>
                        
                        <div class="space-y-1">
                            @php $periodCredits = 0; @endphp
                            @foreach($modules as $module)
                                @php $periodCredits += $module->credits; @endphp
                                <!-- Row Item -->
                                <div class="grid grid-cols-12 gap-2 md:gap-4 items-end p-2 rounded-lg hover:bg-purple-50 transition-colors group">
                                    <div class="col-span-2 text-sm font-semibold text-gray-700 font-mono">{{ $module->code }}</div>
                                    <div class="col-span-6 md:col-span-5 flex items-end overflow-hidden">
                                        <span class="whitespace-nowrap mr-2 text-gray-900 font-medium group-hover:text-itla-purple transition-colors">
                                            {{ $module->name }}
                                            @if($module->is_elective)
                                                <span class="text-[10px] text-amber-600 font-bold ml-1">(ELECTIVA)</span>
                                            @endif
                                        </span>
                                        <div class="dotted-leader flex-grow opacity-40"></div>
                                    </div>
                                    <div class="col-span-2 text-center text-sm font-bold text-gray-600">{{ $module->credits }}</div>
                                    <div class="col-span-2 md:col-span-3 text-right text-xs text-gray-400 font-mono">
                                        @if($module->prerequisites->count() > 0)
                                            @foreach($module->prerequisites as $pre)
                                                {{ $pre->code }}{{ !$loop->last ? ', ' : '' }}
                                            @endforeach
                                        @else
                                            -
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Subtotal Footer -->
                        <div class="flex justify-end items-center mt-3 pt-2 border-t border-dashed border-gray-200 mr-2 md:mr-[25%]">
                            <span class="text-[10px] uppercase tracking-widest text-gray-400 mr-4 font-bold">Créditos del Periodo</span>
                            <span class="bg-gray-100 text-gray-800 px-3 py-1 rounded text-sm font-bold border border-gray-200">{{ $periodCredits }}</span>
                        </div>
                        @php $totalAccumulated += $periodCredits; @endphp
                    </section>
                @endforeach

            </div>

            <!-- Total General -->
            <div class="mt-12 p-6 bg-purple-50 rounded-xl border border-purple-100 flex justify-between items-center">
                <div>
                    <h4 class="text-itla-purple font-bold text-lg">Resumen Académico</h4>
                    <p class="text-sm text-gray-500">Total acumulado de la carrera</p>
                </div>
                <div class="text-right">
                    <span class="block text-3xl font-black text-gray-800">{{ $totalAccumulated }}</span>
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Créditos Totales</span>
                </div>
            </div>

            <!-- Footer -->
            <footer class="mt-16 border-t-2 border-purple-100 pt-8 flex flex-col md:flex-row justify-between items-center text-gray-400 text-xs">
                <div class="flex items-center gap-4 mb-4 md:mb-0">
                    <!-- Placeholder Logo en Footer -->
                    <div class="h-6 w-6 bg-gray-300 rounded-full opacity-50"></div>
                    <div class="h-8 w-px bg-gray-200"></div>
                    <p>&copy; {{ date('Y') }} {{ config('app.name', 'Institución Educativa') }}</p>
                </div>
                <div class="text-right">
                    <p>Documento generado automáticamente</p>
                    <p>SGA System v1.0</p>
                </div>
            </footer>

        </div>
    </div>

</body>
</html>