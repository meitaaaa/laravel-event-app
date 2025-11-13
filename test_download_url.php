<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Certificate;

echo "=== TESTING DOWNLOAD URL ===\n\n";

$cert = Certificate::find(3);

if (!$cert) {
    echo "Certificate not found!\n";
    exit;
}

echo "Certificate ID: {$cert->id}\n";
echo "Serial: {$cert->serial_number}\n";
echo "File Path: {$cert->file_path}\n\n";

// Test different URL formats
$urls = [
    "Correct URL" => url("/api/certificates/{$cert->id}/download"),
    "Wrong URL" => url("/api/certificates/download/{$cert->id}"),
];

foreach ($urls as $label => $url) {
    echo "$label: $url\n";
}

echo "\n=== TESTING ROUTE ===\n";

// Simulate request
$request = \Illuminate\Http\Request::create("/api/certificates/{$cert->id}/download", 'GET');

try {
    $router = app('router');
    $route = $router->getRoutes()->match($request);
    echo "✅ Route found: " . $route->getName() . "\n";
    echo "Controller: " . $route->getActionName() . "\n";
} catch (\Exception $e) {
    echo "❌ Route not found: " . $e->getMessage() . "\n";
}

echo "\n=== DONE ===\n";
