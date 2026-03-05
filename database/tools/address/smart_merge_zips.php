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
// Regex to capture (CityID, 'Name', ProvinceID, 'Zipcode')
// Matches: (1, 'Bangued', 1, '2800')
preg_match_all("/\(\d+, '([^']+)', (\d+), '([^']*)'\)/", $content, $matches, PREG_SET_ORDER);

$updateCount = 0;
$variationCount = 0;
$skipCount = 0;
$missingCount = 0;
$insertCount = 0;

// Update cities that don't have a zipcode yet (NULL or empty)
$stmtUpdate = $pdo->prepare("UPDATE tbladdress SET zipcode = ? WHERE address_type = 'citymun' AND description = ? AND (zipcode IS NULL OR zipcode = '')");

// Check if city exists
$stmtCheck = $pdo->prepare("SELECT id, zipcode FROM tbladdress WHERE address_type = 'citymun' AND description = ? LIMIT 1");

// Insert new city if not exists
// Note: tbladdress only has description, zipcode, address_type columns (no timestamps)
$stmtInsert = $pdo->prepare("INSERT INTO tbladdress (description, zipcode, address_type) VALUES (?, ?, 'citymun')");

// Get province ID mapping from SQL file (we need to map province IDs)
// First, get all provinces from database to map names
$provinces = [];
$stmtProvinces = $pdo->query("SELECT id, description FROM tbladdress WHERE address_type = 'province'");
while ($row = $stmtProvinces->fetch(PDO::FETCH_OBJ)) {
    $provinces[$row->id] = $row->description;
}

// Parse provinces from SQL file to build mapping: SQL_province_id -> province_name
// Province entries have format (id, 'name') - only 2 values, not 4 like cities
// Extract the provinces section first
$provinceSection = '';
if (preg_match("/INSERT INTO `provinces`.*?VALUES\s*(.*?);/s", $content, $sectionMatch)) {
    $provinceSection = $sectionMatch[1];
}
preg_match_all("/\((\d+),\s*'([^']+)'\)(?:,|;)/", $provinceSection, $provinceMatches, PREG_SET_ORDER);
$sqlProvinceMap = []; // SQL province ID -> province name
foreach ($provinceMatches as $pm) {
    $sqlProvinceId = (int)$pm[1];
    $provinceName = trim($pm[2]);
    $sqlProvinceMap[$sqlProvinceId] = $provinceName;
}

// Build reverse mapping: province name -> database province ID (case-insensitive)
$dbProvinceMap = []; // province name (uppercase) -> database province ID
foreach ($provinces as $dbId => $name) {
    $dbProvinceMap[strtoupper($name)] = $dbId;
}

// Build final mapping: SQL province ID -> database province ID
$provinceIdMap = []; // SQL province ID -> database province ID
foreach ($sqlProvinceMap as $sqlId => $provinceName) {
    $upperName = strtoupper($provinceName);
    if (isset($dbProvinceMap[$upperName])) {
        $provinceIdMap[$sqlId] = $dbProvinceMap[$upperName];
    }
}

// Manual mappings for provinces with different names in DB
$manualMappings = [
    'COTABATO' => 'COTABATO (NORTH COTABATO)',  // SQL: Cotabato -> DB: Cotabato (North Cotabato)
    'METRO MANILA' => 'CITY OF MANILA',          // SQL: Metro Manila -> DB: City of Manila
    'SAMAR' => 'SAMAR (WESTERN SAMAR)',          // SQL: Samar -> DB: Samar (Western Samar)
];
foreach ($sqlProvinceMap as $sqlId => $provinceName) {
    $upperName = strtoupper($provinceName);
    if (!isset($provinceIdMap[$sqlId]) && isset($manualMappings[$upperName])) {
        $mappedName = $manualMappings[$upperName];
        if (isset($dbProvinceMap[$mappedName])) {
            $provinceIdMap[$sqlId] = $dbProvinceMap[$mappedName];
        }
    }
}

echo "SQL Provinces found: " . count($sqlProvinceMap) . "\n";
echo "DB Provinces found: " . count($dbProvinceMap) . "\n";
echo "Mapped provinces: " . count($provinceIdMap) . "\n\n";

foreach ($matches as $m) {
    $cityName = trim($m[1]);
    $provinceId = (int)$m[2];
    $zipcode = trim($m[3]);

    // Skip empty zips
    if (empty($zipcode)) {
        continue;
    }

    // Check if city exists
    $stmtCheck->execute([$cityName]);
    $existing = $stmtCheck->fetch(PDO::FETCH_OBJ);
    
    if ($existing) {
        // City exists - check if it needs zipcode update
        if (!empty($existing->zipcode)) {
            // Already has zipcode, skip
            $skipCount++;
            continue;
        }
        
        // Update with zipcode
        $stmtUpdate->execute([$zipcode, $cityName]);
        if ($stmtUpdate->rowCount() > 0) {
            $updateCount++;
        }
        continue;
    }

    // City NOT found - try variation for "X City" -> "CITY OF X"
    if (stripos($cityName, ' City') !== false) {
        $baseName = trim(str_ireplace(' City', '', $cityName));
        $variation1 = "CITY OF " . strtoupper($baseName);
        
        $stmtCheck->execute([$variation1]);
        $existingVar = $stmtCheck->fetch(PDO::FETCH_OBJ);
        
        if ($existingVar) {
            if (!empty($existingVar->zipcode)) {
                $skipCount++;
                continue;
            }
            $stmtUpdate->execute([$zipcode, $variation1]);
            if ($stmtUpdate->rowCount() > 0) {
                $variationCount++;
                continue;
            }
        }
    }

    // City not found in database - INSERT new record
    try {
        $stmtInsert->execute([$cityName, $zipcode]);
        $insertCount++;
    } catch (PDOException $e) {
        // Insert failed
        $missingCount++;
    }
}

echo "\n=== MERGE COMPLETE ===\n";
echo "Direct Matches Updated: $updateCount\n";
echo "Variation Matches Updated: $variationCount\n";
echo "New Cities Inserted: $insertCount\n";
echo "Already Had Zipcode (Skipped): $skipCount\n";
echo "Total Updated: " . ($updateCount + $variationCount) . "\n";
echo "Total Inserted: $insertCount\n";
echo "Insert Failed (missing province): $missingCount\n";

// Count remaining cities without ZIP
$stmt = $pdo->query("SELECT COUNT(*) as cnt FROM tbladdress WHERE address_type = 'citymun' AND (zipcode IS NULL OR zipcode = '')");
$remaining = $stmt->fetch(PDO::FETCH_OBJ)->cnt;
echo "\nCities still without ZIP: {$remaining}\n";
