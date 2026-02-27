<?php
$student = App\Models\Student::with('user')->find(283965);
if (!$student) {
    echo "Student not found\n";
    exit;
}
echo "Student ID: " . $student->id . "\n";
echo "Student User ID: " . $student->user_id . "\n";
if ($student->user) {
    echo "User Model ID: " . $student->user->id . "\n";
    echo "User Name: " . $student->user->name . "\n";
    echo "User PIN in Model: '" . $student->user->kiosk_pin . "'\n";
    echo "User PIN in DB raw: '" . DB::table('users')->where('id', $student->user_id)->value('kiosk_pin') . "'\n";
} else {
    echo "No User related.\n";
}
