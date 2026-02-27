<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SetupReferenceTables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:reference-tables';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup and populate reference tables (refprovince, refcitymun, refbrgy)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== SETUP REFERENCE TABLES ===');
        $this->info('');

        // Check if tables already exist
        $tables = ['refprovince', 'refcitymun', 'refbrgy'];
        $existingTables = [];

        foreach ($tables as $table) {
            try {
                DB::table($table)->count();
                $existingTables[] = $table;
                $this->line("✅ Table '$table' already exists");
            } catch (\Exception $e) {
                $this->line("❌ Table '$table' does not exist");
            }
        }

        if (count($existingTables) === count($tables)) {
            $this->info('');
            $this->info('All reference tables already exist. Skipping setup.');
            return 0;
        }

        $this->info('');
        $this->info('Importing reference data...');

        // SQL files to import
        $sqlFiles = [
            'refprovince' => base_path('address/refProvince.sql'),
            'refcitymun' => base_path('address/refCitymun.sql'),
            'refbrgy' => base_path('address/refBrgy.sql')
        ];

        foreach ($sqlFiles as $table => $file) {
            $this->info('');
            $this->info("📁 Processing $table...");

            if (!file_exists($file)) {
                $this->error("❌ SQL file not found: $file");
                continue;
            }

            try {
                $sql = file_get_contents($file);
                
                // Execute SQL statements
                $statements = array_filter(array_map('trim', explode(';', $sql)));
                
                $count = 0;
                foreach ($statements as $statement) {
                    if (empty($statement)) continue;
                    
                    try {
                        DB::statement($statement);
                        $count++;
                    } catch (\Exception $e) {
                        if (!str_contains($statement, 'CREATE TABLE') && !str_contains($statement, 'DROP TABLE')) {
                            $this->warn("   ⚠️  Warning: " . $e->getMessage());
                        }
                    }
                }
                
                $this->info("   ✅ Executed $count statements");
                
                // Verify the data was imported
                $recordCount = DB::table($table)->count();
                $this->info("   📊 Table '$table' has $recordCount records");
                
            } catch (\Exception $e) {
                $this->error("❌ Failed to import $table: " . $e->getMessage());
            }
        }

        $this->info('');
        $this->info('=== VERIFICATION ===');

        // Test controller methods
        try {
            $controller = new \App\Http\Controllers\BarangayController();
            
            // Test cities
            $request = new \Illuminate\Http\Request();
            $request->merge(['provinceName' => 'CEBU']);
            $result = $controller->getRefCitiesByProvince($request);
            $cities = json_decode($result->getContent(), true);
            $this->info("✅ getRefCitiesByProvince: " . count($cities) . " cities for CEBU");
            
            // Test barangays
            $request = new \Illuminate\Http\Request();
            $request->merge(['cityName' => 'CORDOVA']);
            $result = $controller->getRefBarangaysByCity($request);
            $barangays = json_decode($result->getContent(), true);
            $this->info("✅ getRefBarangaysByCity: " . count($barangays) . " barangays for CORDOVA");
            
        } catch (\Exception $e) {
            $this->error("❌ Controller test failed: " . $e->getMessage());
        }

        $this->info('');
        $this->info('✅ Reference tables setup completed!');
        
        return 0;
    }
}
