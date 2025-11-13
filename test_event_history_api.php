<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;

echo "\n=== TEST EVENT HISTORY API ===\n\n";

// Find user
$user = User::where('name', 'like', '%Meitanti Fadilah%')->first();

if (!$user) {
    echo "❌ User not found\n";
    exit;
}

echo "Testing for user: {$user->name} (ID: {$user->id})\n\n";

// Create mock request
$request = Request::create('/api/user/event-history', 'GET');
$request->setUserResolver(function () use ($user) {
    return $user;
});

// Call controller
$controller = new UserController();

try {
    $response = $controller->getEventHistory($request);
    $data = json_decode($response->getContent(), true);
    
    echo "✅ API Response Status: " . $response->getStatusCode() . "\n\n";
    
    if (isset($data['data']) && is_array($data['data'])) {
        echo "Total events: " . count($data['data']) . "\n\n";
        
        // Find Kompetisi Sains
        foreach ($data['data'] as $event) {
            if (strpos($event['event']['title'], 'Kompetisi Sains') !== false) {
                echo "Found Kompetisi Sains:\n";
                echo json_encode($event, JSON_PRETTY_PRINT);
                echo "\n\n";
                
                if (isset($event['certificate'])) {
                    echo "Certificate Details:\n";
                    echo "  ID: " . $event['certificate']['id'] . "\n";
                    echo "  Serial: " . $event['certificate']['serial_number'] . "\n";
                    echo "  File: " . $event['certificate']['file_path'] . "\n";
                    echo "  Download URL: " . $event['certificate']['download_url'] . "\n";
                }
                break;
            }
        }
    } else {
        echo "❌ No data in response\n";
        echo json_encode($data, JSON_PRETTY_PRINT);
    }
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n";
