{{--
    Componente Modal Universal
    
    MODIFICADO:
    1. 'name' es ahora opcional ('name' => null)
    2. Los listeners x-on:open/close-modal están envueltos en @if($name)
       para prevenir errores cuando el modal NO usa un nombre (ej. PaymentConcepts).
    3. Se mantienen las correcciones del usuario: maxWidths y el init() simplificado.
    4. Se re-agregó x-trap.inert.noscroll para accesibilidad.
--}}
@props([
    'name' => null,  // <-- FIX: Vuelve a ser opcional
    'show' => false,
    'maxWidth' => '2xl'
])

@php
$maxWidth = [
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
    '3xl' => 'sm:max-w-3xl',
    '4xl' => 'sm:max-w-4xl', // <-- Mantenemos tu adición
    '5xl' => 'sm:max-w-5xl', // <-- Mantenemos tu corrección
    '6xl' => 'sm:max-w-6xl', // <-- Mantenemos tu adición
    '7xl' => 'sm:max-w-7xl', // <-- Mantenemos tu adición
][$maxWidth];
@endphp

<div
    x-data="{
        show: @json($show),
        focusables() {
            // All focusable element types...
            let selector = 'a, button, input:not([type=\'hidden\']), textarea, select, details, [tabindex]:not([tabindex=\'-1\'])'
            return [...$el.querySelectorAll(selector)]
                // All non-disabled elements...
                .filter(el => ! el.hasAttribute('disabled'))
        },
        firstFocusable() { return this.focusables()[0] },
        lastFocusable() { return this.focusables().slice(-1)[0] },
        nextFocusable() {
            let S = this.focusables()
            let I = S.indexOf(document.activeElement)
            if (I === S.length - 1) {
                this.firstFocusable().focus()
            } else {
                S[I + 1].focus()
            }
        },
        previousFocusable() {
            let S = this.focusables()
            let I = S.indexOf(document.activeElement)
            if (I === 0) {
                this.lastFocusable().focus()
            } else {
                S[I - 1].focus()
            }
        },

        {{-- ¡Mantenemos tu reparación del init! --}}
        init() {
            $watch('show', value => {
                if (value) {
                    document.body.classList.add('overflow-y-hidden');
                    // Solo intentar enfocar si existe un elemento enfocable
                    let focusable = this.firstFocusable();
                    if (focusable) {
                        focusable.focus();
                    }
                } else {
                    document.body.classList.remove('overflow-y-hidden');
                }
            })
        }
    }"
    
    {{-- FIX: Solo añadir listeners si se proveyó un 'name' --}}
    @if ($name)
        x-on:open-modal.window="$event.detail == '{{ $name }}' ? show = true : null"
        x-on:close-modal.window="$event.detail == '{{ $name }}' ? show = false : null"
    @endif
    
    x-on:keydown.escape.window="show = false"
    x-on:keydown.tab.prevent="$event.shiftKey || nextFocusable() ? previousFocusable() : nextFocusable()"
    x-on:keydown.shift.tab.prevent="previousFocusable()"
    x-show="show"
    class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50"
    style="display: {{ $show ? 'block' : 'none' }};"
>
    <div
        x-show="show"
        class="fixed inset-0 transform transition-all"
        x-on:click="show = false"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
    </div>

    <div
        x-show="show"
        class="mb-6 bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full {{ $maxWidth }} sm:mx-auto"
        {{-- FIX: Re-agregado para accesibilidad --}}
        x-trap.inert.noscroll="show"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
    >
        {{ $slot }}
    </div>
</div>