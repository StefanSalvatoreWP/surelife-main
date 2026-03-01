<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Populates tbladdress with complete Philippine address data
     */
    public function run()
    {
        $this->command->info('Starting Philippine Address Data Import...');

        // Clear existing data (keep table structure)
        DB::table('tbladdress')->truncate();
        $this->command->info('Cleared existing tbladdress data');

        // Insert Regions
        $this->insertRegions();

        // Insert Provinces from refProvince.sql
        $this->insertProvinces();

        // Insert Cities/Municipalities from refCitymun.sql
        $this->insertCities();

        // Insert Barangays from refBrgy.sql
        $this->insertBarangays();

        // Populate Zipcodes
        $this->populateZipcodes();

        // Show summary
        $this->showSummary();
    }

    private function insertRegions()
    {
        $this->command->info('Inserting Regions...');

        $regions = [
            ['01', '010000000', 'REGION I (ILOCOS REGION)'],
            ['02', '020000000', 'REGION II (CAGAYAN VALLEY)'],
            ['03', '030000000', 'REGION III (CENTRAL LUZON)'],
            ['04', '040000000', 'REGION IV-A (CALABARZON)'],
            ['17', '170000000', 'REGION IV-B (MIMAROPA)'],
            ['05', '050000000', 'REGION V (BICOL REGION)'],
            ['06', '060000000', 'REGION VI (WESTERN VISAYAS)'],
            ['07', '070000000', 'REGION VII (CENTRAL VISAYAS)'],
            ['08', '080000000', 'REGION VIII (EASTERN VISAYAS)'],
            ['09', '090000000', 'REGION IX (ZAMBOANGA PENINSULA)'],
            ['10', '100000000', 'REGION X (NORTHERN MINDANAO)'],
            ['11', '110000000', 'REGION XI (DAVAO REGION)'],
            ['12', '120000000', 'REGION XII (SOCCSKSARGEN)'],
            ['13', '130000000', 'NATIONAL CAPITAL REGION (NCR)'],
            ['14', '140000000', 'CORDILLERA ADMINISTRATIVE REGION (CAR)'],
            ['15', '150000000', 'AUTONOMOUS REGION IN MUSLIM MINDANAO (ARMM)'],
            ['16', '160000000', 'REGION XIII (Caraga)'],
        ];

        foreach ($regions as $region) {
            DB::table('tbladdress')->insert([
                'address_type' => 'region',
                'code' => $region[0],
                'psgc_code' => $region[1],
                'description' => $region[2],
                'parent_code' => null,
                'region_code' => null,
                'province_code' => null,
                'citymun_code' => null,
                'level' => 1,
            ]);
        }

        $this->command->info('Inserted ' . count($regions) . ' regions');
    }

    private function insertProvinces()
    {
        $this->command->info('Inserting Provinces...');

        $sqlFile = base_path('address/refProvince.sql');

        if (!file_exists($sqlFile)) {
            $this->command->error('refProvince.sql not found at: ' . $sqlFile);
            return;
        }

        $content = file_get_contents($sqlFile);
        preg_match_all("/INSERT INTO `refprovince` VALUES \('(\d+)', '(\d+)', '([^']+)', '(\d+)', '(\d+)'\);/", $content, $matches, PREG_SET_ORDER);

        $count = 0;
        foreach ($matches as $match) {
            DB::table('tbladdress')->insert([
                'address_type' => 'province',
                'code' => $match[5],        // provCode
                'psgc_code' => $match[2],   // psgcCode
                'description' => $match[3], // provDesc
                'parent_code' => $match[4], // regCode
                'region_code' => $match[4], // regCode
                'province_code' => null,
                'citymun_code' => null,
                'level' => 2,
            ]);
            $count++;
        }

        $this->command->info("Inserted {$count} provinces");
    }

    private function insertCities()
    {
        $this->command->info('Inserting Cities/Municipalities...');

        $sqlFile = base_path('address/refCitymun.sql');

        if (!file_exists($sqlFile)) {
            $this->command->error('refCitymun.sql not found at: ' . $sqlFile);
            return;
        }

        $content = file_get_contents($sqlFile);
        preg_match_all("/INSERT INTO `refcitymun` VALUES \('(\d+)', '(\d+)', '([^']+)', '[^']*', '(\d+)', '(\d+)'\);/", $content, $matches, PREG_SET_ORDER);

        $count = 0;
        foreach ($matches as $match) {
            $provCode = $match[4];
            $regionCode = substr($provCode, 0, 2);

            DB::table('tbladdress')->insert([
                'address_type' => 'citymun',
                'code' => $match[5],        // citymunCode
                'psgc_code' => $match[2],   // psgcCode
                'description' => $match[3], // citymunDesc
                'parent_code' => $provCode, // provCode
                'region_code' => $regionCode,
                'province_code' => $provCode,
                'citymun_code' => null,
                'level' => 3,
            ]);
            $count++;
        }

        $this->command->info("Inserted {$count} cities/municipalities");
    }

    private function insertBarangays()
    {
        $this->command->info('Inserting Barangays (this may take a while)...');

        $sqlFile = base_path('address/refBrgy.sql');

        if (!file_exists($sqlFile)) {
            $this->command->error('refBrgy.sql not found at: ' . $sqlFile);
            return;
        }

        $handle = fopen($sqlFile, 'r');
        if (!$handle) {
            $this->command->error('Cannot open refBrgy.sql');
            return;
        }

        $count = 0;
        $batch = [];
        $batchSize = 1000;

        while (($line = fgets($handle)) !== false) {
            if (preg_match("/INSERT INTO `refbrgy` VALUES \('(\d+)', '(\d+)', '([^']+)', '(\d+)', '(\d+)', '(\d+)'\);/", $line, $match)) {
                $batch[] = [
                    'address_type' => 'barangay',
                    'code' => $match[2],        // brgyCode
                    'psgc_code' => $match[2],   // brgyCode as psgc
                    'description' => $match[3], // brgyDesc
                    'parent_code' => $match[6], // citymunCode
                    'region_code' => $match[4], // regCode
                    'province_code' => $match[5], // provCode
                    'citymun_code' => $match[6], // citymunCode
                    'level' => 4,
                ];

                if (count($batch) >= $batchSize) {
                    DB::table('tbladdress')->insert($batch);
                    $count += count($batch);
                    $batch = [];
                    $this->command->info("Inserted {$count} barangays so far...");
                }
            }
        }

        // Insert remaining batch
        if (!empty($batch)) {
            DB::table('tbladdress')->insert($batch);
            $count += count($batch);
        }

        fclose($handle);
        $this->command->info("Inserted {$count} barangays");
    }

    private function populateZipcodes()
    {
        $this->command->info('Populating Zipcodes with Province-Aware Mapping...');

        $stats = [
            'exact_matches' => 0,
            'city_suffix_matches' => 0,
            'single_fuzzy_matches' => 0,
            'ambiguous_skipped' => 0,
            'not_found' => 0,
            'empty_zip_skipped' => 0,
        ];

        $ambiguousCities = [];
        $zipcodeFile = base_path('philippine-provinces-and-cities-sql-0.3/philippine_provinces_and_cities.sql');

        if (!file_exists($zipcodeFile)) {
            $this->command->error("ZIP code file not found: {$zipcodeFile}");
            return;
        }

        $content = file_get_contents($zipcodeFile);
        preg_match_all("/\((\d+), '([^']+)', (\d+), '([^']*)'\)/", $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $cityName = $match[2];
            $provinceId = $match[3];
            $zipcode = $match[4];

            // Skip empty ZIP codes
            if (empty($zipcode)) {
                $stats['empty_zip_skipped']++;
                continue;
            }

            // Strategy 1: EXACT match
            $exactMatch = DB::table('tbladdress')
                ->where('address_type', 'citymun')
                ->where('description', $cityName)
                ->first();

            if ($exactMatch) {
                DB::table('tbladdress')->where('id', $exactMatch->id)->update(['zipcode' => $zipcode]);
                $stats['exact_matches']++;
                continue;
            }

            // Strategy 2: Handle "City" suffix variations
            $altName = $this->getAlternativeCityName($cityName);
            if ($altName) {
                $altMatch = DB::table('tbladdress')
                    ->where('address_type', 'citymun')
                    ->where('description', $altName)
                    ->first();

                if ($altMatch) {
                    DB::table('tbladdress')->where('id', $altMatch->id)->update(['zipcode' => $zipcode]);
                    $stats['city_suffix_matches']++;
                    continue;
                }
            }

            // Strategy 3: Single fuzzy match only (safe)
            $fuzzyMatches = DB::table('tbladdress')
                ->where('address_type', 'citymun')
                ->where('description', 'LIKE', '%' . $cityName . '%')
                ->get();

            if ($fuzzyMatches->count() === 1) {
                DB::table('tbladdress')->where('id', $fuzzyMatches->first()->id)->update(['zipcode' => $zipcode]);
                $stats['single_fuzzy_matches']++;
            } elseif ($fuzzyMatches->count() > 1) {
                // Ambiguous - multiple cities with same name
                $stats['ambiguous_skipped']++;
                if (!in_array($cityName, array_column($ambiguousCities, 'name'))) {
                    $ambiguousCities[] = [
                        'name' => $cityName,
                        'province_id' => $provinceId,
                        'zipcode' => $zipcode,
                        'matches' => $fuzzyMatches->pluck('description')->take(5)->toArray(),
                    ];
                }
            } else {
                $stats['not_found']++;
            }
        }

        // Show detailed results
        $this->command->newLine();
        $this->command->info('=== ZIP CODE MAPPING DETAILS ===');
        $this->command->info("Exact matches: {$stats['exact_matches']}");
        $this->command->info("City suffix matches: {$stats['city_suffix_matches']}");
        $this->command->info("Single fuzzy matches: {$stats['single_fuzzy_matches']}");
        $this->command->warn("Ambiguous skipped: {$stats['ambiguous_skipped']}");
        $this->command->warn("Not found: {$stats['not_found']}");
        $this->command->info("Empty ZIP skipped: {$stats['empty_zip_skipped']}");

        // Show ambiguous cities that need manual review
        if (!empty($ambiguousCities)) {
            $this->command->newLine();
            $this->command->warn('=== AMBIGUOUS CITIES (Need Manual Review) ===');
            foreach (array_slice($ambiguousCities, 0, 10) as $city) {
                $this->command->warn("  - {$city['name']} (ZIP: {$city['zipcode']}, Province: {$city['province_id']})");
                $this->command->warn("    Matches: " . implode(', ', $city['matches']));
            }
            if (count($ambiguousCities) > 10) {
                $this->command->warn("  ... and " . (count($ambiguousCities) - 10) . " more");
            }
        }
    }

    private function getAlternativeCityName(string $cityName): ?string
    {
        $upperName = strtoupper($cityName);
        
        // 1. Handle "CITY OF X" ↔ "X City" variations
        if (str_ends_with($upperName, ' CITY')) {
            $baseName = substr($cityName, 0, -5); // Remove " City"
            return 'CITY OF ' . strtoupper($baseName);
        }
        if (str_starts_with($upperName, 'CITY OF ')) {
            $baseName = substr($cityName, 8); // Remove "CITY OF "
            return $baseName . ' City';
        }
        
        // 2. Handle "X (Capital)" suffix
        if (str_ends_with($upperName, ' (CAPITAL)')) {
            return substr($cityName, 0, -10); // Remove " (Capital)"
        }
        
        // 3. Handle compound city names
        $compoundMappings = [
            'SAN JOSE DEL MONTE' => 'CITY OF SAN JOSE DEL MONTE',
            'CAGAYAN DE ORO' => 'CAGAYAN DE ORO CITY',
            'SCIENCE CITY OF MUÑOZ' => 'MUÑOZ',
            'ISLAND GARDEN CITY OF SAMAL' => 'SAMAL',
            'SOFRONIO ESPAÑOLA' => 'SOFRONIO ESPANOLA', // Without tilde
            'PEÑABLANCA' => 'PENABLANCA', // Without tilde
            'SANTO NIÑO' => 'SANTO NINO', // Without tilde
        ];
        
        foreach ($compoundMappings as $pattern => $alternative) {
            if ($upperName === $pattern || str_starts_with($upperName, $pattern)) {
                return $alternative;
            }
        }
        
        // 4. Try case-insensitive variations of common cities
        $commonVariations = [
            'TALISAY' => ['CITY OF TALISAY', 'Talisay City'],
            'CEBU' => ['CITY OF CEBU', 'Cebu City', 'Cebu City (Capital)'],
            'DAVAO' => ['CITY OF DAVAO', 'Davao City'],
            'BACOLOD' => ['CITY OF BACOLOD', 'Bacolod City'],
            'ILOILO' => ['CITY OF ILOILO', 'Iloilo City'],
            'BAGUIO' => ['CITY OF BAGUIO', 'Baguio City'],
            'MANDAUE' => ['CITY OF MANDAUE', 'Mandaue City'],
            'LAPU-LAPU' => ['CITY OF LAPU-LAPU', 'Lapu-Lapu City'],
            'PARANAQUE' => ['CITY OF PARAÑAQUE', 'Parañaque City'],
            'LAS PINAS' => ['CITY OF LAS PIÑAS', 'Las Piñas City'],
            'MAKATI' => ['CITY OF MAKATI', 'Makati City'],
            'MARIKINA' => ['CITY OF MARIKINA', 'Marikina City'],
            'MUNTINLUPA' => ['CITY OF MUNTINLUPA', 'Muntinlupa City'],
            'NAVOTAS' => ['CITY OF NAVOTAS', 'Navotas City'],
            'MALABON' => ['CITY OF MALABON', 'Malabon City'],
            'VALENZUELA' => ['CITY OF VALENZUELA', 'Valenzuela City'],
            'MANDALUYONG' => ['CITY OF MANDALUYONG', 'Mandaluyong City'],
            'PASIG' => ['CITY OF PASIG', 'Pasig City'],
            'TAGUIG' => ['CITY OF TAGUIG', 'Taguig City'],
            'CALOOCAN' => ['CITY OF CALOOCAN', 'Caloocan City'],
            'PASAY' => ['CITY OF PASAY', 'Pasay City'],
            'BATANGAS' => ['CITY OF BATANGAS', 'Batangas City'],
            'LIPA' => ['CITY OF LIPA', 'Lipa City'],
            'TANAUAN' => ['CITY OF TANAUAN', 'Tanauan City'],
            'LUCENA' => ['CITY OF LUCENA', 'Lucena City'],
            'TAYABAS' => ['CITY OF TAYABAS', 'Tayabas City'],
            'SAN FERNANDO' => ['CITY OF SAN FERNANDO', 'San Fernando City'],
            'ANGELES' => ['CITY OF ANGELES', 'Angeles City'],
            'OLONGAPO' => ['CITY OF OLONGAPO', 'Olongapo City'],
            'CABANATUAN' => ['CITY OF CABANATUAN', 'Cabanatuan City'],
            'GAPAN' => ['CITY OF GAPAN', 'Gapan City'],
            'PALAYAN' => ['CITY OF PALAYAN', 'Palayan City'],
            'BANGUED' => ['BANGUED (CAPITAL)', 'Bangued (Capital)'],
            'BALANGA' => ['CITY OF BALANGA', 'Balanga City'],
            'BASCO' => ['BASCO (CAPITAL)', 'Basco (Capital)'],
            'BAYOMBONG' => ['BAYOMBONG (CAPITAL)', 'Bayombong (Capital)'],
            'CABARROGUIS' => ['CABARROGUIS (CAPITAL)', 'Cabarroguis (Capital)'],
            'ILAGAN' => ['ILAGAN CITY (CAPITAL)', 'Ilagan City (Capital)'],
            'LAOAG' => ['LAOAG CITY (CAPITAL)', 'Laoag City (Capital)'],
            'LINGAYEN' => ['LINGAYEN (CAPITAL)', 'Lingayen (Capital)'],
            'MALOLOS' => ['CITY OF MALOLOS (CAPITAL)', 'Malolos City (Capital)'],
            'TUGUEGARAO' => ['TUGUEGARAO CITY (CAPITAL)', 'Tuguegarao City (Capital)'],
            'VIGAN' => ['CITY OF VIGAN (CAPITAL)', 'Vigan City (Capital)'],
            'BUTUAN' => ['CITY OF BUTUAN', 'Butuan City'],
            'BAYUGAN' => ['CITY OF BAYUGAN', 'Bayugan City'],
            'LEGAZPI' => ['CITY OF LEGAZPI', 'Legazpi City'],
            'LIGAO' => ['CITY OF LIGAO', 'Ligao City'],
            'TABACO' => ['CITY OF TABACO', 'Tabaco City'],
            'TAGBILARAN' => ['CITY OF TAGBILARAN', 'Tagbilaran City'],
            'MALAYBALAY' => ['CITY OF MALAYBALAY', 'Malaybalay City'],
            'VALENCIA' => ['CITY OF VALENCIA', 'Valencia City'],
            'MALOLOS' => ['CITY OF MALOLOS', 'Malolos City'],
            'MEYCAUAYAN' => ['CITY OF MEYCAUAYAN', 'Meycauayan City'],
            'SAN JOSE DEL MONTE' => ['CITY OF SAN JOSE DEL MONTE', 'San Jose del Monte City'],
            'TUGUEGARAO' => ['CITY OF TUGUEGARAO', 'Tuguegarao City'],
            'CAUAYAN' => ['CITY OF CAUAYAN', 'Cauayan City'],
            'SANTIAGO' => ['CITY OF SANTIAGO', 'Santiago City'],
            'TABUK' => ['CITY OF TABUK', 'Tabuk City'],
            'BIÑAN' => ['CITY OF BIÑAN', 'Biñan City'],
            'CALAMBA' => ['CITY OF CALAMBA', 'Calamba City'],
            'SAN PABLO' => ['CITY OF SAN PABLO', 'San Pablo City'],
            'SANTA ROSA' => ['CITY OF SANTA ROSA', 'Santa Rosa City'],
            'ILIGAN' => ['CITY OF ILIGAN', 'Iligan City'],
            'MARAWI' => ['CITY OF MARAWI', 'Marawi City'],
            'ORmoc' => ['CITY OF ORMOC', 'Ormoc City'],
            'TACLOBAN' => ['CITY OF TACLOBAN', 'Tacloban City'],
            'CALBAYOG' => ['CITY OF CALBAYOG', 'Calbayog City'],
            'CATBALOGAN' => ['CITY OF CATBALOGAN', 'Catbalogan City'],
            'COTABATO' => ['CITY OF COTABATO', 'Cotabato City'],
            'GENERAL SANTOS' => ['CITY OF GENERAL SANTOS', 'General Santos City'],
            'KORONADAL' => ['CITY OF KORONADAL', 'Koronadal City'],
            'KIDAPAWAN' => ['CITY OF KIDAPAWAN', 'Kidapawan City'],
            'DIGOS' => ['CITY OF DIGOS', 'Digos City'],
            'PANABO' => ['CITY OF PANABO', 'Panabo City'],
            'TAGUM' => ['CITY OF TAGUM', 'Tagum City'],
            'MATI' => ['CITY OF MATI', 'Mati City'],
            'TACURONG' => ['CITY OF TACURONG', 'Tacurong City'],
            'ZAMBOANGA' => ['CITY OF ZAMBOANGA', 'Zamboanga City'],
            'PAGADIAN' => ['CITY OF PAGADIAN', 'Pagadian City'],
            'PUERTO PRINCESA' => ['CITY OF PUERTO PRINCESA', 'Puerto Princesa City'],
            'CALAPAN' => ['CITY OF CALAPAN', 'Calapan City'],
            'DAPITAN' => ['CITY OF DAPITAN', 'Dapitan City'],
            'DIPOLOG' => ['CITY OF DIPOLOG', 'Dipolog City'],
            'OROQUIETA' => ['CITY OF OROQUIETA', 'Oroquieta City'],
            'OZAMIZ' => ['CITY OF OZAMIZ', 'Ozamiz City'],
            'TANGUB' => ['CITY OF TANGUB', 'Tangub City'],
            'DAGUPAN' => ['CITY OF DAGUPAN', 'Dagupan City'],
            'SAN CARLOS' => ['CITY OF SAN CARLOS', 'San Carlos City'],
            'URDANETA' => ['CITY OF URDANETA', 'Urdaneta City'],
            'ALAMINOS' => ['CITY OF ALAMINOS', 'Alaminos City'],
            'ANTIPOLO' => ['CITY OF ANTIPOLO', 'Antipolo City'],
            'BAIS' => ['CITY OF BAIS', 'Bais City'],
            'BAYAWAN' => ['CITY OF BAYAWAN', 'Bayawan City'],
            'CANLAON' => ['CITY OF CANLAON', 'Canlaon City'],
            'DUMAGUETE' => ['CITY OF DUMAGUETE', 'Dumaguete City'],
            'GUIHULNGAN' => ['CITY OF GUIHULNGAN', 'Guihulngan City'],
            'TANJAY' => ['CITY OF TANJAY', 'Tanjay City'],
            'BOGO' => ['CITY OF BOGO', 'Bogo City'],
            'CARCAR' => ['CITY OF CARCAR', 'Carcar City'],
            'DANAO' => ['CITY OF DANAO', 'Danao City'],
            'NAGA' => ['CITY OF NAGA', 'Naga City'],
            'TOLEDO' => ['CITY OF TOLEDO', 'Toledo City'],
            'BAGO' => ['CITY OF BAGO', 'Bago City'],
            'CADIZ' => ['CITY OF CADIZ', 'Cadiz City'],
            'ESCALANTE' => ['CITY OF ESCALANTE', 'Escalante City'],
            'Himamaylan' => ['CITY OF HIMAMAYLAN', 'Himamaylan City'],
            'KABANKALAN' => ['CITY OF KABANKALAN', 'Kabankalan City'],
            'LA CARLOTA' => ['CITY OF LA CARLOTA', 'La Carlota City'],
            'SAGAY' => ['CITY OF SAGAY', 'Sagay City'],
            'SAN CARLOS' => ['CITY OF SAN CARLOS', 'San Carlos City'],
            'SILAY' => ['CITY OF SILAY', 'Silay City'],
            'SIPALAY' => ['CITY OF SIPALAY', 'Sipalay City'],
            'TALISAY' => ['CITY OF TALISAY', 'Talisay City'],
            'VICTORIAS' => ['CITY OF VICTORIAS', 'Victorias City'],
            'MAASIN' => ['CITY OF MAASIN', 'Maasin City'],
            'ROXAS' => ['CITY OF ROXAS', 'Roxas City'],
            'PASSI' => ['CITY OF PASSI', 'Passi City'],
            'ILOILO' => ['CITY OF ILOILO', 'Iloilo City'],
            'ISABELA' => ['CITY OF ISABELA', 'Isabela City'],
            'LAMITAN' => ['CITY OF LAMITAN', 'Lamitan City'],
            'BALANGA' => ['CITY OF BALANGA', 'Balanga City'],
            'BALER' => ['CITY OF BALER', 'Baler City'],
            'BASCO' => ['CITY OF BASCO', 'Basco City'],
            'BAYUGAN' => ['CITY OF BAYUGAN', 'Bayugan City'],
            'BISLIG' => ['CITY OF BISLIG', 'Bislig City'],
            'TANDAG' => ['CITY OF TANDAG', 'Tandag City'],
            'SURIGAO' => ['CITY OF SURIGAO', 'Surigao City'],
            'BONGAO' => ['CITY OF BONGAO', 'Bongao City'],
            'BATAC' => ['CITY OF BATAC', 'Batac City'],
            'LAOAG' => ['CITY OF LAOAG', 'Laoag City'],
            'VIGAN' => ['CITY OF VIGAN', 'Vigan City'],
            'CANDON' => ['CITY OF CANDON', 'Candon City'],
        ];
        
        foreach ($commonVariations as $base => $variations) {
            if ($upperName === strtoupper($base)) {
                return $variations[0]; // Return first variation
            }
            foreach ($variations as $variant) {
                if ($upperName === strtoupper($variant)) {
                    return $base; // Return base form
                }
            }
        }
        
        return null;
    }

    private function showSummary()
    {
        $this->command->newLine();
        $this->command->info('=== ADDRESS DATA IMPORT SUMMARY ===');

        $stats = DB::table('tbladdress')
            ->select('address_type', DB::raw('COUNT(*) as count'))
            ->groupBy('address_type')
            ->get()
            ->pluck('count', 'address_type');

        $this->command->info("Regions: " . ($stats['region'] ?? 0));
        $this->command->info("Provinces: " . ($stats['province'] ?? 0));
        $this->command->info("Cities/Municipalities: " . ($stats['citymun'] ?? 0));
        $this->command->info("Barangays: " . ($stats['barangay'] ?? 0));

        $withZip = DB::table('tbladdress')->where('address_type', 'citymun')->whereNotNull('zipcode')->count();
        $this->command->info("Cities with Zipcode: {$withZip}");

        $total = DB::table('tbladdress')->count();
        $this->command->info("Total Records: {$total}");
        $this->command->info('===================================');
    }
}
