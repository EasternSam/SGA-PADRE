<nav x-data="{ open: false }" class="bg-sga-primary border-b border-sga-primary lg:hidden">
    <!-- Primary Navigation Menu (Mobile Header) -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-white" />
                    </a>
                </div>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-white hover:text-white/80 hover:bg-white/10 focus:outline-none focus:bg-white/10 focus:text-white transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Sidebar para Móvil (Fondo oscuro) -->
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

    <!-- Sidebar Principal (Móvil y Desktop) -->
    <!-- Se corrigió la clase dinámica :class para que en escritorio (lg:) siempre esté visible (translate-x-0) -->
    <aside
        class="fixed inset-y-0 left-0 z-40 flex h-screen w-64 transform flex-col overflow-y-auto border-r border-sga-primary bg-sga-primary pt-4 transition-transform duration-300 lg:translate-x-0 lg:static lg:inset-auto lg:h-screen lg:w-64 lg:shadow-xl [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar-thumb]:bg-white/20 [&::-webkit-scrollbar-thumb]:rounded-full hover:[&::-webkit-scrollbar-thumb]:bg-white/40"
        :class="open ? 'translate-x-0 ease-out' : '-translate-x-full ease-in lg:translate-x-0'" 
    >

        <!-- Logo -->
        <div class="mb-6 flex items-center justify-center px-6">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 transition-transform hover:scale-105">
                <x-application-logo class="block h-10 w-auto fill-current text-white" />
            </a>
        </div>

        <!-- Enlaces de Navegación -->
        <nav class="flex-1 space-y-2 px-3 py-2">

            <!-- Dashboard General (Todos los roles permitidos) -->
            <x-responsive-nav-link :href="route('dashboard')"
                :active="request()->routeIs(['dashboard', 'admin.dashboard', 'student.dashboard', 'teacher.dashboard', 'applicant.portal'])"
                wire:navigate>
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
                <span>{{ __('Dashboard') }}</span>
            </x-responsive-nav-link>

            <!-- ================= SECCIÓN ADMINISTRACIÓN ================= -->
            {{-- Visible para Admin, Registro, Contabilidad y Caja --}}
            @hasanyrole('Admin|Registro|Contabilidad|Caja')
                <div class="pt-4 space-y-1">
                    {{-- CAMBIO: text-white para el título de la sección --}}
                    <p class="px-3 pb-2 text-xs font-bold uppercase tracking-wider text-white">
                        {{ __('Administración') }}
                    </p>
                    
                    {{-- MÓDULO ACADÉMICO: Admin y Registro --}}
                    @hasanyrole('Admin|Registro')
                        <x-responsive-nav-link :href="route('admin.students.index')"
                            :active="request()->routeIs(['admin.students.index', 'admin.students.profile'])"
                            wire:navigate>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 19.128a9.38 9.38 0 0 0 2.625.372m-10.75 0a9.38 9.38 0 0 0 2.625.372M12 6.875c-1.036 0-1.875.84-1.875 1.875s.84 1.875 1.875 1.875 1.875-.84 1.875-1.875S13.036 6.875 12 6.875Zm0 0v.002v-.002Z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 15c-1.036 0-1.875.84-1.875 1.875s.84 1.875 1.875 1.875 1.875-.84 1.875-1.875S13.036 15 12 15Zm0 0v.002v-.002Z" />
                            </svg>
                            <span>{{ __('Estudiantes') }}</span>
                        </x-responsive-nav-link>

                        <x-responsive-nav-link :href="route('admin.teachers.index')"
                            :active="request()->routeIs(['admin.teachers.index', 'admin.teachers.profile'])"
                            wire:navigate>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M20.25 14.15v4.075c0 1.313-.964 2.411-2.206 2.597l-5.464.794a2.625 2.625 0 0 1-2.16 0l-5.464-.794A2.57 2.57 0 0 1 2.25 18.225V14.15M3.375 13.5v-3.375A2.25 2.25 0 0 1 5.625 7.875h12.75c1.24 0 2.25 1.01 2.25 2.25V13.5m0-3.375l-5.91-.86a2.625 2.625 0 0 0-2.18 0l-5.91.86" />
                            </svg>
                            <span>{{ __('Docentes') }}</span>
                        </x-responsive-nav-link>

                        <x-responsive-nav-link :href="route('admin.courses.index')" :active="request()->routeIs('admin.courses.index')" wire:navigate>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.25c2.291 0 4.545-.16 6.731-.462a60.504 60.504 0 0 0-.49-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.75c2.395 0 4.708.16 6.949.462a59.903 59.903 0 0 1-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.5c2.389 0 4.692-.157 6.928-.461" />
                            </svg>
                            <span>{{ __('Académico') }}</span>
                        </x-responsive-nav-link>

                        <x-responsive-nav-link :href="route('admin.finance.concepts')" :active="request()->routeIs('admin.finance.concepts')"
                            wire:navigate>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.75A.75.75 0 0 1 3 4.5h.75m0 0H21m-18 0h18M3 6h18M3 6v10.5A2.25 2.25 0 0 0 5.25 18.75h13.5A2.25 2.25 0 0 0 21 16.5V6M3 6l.902.902A2.25 2.25 0 0 0 5.625 9h12.75c1.03 0 1.94-.5 2.48-1.272L21 6" />
                            </svg>
                            <span>{{ __('Conceptos de Pago') }}</span>
                        </x-responsive-nav-link>

                        <x-responsive-nav-link :href="route('admin.requests')" :active="request()->routeIs('admin.requests')" wire:navigate>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M10.125 2.25h-4.5c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125v-9M10.125 2.25h.375c.621 0 1.125.504 1.125 1.125v.375M10.125 2.25v3.375c0 .621.504 1.125 1.125 1.125h3.375M9 15l2.25 2.25L15 15m-6 6h6" />
                            </svg>
                            <span>{{ __('Solicitudes') }}</span>
                        </x-responsive-nav-link>

                        <x-responsive-nav-link :href="route('admin.import')" :active="request()->routeIs('admin.import')" wire:navigate>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                            </svg>
                            <span>{{ __('Importar Datos') }}</span>
                        </x-responsive-nav-link>

                        <x-responsive-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.index')" wire:navigate>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6" />
                            </svg>
                            <span>{{ __('Reportes') }}</span>
                        </x-responsive-nav-link>

                        {{-- Enlace para Certificados y Diplomas --}}
                        <x-responsive-nav-link :href="route('admin.certificates.index')" :active="request()->routeIs('admin.certificates.index')" wire:navigate>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <span>{{ __('Diplomas') }}</span>
                        </x-responsive-nav-link>
                        
                        {{-- Enlace para Admisiones (Nuevo) --}}
                        <x-responsive-nav-link :href="route('admin.admissions.index')" :active="request()->routeIs('admin.admissions.index')" wire:navigate>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 3.75a3 3 0 0 0-3 3v.75h21v-.75a3 3 0 0 0-3-3h-15Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M22.5 9.75h-21v7.5a3 3 0 0 0 3 3h15a3 3 0 0 0 3-3v-7.5Zm-18 3.75a.75.75 0 0 1 .75-.75h6a.75.75 0 0 1 0 1.5h-6a.75.75 0 0 1-.75-.75Zm.75 2.25a.75.75 0 0 0 0 1.5h3a.75.75 0 0 0 0-1.5h-3Z" />
                            </svg>
                            <span>{{ __('Admisiones') }}</span>
                        </x-responsive-nav-link>

                        {{-- Enlace para Calendario (Nuevo) --}}
                        <x-responsive-nav-link :href="route('admin.calendar.index')" :active="request()->routeIs('admin.calendar.index')" wire:navigate>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12.75 12.75a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM7.5 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM8.25 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM9.75 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM10.5 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM12.75 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM14.25 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM15 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM16.5 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM15 12.75a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM16.5 13.5a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 2.25A.75.75 0 0 1 7.5 3v1.5h9V3A.75.75 0 0 1 18 3v1.5h.75a3 3 0 0 1 3 3v11.25a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3V7.5a3 3 0 0 1 3-3H6V3a.75.75 0 0 1 .75-.75Zm13.5 9a1.5 1.5 0 0 0-1.5-1.5H5.25a1.5 1.5 0 0 0-1.5 1.5v7.5a1.5 1.5 0 0 0 1.5 1.5h13.5a1.5 1.5 0 0 0 1.5-1.5v-7.5Z" />
                            </svg>
                            <span>{{ __('Calendario') }}</span>
                        </x-responsive-nav-link>

                    @endhasanyrole

                </div>
            @endhasanyrole

            <!-- Sección Estudiante -->
            @role('Estudiante')
                <div class="pt-4 space-y-1">
                    <p class="px-3 pb-2 text-xs font-bold uppercase tracking-wider text-blue-200/80">
                        {{ __('Portal Estudiante') }}
                    </p>
                    
                    <x-responsive-nav-link :href="route('student.dashboard')" :active="request()->routeIs('student.dashboard')"
                        wire:navigate>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        <span>{{ __('Mi Expediente') }}</span>
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('student.requests')" :active="request()->routeIs('student.requests')" wire:navigate>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M10.125 2.25h-4.5c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125v-9M10.125 2.25h.375c.621 0 1.125.504 1.125 1.125v.375M10.125 2.25v3.375c0 .621.504 1.125 1.125 1.125h3.375M9 15l2.25 2.25L15 15m-6 6h6" />
                        </svg>
                        <span>{{ __('Mis Solicitudes') }}</span>
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('student.payments')" :active="request()->routeIs('student.payments')" wire:navigate>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                        </svg>
                        <span>{{ __('Mis Finanzas') }}</span>
                    </x-responsive-nav-link>

                </div>
            @endrole

            <!-- Sección Solicitante (NUEVO) -->
            @role('Solicitante')
                <div class="pt-4 space-y-1">
                    <p class="px-3 pb-2 text-xs font-bold uppercase tracking-wider text-white/70">
                        {{ __('Admisiones') }}
                    </p>
                    <x-responsive-nav-link :href="route('applicant.portal')" :active="request()->routeIs('applicant.portal')" wire:navigate>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M4.5 3.75a3 3 0 0 0-3 3v.75h21v-.75a3 3 0 0 0-3-3h-15Z" />
                            <path fill-rule="evenodd" d="M22.5 9.75h-21v7.5a3 3 0 0 0 3 3h15a3 3 0 0 0 3-3v-7.5Zm-18 3.75a.75.75 0 0 1 .75-.75h6a.75.75 0 0 1 0 1.5h-6a.75.75 0 0 1-.75-.75Zm.75 2.25a.75.75 0 0 0 0 1.5h3a.75.75 0 0 0 0-1.5h-3Z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ __('Mi Solicitud') }}</span>
                    </x-responsive-nav-link>
                </div>
            @endrole

            <!-- Sección Profesor -->
            @role('Profesor')
                <div class="pt-4 space-y-1">
                    <p class="px-3 pb-2 text-xs font-bold uppercase tracking-wider text-blue-200/80">
                        {{ __('Portal Docente') }}
                    </p>
                    
                    <x-responsive-nav-link :href="route('teacher.dashboard')"
                        :active="request()->routeIs(['teacher.dashboard', 'teacher.attendance', 'teacher.grades'])"
                        wire:navigate>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5h1.5v-4.875c0-.621.504-1.125 1.125-1.125h1.5c.621 0 1.125.504 1.125 1.125v4.875m0 0a1.125 1.125 0 0 0 1.125-1.125m1.125 1.125h1.5v-4.875c0-.621.504-1.125 1.125-1.125h1.5c.621 0 1.125.504 1.125 1.125v4.875M4.5 19.5v-4.875c0-.621.504-1.125 1.125-1.125h1.5c.621 0 1.125.504 1.125 1.125v4.875M19.5 19.5v-4.875c0-.621-.504-1.125-1.125-1.125h-1.5c-.621 0-1.125.504-1.125 1.125v4.875m-7.5 0v-4.875c0-.621.504-1.125 1.125-1.125h1.5c.621 0 1.125.504 1.125 1.125v4.875" />
                        </svg>
                        <span>{{ __('Mis Secciones') }}</span>
                    </x-responsive-nav-link>
                </div>
            @endrole

        </nav>
    </aside>
</nav>