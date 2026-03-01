<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Fix Talisay ZIP codes - corrects the ZIP assignments for duplicate city names
 */
class FixTalisayZipcodes extends Seeder
{
    public function run(): void
    {
        $this->command->info('Fixing Talisay ZIP codes...');

        // Define correct ZIP mappings for Talisay cities by province
        $talisayMappings = [
            // Province 0410 (Batangas) - Talisay (municipality)
            ['province_code' => '0410', 'city_name' => 'TALISAY', 'zipcode' => '4220'],
            
            // Province 0516 (Camarines Norte) - Talisay (municipality)
            ['province_code' => '0516', 'city_name' => 'TALISAY', 'zipcode' => '4602'],
            
            // Province 0722 (Cebu) - CITY OF TALISAY
            ['province_code' => '0722', 'city_name' => 'CITY OF TALISAY', 'zipcode' => '6045'],
            
            // Province 0645 (Negros Occidental) - CITY OF TALISAY
            ['province_code' => '0645', 'city_name' => 'CITY OF TALISAY', 'zipcode' => '6115'],
        ];

        $updated = 0;

        foreach ($talisayMappings as $mapping) {
            $result = DB::table('tbladdress')
                ->where('address_type', 'citymun')
                ->where('province_code', $mapping['province_code'])
                ->where(function ($query) use ($mapping) {
                    $query->where('description', $mapping['city_name'])
                          ->orWhere('description', 'LIKE', '%' . $mapping['city_name'] . '%');
                })
                ->update(['zipcode' => $mapping['zipcode']]);

            if ($result > 0) {
                $this->command->info("Updated {$mapping['city_name']} (Province: {$mapping['province_code']}) → ZIP {$mapping['zipcode']}");
                $updated += $result;
            }
        }

        $this->command->info("Fixed {$updated} Talisay records");

        // Verify the fixes
        $this->verifyTalisayZips();
    }

    private function verifyTalisayZips(): void
    {
        $this->command->newLine();
        $this->command->info('=== Verification ===');

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
