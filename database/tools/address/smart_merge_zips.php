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
$sqlByProvinceAndName = []; // NEW: Index by province_id + name

// Build province name to ID map
$provinceNameToId = [];
preg_match_all("/\(\d+, '([^']+)'/", $content, $provMatches, PREG_SET_ORDER);
foreach ($provMatches as $pm) {
    $provinceNameToId[strtoupper(trim($pm[1]))] = $pm[0];
}

foreach ($matches as $m) {
    $name = trim($m[1]);
    $provinceId = trim($m[2]);
    $zip = trim($m[3]);
    
    if (empty($zip)) continue;
    
    // Index 1: Exact name (for backward compatibility)
    if (!isset($sqlByExactName[$name])) {
        $sqlByExactName[$name] = $zip;
    }
    
    // Index 2: Uppercase
    if (!isset($sqlByUpperName[strtoupper($name)])) {
        $sqlByUpperName[strtoupper($name)] = $zip;
    }
    
    // Index 3: Clean name (remove City suffix)
    $cleanName = strtoupper($name);
    $cleanName = preg_replace('/\s+CITY$/i', '', $cleanName);
    $cleanName = trim($cleanName);
    if (!isset($sqlByCleanName[$cleanName])) {
        $sqlByCleanName[$cleanName] = $zip;
    }
    
    // Index 4: Province ID + Name (for disambiguation)
    $sqlByProvinceAndName[$provinceId . ':' . strtoupper($name)] = $zip;
}

echo "Cities in SQL file: " . count($sqlByExactName) . "\n\n";

$updateCount = 0;
$variationCount = 0;
$skipCount = 0;
$correctCount = 0;

// Get ALL cities from database, including province_code for disambiguation
// We need to fix ALL cities, not just those without zip codes
$dbCities = $pdo->query("
    SELECT code, description, province_code, zipcode 
    FROM tbladdress 
    WHERE address_type = 'citymun'
")->fetchAll(PDO::FETCH_OBJ);

echo "Total cities in DB: " . count($dbCities) . "\n\n";

$stmtUpdate = $pdo->prepare("UPDATE tbladdress SET zipcode = ? WHERE code = ? AND address_type = 'citymun'");

foreach ($dbCities as $dbCity) {
    $dbName = trim($dbCity->description);
    $dbProvinceCode = $dbCity->province_code;
    $currentZip = $dbCity->zipcode;
    $zip = null;
    $matchMethod = '';
    
    // CHECK 0: Province-aware match (highest priority for disambiguation)
    // Map province codes from DB (refCitymun codes) to SQL province IDs
    // IMPORTANT: tbladdress uses refCitymun codes (e.g., '0722' for Cebu), NOT PSGC codes
    $provinceIdMap = [
        '0128' => '34',  // ILOCOS NORTE
        '0129' => '35',  // ILOCOS SUR
        '0133' => '39',  // LA UNION
        '0155' => '60',  // PANGASINAN
        '0209' => '11',  // BATANES
        '0215' => '18',  // CAGAYAN
        '0231' => '37',  // ISABELA
        '0250' => '55',  // NUEVA VIZCAYA
        '0257' => '62',  // QUIRINO
        '0308' => '10',  // BATAAN
        '0314' => '17',  // BULACAN
        '0349' => '54',  // NUEVA ECIJA
        '0354' => '59',  // PAMPANGA
        '0369' => '75',  // TARLAC
        '0371' => '77',  // ZAMBALES
        '0377' => '8',   // AURORA
        '0410' => '12',  // BATANGAS
        '0421' => '24',  // CAVITE
        '0434' => '40',  // LAGUNA
        '0456' => '61',  // QUEZON
        '0458' => '63',  // RIZAL
        '1740' => '45',  // MARINDUQUE
        '1751' => '56',  // OCCIDENTAL MINDORO
        '1752' => '57',  // ORIENTAL MINDORO
        '1753' => '58',  // PALAWAN
        '1759' => '64',  // ROMBLON
        '0505' => '5',   // ALBAY
        '0516' => '19',  // CAMARINES NORTE
        '0517' => '20',  // CAMARINES SUR
        '0520' => '23',  // CATANDUANES
        '0541' => '46',  // MASBATE
        '0562' => '68',  // SORSOGON
        '0604' => '4',   // AKLAN
        '0606' => '6',   // ANTIQUE
        '0619' => '22',  // CAPIZ
        '0630' => '36',  // ILOILO
        '0645' => '51',  // NEGROS OCCIDENTAL
        '0679' => '32',  // GUIMARAS
        '0712' => '15',  // BOHOL
        '0722' => '25',  // CEBU
        '0746' => '52',  // NEGROS ORIENTAL
        '0761' => '67',  // SIQUIJOR
        '0826' => '31',  // EASTERN SAMAR
        '0837' => '43',  // LEYTE
        '0848' => '53',  // NORTHERN SAMAR
        '0860' => '65',  // SAMAR (WESTERN SAMAR)
        '0864' => '70',  // SOUTHERN LEYTE
        '0878' => '14',  // BILIRAN
        '0972' => '78',  // ZAMBOANGA DEL NORTE
        '0973' => '79',  // ZAMBOANGA DEL SUR
        '0983' => '80',  // ZAMBOANGA SIBUGAY
        '1013' => '16',  // BUKIDNON
        '1018' => '21',  // CAMIGUIN
        '1035' => '41',  // LANAO DEL NORTE
        '1042' => '48',  // MISAMIS OCCIDENTAL
        '1043' => '49',  // MISAMIS ORIENTAL
        '1123' => '28',  // DAVAO DEL NORTE
        '1124' => '29',  // DAVAO DEL SUR
        '1125' => '30',  // DAVAO ORIENTAL
        '1182' => '26',  // COMPOSTELA VALLEY
        '1186' => '29',  // DAVAO OCCIDENTAL
        '1247' => '27',  // COTABATO (NORTH COTABATO)
        '1263' => '69',  // SOUTH COTABATO
        '1265' => '71',  // SULTAN KUDARAT
        '1280' => '66',  // SARANGANI
        '1401' => '1',   // ABRA
        '1411' => '13',  // BENGUET
        '1427' => '33',  // IFUGAO
        '1432' => '38',  // KALINGA
        '1444' => '50',  // MOUNTAIN PROVINCE
        '1481' => '7',   // APAYAO
        '1507' => '9',   // BASILAN
        '1536' => '42',  // LANAO DEL SUR
        '1538' => '44',  // MAGUINDANAO
        '1566' => '72',  // SULU
        '1570' => '76',  // TAWI-TAWI
        '1602' => '2',   // AGUSAN DEL NORTE
        '1603' => '3',   // AGUSAN DEL SUR
        '1667' => '73',  // SURIGAO DEL NORTE
        '1668' => '74',  // SURIGAO DEL SUR
        '1685' => '73',  // DINAGAT ISLANDS
    ];
    
    $sqlProvinceId = $provinceIdMap[$dbProvinceCode] ?? null;
    
    // Try province-aware match first for cities with duplicate names
    if ($sqlProvinceId) {
        $provinceKey = $sqlProvinceId . ':' . strtoupper($dbName);
        if (isset($sqlByProvinceAndName[$provinceKey])) {
            $zip = $sqlByProvinceAndName[$provinceKey];
            $matchMethod = 'PROVINCE-AWARE';
        }
        // Also try with "CITY" suffix
        if (!$zip) {
            $provinceKeyWithCity = $sqlProvinceId . ':' . strtoupper($dbName) . ' CITY';
            if (isset($sqlByProvinceAndName[$provinceKeyWithCity])) {
                $zip = $sqlByProvinceAndName[$provinceKeyWithCity];
                $matchMethod = 'PROVINCE-AWARE';
            }
        }
        // Also try without "CITY" suffix
        if (!$zip) {
            $cleanDbName = preg_replace('/\s+CITY$/i', '', strtoupper($dbName));
            $provinceKeyClean = $sqlProvinceId . ':' . $cleanDbName;
            if (isset($sqlByProvinceAndName[$provinceKeyClean])) {
                $zip = $sqlByProvinceAndName[$provinceKeyClean];
                $matchMethod = 'PROVINCE-AWARE';
            }
        }
        // Also try removing "CITY OF" prefix and adding "CITY" suffix
        // DB: "CITY OF TALISAY" -> SQL: "TALISAY CITY"
        if (!$zip) {
            $cleanDbName = strtoupper($dbName);
            $cleanDbName = preg_replace('/^CITY OF\s+/i', '', $cleanDbName);
            $provinceKeyWithCity = $sqlProvinceId . ':' . $cleanDbName . ' CITY';
            if (isset($sqlByProvinceAndName[$provinceKeyWithCity])) {
                $zip = $sqlByProvinceAndName[$provinceKeyWithCity];
                $matchMethod = 'PROVINCE-AWARE';
            }
        }
        // Also try just the name without "CITY OF" prefix
        if (!$zip) {
            $cleanDbName = strtoupper($dbName);
            $cleanDbName = preg_replace('/^CITY OF\s+/i', '', $cleanDbName);
            $provinceKeyClean = $sqlProvinceId . ':' . $cleanDbName;
            if (isset($sqlByProvinceAndName[$provinceKeyClean])) {
                $zip = $sqlByProvinceAndName[$provinceKeyClean];
                $matchMethod = 'PROVINCE-AWARE';
            }
        }
    }
    
    // CHECK 1: Exact match (fallback)
    if (!$zip && isset($sqlByExactName[$dbName])) {
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
        // Only update if zip is different from current (or current is empty)
        if ($zip !== $currentZip) {
            $stmtUpdate->execute([$zip, $dbCity->code]);
            if ($stmtUpdate->rowCount() > 0) {
                if ($matchMethod === 'EXACT' || $matchMethod === 'PROVINCE-AWARE') {
                    $updateCount++;
                } else {
                    $variationCount++;
                }
                echo "[$matchMethod] [$dbCity->code] $dbName => $zip (was: $currentZip)\n";
            }
        } else {
            $correctCount++;
        }
    } else {
        $skipCount++;
    }
}

echo "\n=== MERGE COMPLETE ===\n";
echo "Province-Aware/Exact Matches Updated: $updateCount\n";
echo "Variation Matches Updated: $variationCount\n";
echo "Already Correct: $correctCount\n";
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
