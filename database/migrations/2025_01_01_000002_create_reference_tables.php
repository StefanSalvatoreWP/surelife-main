<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create refprovince table
        Schema::create('refprovince', function (Blueprint $table) {
            $table->id();
            $table->string('psgcCode')->nullable();
            $table->text('provDesc');
            $table->string('regCode')->nullable();
            $table->string('provCode')->nullable();
        });

        // Create refcitymun table
        Schema::create('refcitymun', function (Blueprint $table) {
            $table->id();
            $table->string('psgcCode')->nullable();
            $table->text('citymunDesc');
            $table->string('regDesc')->nullable();
            $table->string('provCode')->nullable();
            $table->string('citymunCode')->nullable();
            $table->string('distCode')->nullable();
            $table->string('citymunCode')->nullable();
        });

        // Create refbrgy table
        Schema::create('refbrgy', function (Blueprint $table) {
            $table->id();
            $table->string('brgyCode')->nullable();
            $table->string('brgyDesc');
            $table->string('regCode')->nullable();
            $table->string('provCode')->nullable();
            $table->string('citymunCode')->nullable();
            $table->string('distCode')->nullable();
        });

        // Import data from SQL files
        $this->importReferenceData();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refbrgy');
        Schema::dropIfExists('refcitymun');
        Schema::dropIfExists('refprovince');
    }

    /**
     * Import reference data from SQL files
     */
    private function importReferenceData(): void
    {
        $sqlFiles = [
            'refprovince' => database_path('refProvince.sql'),
            'refcitymun' => database_path('refCitymun.sql'),
            'refbrgy' => database_path('refBrgy.sql')
        ];

        foreach ($sqlFiles as $table => $file) {
            if (!file_exists($file)) {
                // Try alternative path
                $file = base_path('address/' . basename($file));
            }

            if (file_exists($file)) {
                $sql = file_get_contents($file);
                
                // Remove CREATE TABLE statements and keep only INSERT statements
                $sql = preg_replace('/CREATE TABLE.*?ENGINE=.*?;$/s', '', $sql);
                $sql = preg_replace('/SET FOREIGN_KEY_CHECKS=.*?;$/s', '', $sql);
                $sql = preg_replace('/DROP TABLE IF EXISTS.*?;$/s', '', $sql);
                
                // Split into individual statements
                $statements = array_filter(array_map('trim', explode(';', $sql)));
                
                foreach ($statements as $statement) {
                    if (empty($statement) || !str_starts_with($statement, 'INSERT')) {
                        continue;
                    }
                    
                    try {
                        DB::statement($statement);
                    } catch (\Exception $e) {
                        // Log error but continue with other statements
                        \Log::warning("Failed to import reference data statement: " . $e->getMessage());
                    }
                }
                
                \Log::info("Imported reference data for table: $table");
            } else {
                \Log::warning("Reference SQL file not found: $file");
            }
        }
    }
};
