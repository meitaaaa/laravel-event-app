<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Http\Controllers\Api\CertificateController;
use Illuminate\Http\Request;

// Find user
$user = User::find(8); // Meitanti Fadilah

if (!$user) {
    echo "User not found!\n";
    exit;
}

echo "Testing Certificate API for: {$user->name}\n\n";

// Create mock request
$request = Request::create('/api/me/certificates', 'GET');
$request->setUserResolver(function() use ($user) {
    return $user;
});

// Call controller
$controller = new CertificateController();
$response = $controller->myCertificates($request);

// Get JSON content
$content = $response->getContent();
$data = json_decode($content, true);

echo "API Response:\n";
echo "Status: " . $response->getStatusCode() . "\n";
echo "Certificate Count: " . count($data) . "\n\n";

if (count($data) > 0) {
    foreach ($data as $cert) {
        echo "Certificate ID: {$cert['id']}\n";
        echo "Serial: {$cert['serial_number']}\n";
        echo "Event: {$cert['registration']['event']['title']}\n";
        echo "Download URL: {$cert['download_url']}\n";
        echo "---\n";
    }
} else {
    echo "No certificates found!\n";
}
