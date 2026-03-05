<?php
/**
 * Propagate Zip Codes from Cities to Barangays
 * 
 * This script copies zip codes from city/municipality records to their
 * corresponding barangays based on citymun_code linkage.
 * 
 * Usage: php propagate_barangay_zips.php
 */

// Read database config from .env
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
    $host = 'localhost';
    $dbname = 'slc_db';
    $username = 'root';
    $password = '';
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== PROPAGATING ZIP CODES TO BARANGAYS ===\n\n";
    echo "Database: $host / $dbname\n\n";
    
    // Count before
    $beforeCount = $pdo->query("SELECT COUNT(*) FROM tbladdress WHERE address_type = 'barangay' AND zipcode IS NOT NULL AND zipcode != ''")->fetchColumn();
    echo "Barangays with zip BEFORE: $beforeCount\n";
    
    // Propagate zip codes from city to barangay
    $sql = "
        UPDATE tbladdress b
        INNER JOIN tbladdress c ON c.code = b.citymun_code AND c.address_type = 'citymun'
        SET b.zipcode = c.zipcode
        WHERE b.address_type = 'barangay'
        AND (b.zipcode IS NULL OR b.zipcode = '')
        AND c.zipcode IS NOT NULL 
        AND c.zipcode != ''
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    $rowsUpdated = $stmt->rowCount();
    echo "Rows updated: $rowsUpdated\n";
    
    // Count after
    $afterCount = $pdo->query("SELECT COUNT(*) FROM tbladdress WHERE address_type = 'barangay' AND zipcode IS NOT NULL AND zipcode != ''")->fetchColumn();
    echo "Barangays with zip AFTER: $afterCount\n";
    
    // Check remaining without zip
    $remaining = $pdo->query("SELECT COUNT(*) FROM tbladdress WHERE address_type = 'barangay' AND (zipcode IS NULL OR zipcode = '')")->fetchColumn();
    echo "Barangays still without zip: $remaining\n";
    
    if ($remaining > 0) {
        echo "\nNote: Some barangays don't have zip codes because their city/municipality also has no zip code.\n";
    }
    
    echo "\n=== PROPAGATION COMPLETE ===\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
