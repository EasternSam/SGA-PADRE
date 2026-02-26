<?php
$p = App\Models\Payment::first();
if($p) {
    $p->status = 'Pendiente';
    $p->update();
    $p->status = 'Completado';
    $p->update();
}

$e = App\Models\Enrollment::latest()->first();
if($e) {
    $e->final_grade = 80;
    $e->status = 'Cursando';
    $e->update();
    $e->final_grade = 95;
    $e->status = 'Completado';
    $e->update();
}

$s = App\Models\Setting::firstOrCreate(
    ['key' => 'system_maintenance'],
    ['value' => 'false', 'group' => 'general', 'type' => 'string']
);
$s->value = 'true';
$s->update();
$s->value = 'false';
$s->update();
echo "Audits generated successfully!\n";
