<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PopulateZipcodes extends Command
{
    protected $signature = 'address:populate-zipcodes';
    protected $description = 'Populate zipcodes in tbladdress from philippine_provinces_and_cities.sql';

    public function handle()
    {
        $this->info('Populating zipcodes from SQL file...');

        $sqlFile = base_path('philippine-provinces-and-cities-sql-0.3/philippine_provinces_and_cities.sql');

        if (!file_exists($sqlFile)) {
            $this->error("SQL file not found: {$sqlFile}");
            return 1;
        }

        $content = file_get_contents($sqlFile);

        // Parse city inserts - match individual value lines
        // Format: (id, 'name', province_id, 'zipcode')
        preg_match_all("/\((\d+), '([^']+)', \d+, '([^']*)'\)/", $content, $matches, PREG_SET_ORDER);

        $this->info("Found " . count($matches) . " city records in SQL file");

        $updated = 0;
        $skipped = 0;
        $notFound = 0;

        foreach ($matches as $match) {
            $cityName = $match[2];
            $zipcode = $match[3];

            if (empty($zipcode)) {
                continue;
            }

            // Try EXACT match first to prevent false positives
            $exactMatch = DB::table('tbladdress')
                ->where('address_type', 'citymun')
                ->where('description', $cityName)
                ->first();

            if ($exactMatch) {
                DB::table('tbladdress')
                    ->where('id', $exactMatch->id)
                    ->update(['zipcode' => $zipcode]);
                $updated++;
            } else {
                // Only use fuzzy matching if NO exact match exists
                $fuzzyMatches = DB::table('tbladdress')
                    ->where('address_type', 'citymun')
                    ->where('description', 'LIKE', '%' . $cityName . '%')
                    ->get();

                if ($fuzzyMatches->count() === 1) {
                    // Safe to update - only one match
                    DB::table('tbladdress')
                        ->where('id', $fuzzyMatches->first()->id)
                        ->update(['zipcode' => $zipcode]);
                    $updated++;
                } elseif ($fuzzyMatches->count() > 1) {
                    // Multiple matches - skip to avoid false assignment
                    $this->warn("Skipping ambiguous city '{$cityName}' - {$fuzzyMatches->count()} matches");
                    $skipped++;
                } else {
                    $notFound++;
                }
            }
        }

        // Also try matching from tblcity legacy table for remaining cities
        $this->info('Checking legacy tblcity table for remaining cities...');

        $legacyCities = DB::table('tblcity')->get();
        foreach ($legacyCities as $legacyCity) {
            if (empty($legacyCity->Zipcode)) {
                continue;
            }

            // Skip if city already has zipcode
            $hasZip = DB::table('tbladdress')
                ->where('address_type', 'citymun')
                ->where('description', 'LIKE', '%' . $legacyCity->City . '%')
                ->whereNotNull('zipcode')
                ->exists();

            if ($hasZip) {
                continue;
            }

            // Try exact match first
            $city = DB::table('tbladdress')
                ->where('address_type', 'citymun')
                ->where('description', $legacyCity->City)
                ->whereNull('zipcode')
                ->first();

            if ($city) {
                DB::table('tbladdress')
                    ->where('id', $city->id)
                    ->update(['zipcode' => $legacyCity->Zipcode]);
                $updated++;
            }
        }

        $withZip = DB::table('tbladdress')
            ->where('address_type', 'citymun')
            ->whereNotNull('zipcode')
            ->count();

        $withoutZip = DB::table('tbladdress')
            ->where('address_type', 'citymun')
            ->whereNull('zipcode')
            ->count();

        $this->info("=== ZIPCODE POPULATION SUMMARY ===");
        $this->info("Cities with zipcode: {$withZip}");
        $this->info("Cities without zipcode: {$withoutZip}");
        $this->info("Updated this run: {$updated}");
        $this->info("Skipped (ambiguous): {$skipped}");
        $this->info("Not found: {$notFound}");

        return 0;
    }
}
