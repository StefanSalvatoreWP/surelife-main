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
        $this->command->info('Populating Zipcodes...');

        $updated = 0;

        // First, try to populate from philippine_provinces_and_cities.sql
        $zipcodeFile = base_path('philippine-provinces-and-cities-sql-0.3/philippine_provinces_and_cities.sql');

        if (file_exists($zipcodeFile)) {
            $content = file_get_contents($zipcodeFile);
            preg_match_all("/\((\d+), '([^']+)', \d+, '([^']*)'\)/", $content, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                $cityName = $match[2];
                $zipcode = $match[3];

                if (empty($zipcode)) continue;

                DB::table('tbladdress')
                    ->where('address_type', 'citymun')
                    ->where('description', 'LIKE', '%' . $cityName . '%')
                    ->update(['zipcode' => $zipcode]);
                $updated++;
            }
        }

        // Then, try legacy tblcity for any remaining cities without zipcode
        if (\Illuminate\Support\Facades\Schema::hasTable('tblcity')) {
            $cities = DB::table('tbladdress')
                ->where('address_type', 'citymun')
                ->whereNull('zipcode')
                ->get();

            foreach ($cities as $city) {
                $legacyCity = DB::table('tblcity')
                    ->where('City', 'LIKE', '%' . $city->description . '%')
                    ->first();

                if ($legacyCity && !empty($legacyCity->Zipcode)) {
                    DB::table('tbladdress')
                        ->where('id', $city->id)
                        ->update(['zipcode' => $legacyCity->Zipcode]);
                    $updated++;
                }
            }
        }

        $this->command->info("Updated {$updated} zipcodes");
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
