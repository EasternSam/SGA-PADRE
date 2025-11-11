<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expediente de {{ $student->fullName }}</title>
    <style>
        /* Estilos generales para el PDF */
        @page {
            margin: 20mm; /* Márgenes de la página */
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.4;
        }
        h1, h2, h3 {
            font-weight: bold;
            color: #1e3a8a; /* Color primario (sga-primary) */
            margin-top: 15px;
            margin-bottom: 10px;
        }
        h1 {
            font-size: 18pt;
            text-align: center;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 5px;
        }
        h2 {
            font-size: 14pt;
            border-bottom: 1px solid #e5e7eb; /* sga-gray */
            padding-bottom: 3px;
        }
        h3 {
            font-size: 12pt;
            color: #1f2937; /* sga-text */
        }

        /* Tabla de Información Personal */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .info-table td {
            padding: 6px;
            border: 1px solid #e5e7eb; /* sga-gray */
        }
        .info-table td:first-child {
            font-weight: bold;
            width: 30%;
            background-color: #f9fafb; /* gray-50 */
        }

        /* Tabla de Cursos y Pagos */
        .styled-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin-top: 10px;
        }
        .styled-table thead tr {
            background-color: #1e3a8a; /* sga-primary */
            color: #ffffff;
            text-align: left;
        }
        .styled-table th,
        .styled-table td {
            padding: 8px 10px;
            border: 1px solid #e5e7eb; /* sga-gray */
        }
        .styled-table tbody tr:nth-of-type(even) {
            background-color: #f9fafb; /* gray-50 */
        }
        .styled-table tbody tr:hover {
            background-color: #f3f4f6; /* gray-100 */
        }
        .no-records {
            text-align: center;
            padding: 15px;
            font-style: italic;
            color: #6b7280; /* sga-text-light */
        }

        /* Encabezado y Pie de Página */
        header {
            text-align: center;
            margin-bottom: 25px;
        }
        
        /* --- ¡CSS DEL LOGO MODIFICADO! --- */
        /* Ahora se aplica al IMG */
        header .logo {
            width: 120px; /* Ajusta el ancho según necesites */
            height: auto; /* Altura automática para mantener proporción */
            margin: 0 auto 10px auto;
        }

        footer {
            position: fixed;
            bottom: -20mm; /* Posicionar fuera del área de impresión inicial */
            left: 0;
            right: 0;
            height: 20mm;
            text-align: center;
            font-size: 8pt;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 5px;
        }

        /* Clases de utilidad */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
    </style>
</head>
<body>

    <header>
        {{-- --- ¡MODIFICACIÓN! Usando 'centuu.png' --- --}}
        {{-- Asegúrate de que 'centuu.png' exista en tu carpeta 'public' --}}
        <img src="{{ public_path('centuu.png') }}" class="logo">
        {{-- --- FIN DE LA MODIFICACIÓN --- --}}
        
        <h1>Expediente Académico</h1>
    </header>

    <main>
        <section class="student-info">
            <h2>Información del Estudiante</h2>
            <table class="info-table">
                <tbody>
                    <tr>
                        <td>Nombre Completo:</td>
                        <td>{{ $student->fullName }}</td>
                    </tr>
                    <tr>
                        <td>Email:</td>
                        <td>{{ $student->email }}</td>
                    </tr>
                    <tr>
                        <td>Cédula/DNI:</td>
                        <td>{{ $student->cedula ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td>Teléfono Móvil:</td>
                        <td>{{ $student->mobile_phone ?? $student->phone ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td>Fecha de Nacimiento:</td>
                        <td>{{ $student->birth_date ? $student->birth_date->format('d/m/Y') : 'N/A' }} (Edad: {{ $student->age ?? 'N/A' }})</td>
                    </tr>
                    <tr>
                        <td>Dirección:</td>
                        <td>{{ $student->address ?? 'N/A' }}</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <section class="enrollment-section">
            <h2>Cursos Inscritos</h2>
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Curso</th>
                        <th>Módulo</th>
                        <th>Profesor</th>
                        <th>Estado</th>
                        <th>Calificación</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($enrollments as $enrollment)
                        <tr>
                            <td>{{ $enrollment->courseSchedule->module->course->name ?? 'N/A' }}</td>
                            <td>{{ $enrollment->courseSchedule->module->name ?? 'N/A' }}</td>
                            <td>{{ $enrollment->courseSchedule->teacher->name ?? 'No asignado' }}</td>
                            <td>{{ $enrollment->status }}</td>
                            <td class="text-center">{{ $enrollment->final_grade ?? 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="no-records">No hay matrículas registradas para este estudiante.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <section class="payment-section">
            <h2>Historial de Pagos</h2>
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Concepto</th>
                        <th class="text-right">Monto</th>
                        <th>Método</th>
                        <th>Registrado Por</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr>
                            <td>{{ $payment->created_at->format('d/m/Y') }}</td>
                            <td>{{ $payment->paymentConcept->name ?? $payment->description ?? 'N/A' }}</td>
                            <td class="text-right">${{ number_format($payment->amount, 2) }}</td>
                            <td>{{ $payment->payment_method ?? 'N/A' }}</td>
                            <td>{{ $payment->user->name ?? 'Sistema' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="no-records">No hay pagos registrados para este estudiante.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </main>

    <footer>
        Generado el: {{ now()->format('d/m/Y h:i A') }} | {{ config('app.name', 'SGA') }}
    </footer>

</body>
</html>