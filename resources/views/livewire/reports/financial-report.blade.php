{{-- Reporte Financiero --}}
<div id="printable-area" class="bg-white text-black p-4 md:p-8 font-sans">
    <div class="border-b-2 border-gray-800 pb-4 mb-6">
        <h1 class="text-2xl font-bold uppercase">Reporte Financiero</h1>
        <p class="text-sm text-gray-600">Estado: {{ strtoupper($data['filter_status']) }} | Desde: {{ $data['date_from'] }} Hasta: {{ $data['date_to'] }}</p>
    </div>

    {{-- Sección de Pagos Realizados --}}
    @if(count($data['payments']) > 0)
        <h3 class="text-lg font-bold mb-2 uppercase text-gray-700 mt-6">Transacciones Registradas</h3>
        <table class="w-full border-collapse border border-gray-300 text-xs">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border border-gray-300 p-2 text-left">Fecha</th>
                    <th class="border border-gray-300 p-2 text-left">Estudiante</th>
                    <th class="border border-gray-300 p-2 text-left">Concepto</th>
                    <th class="border border-gray-300 p-2 text-left">Curso</th>
                    <th class="border border-gray-300 p-2 text-right">Monto</th>
                    <th class="border border-gray-300 p-2 text-center">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['payments'] as $payment)
                    <tr>
                        <td class="border border-gray-300 p-2">{{ $payment->created_at->format('d/m/Y') }}</td>
                        <td class="border border-gray-300 p-2 uppercase">{{ $payment->student->fullName ?? 'N/A' }}</td>
                        <td class="border border-gray-300 p-2">{{ $payment->paymentConcept->name ?? 'General' }}</td>
                        <td class="border border-gray-300 p-2">{{ $payment->enrollment->courseSchedule->module->course->name ?? '-' }}</td>
                        <td class="border border-gray-300 p-2 text-right font-mono">{{ number_format($payment->amount, 2) }}</td>
                        <td class="border border-gray-300 p-2 text-center">
                            <span class="px-2 py-0.5 rounded text-[10px] {{ $payment->status == 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $payment->status }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-gray-100 font-bold">
                    <td colspan="4" class="border border-gray-300 p-2 text-right">TOTAL</td>
                    <td class="border border-gray-300 p-2 text-right">{{ number_format($data['payments']->sum('amount'), 2) }}</td>
                    <td class="border border-gray-300 p-2"></td>
                </tr>
            </tfoot>
        </table>
    @endif

    {{-- Sección de Deudas (Simplificada) --}}
    @if(count($data['debts']) > 0)
        <h3 class="text-lg font-bold mb-2 uppercase text-gray-700 mt-8 text-red-600">Pagos Pendientes / Deudas (Muestra)</h3>
        <table class="w-full border-collapse border border-gray-300 text-xs">
            <thead class="bg-red-50">
                <tr>
                    <th class="border border-gray-300 p-2 text-left">Estudiante</th>
                    <th class="border border-gray-300 p-2 text-left">Curso Pendiente</th>
                    <th class="border border-gray-300 p-2 text-left">Teléfono</th>
                    <th class="border border-gray-300 p-2 text-center">Estado Inscripción</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['debts'] as $debt)
                    <tr>
                        <td class="border border-gray-300 p-2 font-bold uppercase">{{ $debt->student->fullName }}</td>
                        <td class="border border-gray-300 p-2">{{ $debt->courseSchedule->module->course->name ?? 'N/A' }}</td>
                        <td class="border border-gray-300 p-2">{{ $debt->student->phone ?? '-' }}</td>
                        <td class="border border-gray-300 p-2 text-center">{{ $debt->status }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>