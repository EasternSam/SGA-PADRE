{{-- Bottom Navigation Bar — Solo visible en móvil (<lg) --}}
<nav x-data="{ moreOpen: false }" class="fixed bottom-0 inset-x-0 z-50 lg:hidden bg-white border-t border-gray-200 shadow-[0_-4px_20px_rgba(0,0,0,0.08)]" style="padding-bottom: env(safe-area-inset-bottom, 0px);">
    
    {{-- Panel "Más" — Menú completo deslizable --}}
    <div x-show="moreOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-y-full opacity-0" x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="translate-y-full opacity-0"
         class="absolute bottom-full left-0 right-0 bg-white rounded-t-2xl shadow-2xl border-t border-gray-100 max-h-[70vh] overflow-y-auto" style="display:none;" @click.outside="moreOpen = false">
        
        <div class="px-4 pt-3 pb-4">
            <div class="w-10 h-1 bg-gray-300 rounded-full mx-auto mb-3"></div>
            
            <div class="grid grid-cols-4 gap-1">
                @hasanyrole('Admin|Registro')
                <a href="{{ route('admin.students.index') }}" wire:navigate class="flex flex-col items-center gap-1.5 p-3 rounded-xl hover:bg-indigo-50 transition {{ request()->routeIs('admin.students.*') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600' }}">
                    <i class="fas fa-user-graduate text-lg"></i>
                    <span class="text-[10px] font-semibold">Estudiantes</span>
                </a>
                <a href="{{ route('admin.teachers.index') }}" wire:navigate class="flex flex-col items-center gap-1.5 p-3 rounded-xl hover:bg-indigo-50 transition {{ request()->routeIs('admin.teachers.*') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600' }}">
                    <i class="fas fa-chalkboard-teacher text-lg"></i>
                    <span class="text-[10px] font-semibold">Docentes</span>
                </a>
                @if(\App\Helpers\SaaS::showCourses())
                <a href="{{ route('admin.courses.index') }}" wire:navigate class="flex flex-col items-center gap-1.5 p-3 rounded-xl hover:bg-indigo-50 transition {{ request()->routeIs('admin.courses.*') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600' }}">
                    <i class="fas fa-book text-lg"></i>
                    <span class="text-[10px] font-semibold">Cursos</span>
                </a>
                @endif
                <a href="{{ route('admin.calendar.index') }}" wire:navigate class="flex flex-col items-center gap-1.5 p-3 rounded-xl hover:bg-indigo-50 transition {{ request()->routeIs('admin.calendar.*') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600' }}">
                    <i class="fas fa-calendar-alt text-lg"></i>
                    <span class="text-[10px] font-semibold">Calendario</span>
                </a>
                @endhasanyrole

                @hasanyrole('Admin|Contabilidad|Caja')
                @if(\App\Helpers\SaaS::has('finance'))
                <a href="{{ route('admin.finance.dashboard') }}" wire:navigate class="flex flex-col items-center gap-1.5 p-3 rounded-xl hover:bg-indigo-50 transition {{ request()->routeIs('admin.finance.*') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600' }}">
                    <i class="fas fa-dollar-sign text-lg"></i>
                    <span class="text-[10px] font-semibold">Finanzas</span>
                </a>
                @endif
                @endhasanyrole

                @hasanyrole('Admin|Contabilidad')
                <a href="{{ route('admin.hr.employees') }}" wire:navigate class="flex flex-col items-center gap-1.5 p-3 rounded-xl hover:bg-indigo-50 transition {{ request()->routeIs('admin.hr.*') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600' }}">
                    <i class="fas fa-users text-lg"></i>
                    <span class="text-[10px] font-semibold">RRHH</span>
                </a>
                @endhasanyrole

                @hasanyrole('Admin|Registro|Contabilidad')
                @if(\App\Helpers\SaaS::has('reports_basic'))
                <a href="{{ route('reports.index') }}" wire:navigate class="flex flex-col items-center gap-1.5 p-3 rounded-xl hover:bg-indigo-50 transition {{ request()->routeIs('reports.*') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600' }}">
                    <i class="fas fa-chart-bar text-lg"></i>
                    <span class="text-[10px] font-semibold">Reportes</span>
                </a>
                @endif
                @endhasanyrole

                @role('Admin')
                <a href="{{ route('admin.users.index') }}" wire:navigate class="flex flex-col items-center gap-1.5 p-3 rounded-xl hover:bg-indigo-50 transition {{ request()->routeIs('admin.users.*') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600' }}">
                    <i class="fas fa-shield-alt text-lg"></i>
                    <span class="text-[10px] font-semibold">Usuarios</span>
                </a>
                <a href="{{ route('admin.settings.index') }}" wire:navigate class="flex flex-col items-center gap-1.5 p-3 rounded-xl hover:bg-indigo-50 transition {{ request()->routeIs('admin.settings.*') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600' }}">
                    <i class="fas fa-cog text-lg"></i>
                    <span class="text-[10px] font-semibold">Ajustes</span>
                </a>
                @endrole

                @role('Estudiante')
                @if(\App\Helpers\SaaS::has('finance'))
                <a href="{{ route('student.payments') }}" wire:navigate class="flex flex-col items-center gap-1.5 p-3 rounded-xl hover:bg-indigo-50 transition {{ request()->routeIs('student.payments') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600' }}">
                    <i class="fas fa-credit-card text-lg"></i>
                    <span class="text-[10px] font-semibold">Mis Pagos</span>
                </a>
                @endif
                @endrole

                {{-- Perfil y Cerrar Sesión siempre visibles --}}
                <a href="{{ route('profile.edit') }}" wire:navigate class="flex flex-col items-center gap-1.5 p-3 rounded-xl hover:bg-indigo-50 transition text-gray-600">
                    <i class="fas fa-user-circle text-lg"></i>
                    <span class="text-[10px] font-semibold">Mi Perfil</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="contents">
                    @csrf
                    <button type="submit" class="flex flex-col items-center gap-1.5 p-3 rounded-xl hover:bg-red-50 transition text-red-500">
                        <i class="fas fa-sign-out-alt text-lg"></i>
                        <span class="text-[10px] font-semibold">Salir</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Barra principal de 5 tabs --}}
    <div class="flex items-center justify-around px-1 pt-1.5 pb-1">
        {{-- Tab 1: Dashboard --}}
        <a href="{{ route('dashboard') }}" wire:navigate class="flex flex-col items-center gap-0.5 min-w-[56px] py-1 {{ request()->routeIs(['dashboard', 'admin.dashboard', 'student.dashboard', 'teacher.dashboard']) ? 'text-indigo-600' : 'text-gray-400' }}">
            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M11.47 3.84a.75.75 0 0 1 1.06 0l8.69 8.69a.75.75 0 1 1-1.06 1.06l-.44-.44V21a.75.75 0 0 1-.75.75h-4.5a.75.75 0 0 1-.75-.75v-3.75h-3V21a.75.75 0 0 1-.75.75h-4.5a.75.75 0 0 1-.75-.75v-7.85l-.44.44a.75.75 0 0 1-1.06-1.06l8.69-8.69Z"/></svg>
            <span class="text-[10px] font-semibold">Inicio</span>
            @if(request()->routeIs(['dashboard', 'admin.dashboard', 'student.dashboard', 'teacher.dashboard']))
                <span class="absolute -top-0.5 w-6 h-0.5 bg-indigo-600 rounded-full"></span>
            @endif
        </a>

        {{-- Tab 2: Dinámico por rol --}}
        @hasanyrole('Admin|Registro|Contabilidad|Caja')
        <a href="{{ route('admin.students.index') }}" wire:navigate class="flex flex-col items-center gap-0.5 min-w-[56px] py-1 {{ request()->routeIs('admin.students.*') ? 'text-indigo-600' : 'text-gray-400' }}">
            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z" clip-rule="evenodd"/></svg>
            <span class="text-[10px] font-semibold">Estudiantes</span>
        </a>
        @endhasanyrole
        @role('Estudiante')
        <a href="{{ route('student.payments') }}" wire:navigate class="flex flex-col items-center gap-0.5 min-w-[56px] py-1 {{ request()->routeIs('student.payments') ? 'text-indigo-600' : 'text-gray-400' }}">
            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 7.5a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5Z"/><path fill-rule="evenodd" d="M1.5 4.875C1.5 3.839 2.34 3 3.375 3h17.25c1.035 0 1.875.84 1.875 1.875v9.75c0 1.036-.84 1.875-1.875 1.875H3.375A1.875 1.875 0 0 1 1.5 14.625v-9.75Z" clip-rule="evenodd"/></svg>
            <span class="text-[10px] font-semibold">Pagos</span>
        </a>
        @endrole
        @role('Profesor')
        <a href="{{ route('teacher.dashboard') }}" wire:navigate class="flex flex-col items-center gap-0.5 min-w-[56px] py-1 {{ request()->routeIs('teacher.dashboard') ? 'text-indigo-600' : 'text-gray-400' }}">
            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M11.7 2.805a.75.75 0 0 1 .6 0A60.65 60.65 0 0 1 22.83 8.72a.75.75 0 0 1-.231 1.337 49.949 49.949 0 0 0-9.902 3.912l-.003.002-.343.18a.75.75 0 0 1-.707 0l-.343-.18a49.949 49.949 0 0 0-9.902-3.912.75.75 0 0 1-.231-1.337A60.653 60.653 0 0 1 11.7 2.805Z"/></svg>
            <span class="text-[10px] font-semibold">Clases</span>
        </a>
        @endrole

        {{-- Tab 3: Finanzas (Admin) / Calendario --}}
        @hasanyrole('Admin|Contabilidad|Caja')
        <a href="{{ route('admin.finance.dashboard') }}" wire:navigate class="flex flex-col items-center gap-0.5 min-w-[56px] py-1 {{ request()->routeIs('admin.finance.*') ? 'text-indigo-600' : 'text-gray-400' }}">
            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 7.5a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5Z"/><path fill-rule="evenodd" d="M1.5 4.875C1.5 3.839 2.34 3 3.375 3h17.25c1.035 0 1.875.84 1.875 1.875v9.75c0 1.036-.84 1.875-1.875 1.875H3.375A1.875 1.875 0 0 1 1.5 14.625v-9.75Z" clip-rule="evenodd"/></svg>
            <span class="text-[10px] font-semibold">Finanzas</span>
        </a>
        @endhasanyrole

        {{-- Tab 4: Notificaciones --}}
        <a href="{{ route('profile.edit') }}" wire:navigate class="flex flex-col items-center gap-0.5 min-w-[56px] py-1 {{ request()->routeIs('profile.*') ? 'text-indigo-600' : 'text-gray-400' }}">
            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M18.685 19.097A9.723 9.723 0 0 0 21.75 12c0-5.385-4.365-9.75-9.75-9.75S2.25 6.615 2.25 12a9.723 9.723 0 0 0 3.065 7.097A9.716 9.716 0 0 0 12 21.75a9.716 9.716 0 0 0 6.685-2.653Zm-12.54-1.285A7.486 7.486 0 0 1 12 15a7.486 7.486 0 0 1 5.855 2.812A8.224 8.224 0 0 1 12 20.25a8.224 8.224 0 0 1-5.855-2.438ZM15.75 9a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" clip-rule="evenodd"/></svg>
            <span class="text-[10px] font-semibold">Perfil</span>
        </a>

        {{-- Tab 5: Más --}}
        <button @click="moreOpen = !moreOpen" class="flex flex-col items-center gap-0.5 min-w-[56px] py-1" :class="moreOpen ? 'text-indigo-600' : 'text-gray-400'">
            <svg class="h-6 w-6 transition-transform duration-200" :class="moreOpen ? 'rotate-45' : ''" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 9a.75.75 0 0 0-1.5 0v2.25H9a.75.75 0 0 0 0 1.5h2.25V15a.75.75 0 0 0 1.5 0v-2.25H15a.75.75 0 0 0 0-1.5h-2.25V9Z" clip-rule="evenodd"/></svg>
            <span class="text-[10px] font-semibold">Más</span>
        </button>
    </div>
</nav>
