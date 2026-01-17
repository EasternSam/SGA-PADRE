<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SGA PADRE') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Styles -->
    @livewireStyles
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-gray-900" x-data="{ sidebarOpen: false }">
    
    <div class="min-h-screen flex flex-col md:flex-row">
        
        <!-- Mobile Header -->
        <div class="md:hidden flex items-center justify-between bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 p-4">
            <div class="flex items-center">
                <a href="{{ route('dashboard') }}">
                    <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                </a>
            </div>
            <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>

        <!-- Sidebar -->
        <aside 
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transform md:translate-x-0 md:static md:inset-auto transition-transform duration-300 ease-in-out flex flex-col"
        >
            <!-- Logo Area -->
            <div class="flex items-center justify-center h-16 border-b border-gray-200 dark:border-gray-700 bg-indigo-600 dark:bg-indigo-900">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-2 text-white font-bold text-xl tracking-wider">
                    <x-application-logo class="block h-8 w-auto fill-current text-white" />
                    <span>SGA PADRE</span>
                </a>
            </div>

            <!-- User Info (Optional Mini Profile) -->
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center space-x-3 bg-gray-50 dark:bg-gray-900/50">
                <div class="h-10 w-10 rounded-full bg-indigo-100 dark:bg-indigo-800 flex items-center justify-center text-indigo-600 dark:text-indigo-200 font-bold text-lg">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate w-32">{{ Auth::user()->email }}</p>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1 custom-scrollbar">
                
                <!-- Section: Principal -->
                <div class="pb-2">
                    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Principal</p>
                    
                    <a href="{{ route('dashboard') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-300' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
                        <svg class="mr-3 flex-shrink-0 h-5 w-5 {{ request()->routeIs('dashboard') ? 'text-indigo-600 dark:text-indigo-300' : 'text-gray-400 group-hover:text-gray-500 dark:text-gray-400 dark:group-hover:text-gray-300' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Dashboard
                    </a>
                </div>

                <!-- Section: Académico -->
                @can('view_academic')
                <div class="py-2">
                    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Académico</p>
                    
                    <!-- Estudiantes Dropdown -->
                    <div x-data="{ open: {{ request()->routeIs('students.*') ? 'true' : 'false' }} }">
                        <button @click="open = !open" class="w-full group flex items-center justify-between px-3 py-2 text-sm font-medium rounded-md transition-colors text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white">
                            <div class="flex items-center">
                                <svg class="mr-3 flex-shrink-0 h-5 w-5 text-gray-400 group-hover:text-gray-500 dark:text-gray-400 dark:group-hover:text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                Estudiantes
                            </div>
                            <svg :class="open ? 'rotate-90' : ''" class="ml-2 h-4 w-4 text-gray-400 transform transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div x-show="open" x-collapse class="space-y-1 mt-1 pl-10">
                            <a href="{{ route('students.index') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('students.index') ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}">
                                Listado
                            </a>
                            <a href="#" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('students.requests') ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}">
                                Solicitudes
                            </a>
                        </div>
                    </div>

                    <!-- Profesores -->
                    <a href="{{ route('teachers.index') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('teachers.*') ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-300' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
                        <svg class="mr-3 flex-shrink-0 h-5 w-5 {{ request()->routeIs('teachers.*') ? 'text-indigo-600 dark:text-indigo-300' : 'text-gray-400 group-hover:text-gray-500 dark:text-gray-400 dark:group-hover:text-gray-300' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Profesores
                    </a>

                    <!-- Cursos -->
                    <a href="{{ route('courses.index') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('courses.*') ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-300' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
                        <svg class="mr-3 flex-shrink-0 h-5 w-5 {{ request()->routeIs('courses.*') ? 'text-indigo-600 dark:text-indigo-300' : 'text-gray-400 group-hover:text-gray-500 dark:text-gray-400 dark:group-hover:text-gray-300' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        Cursos
                    </a>
                </div>
                @endcan

                <!-- Section: Administrativo -->
                @can('view_admin')
                <div class="py-2">
                    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Administrativo</p>
                    
                    <!-- Finanzas -->
                    <a href="{{ route('finance.concepts') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('finance.*') ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-300' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
                        <svg class="mr-3 flex-shrink-0 h-5 w-5 {{ request()->routeIs('finance.*') ? 'text-indigo-600 dark:text-indigo-300' : 'text-gray-400 group-hover:text-gray-500 dark:text-gray-400 dark:group-hover:text-gray-300' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Finanzas
                    </a>

                    <!-- Reportes -->
                    <a href="{{ route('reports.index') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('reports.*') ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-300' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
                        <svg class="mr-3 flex-shrink-0 h-5 w-5 {{ request()->routeIs('reports.*') ? 'text-indigo-600 dark:text-indigo-300' : 'text-gray-400 group-hover:text-gray-500 dark:text-gray-400 dark:group-hover:text-gray-300' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Reportes
                    </a>

                    <!-- Admin Herramientas Dropdown -->
                     <div x-data="{ open: {{ request()->routeIs('admin.*') ? 'true' : 'false' }} }">
                        <button @click="open = !open" class="w-full group flex items-center justify-between px-3 py-2 text-sm font-medium rounded-md transition-colors text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white">
                            <div class="flex items-center">
                                <svg class="mr-3 flex-shrink-0 h-5 w-5 text-gray-400 group-hover:text-gray-500 dark:text-gray-400 dark:group-hover:text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Administración
                            </div>
                             <svg :class="open ? 'rotate-90' : ''" class="ml-2 h-4 w-4 text-gray-400 transform transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div x-show="open" x-collapse class="space-y-1 mt-1 pl-10">
                            <a href="{{ route('admin.requests') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.requests') ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}">
                                Solicitudes
                            </a>
                             <a href="{{ route('admin.database-import') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.database-import') ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}">
                                Importar Datos
                            </a>
                        </div>
                    </div>
                </div>
                @endcan

            </nav>

            <!-- Bottom Actions (Profile & Logout) -->
            <div class="border-t border-gray-200 dark:border-gray-700 p-4">
                <a href="{{ route('profile.edit') }}" class="flex items-center group px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 mb-1">
                    <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Mi Perfil
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center group px-3 py-2 text-sm font-medium text-red-600 rounded-md hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                        <svg class="mr-3 h-5 w-5 text-red-400 group-hover:text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Cerrar Sesión
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto min-h-screen bg-gray-50 dark:bg-gray-900">
            <!-- Top Bar for Desktop (Title/Breadcrumbs - Optional) -->
            @if (isset($header))
                <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <div class="py-6 px-4 sm:px-6 lg:px-8">
                {{ $slot }}
            </div>
        </main>

        <!-- Overlay for Mobile -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-black opacity-50 md:hidden" style="display: none;"></div>
    </div>

    @livewireScripts
</body>
</html>