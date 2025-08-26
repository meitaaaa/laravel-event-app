<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Gate;
use App\Models\User;

echo "=== Detailed Gate Testing ===\n";

// Check if AuthServiceProvider is loaded
echo "1. Checking providers...\n";
$providers = $app->getLoadedProviders();
foreach ($providers as $provider => $loaded) {
    if (strpos($provider, 'Auth') !== false) {
        echo "   Provider loaded: $provider\n";
    }
}

// Check if Gate is accessible
echo "\n2. Checking Gate facade...\n";
echo "   Gate has admin: " . (Gate::has('admin') ? 'yes' : 'no') . "\n";

// Try to get Gate instance directly
try {
    $gate = Gate::getFacadeRoot();
    echo "   Gate facade root class: " . get_class($gate) . "\n";
    
    // Check if we can access Gate methods
    $reflection = new ReflectionClass($gate);
    $methods = $reflection->getMethods();
    echo "   Gate methods: ";
    foreach ($methods as $method) {
        if (strpos($method->getName(), 'allows') !== false || strpos($method->getName(), 'check') !== false) {
            echo $method->getName() . " ";
        }
    }
    echo "\n";
    
} catch (Exception $e) {
    echo "   Error accessing Gate: " . $e->getMessage() . "\n";
}

// Test User model
echo "\n3. Testing User model...\n";
$user = new User(['role' => 'admin']);
echo "   User class: " . get_class($user) . "\n";
echo "   User role: " . $user->role . "\n";
echo "   Role === 'admin': " . ($user->role === 'admin' ? 'true' : 'false') . "\n";

// Test Gate with reflection
echo "\n4. Testing Gate with reflection...\n";
try {
    $gate = Gate::getFacadeRoot();
    $reflection = new ReflectionClass($gate);
    
    if ($reflection->hasMethod('allows')) {
        $method = $reflection->getMethod('allows');
        $result = $method->invoke($gate, 'admin', $user);
        echo "   Reflection Gate allows admin: " . ($result ? 'true' : 'false') . "\n";
    } else {
        echo "   Gate doesn't have 'allows' method\n";
    }
    
} catch (Exception $e) {
    echo "   Error with reflection: " . $e->getMessage() . "\n";
}

// Test Gate normally
echo "\n5. Testing Gate normally...\n";
echo "   Gate allows admin: " . (Gate::allows('admin', $user) ? 'true' : 'false') . "\n";

// Test with different user
$user2 = new User(['role' => 'participant']);
echo "   User2 role: " . $user2->role . "\n";
echo "   Gate allows admin for user2: " . (Gate::allows('admin', $user2) ? 'true' : 'false') . "\n";

echo "\n=== End Testing ===\n";
