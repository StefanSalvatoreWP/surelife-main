<?php
/**
 * MASTER SETUP SCRIPT
 * 
 * Runs all migrations, seeders, and utility scripts in correct order.
 * Use this after importing the original database to set up all required data.
 * 
 * Usage: php master_setup.php
 * 
 * Order of operations:
 * 1. Run migrations (creates missing tables/columns)
 * 2. Run AddressSeeder (Philippine regions/provinces/cities/barangays)
 * 3. Run ReferenceTablesSeeder (reference data)
 * 4. Run LoanMenuSeeder (Loans menu entry)
 * 5. Run smart_merge_zips.php (merge zip codes)
 * 
 * Note: TestClientSeeder is NOT included here. It's a testing tool.
 *       Run it separately if needed: php artisan db:seed --class=TestClientSeeder
 */

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║           SURELIFE - MASTER SETUP SCRIPT                     ║\n";
echo "║           Database Setup & Seeder Runner                     ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

$startTime = microtime(true);

// Step 1: Run Migrations
echo "┌──────────────────────────────────────────────────────────────┐\n";
echo "│ STEP 1: Running Migrations                                   │\n";
echo "└──────────────────────────────────────────────────────────────┘\n";
runCommand("php artisan migrate --force");
echo "\n";

// Step 2: Run AddressSeeder
echo "┌──────────────────────────────────────────────────────────────┐\n";
echo "│ STEP 2: Seeding Address Data (Regions/Provinces/Cities)      │\n";
echo "└──────────────────────────────────────────────────────────────┘\n";
runCommand("php artisan db:seed --class=AddressSeeder --force");
echo "\n";

// Step 3: Run ReferenceTablesSeeder
echo "┌──────────────────────────────────────────────────────────────┐\n";
echo "│ STEP 3: Seeding Reference Tables                             │\n";
echo "└──────────────────────────────────────────────────────────────┘\n";
runCommand("php artisan db:seed --class=ReferenceTablesSeeder --force");
echo "\n";

// Step 4: Run LoanMenuSeeder
echo "┌──────────────────────────────────────────────────────────────┐\n";
echo "│ STEP 4: Seeding Loan Menu Entry                              │\n";
echo "└──────────────────────────────────────────────────────────────┘\n";
runCommand("php artisan db:seed --class=LoanMenuSeeder --force");
echo "\n";

// Step 5: Run smart_merge_zips.php
echo "┌──────────────────────────────────────────────────────────────┐\n";
echo "│ STEP 5: Merging Zip Codes                                    │\n";
echo "└──────────────────────────────────────────────────────────────┘\n";
$smartMergePath = __DIR__ . '/../address/smart_merge_zips.php';
if (file_exists($smartMergePath)) {
    echo "Running smart_merge_zips.php...\n";
    passthru("php " . escapeshellarg($smartMergePath));
} else {
    echo "⚠ smart_merge_zips.php not found at $smartMergePath\n";
}
echo "\n";

// Step 5.5: Fix Orphaned Barangays (wrong citymun_code references)
echo "┌──────────────────────────────────────────────────────────────┐\n";
echo "│ STEP 5.5: Fixing Orphaned Barangays                          │\n";
echo "└──────────────────────────────────────────────────────────────┘\n";
echo "Fixing barangays with wrong citymun_code references...\n";

// Read .env for database config (path is 3 levels up from this script)
$envPath = dirname(__DIR__, 3) . '/.env';
if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    preg_match('/DB_HOST=(.+)/', $envContent, $hostMatch);
    preg_match('/DB_DATABASE=(.+)/', $envContent, $dbMatch);
    preg_match('/DB_USERNAME=(.+)/', $envContent, $userMatch);
    preg_match('/DB_PASSWORD=(.+)/', $envContent, $passMatch);
    
    $dbHost = trim($hostMatch[1] ?? 'localhost');
    $dbName = trim($dbMatch[1] ?? 'slc_db');
    $dbUser = trim($userMatch[1] ?? 'root');
    $dbPass = trim($passMatch[1] ?? '');
    
    try {
        $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Fix orphaned barangays
        $fixes = [
            ['124710', '124702', 'CARMEN (Cotabato)'],
            ['126316', '126319', 'LAKE SEBU (South Cotabato)'],
            ['175306', '175303', 'BROOKE\'S POINT (Palawan)'],
        ];
        
        $totalFixed = 0;
        foreach ($fixes as $fix) {
            $wrongCode = $fix[0];
            $correctCode = $fix[1];
            $cityName = $fix[2];
            
            $stmt = $pdo->prepare("UPDATE tbladdress SET citymun_code = ? WHERE address_type = 'barangay' AND citymun_code = ?");
            $stmt->execute([$correctCode, $wrongCode]);
            $fixed = $stmt->rowCount();
            if ($fixed > 0) {
                $totalFixed += $fixed;
                echo "Fixed $fixed barangays: $wrongCode -> $correctCode ($cityName)\n";
            }
        }
        
        if ($totalFixed == 0) {
            echo "No orphaned barangays found.\n";
        }
        
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
echo "\n";

// Step 6: Propagate Zip Codes to Barangays
echo "┌──────────────────────────────────────────────────────────────┐\n";
echo "│ STEP 6: Propagating Zip Codes to Barangays                   │\n";
echo "└──────────────────────────────────────────────────────────────┘\n";
$propagatePath = __DIR__ . '/../address/propagate_barangay_zips.php';
if (file_exists($propagatePath)) {
    echo "Running propagate_barangay_zips.php...\n";
    passthru("php " . escapeshellarg($propagatePath));
} else {
    echo "⚠ propagate_barangay_zips.php not found at $propagatePath\n";
}
echo "\n";

// Step 7: Build Frontend Assets
echo "┌──────────────────────────────────────────────────────────────┐\n";
echo "│ STEP 7: Building Frontend Assets (npm run build)            │\n";
echo "└──────────────────────────────────────────────────────────────┘\n";
runCommand("npm run build");
echo "\n";

// Step 8: Clear Laravel Cache
echo "┌──────────────────────────────────────────────────────────────┐\n";
echo "│ STEP 8: Clearing Laravel Cache                               │\n";
echo "└──────────────────────────────────────────────────────────────┘\n";
runCommand("php artisan cache:clear");
runCommand("php artisan config:clear");
runCommand("php artisan view:clear");
runCommand("php artisan route:clear");
echo "\n";

// Summary
$endTime = microtime(true);
$duration = round($endTime - $startTime, 2);

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                    SETUP COMPLETE                            ║\n";
echo "╠══════════════════════════════════════════════════════════════╣\n";
echo "║  Duration: {$duration} seconds                                       ║\n";
echo "║                                                              ║\n";
echo "║  Completed steps:                                            ║\n";
echo "║  ✓ Migrations                                                ║\n";
echo "║  ✓ AddressSeeder                                             ║\n";
echo "║  ✓ ReferenceTablesSeeder                                     ║\n";
echo "║  ✓ LoanMenuSeeder                                            ║\n";
echo "║  ✓ Zip Code Merge (Cities)                                   ║\n";
echo "║  ✓ Zip Code Propagation (Barangays)                          ║\n";
echo "║  ✓ Frontend Build (npm run build)                            ║\n";
echo "║  ✓ Laravel Cache Cleared                                     ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

/**
 * Run a shell command and display output
 */
function runCommand($cmd) {
    passthru($cmd, $returnCode);
    if ($returnCode !== 0) {
        echo "⚠ Command returned non-zero exit code: $returnCode\n";
    }
    return $returnCode;
}
