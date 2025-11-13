<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== FIX ATTENDANCE TOKEN (DIRECT) ===\n\n";

$regId = 15;

// Get current values
$reg = DB::table('registrations')->where('id', $regId)->first();

echo "Registration ID: {$reg->id}\n";
echo "token_plain: {$reg->token_plain}\n";
echo "attendance_token BEFORE: " . ($reg->attendance_token ?? 'NULL') . "\n\n";

// Update directly
DB::table('registrations')
    ->where('id', $regId)
    ->update([
        'attendance_token' => $reg->token_plain,
        'updated_at' => now()
    ]);

echo "✅ UPDATED!\n\n";

// Verify
$regAfter = DB::table('registrations')->where('id', $regId)->first();
echo "attendance_token AFTER: " . ($regAfter->attendance_token ?? 'NULL') . "\n";

echo "\n=== DONE ===\n";
