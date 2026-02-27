<?php
$userId = 283965;
$user = App\Models\User::find($userId);
echo "User ID: " . $user->id . "\n";
echo "Current PIN direct access: '" . $user->kiosk_pin . "'\n";
echo "Current PIN DB raw: '" . DB::table('users')->where('id', $userId)->value('kiosk_pin') . "'\n";

$newPin = "5555";
$user->kiosk_pin = $newPin;
$user->save();
echo "Saved new PIN direct access: '" . $user->kiosk_pin . "'\n";

$reloaded = App\Models\User::find($userId);
echo "Reloaded PIN direct access: '" . $reloaded->kiosk_pin . "'\n";
echo "Reloaded PIN DB raw: '" . DB::table('users')->where('id', $userId)->value('kiosk_pin') . "'\n";
