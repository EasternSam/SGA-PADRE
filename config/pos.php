<?php

/*
|--------------------------------------------------------------------------
| Terminal de Caja (POS)
|--------------------------------------------------------------------------
| Registros de referencia que el POS resuelve por clave estable, nunca por ID.
| Ambos se crean de forma idempotente con `php artisan db:seed --class=PosSeeder`.
*/

return [

    // Ficha usada para ventas a público general (sin estudiante identificado).
    'walk_in_student_code' => env('POS_WALK_IN_STUDENT_CODE', 'CLIENTE-GRAL'),

    // Concepto abierto: el cajero escribe la descripción y el precio.
    'open_concept_name' => env('POS_OPEN_CONCEPT_NAME', 'Cobro Diverso'),

    'max_quantity' => 99,

    'gateways' => ['Efectivo', 'Tarjeta', 'Transferencia'],

];
