<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$pdo = DB::connection()->getPdo();

// Modify column size
$stmt = $pdo->prepare("ALTER TABLE registrations MODIFY COLUMN attendance_token VARCHAR(20) NULL");
$stmt->execute();

echo "✅ Column size updated to VARCHAR(20)\n";

// Now copy tokens
$stmt = $pdo->prepare("UPDATE registrations SET attendance_token = token_plain WHERE attendance_token IS NULL OR attendance_token = ''");
$stmt->execute();

echo "✅ Tokens copied from token_plain\n";

// Verify
$stmt = $pdo->query("SELECT id, attendance_token, token_plain FROM registrations WHERE event_id = 52");
$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo "\nVerification for Event 52:\n";
print_r($result);
