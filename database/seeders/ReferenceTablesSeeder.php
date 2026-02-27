<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReferenceTablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding reference tables...');

        $sqlFiles = [
            'refprovince' => base_path('address/refProvince.sql'),
            'refcitymun' => base_path('address/refCitymun.sql'),
            'refbrgy' => base_path('address/refBrgy.sql')
        ];

        foreach ($sqlFiles as $table => $file) {
            $this->command->info("Processing $table from: $file");
            
            if (!file_exists($file)) {
                $this->command->error("SQL file not found: $file");
                continue;
            }

            try {
                $sql = file_get_contents($file);
                
                // Remove CREATE TABLE statements and keep only INSERT statements
                $sql = preg_replace('/CREATE TABLE.*?ENGINE=.*?;$/s', '', $sql);
                $sql = preg_replace('/SET FOREIGN_KEY_CHECKS=.*?;$/s', '', $sql);
                $sql = preg_replace('/DROP TABLE IF EXISTS.*?;$/s', '', $sql);
                
                // Split into individual statements
                $statements = array_filter(array_map('trim', explode(';', $sql)));
                
                $count = 0;
                foreach ($statements as $statement) {
                    if (empty($statement) || !str_starts_with($statement, 'INSERT')) {
                        continue;
                    }
                    
                    try {
                        DB::statement($statement);
                        $count++;
                    } catch (\Exception $e) {
                        $this->command->warn("Warning: Failed to execute statement for $table: " . $e->getMessage());
                    }
                }
                
                $this->command->info("✅ Executed $count statements for $table");
                
                // Verify the data was imported
                $recordCount = DB::table($table)->count();
                $this->command->info("   Table '$table' now has $recordCount records");
                
            } catch (\Exception $e) {
                $this->command->error("❌ Failed to import $table: " . $e->getMessage());
            }
        }

        $this->command->info('✅ Reference tables seeding completed!');
    }
}
