<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Registration;

echo "=== TOKEN VERIFICATION SYSTEM ===\n";
echo "Checking all registrations for token consistency...\n\n";

$allRegistrations = Registration::with(['user', 'event'])->get();
$totalCount = $allRegistrations->count();
$validCount = 0;
$invalidCount = 0;
$issues = [];

foreach ($allRegistrations as $reg) {
    $isValid = true;
    $problems = [];
    
    // Check 1: attendance_token should match token_plain
    if ($reg->attendance_token !== $reg->token_plain) {
        $isValid = false;
        $problems[] = "Token mismatch (attendance: '{$reg->attendance_token}' vs plain: '{$reg->token_plain}')";
    }
    
    // Check 2: token should be 10 digits
    if (!preg_match('/^\d{10}$/', $reg->attendance_token ?? '')) {
        $isValid = false;
        $problems[] = "Invalid format (should be 10 digits): '{$reg->attendance_token}'";
    }
    
    // Check 3: token_plain should also be 10 digits
    if (!preg_match('/^\d{10}$/', $reg->token_plain ?? '')) {
        $isValid = false;
        $problems[] = "Invalid token_plain format: '{$reg->token_plain}'";
    }
    
    if ($isValid) {
        $validCount++;
    } else {
        $invalidCount++;
        $issues[] = [
            'registration_id' => $reg->id,
            'user' => $reg->user->email,
            'event' => $reg->event->title,
            'problems' => $problems
        ];
    }
}

// Display summary
echo "SUMMARY:\n";
echo "========\n";
echo "Total Registrations: {$totalCount}\n";
echo "✅ Valid Tokens: {$validCount}\n";
echo "❌ Invalid Tokens: {$invalidCount}\n\n";

if ($invalidCount > 0) {
    echo "ISSUES FOUND:\n";
    echo "=============\n";
    foreach ($issues as $issue) {
        echo "\nRegistration #{$issue['registration_id']}\n";
        echo "  User: {$issue['user']}\n";
        echo "  Event: {$issue['event']}\n";
        echo "  Problems:\n";
        foreach ($issue['problems'] as $problem) {
            echo "    - {$problem}\n";
        }
    }
    
    echo "\n\n⚠️  WARNING: Found {$invalidCount} registration(s) with token issues!\n";
    echo "Run 'php fix_token_mismatch.php' to fix these issues.\n";
} else {
    echo "✅ All tokens are valid and consistent!\n";
}

echo "\n=== VERIFICATION COMPLETE ===\n";
