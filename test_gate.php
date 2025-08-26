<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Gate;
use App\Models\User;

// Test Gate admin
echo "Testing Gate admin...\n";

// Check if AuthServiceProvider is loaded
echo "Checking providers...\n";
$providers = $app->getLoadedProviders();
foreach ($providers as $provider => $loaded) {
    if (strpos($provider, 'Auth') !== false) {
        echo "Provider loaded: $provider\n";
    }
}

$user = new User(['role' => 'admin']);
echo "User role: " . $user->role . "\n";
echo "Role === 'admin': " . ($user->role === 'admin' ? 'true' : 'false') . "\n";

echo "Gate has admin: " . (Gate::has('admin') ? 'yes' : 'no') . "\n";

// Try to get the Gate definition
try {
    $gate = Gate::getFacadeRoot();
    echo "Gate facade root: " . get_class($gate) . "\n";
    
    // Check if we can access the Gate directly
    $user = new User(['role' => 'admin']);
    echo "Direct Gate check: " . ($gate->allows('admin', $user) ? 'true' : 'false') . "\n";
    
} catch (Exception $e) {
    echo "Error accessing Gate: " . $e->getMessage() . "\n";
}

echo "Gate allows admin: " . (Gate::allows('admin', $user) ? 'true' : 'false') . "\n";

// Test with different user
$user2 = new User(['role' => 'participant']);
echo "User2 role: " . $user2->role . "\n";
echo "Gate allows admin for user2: " . (Gate::allows('admin', $user2) ? 'true' : 'false') . "\n";
