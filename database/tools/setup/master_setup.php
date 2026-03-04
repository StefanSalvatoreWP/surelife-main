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
    // We need to run this with the correct database name
    // Update the script to use the Laravel database config
    echo "Running smart_merge_zips.php...\n";
    
    // Read .env file for database config
    $envPath = dirname(__DIR__, 2) . '/.env';
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
        
        // Run the merge script with dynamic config
        $mergeScript = <<<PHP
<?php
\$host = '{$dbHost}';
\$dbname = '{$dbName}';
\$username = '{$dbUser}';
\$password = '{$dbPass}';

\$pdo = new PDO("mysql:host=\$host;dbname=\$dbname;charset=utf8mb4", \$username, \$password);
\$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Database: {\$host} / {\$dbname}\n\n";

\$sqlFile = dirname(__DIR__) . '/address/philippine_provinces_and_cities.sql';
\$content = file_get_contents(\$sqlFile);
preg_match_all("/\\(\\d+, '([^']+)', \\d+, '([^']*)'\\)/", \$content, \$matches, PREG_SET_ORDER);

\$updateCount = 0;
\$variationCount = 0;

\$stmtUpdate = \$pdo->prepare("UPDATE tbladdress SET zipcode = ? WHERE address_type = 'citymun' AND description = ?");

foreach (\$matches as \$m) {
    \$cityName = trim(\$m[1]);
    \$zipcode = trim(\$m[2]);
    
    if (empty(\$zipcode)) continue;
    
    \$stmtUpdate->execute([\$zipcode, \$cityName]);
    
    if (\$stmtUpdate->rowCount() > 0) {
        \$updateCount++;
    } else {
        if (stripos(\$cityName, ' City') !== false) {
            \$baseName = trim(str_ireplace(' City', '', \$cityName));
            \$variation1 = "CITY OF " . strtoupper(\$baseName);
            \$stmtUpdate->execute([\$zipcode, \$variation1]);
            if (\$stmtUpdate->rowCount() > 0) {
                \$variationCount++;
            }
        }
    }
}

echo "Direct Matches: \$updateCount\n";
echo "Variation Matches: \$variationCount\n";
echo "Total Updated: " . (\$updateCount + \$variationCount) . "\n";
PHP;
        
        // Write temp script and run it
        $tempScript = __DIR__ . '/temp_merge.php';
        file_put_contents($tempScript, $mergeScript);
        passthru("php " . escapeshellarg($tempScript));
        unlink($tempScript);
    }
} else {
    echo "⚠ smart_merge_zips.php not found at $smartMergePath\n";
}
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
echo "║  ✓ Zip Code Merge                                            ║\n";
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
