<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Report of cities that still need ZIP code fixes
 */
class ReportMissingZips extends Seeder
{
    public function run(): void
    {
        $this->command->info('=== Cities Missing ZIP Codes Report ===');

        // Get all cities without ZIP
        $missingZipCities = DB::table('tbladdress')
            ->where('address_type', 'citymun')
            ->whereNull('zipcode')
            ->orWhere('zipcode', '')
            ->orderBy('province_code')
            ->orderBy('description')
            ->get(['description', 'province_code', 'code']);

        $this->command->warn("Total cities without ZIP: {$missingZipCities->count()}");
        $this->command->newLine();

        // Group by first letter for readability
        $grouped = $missingZipCities->groupBy(function($city) {
            return substr(strtoupper($city->description), 0, 1);
        })->sortKeys();

        foreach ($grouped as $letter => $cities) {
            $this->command->info("=== {$letter} ({$cities->count()} cities) ===");
            
            foreach ($cities->take(5) as $city) {
                $this->command->warn("  - {$city->description} (Province: {$city->province_code})");
            }
            
            if ($cities->count() > 5) {
                $this->command->warn("  ... and " . ($cities->count() - 5) . " more");
            }
            
            $this->command->newLine();
        }

        // Show duplicate cities that still have issues
        $this->command->info('=== Duplicate Cities with Missing ZIPs ===');
        
        $duplicates = DB::table('tbladdress')
            ->where('address_type', 'citymun')
            ->select('description')
            ->groupBy('description')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('description');

        foreach ($duplicates as $cityName) {
            $cities = DB::table('tbladdress')
                ->where('address_type', 'citymun')
                ->where('description', $cityName)
                ->where(function ($query) {
                    $query->whereNull('zipcode')
                          ->orWhere('zipcode', '');
                })
                ->get(['province_code', 'zipcode']);

            if ($cities->count() > 0) {
                $this->command->warn("{$cityName}:");
                foreach ($cities as $city) {
                    $zip = $city->zipcode ?: 'EMPTY';
                    $this->command->warn("  - Province: {$city->province_code}, ZIP: {$zip}");
                }
            }
        }
    }
}
