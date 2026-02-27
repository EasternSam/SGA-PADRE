<?php
$student = App\Models\Student::with('user')->findOrFail(283965);
echo "Before PIN: '" . $student->user->kiosk_pin . "'\n";
$student->user->kiosk_pin = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
$saved = $student->user->save();
echo "Save result: " . ($saved ? 'true' : 'false') . "\n";
echo "After PIN: '" . $student->user->kiosk_pin . "'\n";
echo "DB raw: '" . DB::table('users')->where('id', $student->user->id)->value('kiosk_pin') . "'\n";
