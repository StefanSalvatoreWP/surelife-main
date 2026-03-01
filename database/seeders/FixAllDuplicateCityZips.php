<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * Complete ZIP Code Fix for All Duplicate Cities
 * 
 * Parses the SQL file and updates all cities with correct ZIP codes
 * using proper province code mapping.
 */
class FixAllDuplicateCityZips extends Seeder
{
    // SQL province_id => PSGC province_code mapping
    private array $provinceMapping = [
        1 => '0100',    // Abra
        2 => '0101',    // Agusan del Norte
        3 => '0102',    // Agusan del Sur
        4 => '0103',    // Aklan
        5 => '0104',    // Albay
        6 => '0105',    // Antique
        7 => '0106',    // Apayao
        8 => '0107',    // Aurora
        9 => '1501',    // Basilan
        10 => '0308',   // Bataan
        11 => '0209',   // Batanes
        12 => '0410',   // Batangas
        13 => '1411',   // Benguet
        14 => '0786',   // Biliran
        15 => '0712',   // Bohol
        16 => '1013',   // Bukidnon
        17 => '0314',   // Bulacan
        18 => '0215',   // Cagayan
        19 => '0516',   // Camarines Norte
        20 => '0517',   // Camarines Sur
        21 => '1018',   // Camiguin
        22 => '0619',   // Capiz
        23 => '0520',   // Catanduanes
        24 => '0421',   // Cavite
        25 => '0722',   // Cebu
        26 => '1182',   // Compostela Valley
        27 => '1247',   // Cotabato
        28 => '1123',   // Davao del Norte
        29 => '1124',   // Davao del Sur
        30 => '1125',   // Davao Oriental
        31 => '0826',   // Eastern Samar
        32 => '0679',   // Guimaras
        33 => '1427',   // Ifugao
        34 => '0128',   // Ilocos Norte
        35 => '0129',   // Ilocos Sur
        36 => '0630',   // Iloilo
        37 => '0231',   // Isabela
        38 => '1432',   // Kalinga
        39 => '0133',   // La Union
        40 => '0349',   // Laguna
        41 => '1035',   // Lanao del Norte
        42 => '1536',   // Lanao del Sur
        43 => '0837',   // Leyte
        44 => '1538',   // Maguindanao
        45 => '1740',   // Marinduque
        46 => '1601',   // Masbate
        47 => '1351',   // Metro Manila (special handling)
        48 => '1042',   // Misamis Occidental
        49 => '1043',   // Misamis Oriental
        50 => '1751',   // Mountain Province
        51 => '0645',   // Negros Occidental
        52 => '0746',   // Negros Oriental
        53 => '1548',   // Northern Samar
        54 => '0349',   // Nueva Ecija (note: same as Laguna?)
        55 => '0250',   // Nueva Vizcaya
        56 => '1752',   // Occidental Mindoro
        57 => '1753',   // Oriental Mindoro
        58 => '1756',   // Palawan
        59 => '0354',   // Pampanga
        60 => '0155',   // Pangasinan
        61 => '1667',   // Quezon
        62 => '1668',   // Quirino
        63 => '0405',   // Rizal
        64 => '1759',   // Romblon
        65 => '0604',   // Samar
        66 => '1280',   // Sarangani
        67 => '0860',   // Siquijor
        68 => '1263',   // Sorsogon
        69 => '1264',   // South Cotabato
        70 => '0864',   // Southern Leyte
        71 => '1538',   // Sultan Kudarat
        72 => '1566',   // Sulu
        73 => '1667',   // Surigao del Norte
        74 => '1668',   // Surigao del Sur
        75 => '0369',   // Tarlac
        76 => '1570',   // Tawi-Tawi
        77 => '0371',   // Zambales
        78 => '0972',   // Zamboanga del Norte
        79 => '0973',   // Zamboanga del Sur
        80 => '0983',   // Zamboanga Sibugay
    ];

    private array $stats = [
        'parsed' => 0,
        'updated' => 0,
        'not_found' => 0,
        'empty_zip' => 0,
    ];

    public function run(): void
    {
        $this->command->info('=== Complete ZIP Code Fix for Duplicate Cities ===');
        
        $sqlFile = base_path('script/philippine_provinces_and_cities.sql');
        
        if (!file_exists($sqlFile)) {
            $this->command->error("SQL file not found: {$sqlFile}");
            return;
        }

        $content = file_get_contents($sqlFile);
        
        // Parse all city entries
        preg_match_all("/\((\d+),\s*'([^']+)',\s*(\d+),\s*'([^']*)'\)/", $content, $matches, PREG_SET_ORDER);

        $this->command->info("Found " . count($matches) . " entries in SQL file");

        foreach ($matches as $match) {
            $this->processCity($match);
        }

        $this->showStats();
        $this->showVerification();
    }

    private function processCity(array $match): void
    {
        $cityId = $match[1];
        $cityName = $match[2];
        $sqlProvinceId = (int)$match[3];
        $zipcode = $match[4];

        // Skip empty ZIP codes
        if (empty($zipcode)) {
            $this->stats['empty_zip']++;
            return;
        }

        $this->stats['parsed']++;

        // Map SQL province_id to PSGC province_code
        $psgcCode = $this->provinceMapping[$sqlProvinceId] ?? null;
        
        if (!$psgcCode) {
            $this->command->warn("No mapping for province ID: {$sqlProvinceId}");
            return;
        }

        // Find and update the city in database
        $updated = $this->updateCityZip($cityName, $psgcCode, $zipcode);
        
        if ($updated) {
            $this->stats['updated']++;
        } else {
            $this->stats['not_found']++;
        }
    }

    private function updateCityZip(string $cityName, string $provinceCode, string $zipcode): bool
    {
        // Try exact match first
        $result = DB::table('tbladdress')
            ->where('address_type', 'citymun')
            ->where('province_code', $provinceCode)
            ->where(function ($query) use ($cityName) {
                $query->where('description', $cityName)
                      ->orWhere('description', 'CITY OF ' . $cityName)
                      ->orWhere('description', str_replace(' City', '', $cityName))
                      ->orWhere('description', str_replace(' City', '', $cityName) . ' City');
            })
            ->update(['zipcode' => $zipcode]);

        if ($result > 0) {
            return true;
        }

        // Try fuzzy match with LIKE
        $result = DB::table('tbladdress')
            ->where('address_type', 'citymun')
            ->where('province_code', $provinceCode)
            ->where('description', 'LIKE', '%' . str_replace([' City', 'CITY OF '], '', $cityName) . '%')
            ->update(['zipcode' => $zipcode]);

        return $result > 0;
    }

    private function showStats(): void
    {
        $this->command->newLine();
        $this->command->info('=== Statistics ===');
        $this->command->info("Parsed from SQL: {$this->stats['parsed']}");
        $this->command->info("Updated: {$this->stats['updated']}");
        $this->command->info("Not found: {$this->stats['not_found']}");
        $this->command->info("Empty ZIP in SQL: {$this->stats['empty_zip']}");
    }

    private function showVerification(): void
    {
        $this->command->newLine();
        $this->command->info('=== Verification: TALISAY Cities ===');
        
        $talisays = DB::table('tbladdress')
            ->where('address_type', 'citymun')
            ->where(function ($query) {
                $query->where('description', 'LIKE', '%TALISAY%')
                      ->orWhere('description', 'LIKE', '%Talisay%');
            })
            ->get(['description', 'province_code', 'zipcode']);

        foreach ($talisays as $city) {
            $this->command->info("{$city->description} ({$city->province_code}): {$city->zipcode}");
        }
    }
}
