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
        $notFound = 0;

        foreach ($matches as $match) {
            $cityName = $match[2];
            $zipcode = $match[3];

            if (empty($zipcode)) {
                continue;
            }

            // Try to find matching city in tbladdress
            $city = DB::table('tbladdress')
                ->where('address_type', 'citymun')
                ->where('description', 'LIKE', '%' . $cityName . '%')
                ->first();

            if ($city) {
                DB::table('tbladdress')
                    ->where('id', $city->id)
                    ->update(['zipcode' => $zipcode]);
                $updated++;
            } else {
                $notFound++;
            }
        }

        // Also try matching from tblcity legacy table
        $this->info('Also checking legacy tblcity table...');

        $legacyCities = DB::table('tblcity')->get();
        foreach ($legacyCities as $legacyCity) {
            if (empty($legacyCity->Zipcode)) {
                continue;
            }

            $city = DB::table('tbladdress')
                ->where('address_type', 'citymun')
                ->where('description', 'LIKE', '%' . $legacyCity->City . '%')
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
        $this->info("Total updated this run: {$updated}");

        return 0;
    }
}
