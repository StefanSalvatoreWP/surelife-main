<?php
/**
 * Fix Orphaned Barangays
 * 
 * Fixes barangays with wrong citymun_code references.
 * 
 * Usage: php fix_orphaned_barangays.php
 */

// Read database config from .env
$envPath = dirname(__DIR__, 3) . '/.env';
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
    $host = 'localhost';
    $dbname = 'slc_db';
    $username = 'root';
    $password = '';
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== FIXING ORPHANED BARANGAYS ===\n\n";
    echo "Database: $host / $dbname\n\n";
    
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
    } else {
        echo "\nTotal fixed: $totalFixed barangays\n";
    }
    
    echo "\n=== COMPLETE ===\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
