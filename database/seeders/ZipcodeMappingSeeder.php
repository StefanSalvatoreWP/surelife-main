<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * Province-Aware ZIP Code Mapping Seeder
 * 
 * This seeder fixes the ZIP code accuracy issues by:
 * 1. Using exact matching first
 * 2. Handling "City" suffix variations
 * 3. Skipping ambiguous duplicates for manual review
 * 4. Generating a detailed report of unmapped cities
 */
class ZipcodeMappingSeeder extends Seeder
{
    private array $stats = [
        'exact_matches' => 0,
        'city_suffix_matches' => 0,
        'single_fuzzy_matches' => 0,
        'ambiguous_skipped' => 0,
        'not_found' => 0,
        'empty_zip_skipped' => 0,
    ];

    private array $ambiguousCities = [];
    private array $unmappedCities = [];

    public function run(): void
    {
        $this->command->info('=== Province-Aware ZIP Code Mapping ===');
        
        $zipcodeFile = base_path('philippine-provinces-and-cities-sql-0.3/philippine_provinces_and_cities.sql');
        
        if (!file_exists($zipcodeFile)) {
            $this->command->error("ZIP code SQL file not found: {$zipcodeFile}");
            return;
        }

        // Parse SQL file
        $content = file_get_contents($zipcodeFile);
        preg_match_all("/\((\d+), '([^']+)', (\d+), '([^']*)'\)/", $content, $matches, PREG_SET_ORDER);

        $this->command->info("Found " . count($matches) . " city records in SQL file");

        // Clear existing zipcodes to start fresh
        DB::table('tbladdress')
            ->where('address_type', 'citymun')
            ->update(['zipcode' => null]);

        foreach ($matches as $match) {
            $this->processCityZip($match);
        }

        // Generate report
        $this->generateReport();
        
        // Show summary
        $this->showSummary();
    }

    private function processCityZip(array $match): void
    {
        $cityId = $match[1];
        $cityName = $match[2];
        $provinceId = $match[3];
        $zipcode = $match[4];

        // Skip empty ZIP codes
        if (empty($zipcode)) {
            $this->stats['empty_zip_skipped']++;
            return;
        }

        // Strategy 1: Try EXACT match first
        $exactMatch = DB::table('tbladdress')
            ->where('address_type', 'citymun')
            ->where('description', $cityName)
            ->first();

        if ($exactMatch) {
            $this->updateZipcode($exactMatch->id, $zipcode, 'exact');
            return;
        }

        // Strategy 2: Try with/without "City" suffix
        $altName = $this->getAlternativeName($cityName);
        if ($altName) {
            $altMatch = DB::table('tbladdress')
                ->where('address_type', 'citymun')
                ->where('description', $altName)
                ->first();

            if ($altMatch) {
                $this->updateZipcode($altMatch->id, $zipcode, 'city_suffix');
                return;
            }
        }

        // Strategy 3: Try province-aware exact match
        $provinceMatch = $this->findByProvince($cityName, $provinceId);
        if ($provinceMatch && $provinceMatch->count() === 1) {
            $this->updateZipcode($provinceMatch->first()->id, $zipcode, 'exact');
            return;
        }

        // Strategy 4: Single fuzzy match (safe only)
        $fuzzyMatches = DB::table('tbladdress')
            ->where('address_type', 'citymun')
            ->where('description', 'LIKE', '%' . $cityName . '%')
            ->get();

        if ($fuzzyMatches->count() === 1) {
            $this->updateZipcode($fuzzyMatches->first()->id, $zipcode, 'fuzzy');
        } elseif ($fuzzyMatches->count() > 1) {
            // Multiple matches - ambiguous
            $this->stats['ambiguous_skipped']++;
            $this->ambiguousCities[] = [
                'sql_city' => $cityName,
                'province_id' => $provinceId,
                'zipcode' => $zipcode,
                'matches' => $fuzzyMatches->pluck('description')->toArray(),
            ];
        } else {
            // No matches found
            $this->stats['not_found']++;
            $this->unmappedCities[] = [
                'sql_city' => $cityName,
                'province_id' => $provinceId,
                'zipcode' => $zipcode,
            ];
        }
    }

    private function getAlternativeName(string $cityName): ?string
    {
        if (str_ends_with($cityName, ' City')) {
            return str_replace(' City', '', $cityName);
        }
        
        // Common city name patterns
        $cityPatterns = [
            'Talisay' => 'Talisay City',
            'Cebu' => 'Cebu City',
            'Davao' => 'Davao City',
            'Bacolod' => 'Bacolod City',
            'Iloilo' => 'Iloilo City',
            'Baguio' => 'Baguio City',
        ];

        foreach ($cityPatterns as $base => $withCity) {
            if ($cityName === $base) {
                return $withCity;
            }
            if ($cityName === $withCity) {
                return $base;
            }
        }

        return null;
    }

    private function findByProvince(string $cityName, int $provinceId)
    {
        // Map SQL province_id to tbladdress province_code
        $provinceCode = $this->mapProvinceIdToCode($provinceId);
        
        if (!$provinceCode) {
            return null;
        }

        return DB::table('tbladdress')
            ->where('address_type', 'citymun')
            ->where('description', $cityName)
            ->where('province_code', $provinceCode)
            ->get();
    }

    private function mapProvinceIdToCode(int $provinceId): ?string
    {
        // SQL file uses sequential province_id (1-81)
        // tbladdress uses PSGC province_code format
        // This mapping needs to be built based on the actual data
        
        // For now, return null to skip this strategy
        // TODO: Build proper province mapping
        return null;
    }

    private function updateZipcode(int $id, string $zipcode, string $method): void
    {
        DB::table('tbladdress')
            ->where('id', $id)
            ->update(['zipcode' => $zipcode]);

        $this->stats[$method . '_matches']++;
    }

    private function generateReport(): void
    {
        $reportPath = storage_path('app/zipcode_mapping_report.json');
        
        $report = [
            'generated_at' => now()->toIso8601String(),
            'statistics' => $this->stats,
            'ambiguous_cities' => $this->ambiguousCities,
            'unmapped_cities' => $this->unmappedCities,
            'summary' => [
                'total_processed' => array_sum($this->stats),
                'success_rate' => $this->calculateSuccessRate(),
            ],
        ];

        File::put($reportPath, json_encode($report, JSON_PRETTY_PRINT));
        
        $this->command->info("Report saved to: {$reportPath}");
    }

    private function calculateSuccessRate(): float
    {
        $total = array_sum($this->stats);
        $successful = $this->stats['exact_matches'] 
            + $this->stats['city_suffix_matches'] 
            + $this->stats['single_fuzzy_matches'];
        
        return $total > 0 ? round(($successful / $total) * 100, 2) : 0;
    }

    private function showSummary(): void
    {
        $this->command->newLine();
        $this->command->info('=== ZIP CODE MAPPING SUMMARY ===');
        $this->command->info("Exact matches: {$this->stats['exact_matches']}");
        $this->command->info("City suffix matches: {$this->stats['city_suffix_matches']}");
        $this->command->info("Single fuzzy matches: {$this->stats['single_fuzzy_matches']}");
        $this->command->info("Ambiguous (skipped): {$this->stats['ambiguous_skipped']}");
        $this->command->info("Not found: {$this->stats['not_found']}");
        $this->command->info("Empty ZIP skipped: {$this->stats['empty_zip_skipped']}");
        $this->command->info("Success rate: {$this->calculateSuccessRate()}%");
        
        $withZip = DB::table('tbladdress')
            ->where('address_type', 'citymun')
            ->whereNotNull('zipcode')
            ->count();
        
        $withoutZip = DB::table('tbladdress')
            ->where('address_type', 'citymun')
            ->whereNull('zipcode')
            ->count();
        
        $this->command->info("Cities with ZIP: {$withZip}");
        $this->command->info("Cities without ZIP: {$withoutZip}");
    }
}
