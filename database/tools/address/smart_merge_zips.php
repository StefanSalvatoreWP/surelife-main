<?php
/**
 * Smart Merge Zip Codes
 * 
 * 1. Parses philippine_provinces_and_cities.sql
 * 2. Updates tbladdress with matching names
 * 3. Handles "City" suffix variations
 * 
 * Usage: php smart_merge_zips.php
 * 
 * Database config is read from Laravel .env file automatically.
 */

// ==========================================
// READ DATABASE CONFIG FROM .ENV FILE
$envPath = dirname(__DIR__, 2) . '/.env';
if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    preg_match('/DB_HOST=(.+)/', $envContent, $hostMatch);
    preg_match('/DB_DATABASE=(.+)/', $envContent, $dbMatch);
    preg_match('/DB_USERNAME=(.+)/', $envContent, $userMatch);
    preg_match('/DB_PASSWORD=(.+)/', $envContent, $passMatch);
    
    $host = trim($hostMatch[1] ?? 'localhost');
    $dbname = trim($dbMatch[1] ?? 'slc_db');
    $username = trim($userMatch[1] ?? 'root');
    $password = trim($passMatch[1] ?? '');
} else {
    // Fallback defaults
    $host = 'localhost';
    $dbname = 'slc_db';
    $username = 'root';
    $password = '';
}
// ==========================================

$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== SMART MERGE ZIP CODES (LOCAL) ===\n";
echo "Database: {$host} / {$dbname}\n\n";

// 1. Parse SQL File
$sqlFile = __DIR__ . '/philippine_provinces_and_cities.sql';
if (!file_exists($sqlFile)) {
    die("Error: SQL file not found at $sqlFile\n");
}

$content = file_get_contents($sqlFile);
// Regex to capture ('Name', ProvinceID, 'Zipcode')
// Matches: (1, 'Bangued', 1, '2800')
preg_match_all("/\(\d+, '([^']+)', \d+, '([^']*)'\)/", $content, $matches, PREG_SET_ORDER);

$updateCount = 0;
$variationCount = 0;
$skipCount = 0;
$missingCount = 0;

// Only update cities that don't have a zipcode yet (NULL or empty)
$stmtUpdate = $pdo->prepare("UPDATE tbladdress SET zipcode = ? WHERE address_type = 'citymun' AND description = ? AND (zipcode IS NULL OR zipcode = '')");

// Also prepare a statement to check if city already has zipcode
$stmtCheck = $pdo->prepare("SELECT zipcode FROM tbladdress WHERE address_type = 'citymun' AND description = ? LIMIT 1");

foreach ($matches as $m) {
    $cityName = trim($m[1]);
    $zipcode = trim($m[2]);

    // Skip empty zips (like Manila in this specific file)
    if (empty($zipcode)) {
        continue;
    }

    // Check if city already has a zipcode
    $stmtCheck->execute([$cityName]);
    $existing = $stmtCheck->fetch(PDO::FETCH_OBJ);
    
    if ($existing && !empty($existing->zipcode)) {
        // City already has a zipcode, skip it
        $skipCount++;
        continue;
    }

    // Try Direct Update (only if zipcode is NULL or empty)
    $stmtUpdate->execute([$zipcode, $cityName]);

    if ($stmtUpdate->rowCount() > 0) {
        $updateCount++;
    } else {
        // Try Variation: "X City" -> "CITY OF X" or "X"
        // Most common mismatch: SQL file says "Batac City", DB says "CITY OF BATAC"
        if (stripos($cityName, ' City') !== false) {
            $baseName = trim(str_ireplace(' City', '', $cityName));

            // Check if variation already has zipcode
            $variation1 = "CITY OF " . strtoupper($baseName);
            $stmtCheck->execute([$variation1]);
            $existingVar = $stmtCheck->fetch(PDO::FETCH_OBJ);
            
            if ($existingVar && !empty($existingVar->zipcode)) {
                // Variation already has zipcode, skip
                $skipCount++;
                continue;
            }

            $stmtUpdate->execute([$zipcode, $variation1]);
            if ($stmtUpdate->rowCount() > 0) {
                $variationCount++;
                continue;
            }
        }
        $missingCount++;
        // echo "No match for: $cityName\n"; // Uncomment for detailed debug
    }
}

echo "\n=== MERGE COMPLETE ===\n";
echo "Direct Matches Updated: $updateCount\n";
echo "Variation Matches Updated: $variationCount\n";
echo "Already Had Zipcode (Skipped): $skipCount\n";
echo "Total Updated: " . ($updateCount + $variationCount) . "\n";
echo "Unmatched Source Cities: $missingCount\n";

// Count remaining cities without ZIP
$stmt = $pdo->query("SELECT COUNT(*) as cnt FROM tbladdress WHERE address_type = 'citymun' AND (zipcode IS NULL OR zipcode = '')");
$remaining = $stmt->fetch(PDO::FETCH_OBJ)->cnt;
echo "\nCities still without ZIP: {$remaining}\n";
