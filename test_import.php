<?php

use App\Services\CsvImportService;
use App\Models\Student;

$service = app(CsvImportService::class);

$mappingStudents = [
    'Nombre' => 'Nombre',
    'Apellido' => 'Apellido',
    'Cedula' => 'Cedula',
    'Telefono' => 'Telefono',
    'Email' => 'Email',
    'Grupo_ID' => 'Grupo_ID',
    'Asignatura_ID' => 'Asignatura_ID'
];

echo "Testing Students...\n";
$res1 = $service->importBatch('students_csv', base_path('test_students.csv'), $mappingStudents, 0, 10);
print_r($res1);

$mappingPayments = [
    'Cedula_Estudiante' => 'Cedula_Estudiante',
    'Monto' => 'Monto',
    'Fecha' => 'Fecha',
    'Concepto' => 'Concepto',
    'Metodo_Pago' => 'Metodo_Pago',
    'Estado' => 'Estado',
    'NCF' => 'NCF'
];

echo "\nTesting Payments...\n";
$res2 = $service->importBatch('financial_csv', base_path('test_payments.csv'), $mappingPayments, 0, 10);
print_r($res2);

echo "\nResulting Student Data:\n";
$student = Student::with('enrollments', 'payments')->where('cedula', '402-9999999-9')->first();
if ($student) {
    echo json_encode($student->toArray(), JSON_PRETTY_PRINT);
} else {
    echo "Student not found!\n";
}
