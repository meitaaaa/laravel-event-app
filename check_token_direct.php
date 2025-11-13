<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$pdo = DB::connection()->getPdo();

$stmt = $pdo->query("SELECT id, user_id, event_id, attendance_token, token_plain FROM registrations WHERE id = 14");
$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Direct DB Query for Registration ID 14:\n";
print_r($result);
