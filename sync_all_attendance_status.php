<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Registration;

echo "=== SYNC ALL ATTENDANCE STATUS ===\n\n";

// Update all registrations that have attendance
$updated = Registration::whereHas('attendance')
    ->whereNull('attendance_status')
    ->update([
        'attendance_status' => \DB::raw('(SELECT status FROM attendances WHERE attendances.registration_id = registrations.id LIMIT 1)'),
        'attended_at' => \DB::raw('(SELECT attendance_time FROM attendances WHERE attendances.registration_id = registrations.id LIMIT 1)')
    ]);

echo "Updated {$updated} registration(s)\n\n";

// Verify
$total = Registration::whereHas('attendance')->count();
$synced = Registration::whereHas('attendance')->whereNotNull('attendance_status')->count();

echo "Total registrations with attendance: {$total}\n";
echo "Synced registrations: {$synced}\n";

if ($total === $synced) {
    echo "\n✅ All registrations synced successfully!\n";
} else {
    echo "\n⚠️  Some registrations not synced. Run again.\n";
}
