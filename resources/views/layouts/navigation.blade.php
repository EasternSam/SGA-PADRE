@php
    $navBackground = \App\Models\Setting::where('key', 'brand_primary_color')->value('value') ?? '#1e3a8a';
@endphp

<!-- Overlay/Fondo oscuro para Móvil -->
<div x-show="open" 
    x-transition:enter="transition-opacity ease-linear duration-300"
    x-transition:enter-start="opacity-0" 
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity ease-linear duration-300" 
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0" 
    class="fixed inset-0 z-30 bg-gray-900/50 lg:hidden"
    @click="open = false"
    style="display: none;">
</div>

<aside
    class="fixed inset-y-0 left-0 z-40 flex h-screen w-64 transform flex-col overflow-y-auto border-r border-white/10 pt-4 transition-transform duration-300 lg:translate-x-0 lg:static lg:inset-auto lg:h-screen lg:w-64 lg:shadow-xl [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar-thumb]:bg-white/20 [&::-webkit-scrollbar-thumb]:rounded-full hover:[&::-webkit-scrollbar-thumb]:bg-white/40"
    style="background: {{ $navBackground }}; color: white;"
    :class="open ? 'translate-x-0 ease-out' : '-translate-x-full ease-in lg:translate-x-0'" 
>

    <!-- Logo Sidebar -->
    <div class="mb-6 flex flex-col items-center justify-center px-6">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 transition-transform hover:scale-105">
            @if(isset($branding) && !empty($branding->logo_url))
                <img src="{{ asset($branding->logo_url) }}" 
                     alt="{{ config('app.name') }}" 
                     class="block h-16 w-auto object-contain rounded-lg p-1 backdrop-blur-sm shadow-sm">
            @else
                <x-application-logo class="block h-10 w-auto fill-current text-white" />
            @endif
        </a>
    </div>

    <!-- Enlaces de Navegación -->
    <nav class="flex-1 space-y-2 px-3 py-2">

        <!-- Dashboard General -->
        <x-responsive-nav-link :href="route('dashboard')"
            :active="request()->routeIs(['dashboard', 'admin.dashboard', 'student.dashboard', 'teacher.dashboard', 'applicant.portal'])"
            wire:navigate>
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path d="M11.47 3.84a.75.75 0 0 1 1.06 0l8.635 8.635a.75.75 0 1 1-1.06 1.06l-.312-.31V21a.75.75 0 0 1-.75.75H2.75a.75.75 0 0 1-.75-.75v-7.775l-.31.31a.75.75 0 1 1-1.06-1.06l8.635-8.635ZM18.75 12.016v-2.072l-6.75-6.75-6.75 6.75v2.072h13.5Z" />
                <path d="M12 5.313 18.75 12.062V19.5H13.5v-6h-3v6H5.25v-7.438L12 5.313Z" />
            </svg>
            <span>{{ __('Dashboard') }}</span>
        </x-responsive-nav-link>

        <!-- SECCIÓN ADMINISTRACIÓN -->
        @hasanyrole('Admin|Registro|Contabilidad|Caja')
            <div class="pt-4 space-y-1">
                
                @if(true) {{-- Distribución Escolar: siempre visible --}}
                    <p class="px-3 pb-2 text-xs font-bold uppercase tracking-wider text-white/80">
                        {{ __('Gestión Escolar') }}
                    </p>
                    
                    @hasanyrole('Admin|Registro')
                        <x-responsive-nav-link :href="route('admin.students.index')"
                            :active="request()->routeIs(['admin.students.index', 'admin.students.profile'])"
                            wire:navigate>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z" clip-rule="evenodd" />
                            </svg>
                            <span>{{ __('Estudiantes') }}</span>
                        </x-responsive-nav-link>

                        <x-responsive-nav-link :href="route('admin.teachers.index')"
                            :active="request()->routeIs(['admin.teachers.index', 'admin.teachers.profile'])"
                            wire:navigate>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M11.7 2.805a.75.75 0 0 1 .6 0A60.65 60.65 0 0 1 22.83 8.72a.75.75 0 0 1-.231 1.337 49.949 49.949 0 0 0-9.902 3.912l-.003.002c-.114.06-.227.119-.343.18a.75.75 0 0 1-.707 0c-.116-.061-.23-.12-.343-.18a49.949 49.949 0 0 0-9.902-3.912.75.75 0 0 1-.231-1.337A60.653 60.653 0 0 1 11.7 2.805Z" />
                                <path d="M13.06 15.473a48.45 48.45 0 0 1 7.666-3.282c.134 1.414.22 2.843.255 4.285a.75.75 0 0 1-.46.71 47.878 47.878 0 0 0-8.105 4.342.75.75 0 0 1-.832 0 47.877 47.877 0 0 0-8.104-4.342.75.75 0 0 1-.461-.71c.035-1.442.121-2.87.255-4.286A48.4 48.4 0 0 1 6 13.18v2.296c0 2.267.18 4.505.54 6.713a.75.75 0 0 1-1.485.276c-.5-3.07-.75-6.195-.75-9.315a.75.75 0 0 1 .631-.74A47.98 47.98 0 0 0 11.963 2.37a.75.75 0 0 1 1.074 0c.055.056.11.11.166.166.056.056.11.11.166.166a.75.75 0 1 1-1.074 1.074 46.471 46.471 0 0 1-5.118-4.048c.07-.157.14-.312.212-.465A48.369 48.369 0 0 1 13.06 15.473Z" />
                                <path fill-rule="evenodd" d="M1.5 12.75a.75.75 0 0 1 .75-.75c.086 0 .17.005.254.015.65.08 1.33.155 2.052.222v2.246c-.722.067-1.402.142-2.052.223a.75.75 0 0 1-.75-.751v-1.205ZM18.75 14.482v-2.246c.722-.067 1.402-.142 2.052-.222.084-.01.168-.015.254-.015a.75.75 0 0 1 .75.75v1.206a.75.75 0 0 1-.75.751c-.65.08-1.33.156-2.052.223Z" clip-rule="evenodd" />
                            </svg>
                            <span>{{ __('Docentes') }}</span>
                        </x-responsive-nav-link>


                        {{-- GESTIÓN ESCOLAR MINERD --}}
                        <div x-data="{ openSchool: {{ request()->routeIs('admin.school.*') ? 'true' : 'false' }} }">
                            <button @click="openSchool = !openSchool" 
                                class="flex w-full items-center justify-between px-4 py-2 text-sm font-medium transition duration-150 ease-in-out rounded-md focus:outline-none {{ request()->routeIs('admin.school.*') ? 'bg-white text-gray-900' : 'text-white hover:bg-white/10 focus:bg-white/10' }}">
                                <div class="flex items-center gap-3">
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M11.584 2.376a.75.75 0 01.832 0l9 6a.75.75 0 01-.832 1.248L12 3.901 3.416 9.624a.75.75 0 01-.832-1.248l9-6z" />
                                        <path fill-rule="evenodd" d="M20.25 10.332v9.918H21a.75.75 0 010 1.5H3a.75.75 0 010-1.5h.75v-9.918a.75.75 0 01.634-.74A49.109 49.109 0 0112 9c2.59 0 5.134.202 7.616.592a.75.75 0 01.634.74zm-7.5 2.418a.75.75 0 00-1.5 0v6.75a.75.75 0 001.5 0v-6.75zm3-.75a.75.75 0 01.75.75v6.75a.75.75 0 01-1.5 0v-6.75a.75.75 0 01.75-.75zM9 12.75a.75.75 0 00-1.5 0v6.75a.75.75 0 001.5 0v-6.75z" clip-rule="evenodd" />
                                    </svg>
                                    <span>{{ __('Escolar MINERD') }}</span>
                                </div>
                                <svg class="h-4 w-4 transform transition-transform duration-200" :class="openSchool ? 'rotate-180' : ''" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                    <path fill-rule="evenodd" d="M12.53 16.28a.75.75 0 0 1-1.06 0l-7.5-7.5a.75.75 0 0 1 1.06-1.06L12 14.69l6.97-6.97a.75.75 0 1 1 1.06 1.06l-7.5 7.5Z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            
                            <div x-show="openSchool" x-collapse class="pl-10 space-y-1 mt-1">
                                <a href="{{ route('admin.school.dashboard') }}" wire:navigate 
                                    class="block px-4 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('admin.school.dashboard') ? 'bg-white text-gray-900 font-bold' : 'text-gray-200 hover:text-white hover:bg-white/5' }}">
                                    {{ __('📊 Panel Escolar') }}
                                </a>
                                <a href="{{ route('admin.school.academic-years') }}" wire:navigate 
                                    class="block px-4 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('admin.school.academic-years') ? 'bg-white text-gray-900 font-bold' : 'text-gray-200 hover:text-white hover:bg-white/5' }}">
                                    {{ __('Años Escolares') }}
                                </a>
                                <a href="{{ route('admin.school.sections') }}" wire:navigate 
                                    class="block px-4 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('admin.school.sections') ? 'bg-white text-gray-900 font-bold' : 'text-gray-200 hover:text-white hover:bg-white/5' }}">
                                    {{ __('Secciones') }}
                                </a>
                                <a href="{{ route('admin.school.subjects') }}" wire:navigate 
                                    class="block px-4 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('admin.school.subjects') ? 'bg-white text-gray-900 font-bold' : 'text-gray-200 hover:text-white hover:bg-white/5' }}">
                                    {{ __('Asignaturas') }}
                                </a>
                                <a href="{{ route('admin.school.grades') }}" wire:navigate 
                                    class="block px-4 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('admin.school.grades') ? 'bg-white text-gray-900 font-bold' : 'text-gray-200 hover:text-white hover:bg-white/5' }}">
                                    {{ __('Calificaciones') }}
                                </a>
                                <a href="{{ route('admin.school.attendance') }}" wire:navigate 
                                    class="block px-4 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('admin.school.attendance') ? 'bg-white text-gray-900 font-bold' : 'text-gray-200 hover:text-white hover:bg-white/5' }}">
                                    {{ __('Asistencia') }}
                                </a>
                                <a href="{{ route('admin.school.enrollment') }}" wire:navigate 
                                    class="block px-4 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('admin.school.enrollment') ? 'bg-white text-gray-900 font-bold' : 'text-gray-200 hover:text-white hover:bg-white/5' }}">
                                    {{ __('Inscripciones') }}
                                </a>
                                <a href="{{ route('admin.school.discipline') }}" wire:navigate 
                                    class="block px-4 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('admin.school.discipline') ? 'bg-white text-gray-900 font-bold' : 'text-gray-200 hover:text-white hover:bg-white/5' }}">
                                    {{ __('Disciplina') }}
                                </a>
                                <a href="{{ route('admin.school.report-cards') }}" wire:navigate 
                                    class="block px-4 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('admin.school.report-cards') ? 'bg-white text-gray-900 font-bold' : 'text-gray-200 hover:text-white hover:bg-white/5' }}">
                                    {{ __('Boletines') }}
                                </a>
                                <a href="{{ route('admin.school.schedule') }}" wire:navigate 
                                    class="block px-4 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('admin.school.schedule') ? 'bg-white text-gray-900 font-bold' : 'text-gray-200 hover:text-white hover:bg-white/5' }}">
                                    {{ __('Horarios') }}
                                </a>
                                <a href="{{ route('admin.school.calendar') }}" wire:navigate 
                                    class="block px-4 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('admin.school.calendar') ? 'bg-white text-gray-900 font-bold' : 'text-gray-200 hover:text-white hover:bg-white/5' }}">
                                    {{ __('Calendario') }}
                                </a>
                                <a href="{{ route('admin.school.announcements') }}" wire:navigate 
                                    class="block px-4 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('admin.school.announcements') ? 'bg-white text-gray-900 font-bold' : 'text-gray-200 hover:text-white hover:bg-white/5' }}">
                                    {{ __('Comunicaciones') }}
                                </a>
                                <a href="{{ route('admin.school.student-profile') }}" wire:navigate 
                                    class="block px-4 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('admin.school.student-profile') ? 'bg-white text-gray-900 font-bold' : 'text-gray-200 hover:text-white hover:bg-white/5' }}">
                                    {{ __('Ficha Estudiante') }}
                                </a>
                                <a href="{{ route('admin.school.teacher-schedule') }}" wire:navigate 
                                    class="block px-4 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('admin.school.teacher-schedule') ? 'bg-white text-gray-900 font-bold' : 'text-gray-200 hover:text-white hover:bg-white/5' }}">
                                    {{ __('Horario Docente') }}
                                </a>
                                <a href="{{ route('admin.school.teacher-assignments') }}" wire:navigate 
                                    class="block px-4 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('admin.school.teacher-assignments') ? 'bg-white text-gray-900 font-bold' : 'text-gray-200 hover:text-white hover:bg-white/5' }}">
                                    {{ __('Asign. Docentes') }}
                                </a>
                                <a href="{{ route('admin.school.honor-roll') }}" wire:navigate 
                                    class="block px-4 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('admin.school.honor-roll') ? 'bg-white text-gray-900 font-bold' : 'text-gray-200 hover:text-white hover:bg-white/5' }}">
                                    {{ __('🏆 Cuadro de Honor') }}
                                </a>
                                <a href="{{ route('admin.school.guardians') }}" wire:navigate 
                                    class="block px-4 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('admin.school.guardians') ? 'bg-white text-gray-900 font-bold' : 'text-gray-200 hover:text-white hover:bg-white/5' }}">
                                    {{ __('Padres/Tutores') }}
                                </a>
                                <a href="{{ route('admin.school.promotions') }}" wire:navigate 
                                    class="block px-4 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('admin.school.promotions') ? 'bg-white text-gray-900 font-bold' : 'text-gray-200 hover:text-white hover:bg-white/5' }}">
                                    {{ __('Promoción/Repitencia') }}
                                </a>
                                <a href="{{ route('admin.school.alerts') }}" wire:navigate 
                                    class="block px-4 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('admin.school.alerts') ? 'bg-white text-gray-900 font-bold' : 'text-gray-200 hover:text-white hover:bg-white/5' }}">
                                    {{ __('🚨 Alertas') }}
                                </a>
                                <a href="{{ route('admin.school.grade-locks') }}" wire:navigate 
                                    class="block px-4 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('admin.school.grade-locks') ? 'bg-white text-gray-900 font-bold' : 'text-gray-200 hover:text-white hover:bg-white/5' }}">
                                    {{ __('🔒 Bloqueo Notas') }}
                                </a>
                                <a href="{{ route('admin.school.report-center') }}" wire:navigate 
                                    class="block px-4 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('admin.school.report-center') ? 'bg-white text-gray-900 font-bold' : 'text-gray-200 hover:text-white hover:bg-white/5' }}">
                                    {{ __('📊 Centro Reportes') }}
                                </a>
                                <a href="{{ route('admin.school.justifications') }}" wire:navigate 
                                    class="block px-4 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('admin.school.justifications') ? 'bg-white text-gray-900 font-bold' : 'text-gray-200 hover:text-white hover:bg-white/5' }}">
                                    {{ __('📋 Justificaciones') }}
                                </a>
                                <a href="{{ route('admin.school.reinscription') }}" wire:navigate 
                                    class="block px-4 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('admin.school.reinscription') ? 'bg-white text-gray-900 font-bold' : 'text-gray-200 hover:text-white hover:bg-white/5' }}">
                                    {{ __('🔄 Reinscripción') }}
                                </a>
                                <a href="{{ route('admin.school.subject-stats') }}" wire:navigate 
                                    class="block px-4 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('admin.school.subject-stats') ? 'bg-white text-gray-900 font-bold' : 'text-gray-200 hover:text-white hover:bg-white/5' }}">
                                    {{ __('📊 Estadísticas') }}
                                </a>
                                <a href="{{ route('admin.school.settings') }}" wire:navigate 
                                    class="block px-4 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('admin.school.settings') ? 'bg-white text-gray-900 font-bold' : 'text-gray-200 hover:text-white hover:bg-white/5' }}">
                                    {{ __('Config. Centro') }}
                                </a>
                            </div>
                        </div>

                        {{-- CALENDARIO --}}
                        <x-responsive-nav-link :href="route('admin.calendar.index')" :active="request()->routeIs('admin.calendar.index')" wire:navigate>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12.75 12.75a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM7.5 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM8.25 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM9.75 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM10.5 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM12.75 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM14.25 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM15 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM16.5 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM15 12.75a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM16.5 13.5a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" />
                                <path fill-rule="evenodd" d="M6.75 2.25A.75.75 0 0 1 7.5 3v1.5h9V3A.75.75 0 0 1 18 3v1.5h.75a3 3 0 0 1 3 3v11.25a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3V7.5a3 3 0 0 1 3-3H6V3a.75.75 0 0 1 .75-.75Zm13.5 9a1.5 1.5 0 0 0-1.5-1.5H5.25a1.5 1.5 0 0 0-1.5 1.5v7.5a1.5 1.5 0 0 0 1.5 1.5h13.5a1.5 1.5 0 0 0 1.5-1.5v-7.5Z" clip-rule="evenodd" />
                            </svg>
                            <span>{{ __('Calendario') }}</span>
                        </x-responsive-nav-link>

                        {{-- Trámites y Admisiones removidos en distribución escolar --}}

                        @if(\App\Helpers\SaaS::has('inventory'))
                            <x-responsive-nav-link :href="route('admin.inventory.index')" :active="request()->routeIs('admin.inventory.index')" wire:navigate>
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.625 1.5c-1.036 0-1.875.84-1.875 1.875v17.25c0 1.035.84 1.875 1.875 1.875h12.75c1.035 0 1.875-.84 1.875-1.875V12.75A3.75 3.75 0 0 0 16.5 9h-1.875a1.875 1.875 0 0 1-1.875-1.875V5.25A3.75 3.75 0 0 0 9 1.5H5.625ZM7.5 15a.75.75 0 0 1 .75-.75h7.5a.75.75 0 0 1 0 1.5h-7.5A.75.75 0 0 1 7.5 15Zm.75 2.25a.75.75 0 0 0 0 1.5h7.5a.75.75 0 0 0 0-1.5h-7.5Z" clip-rule="evenodd" />
                                    <path d="M12.971 1.816A5.23 5.23 0 0 1 14.25 5.25v1.875c0 .207.168.375.375.375H16.5a5.23 5.23 0 0 1 3.434 1.279 9.768 9.768 0 0 0-6.963-6.963Z" />
                                </svg>
                                <span>{{ __('Inventario') }}</span>
                            </x-responsive-nav-link>
                        @endif
                    @endhasanyrole
                @endif

                {{-- MÓDULO FINANCIERO --}}
                @hasanyrole('Admin|Contabilidad|Caja')
                    @if(\App\Helpers\SaaS::has('finance'))
                        <p class="px-3 pt-4 pb-2 text-xs font-bold uppercase tracking-wider text-white/80">
                            {{ __('Finanzas') }}
                        </p>
                        <x-responsive-nav-link :href="route('admin.finance.dashboard')" :active="request()->routeIs('admin.finance.*')"
                            wire:navigate>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 7.5a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5Z" />
                                <path fill-rule="evenodd" d="M1.5 4.875C1.5 3.839 2.34 3 3.375 3h17.25c1.035 0 1.875.84 1.875 1.875v9.75c0 1.036-.84 1.875-1.875 1.875H3.375A1.875 1.875 0 0 1 1.5 14.625v-9.75ZM8.25 9.75a3.75 3.75 0 1 1 7.5 0 3.75 3.75 0 0 1-7.5 0ZM18.75 9a.75.75 0 0 0-.75.75v.008c0 .414.336.75.75.75h.008a.75.75 0 0 0 .75-.75V9.75a.75.75 0 0 0-.75-.75h-.008ZM4.5 9.75A.75.75 0 0 1 5.25 9h.008a.75.75 0 0 1 .75.75v.008a.75.75 0 0 1-.75.75H5.25a.75.75 0 0 1-.75-.75V9.75Z" clip-rule="evenodd" />
                                <path d="M2.25 18a.75.75 0 0 0 0 1.5c5.4 0 10.63.722 15.6 2.075 1.19.324 2.4-.558 2.4-1.82V18.75a.75.75 0 0 0-.75-.75H2.25Z" />
                            </svg>
                            <span>{{ __('Panel Financiero') }}</span>
                        </x-responsive-nav-link>
                        
                        @hasanyrole('Admin|Contabilidad')
                            <x-responsive-nav-link :href="route('admin.finance.concepts')" :active="request()->routeIs('admin.finance.concepts')" wire:navigate>
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                    <path fill-rule="evenodd" d="M11.078 2.25c-.917 0-1.699.663-1.85 1.567L9.05 4.889c-.02.12-.115.26-.297.348a7.493 7.493 0 0 0-.986.57c-.166.115-.334.126-.45.083L6.3 5.508a1.875 1.875 0 0 0-2.282.819l-.922 1.597a1.875 1.875 0 0 0 .432 2.385l.84.692c.095.078.17.229.154.43a7.598 7.598 0 0 0 0 1.139c.015.2-.059.352-.153.43l-.841.692a1.875 1.875 0 0 0-.432 2.385l.922 1.597a1.875 1.875 0 0 0 2.282.818l1.019-.382c.115-.043.283-.031.45.082.312.214.641.405.985.57.182.088.277.228.297.35l.178 1.071c.151.904.933 1.567 1.85 1.567h1.844c.916 0 1.699-.663 1.85-1.567l.178-1.072c.02-.12.114-.26.297-.349.344-.165.673-.356.985-.57.167-.114.335-.125.45-.082l1.02.382a1.875 1.875 0 0 0 2.28-.819l.923-1.597a1.875 1.875 0 0 0-.432-2.385l-.84-.692c-.114.043-.282.031-.449-.083a7.49 7.49 0 0 0-.985-.57c-.183-.087-.277-.227-.297-.348l-.179-1.072a1.875 1.875 0 0 0-1.85-1.567h-1.843ZM12 15.75a3.75 3.75 0 1 0 0-7.5 3.75 3.75 0 0 0 0 7.5Z" clip-rule="evenodd" />
                                </svg>
                                <span>{{ __('Conceptos Pago') }}</span>
                            </x-responsive-nav-link>

                            <x-responsive-nav-link :href="route('admin.finance.chart-of-accounts')" :active="request()->routeIs('admin.finance.chart-of-accounts')" wire:navigate>
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                  <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" />
                                </svg>
                                <span>{{ __('Catálogo CC') }}</span>
                            </x-responsive-nav-link>

                            <x-responsive-nav-link :href="route('admin.finance.manual-entry')" :active="request()->routeIs('admin.finance.manual-entry')" wire:navigate>
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                  <path d="M21.731 2.269a2.625 2.625 0 00-3.712 0l-1.157 1.158 3.71 3.71 1.159-1.157a2.625 2.625 0 000-3.711z" />
                                  <path d="M10.75 13a4.156 4.156 0 011.66-3.329l5.122-5.123-3.71-3.71-5.123 5.122A4.156 4.156 0 015.371 7.62l-1.766 1.767c-.22.22-.36.505-.407.817L2.03 15.65a.75.75 0 00.865.865l5.446-1.168a1.5 1.5 0 00.817-.407l1.767-1.766z" />
                                </svg>
                                <span>{{ __('Asiento Manual') }}</span>
                            </x-responsive-nav-link>

                            <x-responsive-nav-link :href="route('admin.finance.expenses')" :active="request()->routeIs('admin.finance.expenses')" wire:navigate>
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                  <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM9 7.5A1.5 1.5 0 0 0 7.5 9v1.5h9V9A1.5 1.5 0 0 0 15 7.5H9Zm6.5 4.5h-7A1.5 1.5 0 0 0 7 13.5V15h10v-1.5A1.5 1.5 0 0 0 15.5 12Z" clip-rule="evenodd" />
                                </svg>
                                <span>{{ __('Cuentas por Pagar') }}</span>
                            </x-responsive-nav-link>

                            <x-responsive-nav-link :href="route('admin.finance.statements')" :active="request()->routeIs('admin.finance.statements')" wire:navigate>
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                  <path fill-rule="evenodd" d="M7.5 5.25a3 3 0 0 1 3-3h3a3 3 0 0 1 3 3v.205c.933.085 1.857.197 2.774.334 1.454.218 2.476 1.483 2.476 2.917v3.033c0 1.211-.734 2.352-1.936 2.752A24.726 24.726 0 0 1 12 15.75c-2.73 0-5.36-.442-7.814-1.259-1.202-.4-1.936-1.541-1.936-2.752V8.706c0-1.434 1.022-2.7 2.476-2.917A48.814 48.814 0 0 1 7.5 5.455V5.25Zm7.5 0v.09a49.014 49.014 0 0 0-6 0v-.09a1.5 1.5 0 0 1 1.5-1.5h3a1.5 1.5 0 0 1 1.5 1.5Zm-3 8.25a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd" />
                                  <path d="M3 18.4v-2.796a4.3 4.3 0 0 0 .713.31A26.226 26.226 0 0 0 12 17.25c2.892 0 5.68-.468 8.287-1.335.252-.084.49-.189.713-.311V18.4c0 1.452-1.047 2.728-2.523 2.923-2.12.282-4.282.427-6.477.427a49.19 49.19 0 0 1-6.477-.427C4.047 21.128 3 19.852 3 18.4Z" />
                                </svg>
                                <span>{{ __('Estados Financieros') }}</span>
                            </x-responsive-nav-link>

                            <x-responsive-nav-link :href="route('admin.finance.ledger')" :active="request()->routeIs('admin.finance.ledger')" wire:navigate>
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                  <path d="M10.464 8.746c.227-.18.497-.311.786-.394v2.795a2.252 2.252 0 0 1-.786-.393c-.394-.313-.546-.681-.546-1.004 0-.323.152-.691.546-1.004ZM12.75 15.662v-2.824c.347.085.664.228.921.421.427.32.579.686.579.991 0 .305-.152.671-.579.991a2.534 2.534 0 0 1-.921.42Z" />
                                  <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 6a.75.75 0 0 0-1.5 0v.816a3.836 3.836 0 0 0-1.72.756c-.712.566-1.112 1.35-1.112 2.178 0 .829.4 1.612 1.113 2.178.502.4 1.102.647 1.719.756v2.978a2.536 2.536 0 0 1-.921-.421l-.879-.66a.75.75 0 0 0-.9 1.2l.879.66c.533.4 1.169.645 1.821.75V18a.75.75 0 0 0 1.5 0v-.81a4.124 4.124 0 0 0 1.821-.749c.745-.559 1.179-1.344 1.179-2.191 0-.847-.434-1.632-1.179-2.191a4.122 4.122 0 0 0-1.821-.75V8.354c.29.082.559.213.786.393l.415.33a.75.75 0 0 0 .933-1.175l-.415-.33a3.836 3.836 0 0 0-1.719-.755V6Z" clip-rule="evenodd" />
                                </svg>
                                <span>{{ __('Libro Mayor') }}</span>
                            </x-responsive-nav-link>

                            <x-responsive-nav-link :href="route('admin.finance.period-closing')" :active="request()->routeIs('admin.finance.period-closing')" wire:navigate>
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                  <path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 0 0-5.25 5.25v3a3 3 0 0 0-3 3v6.75a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3v-6.75a3 3 0 0 0-3-3v-3c0-2.9-2.35-5.25-5.25-5.25Zm3.75 8.25v-3a3.75 3.75 0 1 0-7.5 0v3h7.5Z" clip-rule="evenodd" />
                                </svg>
                                <span>{{ __('Cierre de Período') }}</span>
                            </x-responsive-nav-link>

                            <x-responsive-nav-link :href="route('admin.finance.dgii-reports')" :active="request()->routeIs('admin.finance.dgii-reports')" wire:navigate>
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                                <span>{{ __('Reportes DGII') }}</span>
                            </x-responsive-nav-link>
                        @endhasanyrole
                    @endif
                @endhasanyrole

                {{-- MÓDULO RECURSOS HUMANOS --}}
                @hasanyrole('Admin|Contabilidad')
                    <p class="px-3 pt-4 pb-2 text-xs font-bold uppercase tracking-wider text-white/80">
                        {{ __('Recursos Humanos') }}
                    </p>
                    <x-responsive-nav-link :href="route('admin.hr.employees')" :active="request()->routeIs('admin.hr.employees')" wire:navigate>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                          <path fill-rule="evenodd" d="M8.25 6.75a3.75 3.75 0 1 1 7.5 0 3.75 3.75 0 0 1-7.5 0ZM15.75 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM2.25 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM6.31 15.117A6.745 6.745 0 0 1 12 12a6.745 6.745 0 0 1 6.709 7.498.75.75 0 0 1-.372.568A12.696 12.696 0 0 1 12 21.75c-2.305 0-4.47-.612-6.337-1.684a.75.75 0 0 1-.372-.568 6.787 6.787 0 0 1 1.019-4.38Z" clip-rule="evenodd" />
                          <path d="M5.082 14.254a8.287 8.287 0 0 0-1.308 5.135 9.687 9.687 0 0 1-1.764-.44l-.115-.04a.563.563 0 0 1-.373-.487l-.01-.121a3.75 3.75 0 0 1 3.57-4.047ZM20.226 19.389a8.287 8.287 0 0 0-1.308-5.135 3.75 3.75 0 0 1 3.57 4.047l-.01.121a.563.563 0 0 1-.373.486l-.115.04c-.56.195-1.15.349-1.764.441Z" />
                        </svg>
                        <span>{{ __('Empleados') }}</span>
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('admin.hr.attendances')" :active="request()->routeIs('admin.hr.attendances')" wire:navigate>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                          <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 6a.75.75 0 0 0-1.5 0v6c0 .414.336.75.75.75h4.5a.75.75 0 0 0 0-1.5h-3.75V6Z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ __('Asistencia (Reloj)') }}</span>
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('admin.hr.payroll')" :active="request()->routeIs('admin.hr.payroll')" wire:navigate>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                          <path fill-rule="evenodd" d="M12 2.25a.75.75 0 0 1 .75.75v11.69l3.22-3.22a.75.75 0 1 1 1.06 1.06l-4.5 4.5a.75.75 0 0 1-1.06 0l-4.5-4.5a.75.75 0 1 1 1.06-1.06l3.22 3.22V3a.75.75 0 0 1 .75-.75Zm-9 13.5a.75.75 0 0 1 .75.75v2.25a1.5 1.5 0 0 0 1.5 1.5h13.5a1.5 1.5 0 0 0 1.5-1.5V16.5a.75.75 0 0 1 1.5 0v2.25a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3V16.5a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ __('Nómina de Pago') }}</span>
                    </x-responsive-nav-link>
                @endhasanyrole

                {{-- MÓDULO REPORTES --}}
                @hasanyrole('Admin|Registro|Contabilidad')
                    @if(\App\Helpers\SaaS::has('reports_basic'))
                        <p class="px-3 pt-4 pb-2 text-xs font-bold uppercase tracking-wider text-white/80">
                            {{ __('Reportes') }}
                        </p>
                        <x-responsive-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.index')" wire:navigate>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <path fill-rule="evenodd" d="M2.25 2.25a.75.75 0 0 0 0 1.5H3v10.5a3 3 0 0 0 3 3h1.21l-1.172 3.513a.75.75 0 0 0 1.424.474l.329-.987h8.418l.33.987a.75.75 0 0 0 1.422-.474l-1.17-3.513H18a3 3 0 0 0 3-3V3.75h.75a.75.75 0 0 0 0-1.5H2.25Zm6.04 16.5.5-1.5h6.42l.5 1.5H8.29Zm7.46-12a.75.75 0 0 0-1.5 0v6a.75.75 0 0 0 1.5 0v-6Zm-3 2.25a.75.75 0 0 0-1.5 0v3.75a.75.75 0 0 0 1.5 0V9Zm-3 2.25a.75.75 0 0 0-1.5 0v1.5a.75.75 0 0 0 1.5 0v-1.5Z" clip-rule="evenodd" />
                            </svg>
                            <span>{{ __('Centro de Reportes') }}</span>
                        </x-responsive-nav-link>
                    @endif
                @endhasanyrole

                {{-- SOLO ADMIN --}}
                @role('Admin')
                    <p class="px-3 pt-4 pb-2 text-xs font-bold uppercase tracking-wider text-white/80">
                        {{ __('Configuración') }}
                    </p>
                    <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')" wire:navigate>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M4.5 6.375a4.125 4.125 0 1 1 8.25 0 4.125 4.125 0 0 1-8.25 0ZM14.25 8.625a3.375 3.375 0 1 1 6.75 0 3.375 3.375 0 0 1-6.75 0ZM1.5 19.125a7.125 7.125 0 0 1 14.25 0v.003l-.001.119a.75.75 0 0 1-.363.63 13.067 13.067 0 0 1-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 0 1-.364-.63l-.001-.122ZM17.25 19.128l-.001.144a2.25 2.25 0 0 1-.233.96 10.088 10.088 0 0 0 5.06-1.01.75.75 0 0 0 .42-.687v-.22a2.25 2.25 0 0 0-.872-1.784 6.262 6.262 0 0 0-3.218-1.18.75.75 0 0 1-.545-.81 3.802 3.802 0 0 1 2.762-3.665.75.75 0 0 0 .532-.71c0-.466-.345-.853-.806-.92a3.801 3.801 0 0 1-2.923-2.61.75.75 0 0 0-1.424.474 5.303 5.303 0 0 0 3.803 3.395 7.75 7.75 0 0 1-3.228 1.942.75.75 0 0 0-.533.71v6.985Z" />
                        </svg>
                        <span>{{ __('Usuarios') }}</span>
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('admin.classrooms.index')" :active="request()->routeIs('admin.classrooms.index')" wire:navigate>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M18 1.5c2.9 0 5.25 2.35 5.25 5.25v3.75a.75.75 0 0 1-1.5 0V6.75a3.75 3.75 0 1 0-7.5 0v3a3 3 0 1 0-6 0v-3a3.75 3.75 0 1 0-7.5 0v3.75a.75.75 0 0 1-1.5 0V6.75a5.25 5.25 0 0 1 5.25-5.25H18Z" />
                        </svg>
                        <span>{{ __('Aulas') }}</span>
                    </x-responsive-nav-link>

                    @if(\App\Helpers\SaaS::has('reports_advanced'))
                        <div x-data="{ openDiplomas: {{ request()->routeIs('admin.certificates.*') ? 'true' : 'false' }} }">
                            <button @click="openDiplomas = !openDiplomas" 
                                class="flex w-full items-center justify-between px-4 py-2 text-sm font-medium transition duration-150 ease-in-out rounded-md focus:outline-none {{ request()->routeIs('admin.certificates.*') ? 'bg-white text-gray-900' : 'text-white/80 hover:bg-white/10' }}">
                                <div class="flex items-center gap-3">
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10.5 3.75a6.75 6.75 0 1 0 0 13.5 6.75 6.75 0 0 0 0-13.5ZM2.25 10.5a8.25 8.25 0 1 1 14.59 5.28l4.69 4.69a.75.75 0 1 1-1.06 1.06l-4.69-4.69A8.25 8.25 0 0 1 2.25 10.5Z" clip-rule="evenodd" />
                                    </svg>
                                    <span>{{ __('Diplomas') }}</span>
                                </div>
                                <svg class="h-4 w-4 transform transition-transform duration-200" :class="openDiplomas ? 'rotate-180' : ''" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                    <path fill-rule="evenodd" d="M12.53 16.28a.75.75 0 0 1-1.06 0l-7.5-7.5a.75.75 0 0 1 1.06-1.06L12 14.69l6.97-6.97a.75.75 0 1 1 1.06 1.06l-7.5 7.5Z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            
                            <div x-show="openDiplomas" x-collapse class="pl-10 space-y-1 mt-1">
                                <a href="{{ route('admin.certificates.index') }}" wire:navigate 
                                    class="block px-4 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('admin.certificates.index') ? 'bg-white text-gray-900 font-bold' : 'text-gray-200 hover:text-white hover:bg-white/10' }}">
                                    {{ __('Emitidos') }}
                                </a>
                                <a href="{{ route('admin.certificates.templates') }}" wire:navigate 
                                    class="block px-4 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('admin.certificates.templates') ? 'bg-white text-gray-900 font-bold' : 'text-white/80 hover:bg-white/10' }}">
                                    {{ __('Plantillas') }}
                                </a>
                            </div>
                        </div>
                    @endif

                    <x-responsive-nav-link :href="route('admin.import')" :active="request()->routeIs('admin.import')" wire:navigate>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M11.644 1.59a.75.75 0 0 1 .712 0l9.75 5.25a.75.75 0 0 1 0 1.32l-9.75 5.25a.75.75 0 0 1-.712 0l-9.75-5.25a.75.75 0 0 1 0-1.32l9.75-5.25Z" />
                            <path d="M3.265 10.602l7.668 4.129a2.25 2.25 0 0 0 2.134 0l7.668-4.13 1.37.739a.75.75 0 0 1 0 1.32l-9.75 5.25a.75.75 0 0 1-.71 0l-9.75-5.25a.75.75 0 0 1 0-1.32l1.37-.738Z" />
                            <path d="M10.933 19.231l-7.668-4.13-1.37.739a.75.75 0 0 0 0 1.32l9.75 5.25c.221.12.489.12.71 0l9.75-5.25a.75.75 0 0 0 0-1.32l-1.37-.738-7.668 4.13a2.25 2.25 0 0 1-2.134 0Z" />
                        </svg>
                        <span>{{ __('Importar Datos') }}</span>
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('admin.email-tester')" :active="request()->routeIs('admin.email-tester')" wire:navigate>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M1.5 8.67v8.58a3 3 0 0 0 3 3h15a3 3 0 0 0 3-3V8.67l-8.928 5.493a3 3 0 0 1-3.144 0L1.5 8.67Z" />
                            <path d="M22.5 6.908V6.75a3 3 0 0 0-3-3h-15a3 3 0 0 0-3 3v.158l9.714 5.978a1.5 1.5 0 0 0 1.572 0L22.5 6.908Z" />
                        </svg>
                        <span>{{ __('Panel Comunicación') }}</span>
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('admin.activity-logs.index')" :active="request()->routeIs('admin.activity-logs.index')" wire:navigate>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                            <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm11.378-3.917c-.89-.777-2.366-.777-3.255 0a.75.75 0 0 1-.988-1.129c1.454-1.272 3.776-1.272 5.23 0 1.539 1.345 1.539 3.535 0 4.881L10.3 16.912a.75.75 0 0 1-1.133-.976l4.312-5.042c.835-.976.835-2.564 0-3.54Z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ __('Registro de Actividad y Auditoría') }}</span>
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('admin.scholarships.index')" :active="request()->routeIs('admin.scholarships.index')" wire:navigate>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 3L1 9l4 2.18v6L12 21l7-3.82v-6l2-1.09V17h2V9L12 3zm6.82 6L12 12.72 5.18 9 12 5.28 18.82 9zM17 15.99l-5 2.73-5-2.73v-3.72l5 2.73 5-2.73v3.72z"/>
                        </svg>
                        <span>{{ __('Becas Institucionales') }}</span>
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('admin.settings.index')" :active="request()->routeIs('admin.settings.index')" wire:navigate>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                          <path fill-rule="evenodd" d="M11.078 2.25c-.917 0-1.699.663-1.85 1.567L9.05 4.889c-.02.12-.115.26-.297.348a7.493 7.493 0 0 0-.986.57c-.166.115-.334.126-.45.083L6.3 5.508a1.875 1.875 0 0 0-2.282.819l-.922 1.597a1.875 1.875 0 0 0 .432 2.385l.84.692c.095.078.17.229.154.43a7.598 7.598 0 0 0 0 1.139c.015.2-.059.352-.153.43l-.841.692a1.875 1.875 0 0 0-.432 2.385l.922 1.597a1.875 1.875 0 0 0 2.282.818l1.019-.382c.115-.043.283-.031.45.082.312.214.641.405.985.57.182.088.277.228.297.35l.178 1.071c.151.904.933 1.567 1.85 1.567h1.844c.916 0 1.699-.663 1.85-1.567l.178-1.072c.02-.12.114-.26.297-.349.344-.165.673-.356.985-.57.167-.114.335-.125.45-.082l1.02.382a1.875 1.875 0 0 0 2.28-.819l.923-1.597a1.875 1.875 0 0 0-.432-2.385l-.84-.692c-.114.043-.282.031-.449-.083a7.49 7.49 0 0 0-.985-.57c-.183-.087-.277-.227-.297-.348l-.179-1.072a1.875 1.875 0 0 0-1.85-1.567h-1.843ZM12 15.75a3.75 3.75 0 1 0 0-7.5 3.75 3.75 0 0 0 0 7.5Z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ __('Ajustes Globales') }}</span>
                    </x-responsive-nav-link>
                @endrole

            </div>
        @endhasanyrole

        <!-- Sección Estudiante -->
        @role('Estudiante')
            <div class="pt-4 space-y-1">
                <p class="px-3 pb-2 text-xs font-bold uppercase tracking-wider text-white/80">
                    {{ __('Mi Portal') }}
                </p>

                @if(\App\Helpers\SaaS::has('finance'))
                    <x-responsive-nav-link :href="route('student.payments')" :active="request()->routeIs('student.payments')" wire:navigate>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 7.5a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5Z" />
                            <path fill-rule="evenodd" d="M1.5 4.875C1.5 3.839 2.34 3 3.375 3h17.25c1.035 0 1.875.84 1.875 1.875v9.75c0 1.036-.84 1.875-1.875 1.875H3.375A1.875 1.875 0 0 1 1.5 14.625v-9.75ZM8.25 9.75a3.75 3.75 0 1 1 7.5 0 3.75 3.75 0 0 1-7.5 0ZM18.75 9a.75.75 0 0 0-.75.75v.008c0 .414.336.75.75.75h.008a.75.75 0 0 0 .75-.75V9.75a.75.75 0 0 0-.75-.75h-.008ZM4.5 9.75A.75.75 0 0 1 5.25 9h.008a.75.75 0 0 1 .75.75v.008a.75.75 0 0 1-.75.75H5.25a.75.75 0 0 1-.75-.75V9.75Z" clip-rule="evenodd" />
                            <path d="M2.25 18a.75.75 0 0 0 0 1.5c5.4 0 10.63.722 15.6 2.075 1.19.324 2.4-.558 2.4-1.82V18.75a.75.75 0 0 0-.75-.75H2.25Z" />
                        </svg>
                        <span>{{ __('Mis Pagos') }}</span>
                    </x-responsive-nav-link>
                @endif

                {{-- Solicitudes removidas en distribución escolar --}}
            </div>
        @endrole

        {{-- Sección Solicitante removida en distribución escolar --}}

        <!-- Sección Profesor -->
        @role('Profesor')
            <div class="pt-4 space-y-1">
                <p class="px-3 pb-2 text-xs font-bold uppercase tracking-wider text-white/80">
                    {{ __('Docencia') }}
                </p>
                
                <x-responsive-nav-link :href="route('teacher.dashboard')" :active="request()->routeIs('teacher.dashboard')" wire:navigate>
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M11.7 2.805a.75.75 0 0 1 .6 0A60.65 60.65 0 0 1 22.83 8.72a.75.75 0 0 1-.231 1.337 49.949 49.949 0 0 0-9.902 3.912l-.003.002c-.114.06-.227.119-.343.18a.75.75 0 0 1-.707 0c-.116-.061-.23-.12-.343-.18a49.949 49.949 0 0 0-9.902-3.912.75.75 0 0 1-.231-1.337A60.653 60.653 0 0 1 11.7 2.805Z" />
                        <path d="M13.06 15.473a48.45 48.45 0 0 1 7.666-3.282c.134 1.414.22 2.843.255 4.285a.75.75 0 0 1-.46.71 47.878 47.878 0 0 0-8.105 4.342.75.75 0 0 1-.832 0 47.877 47.877 0 0 0-8.104-4.342.75.75 0 0 1-.461-.71c.035-1.442.121-2.87.255-4.286A48.4 48.4 0 0 1 6 13.18v2.296c0 2.267.18 4.505.54 6.713a.75.75 0 0 1-1.485.276c-.5-3.07-.75-6.195-.75-9.315a.75.75 0 0 1 .631-.74A47.98 47.98 0 0 0 11.963 2.37a.75.75 0 0 1 1.074 0c.055.056.11.11.166.166.056.056.11.11.166.166a.75.75 0 1 1-1.074 1.074 46.471 46.471 0 0 1-5.118-4.048c.07-.157.14-.312.212-.465A48.369 48.369 0 0 1 13.06 15.473Z" />
                        <path fill-rule="evenodd" d="M1.5 12.75a.75.75 0 0 1 .75-.75c.086 0 .17.005.254.015.65.08 1.33.155 2.052.222v2.246c-.722.067-1.402.142-2.052.223a.75.75 0 0 1-.75-.751v-1.205ZM18.75 14.482v-2.246c.722-.067 1.402-.142 2.052-.222.084-.01.168-.015.254-.015a.75.75 0 0 1 .75.75v1.206a.75.75 0 0 1-.75.751c-.65.08-1.33.156-2.052.223Z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ __('Mis Clases') }}</span>
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('teacher.payroll')" :active="request()->routeIs('teacher.payroll')" wire:navigate>
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                      <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-1.72 6.97a.75.75 0 1 0-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 1 0 1.06 1.06L12 13.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L13.06 12l1.72-1.72a.75.75 0 1 0-1.06-1.06L12 10.94l-1.72-1.72Z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ __('Mis Pagos') }}</span>
                </x-responsive-nav-link>
            </div>
        @endrole

    </nav>
</aside>