<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Certificate;
use App\Models\User;

echo "=== FULL DOWNLOAD FLOW TEST ===\n\n";

// Find user
$user = User::find(8); // Meitanti Fadilah
if (!$user) {
    echo "User not found!\n";
    exit;
}

echo "User: {$user->name} (ID: {$user->id})\n";
echo "Email: {$user->email}\n\n";

// Find certificates for this user
$certificates = Certificate::with('registration.event')
    ->whereHas('registration', fn($q) => $q->where('user_id', $user->id))
    ->get();

echo "Total Certificates: " . $certificates->count() . "\n\n";

if ($certificates->count() === 0) {
    echo "No certificates found for this user!\n";
    exit;
}

foreach ($certificates as $cert) {
    echo "=== CERTIFICATE #{$cert->id} ===\n";
    echo "Serial: {$cert->serial_number}\n";
    echo "Event: {$cert->registration->event->title}\n";
    echo "File Path: {$cert->file_path}\n";
    
    // Check file exists
    $fullPath = storage_path('app/public/' . $cert->file_path);
    $fileExists = file_exists($fullPath);
    echo "File Exists: " . ($fileExists ? "YES" : "NO") . "\n";
    
    if ($fileExists) {
        echo "File Size: " . filesize($fullPath) . " bytes\n";
    }
    
    // Test URL
    $downloadUrl = url("/api/certificates/{$cert->id}/download");
    echo "Download URL: $downloadUrl\n";
    
    // Test route matching
    $request = \Illuminate\Http\Request::create("/api/certificates/{$cert->id}/download", 'GET');
    try {
        $router = app('router');
        $route = $router->getRoutes()->match($request);
        echo "Route Match: ✅ YES\n";
        echo "Controller: " . $route->getActionName() . "\n";
    } catch (\Exception $e) {
        echo "Route Match: ❌ NO - " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

// Test actual download
echo "=== TESTING ACTUAL DOWNLOAD ===\n";
$cert = $certificates->first();
echo "Testing certificate ID: {$cert->id}\n\n";

try {
    $controller = new \App\Http\Controllers\Api\CertificateController();
    $response = $controller->download($cert);
    
    echo "✅ Download controller executed successfully!\n";
    echo "Response Status: " . $response->getStatusCode() . "\n";
    echo "Response Type: " . get_class($response) . "\n";
    
    $headers = $response->headers->all();
    echo "\nResponse Headers:\n";
    foreach ($headers as $key => $value) {
        if (is_array($value)) {
            $value = implode(', ', $value);
        }
        echo "  $key: $value\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "\n=== TESTING CURL ===\n";
$curlUrl = "http://127.0.0.1:8000/api/certificates/{$cert->id}/download";
echo "Testing URL: $curlUrl\n";
echo "Run this in PowerShell:\n";
echo "Invoke-WebRequest -Uri \"$curlUrl\" -OutFile \"test_download.pdf\"\n";

echo "\n=== DONE ===\n";
