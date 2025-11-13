<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$pdo = DB::connection()->getPdo();

// Update registration 14 with token from token_plain
$stmt = $pdo->prepare("UPDATE registrations SET attendance_token = token_plain WHERE id = 14");
$stmt->execute();

echo "✅ Updated registration 14\n";

// Verify
$stmt = $pdo->query("SELECT id, attendance_token, token_plain FROM registrations WHERE id = 14");
$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo "\nVerification:\n";
print_r($result);
