<?php
/**
 * Smart Merge Zip Codes
 * 
 * 1. Parses philippine_provinces_and_cities.sql
 * 2. Updates tbladdress with matching names (triple-check matching)
 * 3. Handles multiple name variations:
 *    - Exact match
 *    - Case-insensitive match
 *    - "X City" <-> "CITY OF X" variations
 *    - Suffix removal: (Capital), (old_name), etc.
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

echo "=== SMART MERGE ZIP CODES ===\n";
echo "Database: {$host} / {$dbname}\n\n";

// 1. Parse SQL File
$sqlFile = __DIR__ . '/philippine_provinces_and_cities.sql';
if (!file_exists($sqlFile)) {
    die("Error: SQL file not found at $sqlFile\n");
}

$content = file_get_contents($sqlFile);
// Regex to capture (CityID, 'Name', ProvinceID, 'Zipcode')
preg_match_all("/\(\d+, '([^']+)', (\d+), '([^']*)'\)/", $content, $matches, PREG_SET_ORDER);

// Build multiple lookup indexes for triple-check matching
$sqlByExactName = [];
$sqlByUpperName = [];
$sqlByCleanName = [];

foreach ($matches as $m) {
    $name = trim($m[1]);
    $zip = trim($m[3]);
    
    if (empty($zip)) continue;
    
    // Index 1: Exact name
    $sqlByExactName[$name] = $zip;
    
    // Index 2: Uppercase
    $sqlByUpperName[strtoupper($name)] = $zip;
    
    // Index 3: Clean name (remove City suffix)
    $cleanName = strtoupper($name);
    $cleanName = preg_replace('/\s+CITY$/i', '', $cleanName);
    $cleanName = trim($cleanName);
    $sqlByCleanName[$cleanName] = $zip;
}

echo "Cities in SQL file: " . count($sqlByExactName) . "\n\n";

$updateCount = 0;
$variationCount = 0;
$skipCount = 0;

// Get all cities from database without zip
$dbCities = $pdo->query("
    SELECT code, description 
    FROM tbladdress 
    WHERE address_type = 'citymun' 
    AND (zipcode IS NULL OR zipcode = '')
")->fetchAll(PDO::FETCH_OBJ);

echo "Cities in DB without zip: " . count($dbCities) . "\n\n";

$stmtUpdate = $pdo->prepare("UPDATE tbladdress SET zipcode = ? WHERE code = ? AND address_type = 'citymun'");

foreach ($dbCities as $dbCity) {
    $dbName = trim($dbCity->description);
    $zip = null;
    $matchMethod = '';
    
    // CHECK 1: Exact match
    if (isset($sqlByExactName[$dbName])) {
        $zip = $sqlByExactName[$dbName];
        $matchMethod = 'EXACT';
    }
    
    // CHECK 2: Case-insensitive match
    if (!$zip && isset($sqlByUpperName[strtoupper($dbName)])) {
        $zip = $sqlByUpperName[strtoupper($dbName)];
        $matchMethod = 'UPPERCASE';
    }
    
    // CHECK 3: Clean name match (remove suffixes)
    if (!$zip) {
        $cleanDbName = strtoupper($dbName);
        $cleanDbName = preg_replace('/\s+/', ' ', $cleanDbName);
        $cleanDbName = preg_replace('/\s*\(CAPITAL\)/i', '', $cleanDbName);
        $cleanDbName = preg_replace('/\s*\(.*?\)/', '', $cleanDbName);
        $cleanDbName = preg_replace('/^CITY OF\s+/i', '', $cleanDbName);
        $cleanDbName = preg_replace('/\s+CITY$/i', '', $cleanDbName);
        $cleanDbName = trim($cleanDbName);
        
        if (isset($sqlByCleanName[$cleanDbName])) {
            $zip = $sqlByCleanName[$cleanDbName];
            $matchMethod = 'CLEAN';
        }
    }
    
    // CHECK 3b: Try adding "City" suffix
    if (!$zip) {
        $withCity = strtoupper($dbName);
        $withCity = preg_replace('/\s*\(.*?\)/', '', $withCity);
        $withCity = trim($withCity);
        if (isset($sqlByUpperName[$withCity . ' CITY'])) {
            $zip = $sqlByUpperName[$withCity . ' CITY'];
            $matchMethod = 'WITH CITY';
        }
    }
    
    if ($zip) {
        $stmtUpdate->execute([$zip, $dbCity->code]);
        if ($stmtUpdate->rowCount() > 0) {
            if ($matchMethod === 'EXACT') {
                $updateCount++;
            } else {
                $variationCount++;
            }
            echo "[$matchMethod] [$dbCity->code] $dbName => $zip\n";
        }
    } else {
        $skipCount++;
    }
}

echo "\n=== MERGE COMPLETE ===\n";
echo "Exact Matches: $updateCount\n";
echo "Variation Matches: $variationCount\n";
echo "Not Found in SQL: $skipCount\n";
echo "Total Updated: " . ($updateCount + $variationCount) . "\n";

// Manual zip codes for cities not in SQL or with encoding issues
echo "\n=== ADDING MANUAL ZIP CODES ===\n";
$manualZips = [
    // Cities in SQL but with encoding issues (ñ)
    '042106' => '4114',  // CITY OF DASMARIÑAS -> Dasmariñas City
    '043403' => '4024',  // CITY OF BIÑAN -> Biñan City
    '137601' => '1740',  // CITY OF LAS PIÑAS -> Las Piñas City
    '137604' => '1700',  // CITY OF PARAÑAQUE -> Parañaque City
    
    // Cities with special characters
    '021519' => '3502',  // PEÑABLANCA
    '034921' => '3118',  // PEÑARANDA
    '140117' => '2804',  // PEÑARRUBIA
    '175324' => '5305',  // SOFRONIO ESPAÑOLA
    
    // Cities not in SQL file
    '015530' => '2425',  // POZORRUBIO
    '021526' => '3526',  // SANTO NIÑO (FAIRE)
    '031424' => '3009',  // DOÑA REMEDIOS TRINIDAD
    '034917' => '3115',  // SCIENCE CITY OF MUÑOZ
    '034931' => '3117',  // TALUGTUG
    '034932' => '3111',  // ZARAGOZA
    '041018' => '4226',  // MATAASNAKAHOY
    '042123' => '4117',  // GEN. MARIANO ALVAREZ
    '043411' => '4031',  // LOS BAÑOS
    '045807' => '1990',  // JALA-JALA
    '051731' => '4415',  // SAGÑAY
    '063017' => '5030',  // DUEÑAS
    '064532' => '6133',  // SALVADOR BENEDICTO
    '071226' => '6334',  // JETAFE
    '071235' => '6346',  // PRES. CARLOS P. GARCIA
    '072220' => '6017',  // CORDOVA
    '086018' => '6709',  // SANTO NIÑO (Samar)
    '097209' => '7112',  // PIÑAN
    '097211' => '7103',  // PRES. MANUEL A. ROXAS
    '097214' => '7106',  // SERGIO OSMEÑA SR.
    '104210' => '7200',  // OZAMIS CITY
    '126318' => '9509',  // SANTO NIÑO (South Cotabato)
    '126319' => '9712',  // LAKE SEBU
    '126512' => '9808',  // SEN. NINOY AQUINO
    '153819' => '9610',  // GEN. S. K. PENDATUN
    '153822' => '9613',  // PAGAGAWAN
    '156615' => '7417',  // TONGKIL
    
    // Manila districts (NCR)
    '133901' => '1000',  // TONDO I / II
    '133902' => '1006',  // BINONDO
    '133903' => '1001',  // QUIAPO
    '133908' => '1000',  // ERMITA
    '133909' => '1002',  // INTRAMUROS
    '133910' => '1004',  // MALATE
    '133911' => '1006',  // PACO
    '133912' => '1006',  // PANDACAN
    '133913' => '1003',  // PORT AREA
];

$stmtManual = $pdo->prepare("UPDATE tbladdress SET zipcode = ? WHERE code = ? AND address_type = 'citymun' AND (zipcode IS NULL OR zipcode = '')");
$manualCount = 0;
foreach ($manualZips as $code => $zip) {
    $stmtManual->execute([$zip, $code]);
    if ($stmtManual->rowCount() > 0) {
        $manualCount++;
        echo "Manual: [$code] => $zip\n";
    }
}
echo "Manual Updates: $manualCount\n";

// Count remaining cities without ZIP
$stmt = $pdo->query("SELECT COUNT(*) as cnt FROM tbladdress WHERE address_type = 'citymun' AND (zipcode IS NULL OR zipcode = '')");
$remaining = $stmt->fetch(PDO::FETCH_OBJ)->cnt;
echo "\nCities still without ZIP: {$remaining}\n";
