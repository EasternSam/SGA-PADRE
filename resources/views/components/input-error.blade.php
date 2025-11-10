@props(['messages'])

@if ($messages)
    <!-- --- ¡ACTUALIZADO! --- -->
    {{-- Clases de texto actualizadas a 'sga-danger' --}}
    <ul {{ $attributes->merge(['class' => 'mt-2 space-y-1 text-sm text-sga-danger']) }}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif