<?php
/**
 * Smart Merge Zip Codes
 * 
 * 1. Parses philippine_provinces_and_cities.sql
 * 2. Updates tbladdress with matching names
 * 3. Handles "City" suffix variations
 */

// ==========================================
// MANUAL DATABASE CONFIGURATION
$host = '127.0.0.1';
$dbname = 'surelifenet12_surelifetest';
$username = 'surelifenet12_surelifetest';
$password = '@ZIyl#7w@Q7?~d&J';
// ==========================================

$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);

echo "--- STARTING SMART MERGE ---\n";

// 1. Parse SQL File
// But first, ensure validation of table structure
try {
    $pdo->exec("ALTER TABLE tbladdress ADD COLUMN zipcode VARCHAR(10) DEFAULT NULL AFTER citymun_code");
    echo "✓ Added 'zipcode' column to tbladdress\n";
} catch (PDOException $e) {
    // Column likely exists, continue
}

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
$missingCount = 0;

$stmtUpdate = $pdo->prepare("UPDATE tbladdress SET zipcode = ? WHERE address_type = 'citymun' AND description = ?");

foreach ($matches as $m) {
    $cityName = trim($m[1]);
    $zipcode = trim($m[2]);

    // Skip empty zips (like Manila in this specific file)
    if (empty($zipcode)) {
        continue;
    }

    // Try Direct Update
    $stmtUpdate->execute([$zipcode, $cityName]);

    if ($stmtUpdate->rowCount() > 0) {
        $updateCount++;
    } else {
        // Try Variation: "X City" -> "CITY OF X" or "X"
        // Most common mismatch: SQL file says "Batac City", DB says "CITY OF BATAC"
        if (stripos($cityName, ' City') !== false) {
            $baseName = trim(str_ireplace(' City', '', $cityName));

            // Try "CITY OF X"
            $variation1 = "CITY OF " . strtoupper($baseName);
            $stmtUpdate->execute([$zipcode, $variation1]);
            if ($stmtUpdate->rowCount() > 0) {
                $variationCount++;
                continue;
            }

            // Try Just "X" (if needed, though rare for cities)
            /*
            $stmtUpdate->execute([$zipcode, $baseName]);
            if ($stmtUpdate->rowCount() > 0) {
                $variationCount++;
                continue;
            }
            */
        }
        $missingCount++;
        // echo "No match for: $cityName\n"; // Uncomment for detailed debug
    }
}

echo "--- MERGE COMPLETE ---\n";
echo "Direct Matches Updated: $updateCount\n";
echo "Variation Matches Updated: $variationCount\n";
echo "Total Updated: " . ($updateCount + $variationCount) . "\n";
echo "Unmatched Source Cities: $missingCount (These are in the SQL file but not found in tbladdress)\n";
