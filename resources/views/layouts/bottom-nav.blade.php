{{-- Bottom Navigation Bar — Solo visible en móvil (<lg) --}}
{{-- v2.0: Glassmorphism + active dots + iOS sheet panel --}}
<nav x-data="{ moreOpen: false }" 
     class="fixed bottom-0 inset-x-0 z-50 lg:hidden bg-white/80 backdrop-blur-xl border-t border-gray-200/60 shadow-[0_-4px_30px_rgba(0,0,0,0.06)]" 
     style="padding-bottom: env(safe-area-inset-bottom, 0px);">
    
    {{-- Panel "Más" — iOS Sheet Style --}}
    <div x-show="moreOpen" 
         x-transition:enter="transition ease-out duration-250" 
         x-transition:enter-start="translate-y-full opacity-0" 
         x-transition:enter-end="translate-y-0 opacity-100" 
         x-transition:leave="transition ease-in duration-200" 
         x-transition:leave-start="translate-y-0 opacity-100" 
         x-transition:leave-end="translate-y-full opacity-0"
         class="absolute bottom-full left-0 right-0 bg-white/96 backdrop-blur-2xl rounded-t-[1.25rem] shadow-[0_-8px_40px_rgba(0,0,0,0.12)] border-t border-gray-100/80 max-h-[75vh] overflow-y-auto" 
         style="display:none;" 
         @click.outside="moreOpen = false">
        
        <div class="px-5 pt-3.5 pb-5">
            {{-- Drag handle --}}
            <div class="w-9 h-1 bg-gray-300/80 rounded-full mx-auto mb-4"></div>
            
            {{-- Section title --}}
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-3 px-1">Menú completo</p>
            
            <div class="grid grid-cols-4 gap-1.5">
                @hasanyrole('Admin|Registro')
                <a href="{{ route('admin.students.index') }}" wire:navigate class="flex flex-col items-center gap-1.5 p-3 rounded-2xl transition-all duration-150 {{ request()->routeIs('admin.students.*') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 active:bg-gray-100' }}">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ request()->routeIs('admin.students.*') ? 'bg-indigo-100' : 'bg-gray-100' }}">
                        <i class="fas fa-user-graduate text-base"></i>
                    </div>
                    <span class="text-[10px] font-semibold leading-tight text-center">Estudiantes</span>
                </a>
                <a href="{{ route('admin.teachers.index') }}" wire:navigate class="flex flex-col items-center gap-1.5 p-3 rounded-2xl transition-all duration-150 {{ request()->routeIs('admin.teachers.*') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 active:bg-gray-100' }}">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ request()->routeIs('admin.teachers.*') ? 'bg-indigo-100' : 'bg-gray-100' }}">
                        <i class="fas fa-chalkboard-teacher text-base"></i>
                    </div>
                    <span class="text-[10px] font-semibold leading-tight text-center">Docentes</span>
                </a>
                @if(\App\Helpers\SaaS::showCourses())
                <a href="{{ route('admin.courses.index') }}" wire:navigate class="flex flex-col items-center gap-1.5 p-3 rounded-2xl transition-all duration-150 {{ request()->routeIs('admin.courses.*') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 active:bg-gray-100' }}">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ request()->routeIs('admin.courses.*') ? 'bg-indigo-100' : 'bg-gray-100' }}">
                        <i class="fas fa-book text-base"></i>
                    </div>
                    <span class="text-[10px] font-semibold leading-tight text-center">Cursos</span>
                </a>
                @endif
                <a href="{{ route('admin.calendar.index') }}" wire:navigate class="flex flex-col items-center gap-1.5 p-3 rounded-2xl transition-all duration-150 {{ request()->routeIs('admin.calendar.*') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 active:bg-gray-100' }}">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ request()->routeIs('admin.calendar.*') ? 'bg-indigo-100' : 'bg-gray-100' }}">
                        <i class="fas fa-calendar-alt text-base"></i>
                    </div>
                    <span class="text-[10px] font-semibold leading-tight text-center">Calendario</span>
                </a>
                @endhasanyrole

                @hasanyrole('Admin|Contabilidad|Caja')
                @if(\App\Helpers\SaaS::has('finance'))
                <a href="{{ route('admin.finance.dashboard') }}" wire:navigate class="flex flex-col items-center gap-1.5 p-3 rounded-2xl transition-all duration-150 {{ request()->routeIs('admin.finance.*') ? 'bg-emerald-50 text-emerald-600' : 'text-gray-600 active:bg-gray-100' }}">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ request()->routeIs('admin.finance.*') ? 'bg-emerald-100' : 'bg-gray-100' }}">
                        <i class="fas fa-dollar-sign text-base"></i>
                    </div>
                    <span class="text-[10px] font-semibold leading-tight text-center">Finanzas</span>
                </a>
                @endif
                @endhasanyrole

                @hasanyrole('Admin|Contabilidad')
                <a href="{{ route('admin.hr.employees') }}" wire:navigate class="flex flex-col items-center gap-1.5 p-3 rounded-2xl transition-all duration-150 {{ request()->routeIs('admin.hr.*') ? 'bg-purple-50 text-purple-600' : 'text-gray-600 active:bg-gray-100' }}">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ request()->routeIs('admin.hr.*') ? 'bg-purple-100' : 'bg-gray-100' }}">
                        <i class="fas fa-users text-base"></i>
                    </div>
                    <span class="text-[10px] font-semibold leading-tight text-center">RRHH</span>
                </a>
                @endhasanyrole

                @hasanyrole('Admin|Registro|Contabilidad')
                @if(\App\Helpers\SaaS::has('reports_basic'))
                <a href="{{ route('reports.index') }}" wire:navigate class="flex flex-col items-center gap-1.5 p-3 rounded-2xl transition-all duration-150 {{ request()->routeIs('reports.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-600 active:bg-gray-100' }}">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ request()->routeIs('reports.*') ? 'bg-blue-100' : 'bg-gray-100' }}">
                        <i class="fas fa-chart-bar text-base"></i>
                    </div>
                    <span class="text-[10px] font-semibold leading-tight text-center">Reportes</span>
                </a>
                @endif
                @endhasanyrole

                @role('Admin')
                <a href="{{ route('admin.users.index') }}" wire:navigate class="flex flex-col items-center gap-1.5 p-3 rounded-2xl transition-all duration-150 {{ request()->routeIs('admin.users.*') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 active:bg-gray-100' }}">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ request()->routeIs('admin.users.*') ? 'bg-indigo-100' : 'bg-gray-100' }}">
                        <i class="fas fa-shield-alt text-base"></i>
                    </div>
                    <span class="text-[10px] font-semibold leading-tight text-center">Usuarios</span>
                </a>
                <a href="{{ route('admin.settings.index') }}" wire:navigate class="flex flex-col items-center gap-1.5 p-3 rounded-2xl transition-all duration-150 {{ request()->routeIs('admin.settings.*') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 active:bg-gray-100' }}">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ request()->routeIs('admin.settings.*') ? 'bg-indigo-100' : 'bg-gray-100' }}">
                        <i class="fas fa-cog text-base"></i>
                    </div>
                    <span class="text-[10px] font-semibold leading-tight text-center">Ajustes</span>
                </a>
                @endrole

                @role('Estudiante')
                @if(\App\Helpers\SaaS::has('finance'))
                <a href="{{ route('student.payments') }}" wire:navigate class="flex flex-col items-center gap-1.5 p-3 rounded-2xl transition-all duration-150 {{ request()->routeIs('student.payments') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 active:bg-gray-100' }}">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ request()->routeIs('student.payments') ? 'bg-indigo-100' : 'bg-gray-100' }}">
                        <i class="fas fa-credit-card text-base"></i>
                    </div>
                    <span class="text-[10px] font-semibold leading-tight text-center">Mis Pagos</span>
                </a>
                @endif
                @endrole

                {{-- Separator --}}
                <div class="col-span-4 border-t border-gray-100 my-1"></div>

                {{-- Perfil y Cerrar Sesión --}}
                <a href="{{ route('profile.edit') }}" wire:navigate class="flex flex-col items-center gap-1.5 p-3 rounded-2xl transition-all duration-150 text-gray-600 active:bg-gray-100">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-gray-100">
                        <i class="fas fa-user-circle text-base"></i>
                    </div>
                    <span class="text-[10px] font-semibold leading-tight text-center">Mi Perfil</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="contents">
                    @csrf
                    <button type="submit" class="flex flex-col items-center gap-1.5 p-3 rounded-2xl transition-all duration-150 text-red-500 active:bg-red-50">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-red-50">
                            <i class="fas fa-sign-out-alt text-base"></i>
                        </div>
                        <span class="text-[10px] font-semibold leading-tight text-center">Salir</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Main Tab Bar — 5 tabs --}}
    <div class="flex items-center justify-around px-2 pt-2 pb-1.5">
        {{-- Tab 1: Dashboard --}}
        @php $isDashboard = request()->routeIs(['dashboard', 'admin.dashboard', 'student.dashboard', 'teacher.dashboard']); @endphp
        <a href="{{ route('dashboard') }}" wire:navigate 
           class="flex flex-col items-center gap-0.5 min-w-[52px] py-1 relative transition-colors duration-200 {{ $isDashboard ? 'text-indigo-600' : 'text-gray-400' }}">
            <svg class="h-[22px] w-[22px]" fill="currentColor" viewBox="0 0 24 24"><path d="M11.47 3.84a.75.75 0 0 1 1.06 0l8.69 8.69a.75.75 0 1 1-1.06 1.06l-.44-.44V21a.75.75 0 0 1-.75.75h-4.5a.75.75 0 0 1-.75-.75v-3.75h-3V21a.75.75 0 0 1-.75.75h-4.5a.75.75 0 0 1-.75-.75v-7.85l-.44.44a.75.75 0 0 1-1.06-1.06l8.69-8.69Z"/></svg>
            <span class="text-[10px] font-semibold">Inicio</span>
            @if($isDashboard)
                <span class="w-1 h-1 rounded-full bg-indigo-600 shadow-[0_0_4px_rgba(99,102,241,0.5)]"></span>
            @endif
        </a>

        {{-- Tab 2: Dynamic by role --}}
        @hasanyrole('Admin|Registro|Contabilidad|Caja')
        @php $isStudents = request()->routeIs('admin.students.*'); @endphp
        <a href="{{ route('admin.students.index') }}" wire:navigate 
           class="flex flex-col items-center gap-0.5 min-w-[52px] py-1 relative transition-colors duration-200 {{ $isStudents ? 'text-indigo-600' : 'text-gray-400' }}">
            <svg class="h-[22px] w-[22px]" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z" clip-rule="evenodd"/></svg>
            <span class="text-[10px] font-semibold">Alumnos</span>
            @if($isStudents)
                <span class="w-1 h-1 rounded-full bg-indigo-600 shadow-[0_0_4px_rgba(99,102,241,0.5)]"></span>
            @endif
        </a>
        @endhasanyrole
        @role('Estudiante')
        @php $isPayments = request()->routeIs('student.payments'); @endphp
        <a href="{{ route('student.payments') }}" wire:navigate 
           class="flex flex-col items-center gap-0.5 min-w-[52px] py-1 relative transition-colors duration-200 {{ $isPayments ? 'text-indigo-600' : 'text-gray-400' }}">
            <svg class="h-[22px] w-[22px]" fill="currentColor" viewBox="0 0 24 24"><path d="M12 7.5a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5Z"/><path fill-rule="evenodd" d="M1.5 4.875C1.5 3.839 2.34 3 3.375 3h17.25c1.035 0 1.875.84 1.875 1.875v9.75c0 1.036-.84 1.875-1.875 1.875H3.375A1.875 1.875 0 0 1 1.5 14.625v-9.75Z" clip-rule="evenodd"/></svg>
            <span class="text-[10px] font-semibold">Pagos</span>
            @if($isPayments)
                <span class="w-1 h-1 rounded-full bg-indigo-600 shadow-[0_0_4px_rgba(99,102,241,0.5)]"></span>
            @endif
        </a>
        @endrole
        @role('Profesor')
        @php $isTeacherDash = request()->routeIs('teacher.dashboard'); @endphp
        <a href="{{ route('teacher.dashboard') }}" wire:navigate 
           class="flex flex-col items-center gap-0.5 min-w-[52px] py-1 relative transition-colors duration-200 {{ $isTeacherDash ? 'text-indigo-600' : 'text-gray-400' }}">
            <svg class="h-[22px] w-[22px]" fill="currentColor" viewBox="0 0 24 24"><path d="M11.7 2.805a.75.75 0 0 1 .6 0A60.65 60.65 0 0 1 22.83 8.72a.75.75 0 0 1-.231 1.337 49.949 49.949 0 0 0-9.902 3.912l-.003.002-.343.18a.75.75 0 0 1-.707 0l-.343-.18a49.949 49.949 0 0 0-9.902-3.912.75.75 0 0 1-.231-1.337A60.653 60.653 0 0 1 11.7 2.805Z"/></svg>
            <span class="text-[10px] font-semibold">Clases</span>
            @if($isTeacherDash)
                <span class="w-1 h-1 rounded-full bg-indigo-600 shadow-[0_0_4px_rgba(99,102,241,0.5)]"></span>
            @endif
        </a>
        @endrole

        {{-- Tab 3: Finance (Admin) --}}
        @hasanyrole('Admin|Contabilidad|Caja')
        @php $isFinance = request()->routeIs('admin.finance.*'); @endphp
        <a href="{{ route('admin.finance.dashboard') }}" wire:navigate 
           class="flex flex-col items-center gap-0.5 min-w-[52px] py-1 relative transition-colors duration-200 {{ $isFinance ? 'text-emerald-600' : 'text-gray-400' }}">
            <svg class="h-[22px] w-[22px]" fill="currentColor" viewBox="0 0 24 24"><path d="M12 7.5a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5Z"/><path fill-rule="evenodd" d="M1.5 4.875C1.5 3.839 2.34 3 3.375 3h17.25c1.035 0 1.875.84 1.875 1.875v9.75c0 1.036-.84 1.875-1.875 1.875H3.375A1.875 1.875 0 0 1 1.5 14.625v-9.75Z" clip-rule="evenodd"/></svg>
            <span class="text-[10px] font-semibold">Finanzas</span>
            @if($isFinance)
                <span class="w-1 h-1 rounded-full bg-emerald-600 shadow-[0_0_4px_rgba(16,185,129,0.5)]"></span>
            @endif
        </a>
        @endhasanyrole

        {{-- Tab 4: Profile --}}
        @php $isProfile = request()->routeIs('profile.*'); @endphp
        <a href="{{ route('profile.edit') }}" wire:navigate 
           class="flex flex-col items-center gap-0.5 min-w-[52px] py-1 relative transition-colors duration-200 {{ $isProfile ? 'text-indigo-600' : 'text-gray-400' }}">
            <svg class="h-[22px] w-[22px]" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M18.685 19.097A9.723 9.723 0 0 0 21.75 12c0-5.385-4.365-9.75-9.75-9.75S2.25 6.615 2.25 12a9.723 9.723 0 0 0 3.065 7.097A9.716 9.716 0 0 0 12 21.75a9.716 9.716 0 0 0 6.685-2.653Zm-12.54-1.285A7.486 7.486 0 0 1 12 15a7.486 7.486 0 0 1 5.855 2.812A8.224 8.224 0 0 1 12 20.25a8.224 8.224 0 0 1-5.855-2.438ZM15.75 9a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" clip-rule="evenodd"/></svg>
            <span class="text-[10px] font-semibold">Perfil</span>
            @if($isProfile)
                <span class="w-1 h-1 rounded-full bg-indigo-600 shadow-[0_0_4px_rgba(99,102,241,0.5)]"></span>
            @endif
        </a>

        {{-- Tab 5: More --}}
        <button @click="moreOpen = !moreOpen" 
                class="flex flex-col items-center gap-0.5 min-w-[52px] py-1 relative transition-colors duration-200" 
                :class="moreOpen ? 'text-indigo-600' : 'text-gray-400'">
            <svg class="h-[22px] w-[22px] transition-transform duration-250" :class="moreOpen ? 'rotate-45' : ''" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 9a.75.75 0 0 0-1.5 0v2.25H9a.75.75 0 0 0 0 1.5h2.25V15a.75.75 0 0 0 1.5 0v-2.25H15a.75.75 0 0 0 0-1.5h-2.25V9Z" clip-rule="evenodd"/></svg>
            <span class="text-[10px] font-semibold">Más</span>
            <span x-show="moreOpen" class="w-1 h-1 rounded-full bg-indigo-600 shadow-[0_0_4px_rgba(99,102,241,0.5)]"></span>
        </button>
    </div>
</nav>
