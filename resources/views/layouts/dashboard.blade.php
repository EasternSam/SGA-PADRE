<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-sga-background">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    {{-- Link para que funcionen los íconos (fas fa-eye, etc.) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" xintegrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Scripts -->
    {{-- Esta directiva de Vite carga tu app.css y app.js --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Carga los estilos de Livewire (para modales, etc.) -->
    @livewireStyles
    
    <style>
        /* Custom Scrollbar for Sidebar */
        .sidebar-scrollbar::-webkit-scrollbar {
            width: 5px;
        }
        .sidebar-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .sidebar-scrollbar::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 20px;
        }
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="h-full font-sans antialiased text-sga-text">
    <!-- Main Layout State -->
    <div x-data="{ open: false }" @keydown.window.escape="open = false" class="h-full flex overflow-hidden bg-sga-background">

        <!-- Mobile Sidebar Overlay -->
        <div x-show="open" class="relative z-50 lg:hidden" x-description="Off-canvas menu for mobile, show/hide based on off-canvas menu state." x-cloak>
            <div x-show="open" x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900/80"></div>

            <div class="fixed inset-0 flex">
                <div x-show="open" x-transition:enter="transition ease-in-out duration-300 transform" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in-out duration-300 transform" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="relative mr-16 flex w-full max-w-xs flex-1">
                    <div x-show="open" x-transition:enter="ease-in-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="absolute left-full top-0 flex w-16 justify-center pt-5">
                        <button type="button" class="-m-2.5 p-2.5" @click="open = false">
                            <span class="sr-only">Close sidebar</span>
                            <i class="fas fa-times text-white text-xl"></i>
                        </button>
                    </div>

                    <!-- Mobile Sidebar Content -->
                    <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-white px-6 pb-4">
                        <div class="flex h-16 shrink-0 items-center">
                             <x-application-logo class="h-8 w-auto text-sga-blue" />
                             <span class="ml-3 text-lg font-bold text-sga-blue tracking-tight">SGA PADRE</span>
                        </div>
                        <nav class="flex flex-1 flex-col">
                            <!-- MENÚ MÓVIL (Duplicado del Desktop para evitar error de vista no encontrada) -->
                            <ul role="list" class="flex flex-1 flex-col gap-y-7">
                                <!-- Principal Group -->
                                <li>
                                    <div class="text-xs font-semibold leading-6 text-sga-text-light uppercase tracking-wider mb-2 pl-2">Principal</div>
                                    <ul role="list" class="-mx-2 space-y-1">
                                        <li>
                                            <a href="{{ route('dashboard') }}" class="group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-medium {{ request()->routeIs('dashboard') ? 'bg-sga-blue/10 text-sga-blue' : 'text-sga-text hover:bg-gray-50 hover:text-sga-blue' }} transition-all duration-200">
                                                <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg {{ request()->routeIs('dashboard') ? 'text-sga-blue' : 'text-gray-400 group-hover:text-sga-blue' }} bg-white border border-gray-100 shadow-sm group-hover:border-sga-blue/20">
                                                    <i class="fas fa-home text-xs"></i>
                                                </div>
                                                Dashboard
                                            </a>
                                        </li>
                                    </ul>
                                </li>
        
                                <!-- Academic Group -->
                                @can('view_academic')
                                <li>
                                    <div class="text-xs font-semibold leading-6 text-sga-text-light uppercase tracking-wider mb-2 pl-2">Académico</div>
                                    <ul role="list" class="-mx-2 space-y-1" x-data="{ 
                                        expanded: {{ request()->routeIs('students.*') || request()->routeIs('teachers.*') || request()->routeIs('courses.*') ? 'true' : 'false' }},
                                        activeSub: null
                                    }">
                                        
                                        <!-- Estudiantes Dropdown -->
                                        <li x-data="{ open: {{ request()->routeIs('students.*') ? 'true' : 'false' }} }">
                                            <button @click="open = !open" type="button" class="flex items-center w-full gap-x-3 rounded-md p-2 text-sm leading-6 font-medium text-sga-text hover:bg-gray-50 hover:text-sga-blue transition-all duration-200 group text-left">
                                                <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg text-gray-400 group-hover:text-sga-blue bg-white border border-gray-100 shadow-sm group-hover:border-sga-blue/20">
                                                    <i class="fas fa-user-graduate text-xs"></i>
                                                </div>
                                                <span class="flex-1">Estudiantes</span>
                                                <i class="fas fa-chevron-right text-[10px] text-gray-400 transition-transform duration-200" :class="open ? 'rotate-90' : ''"></i>
                                            </button>
                                            <ul x-show="open" x-collapse class="mt-1 px-2 space-y-1 border-l-2 border-gray-100 ml-5" x-cloak>
                                                <li>
                                                    <a href="{{ route('students.index') }}" class="block rounded-md py-2 pr-2 pl-4 text-xs font-medium leading-6 {{ request()->routeIs('students.index') ? 'text-sga-blue bg-blue-50/50' : 'text-sga-text-light hover:text-sga-blue hover:bg-gray-50' }}">
                                                        Listado General
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="#" class="block rounded-md py-2 pr-2 pl-4 text-xs font-medium leading-6 {{ request()->routeIs('students.requests') ? 'text-sga-blue bg-blue-50/50' : 'text-sga-text-light hover:text-sga-blue hover:bg-gray-50' }}">
                                                        Solicitudes
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>
        
                                        <!-- Profesores Dropdown -->
                                        <li x-data="{ open: {{ request()->routeIs('teachers.*') ? 'true' : 'false' }} }">
                                            <button @click="open = !open" type="button" class="flex items-center w-full gap-x-3 rounded-md p-2 text-sm leading-6 font-medium text-sga-text hover:bg-gray-50 hover:text-sga-blue transition-all duration-200 group text-left">
                                                <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg text-gray-400 group-hover:text-sga-blue bg-white border border-gray-100 shadow-sm group-hover:border-sga-blue/20">
                                                    <i class="fas fa-chalkboard-teacher text-xs"></i>
                                                </div>
                                                <span class="flex-1">Profesores</span>
                                                <i class="fas fa-chevron-right text-[10px] text-gray-400 transition-transform duration-200" :class="open ? 'rotate-90' : ''"></i>
                                            </button>
                                            <ul x-show="open" x-collapse class="mt-1 px-2 space-y-1 border-l-2 border-gray-100 ml-5" x-cloak>
                                                <li>
                                                    <a href="{{ route('teachers.index') }}" class="block rounded-md py-2 pr-2 pl-4 text-xs font-medium leading-6 {{ request()->routeIs('teachers.index') ? 'text-sga-blue bg-blue-50/50' : 'text-sga-text-light hover:text-sga-blue hover:bg-gray-50' }}">
                                                        Directorio
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>
        
                                        <!-- Cursos (Single Link) -->
                                        <li>
                                            <a href="{{ route('courses.index') }}" class="group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-medium {{ request()->routeIs('courses.*') ? 'bg-sga-blue/10 text-sga-blue' : 'text-sga-text hover:bg-gray-50 hover:text-sga-blue' }} transition-all duration-200">
                                                <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg {{ request()->routeIs('courses.*') ? 'text-sga-blue' : 'text-gray-400 group-hover:text-sga-blue' }} bg-white border border-gray-100 shadow-sm group-hover:border-sga-blue/20">
                                                    <i class="fas fa-book-open text-xs"></i>
                                                </div>
                                                Cursos
                                            </a>
                                        </li>
        
                                    </ul>
                                </li>
                                @endcan
        
                                <!-- Administrative Group -->
                                @can('view_admin')
                                <li>
                                    <div class="text-xs font-semibold leading-6 text-sga-text-light uppercase tracking-wider mb-2 pl-2">Administración</div>
                                    <ul role="list" class="-mx-2 space-y-1">
                                        
                                        <!-- Finanzas Dropdown -->
                                        <li x-data="{ open: {{ request()->routeIs('finance.*') ? 'true' : 'false' }} }">
                                            <button @click="open = !open" type="button" class="flex items-center w-full gap-x-3 rounded-md p-2 text-sm leading-6 font-medium text-sga-text hover:bg-gray-50 hover:text-sga-blue transition-all duration-200 group text-left">
                                                <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg text-gray-400 group-hover:text-sga-blue bg-white border border-gray-100 shadow-sm group-hover:border-sga-blue/20">
                                                    <i class="fas fa-file-invoice-dollar text-xs"></i>
                                                </div>
                                                <span class="flex-1">Finanzas</span>
                                                <i class="fas fa-chevron-right text-[10px] text-gray-400 transition-transform duration-200" :class="open ? 'rotate-90' : ''"></i>
                                            </button>
                                            <ul x-show="open" x-collapse class="mt-1 px-2 space-y-1 border-l-2 border-gray-100 ml-5" x-cloak>
                                                <li>
                                                    <a href="{{ route('finance.concepts') }}" class="block rounded-md py-2 pr-2 pl-4 text-xs font-medium leading-6 {{ request()->routeIs('finance.concepts') ? 'text-sga-blue bg-blue-50/50' : 'text-sga-text-light hover:text-sga-blue hover:bg-gray-50' }}">
                                                        Conceptos de Pago
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>
        
                                        <!-- Reportes Dropdown -->
                                        <li x-data="{ open: {{ request()->routeIs('reports.*') ? 'true' : 'false' }} }">
                                            <button @click="open = !open" type="button" class="flex items-center w-full gap-x-3 rounded-md p-2 text-sm leading-6 font-medium text-sga-text hover:bg-gray-50 hover:text-sga-blue transition-all duration-200 group text-left">
                                                <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg text-gray-400 group-hover:text-sga-blue bg-white border border-gray-100 shadow-sm group-hover:border-sga-blue/20">
                                                    <i class="fas fa-chart-line text-xs"></i>
                                                </div>
                                                <span class="flex-1">Reportes</span>
                                                <i class="fas fa-chevron-right text-[10px] text-gray-400 transition-transform duration-200" :class="open ? 'rotate-90' : ''"></i>
                                            </button>
                                            <ul x-show="open" x-collapse class="mt-1 px-2 space-y-1 border-l-2 border-gray-100 ml-5" x-cloak>
                                                <li>
                                                    <a href="{{ route('reports.index') }}" class="block rounded-md py-2 pr-2 pl-4 text-xs font-medium leading-6 {{ request()->routeIs('reports.index') ? 'text-sga-blue bg-blue-50/50' : 'text-sga-text-light hover:text-sga-blue hover:bg-gray-50' }}">
                                                        Reportes Generales
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>
        
                                        <!-- Herramientas Dropdown -->
                                        <li x-data="{ open: {{ request()->routeIs('admin.*') ? 'true' : 'false' }} }">
                                            <button @click="open = !open" type="button" class="flex items-center w-full gap-x-3 rounded-md p-2 text-sm leading-6 font-medium text-sga-text hover:bg-gray-50 hover:text-sga-blue transition-all duration-200 group text-left">
                                                <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg text-gray-400 group-hover:text-sga-blue bg-white border border-gray-100 shadow-sm group-hover:border-sga-blue/20">
                                                    <i class="fas fa-cogs text-xs"></i>
                                                </div>
                                                <span class="flex-1">Herramientas</span>
                                                <i class="fas fa-chevron-right text-[10px] text-gray-400 transition-transform duration-200" :class="open ? 'rotate-90' : ''"></i>
                                            </button>
                                            <ul x-show="open" x-collapse class="mt-1 px-2 space-y-1 border-l-2 border-gray-100 ml-5" x-cloak>
                                                <li>
                                                    <a href="{{ route('admin.requests') }}" class="block rounded-md py-2 pr-2 pl-4 text-xs font-medium leading-6 {{ request()->routeIs('admin.requests') ? 'text-sga-blue bg-blue-50/50' : 'text-sga-text-light hover:text-sga-blue hover:bg-gray-50' }}">
                                                        Gestionar Solicitudes
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="{{ route('admin.database-import') }}" class="block rounded-md py-2 pr-2 pl-4 text-xs font-medium leading-6 {{ request()->routeIs('admin.database-import') ? 'text-sga-blue bg-blue-50/50' : 'text-sga-text-light hover:text-sga-blue hover:bg-gray-50' }}">
                                                        Importar Base de Datos
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                                @endcan
                             </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Desktop Sidebar -->
        <div class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col">
            <div class="flex grow flex-col gap-y-5 overflow-y-auto border-r border-sga-gray bg-white px-6 pb-4 sidebar-scrollbar shadow-[4px_0_24px_rgba(0,0,0,0.02)]">
                <!-- Logo -->
                <div class="flex h-16 shrink-0 items-center border-b border-gray-100">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 group">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-sga-blue text-white shadow-md group-hover:bg-sga-blue-light transition-colors">
                            <i class="fas fa-graduation-cap text-lg"></i>
                        </div>
                        <span class="text-lg font-bold text-sga-blue tracking-tight">SGA PADRE</span>
                    </a>
                </div>

                <!-- Navigation -->
                <nav class="flex flex-1 flex-col mt-4">
                     <!-- MENÚ ESCRITORIO -->
                     <ul role="list" class="flex flex-1 flex-col gap-y-7">
                        
                        <!-- Principal Group -->
                        <li>
                            <div class="text-xs font-semibold leading-6 text-sga-text-light uppercase tracking-wider mb-2 pl-2">Principal</div>
                            <ul role="list" class="-mx-2 space-y-1">
                                <li>
                                    <a href="{{ route('dashboard') }}" class="group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-medium {{ request()->routeIs('dashboard') ? 'bg-sga-blue/10 text-sga-blue' : 'text-sga-text hover:bg-gray-50 hover:text-sga-blue' }} transition-all duration-200">
                                        <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg {{ request()->routeIs('dashboard') ? 'text-sga-blue' : 'text-gray-400 group-hover:text-sga-blue' }} bg-white border border-gray-100 shadow-sm group-hover:border-sga-blue/20">
                                            <i class="fas fa-home text-xs"></i>
                                        </div>
                                        Dashboard
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Academic Group -->
                        @can('view_academic')
                        <li>
                            <div class="text-xs font-semibold leading-6 text-sga-text-light uppercase tracking-wider mb-2 pl-2">Académico</div>
                            <ul role="list" class="-mx-2 space-y-1" x-data="{ 
                                expanded: {{ request()->routeIs('students.*') || request()->routeIs('teachers.*') || request()->routeIs('courses.*') ? 'true' : 'false' }},
                                activeSub: null
                            }">
                                
                                <!-- Estudiantes Dropdown -->
                                <li x-data="{ open: {{ request()->routeIs('students.*') ? 'true' : 'false' }} }">
                                    <button @click="open = !open" type="button" class="flex items-center w-full gap-x-3 rounded-md p-2 text-sm leading-6 font-medium text-sga-text hover:bg-gray-50 hover:text-sga-blue transition-all duration-200 group text-left">
                                        <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg text-gray-400 group-hover:text-sga-blue bg-white border border-gray-100 shadow-sm group-hover:border-sga-blue/20">
                                            <i class="fas fa-user-graduate text-xs"></i>
                                        </div>
                                        <span class="flex-1">Estudiantes</span>
                                        <i class="fas fa-chevron-right text-[10px] text-gray-400 transition-transform duration-200" :class="open ? 'rotate-90' : ''"></i>
                                    </button>
                                    <ul x-show="open" x-collapse class="mt-1 px-2 space-y-1 border-l-2 border-gray-100 ml-5" x-cloak>
                                        <li>
                                            <a href="{{ route('students.index') }}" class="block rounded-md py-2 pr-2 pl-4 text-xs font-medium leading-6 {{ request()->routeIs('students.index') ? 'text-sga-blue bg-blue-50/50' : 'text-sga-text-light hover:text-sga-blue hover:bg-gray-50' }}">
                                                Listado General
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#" class="block rounded-md py-2 pr-2 pl-4 text-xs font-medium leading-6 {{ request()->routeIs('students.requests') ? 'text-sga-blue bg-blue-50/50' : 'text-sga-text-light hover:text-sga-blue hover:bg-gray-50' }}">
                                                Solicitudes
                                            </a>
                                        </li>
                                    </ul>
                                </li>

                                <!-- Profesores Dropdown -->
                                <li x-data="{ open: {{ request()->routeIs('teachers.*') ? 'true' : 'false' }} }">
                                    <button @click="open = !open" type="button" class="flex items-center w-full gap-x-3 rounded-md p-2 text-sm leading-6 font-medium text-sga-text hover:bg-gray-50 hover:text-sga-blue transition-all duration-200 group text-left">
                                        <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg text-gray-400 group-hover:text-sga-blue bg-white border border-gray-100 shadow-sm group-hover:border-sga-blue/20">
                                            <i class="fas fa-chalkboard-teacher text-xs"></i>
                                        </div>
                                        <span class="flex-1">Profesores</span>
                                        <i class="fas fa-chevron-right text-[10px] text-gray-400 transition-transform duration-200" :class="open ? 'rotate-90' : ''"></i>
                                    </button>
                                    <ul x-show="open" x-collapse class="mt-1 px-2 space-y-1 border-l-2 border-gray-100 ml-5" x-cloak>
                                        <li>
                                            <a href="{{ route('teachers.index') }}" class="block rounded-md py-2 pr-2 pl-4 text-xs font-medium leading-6 {{ request()->routeIs('teachers.index') ? 'text-sga-blue bg-blue-50/50' : 'text-sga-text-light hover:text-sga-blue hover:bg-gray-50' }}">
                                                Directorio
                                            </a>
                                        </li>
                                    </ul>
                                </li>

                                <!-- Cursos (Single Link) -->
                                <li>
                                    <a href="{{ route('courses.index') }}" class="group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-medium {{ request()->routeIs('courses.*') ? 'bg-sga-blue/10 text-sga-blue' : 'text-sga-text hover:bg-gray-50 hover:text-sga-blue' }} transition-all duration-200">
                                        <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg {{ request()->routeIs('courses.*') ? 'text-sga-blue' : 'text-gray-400 group-hover:text-sga-blue' }} bg-white border border-gray-100 shadow-sm group-hover:border-sga-blue/20">
                                            <i class="fas fa-book-open text-xs"></i>
                                        </div>
                                        Cursos
                                    </a>
                                </li>

                            </ul>
                        </li>
                        @endcan

                        <!-- Administrative Group -->
                        @can('view_admin')
                        <li>
                            <div class="text-xs font-semibold leading-6 text-sga-text-light uppercase tracking-wider mb-2 pl-2">Administración</div>
                            <ul role="list" class="-mx-2 space-y-1">
                                
                                <!-- Finanzas Dropdown -->
                                <li x-data="{ open: {{ request()->routeIs('finance.*') ? 'true' : 'false' }} }">
                                    <button @click="open = !open" type="button" class="flex items-center w-full gap-x-3 rounded-md p-2 text-sm leading-6 font-medium text-sga-text hover:bg-gray-50 hover:text-sga-blue transition-all duration-200 group text-left">
                                        <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg text-gray-400 group-hover:text-sga-blue bg-white border border-gray-100 shadow-sm group-hover:border-sga-blue/20">
                                            <i class="fas fa-file-invoice-dollar text-xs"></i>
                                        </div>
                                        <span class="flex-1">Finanzas</span>
                                        <i class="fas fa-chevron-right text-[10px] text-gray-400 transition-transform duration-200" :class="open ? 'rotate-90' : ''"></i>
                                    </button>
                                    <ul x-show="open" x-collapse class="mt-1 px-2 space-y-1 border-l-2 border-gray-100 ml-5" x-cloak>
                                        <li>
                                            <a href="{{ route('finance.concepts') }}" class="block rounded-md py-2 pr-2 pl-4 text-xs font-medium leading-6 {{ request()->routeIs('finance.concepts') ? 'text-sga-blue bg-blue-50/50' : 'text-sga-text-light hover:text-sga-blue hover:bg-gray-50' }}">
                                                Conceptos de Pago
                                            </a>
                                        </li>
                                    </ul>
                                </li>

                                <!-- Reportes Dropdown -->
                                <li x-data="{ open: {{ request()->routeIs('reports.*') ? 'true' : 'false' }} }">
                                    <button @click="open = !open" type="button" class="flex items-center w-full gap-x-3 rounded-md p-2 text-sm leading-6 font-medium text-sga-text hover:bg-gray-50 hover:text-sga-blue transition-all duration-200 group text-left">
                                        <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg text-gray-400 group-hover:text-sga-blue bg-white border border-gray-100 shadow-sm group-hover:border-sga-blue/20">
                                            <i class="fas fa-chart-line text-xs"></i>
                                        </div>
                                        <span class="flex-1">Reportes</span>
                                        <i class="fas fa-chevron-right text-[10px] text-gray-400 transition-transform duration-200" :class="open ? 'rotate-90' : ''"></i>
                                    </button>
                                    <ul x-show="open" x-collapse class="mt-1 px-2 space-y-1 border-l-2 border-gray-100 ml-5" x-cloak>
                                        <li>
                                            <a href="{{ route('reports.index') }}" class="block rounded-md py-2 pr-2 pl-4 text-xs font-medium leading-6 {{ request()->routeIs('reports.index') ? 'text-sga-blue bg-blue-50/50' : 'text-sga-text-light hover:text-sga-blue hover:bg-gray-50' }}">
                                                Reportes Generales
                                            </a>
                                        </li>
                                    </ul>
                                </li>

                                <!-- Herramientas Dropdown -->
                                <li x-data="{ open: {{ request()->routeIs('admin.*') ? 'true' : 'false' }} }">
                                    <button @click="open = !open" type="button" class="flex items-center w-full gap-x-3 rounded-md p-2 text-sm leading-6 font-medium text-sga-text hover:bg-gray-50 hover:text-sga-blue transition-all duration-200 group text-left">
                                        <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg text-gray-400 group-hover:text-sga-blue bg-white border border-gray-100 shadow-sm group-hover:border-sga-blue/20">
                                            <i class="fas fa-cogs text-xs"></i>
                                        </div>
                                        <span class="flex-1">Herramientas</span>
                                        <i class="fas fa-chevron-right text-[10px] text-gray-400 transition-transform duration-200" :class="open ? 'rotate-90' : ''"></i>
                                    </button>
                                    <ul x-show="open" x-collapse class="mt-1 px-2 space-y-1 border-l-2 border-gray-100 ml-5" x-cloak>
                                        <li>
                                            <a href="{{ route('admin.requests') }}" class="block rounded-md py-2 pr-2 pl-4 text-xs font-medium leading-6 {{ request()->routeIs('admin.requests') ? 'text-sga-blue bg-blue-50/50' : 'text-sga-text-light hover:text-sga-blue hover:bg-gray-50' }}">
                                                Gestionar Solicitudes
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('admin.database-import') }}" class="block rounded-md py-2 pr-2 pl-4 text-xs font-medium leading-6 {{ request()->routeIs('admin.database-import') ? 'text-sga-blue bg-blue-50/50' : 'text-sga-text-light hover:text-sga-blue hover:bg-gray-50' }}">
                                                Importar Base de Datos
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        @endcan
                     </ul>

                     <!-- Sidebar Footer (User Mini Profile) -->
                     <div class="mt-auto -mx-6 px-6 pt-6 pb-4 border-t border-gray-100">
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-x-4 px-2 py-3 text-sm font-semibold leading-6 text-sga-text hover:bg-gray-50 rounded-md transition-colors group">
                            <div class="h-8 w-8 rounded-full bg-sga-blue flex items-center justify-center text-white font-bold text-xs ring-2 ring-white shadow-sm">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <span class="sr-only">Your profile</span>
                            <div class="flex flex-col truncate">
                                <span aria-hidden="true" class="truncate">{{ Auth::user()->name }}</span>
                                <span class="text-xs font-normal text-gray-500 truncate">{{ Auth::user()->email }}</span>
                            </div>
                        </a>
                     </div>
                </nav>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex flex-1 flex-col lg:pl-72 transition-all duration-300">
            <!-- Top bar -->
            <header class="sticky top-0 z-10 flex h-16 flex-shrink-0 border-b border-sga-gray bg-white shadow-sm">
                <div class="flex flex-1 items-center justify-between px-4 sm:px-6 lg:px-8">
                    <!-- Hamburger button -->
                    <button @click.stop="open = !open" type="button" class="-m-2.5 p-2.5 text-sga-text-light lg:hidden hover:text-sga-blue transition-colors">
                        <span class="sr-only">Open sidebar</span>
                        <i class="fas fa-bars text-xl"></i>
                    </button>

                    <!-- Separador (solo se muestra en móvil) -->
                    <div class="h-6 w-px bg-sga-gray lg:hidden mx-4" aria-hidden="true"></div>

                    <div class="flex flex-1 items-center justify-between gap-x-4 self-stretch lg:gap-x-6">
                        <!-- Título de la página (slot 'header') -->
                        <div class="flex-1">
                            @if (isset($header))
                                <div class="flex h-full items-center text-base font-semibold leading-6 text-sga-text sm:text-xl">
                                    {{ $header }}
                                </div>
                            @endif
                        </div>

                        <!-- Menú de usuario (Desktop) -->
                        <div class="hidden lg:block">
                            <div class="hidden sm:flex sm:items-center">
                                <x-dropdown align="right" width="48">
                                    <x-slot name="trigger">
                                        <button class="inline-flex items-center gap-2 px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-sga-text-light bg-white hover:text-sga-blue focus:outline-none transition ease-in-out duration-150">
                                            <div>{{ Auth::user()->name }}</div>
                                            <div class="h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center text-sga-blue font-bold border border-gray-200">
                                                {{ substr(Auth::user()->name, 0, 1) }}
                                            </div>
                                            <i class="fas fa-chevron-down text-xs ml-1"></i>
                                        </button>
                                    </x-slot>

                                    <x-slot name="content">
                                        <x-dropdown-link :href="route('profile.edit')" wire:navigate class="flex items-center gap-2">
                                            <i class="fas fa-user-circle text-gray-400"></i>
                                            {{ __('Mi Perfil') }}
                                        </x-dropdown-link>

                                        <div class="border-t border-gray-100"></div>

                                        <!-- Authentication -->
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <x-dropdown-link :href="route('logout')"
                                                onclick="event.preventDefault(); this.closest('form').submit();"
                                                class="text-sga-danger hover:bg-red-50 hover:text-red-700 flex items-center gap-2">
                                                <i class="fas fa-sign-out-alt"></i>
                                                {{ __('Cerrar Sesión') }}
                                            </x-dropdown-link>
                                        </form>
                                    </x-slot>
                                </x-dropdown>
                            </div>
                        </div>

                        {{-- Botón de Cerrar Sesión para MÓVIL (lg:hidden) --}}
                        <div class="flex items-center lg:hidden gap-3">
                             <a href="{{ route('profile.edit') }}" class="text-sga-text-light hover:text-sga-blue">
                                <i class="fas fa-user-circle text-xl"></i>
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" onclick="event.preventDefault(); this.closest('form').submit();"
                                    class="-m-2.5 p-2.5 text-gray-400 hover:text-sga-danger transition-colors"
                                    title="Cerrar Sesión">
                                    <span class="sr-only">Cerrar Sesión</span>
                                    <i class="fas fa-sign-out-alt text-xl"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-sga-background p-4 sm:p-6 lg:p-8">
                 {{ $slot }}
            </main>
        </div>
    </div>

    <!-- Carga los scripts de Livewire (para wire:click, etc.) -->
    @livewireScripts
</body>

</html>