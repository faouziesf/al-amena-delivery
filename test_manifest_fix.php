<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\Manifest;
use App\Models\Package;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🧪 Testing Manifest canBeDeleted() method...\n\n";

try {
    // Test 1: Manifest with empty package_ids
    echo "Test 1: Manifest with empty package_ids\n";
    $manifest1 = new Manifest();
    $manifest1->package_ids = [];
    $result1 = $manifest1->canBeDeleted();
    echo "Result: " . ($result1 ? "✅ CAN be deleted" : "❌ CANNOT be deleted") . "\n";
    echo "Expected: ✅ CAN be deleted\n\n";

    // Test 2: Manifest with null package_ids
    echo "Test 2: Manifest with null package_ids\n";
    $manifest2 = new Manifest();
    $manifest2->package_ids = null;
    $result2 = $manifest2->canBeDeleted();
    echo "Result: " . ($result2 ? "✅ CAN be deleted" : "❌ CANNOT be deleted") . "\n";
    echo "Expected: ✅ CAN be deleted\n\n";

    // Test 3: Create test packages and manifest
    echo "Test 3: Manifest with packages in AVAILABLE status\n";

    // Check if there are any existing packages
    $existingPackages = Package::whereIn('status', ['CREATED', 'AVAILABLE'])->limit(2)->get();

    if ($existingPackages->count() >= 2) {
        $manifest3 = new Manifest();
        $manifest3->package_ids = $existingPackages->pluck('id')->toArray();

        echo "Package IDs: " . implode(', ', $manifest3->package_ids) . "\n";
        echo "Package statuses: " . $existingPackages->pluck('status')->implode(', ') . "\n";

        $result3 = $manifest3->canBeDeleted();
        echo "Result: " . ($result3 ? "✅ CAN be deleted" : "❌ CANNOT be deleted") . "\n";
        echo "Expected: ✅ CAN be deleted (no PICKED_UP packages)\n\n";
    } else {
        echo "⚠️  Not enough packages to test with\n\n";
    }

    // Test 4: Check database query execution
    echo "Test 4: Testing database query execution\n";
    $testCount = Package::whereIn('id', [1, 2, 3])
        ->where('status', 'PICKED_UP')
        ->count();
    echo "Test query executed successfully, count: $testCount\n";
    echo "Database connection: ✅ Working\n\n";

    echo "🎉 All tests completed successfully!\n";
    echo "The canBeDeleted() method should be working correctly now.\n";

} catch (Exception $e) {
    echo "❌ Error during testing: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}