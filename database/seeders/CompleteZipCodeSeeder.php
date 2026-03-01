<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompleteZipCodeSeeder extends Seeder
{
    /**
     * ZIP Code Seeder
     * 
     * Reads from: script/philippine_provinces_and_cities.sql
     * Updates tbladdress with ZIP codes by matching city names.
     */
    private const SQL_FILE = __DIR__ . '/../../script/philippine_provinces_and_cities.sql';
    
    /**
     * Run the seeder
     */
    public function run(): void
    {
        echo "=== ZIP CODE SEEDER ===\n";
        echo "Source: " . self::SQL_FILE . "\n\n";
        
        $zipData = $this->loadFromSql();
        
        if (empty($zipData)) {
            echo "ERROR: Could not load ZIP data from SQL file\n";
            return;
        }
        
        $stats = $this->applyZipCodes($zipData);
        
        // Final count
        $remaining = DB::table('tbladdress')
            ->where('address_type', 'citymun')
            ->where(function ($query) {
                $query->whereNull('zipcode')
                      ->orWhere('zipcode', '');
            })
            ->count();
        
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "FINAL SUMMARY:\n";
        echo "✓ Direct matches: {$stats['direct']}\n";
        echo "✓ Variation matches: {$stats['variation']}\n";
        echo "- Already had ZIP: {$stats['skipped']}\n";
        echo "⚠ Empty ZIP in SQL: {$stats['empty']}\n";
        echo "⚠ Not found in DB: {$stats['not_found']}\n";
        echo "⚠ Cities still without ZIP: {$remaining}\n";
        echo str_repeat("=", 60) . "\n";
        
        if ($remaining > 0) {
            echo "\nCities still needing ZIP codes:\n";
            $this->listRemainingCities();
        }
    }
    
    /**
     * Load ZIP data from SQL file
     */
    private function loadFromSql(): array
    {
        if (!file_exists(self::SQL_FILE)) {
            echo "ERROR: SQL file not found\n";
            return [];
        }
        
        $content = file_get_contents(self::SQL_FILE);
        
        // Match: (id, 'city_name', province_id, 'zipcode')
        preg_match_all("/\(\d+,\s*'([^']+)',\s*\d+,\s*'([^']*)'\)/", $content, $matches, PREG_SET_ORDER);
        
        $data = [];
        $withZip = 0;
        $withoutZip = 0;
        
        foreach ($matches as $match) {
            $cityName = trim($match[1]);
            $zipcode = trim($match[2]);
            
            $data[] = [
                'name' => $cityName,
                'zip' => $zipcode
            ];
            
            if (!empty($zipcode)) {
                $withZip++;
            } else {
                $withoutZip++;
            }
        }
        
        echo "✓ Parsed " . count($data) . " cities from SQL file\n";
        echo "  With ZIP: {$withZip}\n";
        echo "  Without ZIP: {$withoutZip}\n\n";
        
        return $data;
    }
    
    /**
     * Apply ZIP codes to database
     */
    private function applyZipCodes(array $zipData): array
    {
        $stats = [
            'direct' => 0,
            'variation' => 0,
            'skipped' => 0,
            'empty' => 0,
            'not_found' => 0
        ];
        
        foreach ($zipData as $entry) {
            $cityName = $entry['name'];
            $zipcode = $entry['zip'];
            
            // Skip empty ZIP codes
            if (empty($zipcode)) {
                $stats['empty']++;
                continue;
            }
            
            // Strategy 1: Direct match
            $city = DB::table('tbladdress')
                ->where('address_type', 'citymun')
                ->where('description', strtoupper($cityName))
                ->first();
            
            if ($city) {
                if (!empty($city->zipcode)) {
                    $stats['skipped']++;
                    continue;
                }
                DB::table('tbladdress')->where('id', $city->id)->update(['zipcode' => $zipcode]);
                $stats['direct']++;
                continue;
            }
            
            // Strategy 2: Handle "City" suffix variations
            if (stripos($cityName, ' City') !== false) {
                $baseName = trim(str_ireplace(' City', '', $cityName));
                $variation = 'CITY OF ' . strtoupper($baseName);
                
                $city = DB::table('tbladdress')
                    ->where('address_type', 'citymun')
                    ->where('description', $variation)
                    ->first();
                
                if ($city) {
                    if (!empty($city->zipcode)) {
                        $stats['skipped']++;
                        continue;
                    }
                    DB::table('tbladdress')->where('id', $city->id)->update(['zipcode' => $zipcode]);
                    $stats['variation']++;
                    continue;
                }
            }
            
            $stats['not_found']++;
        }
        
        return $stats;
    }
    
    /**
     * List remaining cities without ZIP codes
     */
    private function listRemainingCities(): void
    {
        $cities = DB::table('tbladdress as c')
            ->select('c.code', 'c.description as city', 'p.description as province')
            ->join('tbladdress as p', function($join) {
                $join->on('c.province_code', '=', 'p.code')
                     ->where('p.address_type', '=', 'province');
            })
            ->where('c.address_type', 'citymun')
            ->where(function ($query) {
                $query->whereNull('c.zipcode')
                      ->orWhere('c.zipcode', '');
            })
            ->orderBy('p.description')
            ->orderBy('c.description')
            ->limit(20)
            ->get();
        
        foreach ($cities as $city) {
            echo "  [{$city->code}] {$city->city} ({$city->province})\n";
        }
        
        $total = DB::table('tbladdress')
            ->where('address_type', 'citymun')
            ->where(function ($query) {
                $query->whereNull('zipcode')
                      ->orWhere('zipcode', '');
            })
            ->count();
        
        if ($total > 20) {
            echo "  ... and " . ($total - 20) . " more\n";
        }
    }
}
