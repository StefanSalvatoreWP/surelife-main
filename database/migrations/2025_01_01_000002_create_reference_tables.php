<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop existing tables to ensure a clean slate if re-running
        Schema::dropIfExists('refbrgy');
        Schema::dropIfExists('refcitymun');
        Schema::dropIfExists('refprovince');

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
        });

        // Create refbrgy table
        Schema::create('refbrgy', function (Blueprint $table) {
            $table->id();
            $table->string('brgyCode')->nullable();
            $table->string('brgyDesc');
            $table->string('regCode')->nullable();
            $table->string('provCode')->nullable();
            $table->string('citymunCode')->nullable();
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
                \Log::info("Processing SQL file for table $table: $file");
                $handle = fopen($file, 'r');
                if ($handle) {
                    DB::beginTransaction();
                    try {
                        $buffer = '';
                        while (($line = fgets($handle)) !== false) {
                            $trimmed = trim($line);
                            if (empty($trimmed) || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*')) {
                                continue;
                            }
                            $buffer .= $line;
                            if (str_ends_with($trimmed, ';')) {
                                $statement = trim($buffer);
                                if (str_starts_with(strtoupper($statement), 'INSERT')) {
                                    DB::statement($statement);
                                }
                                $buffer = '';
                            }
                        }
                        DB::commit();
                        $count = DB::table($table)->count();
                        \Log::info("Successfully imported $table. Total records: $count");
                    } catch (\Exception $e) {
                        DB::rollBack();
                        \Log::error("Failed to import table $table: " . $e->getMessage());
                    }
                    fclose($handle);
                }
            } else {
                \Log::warning("Reference SQL file not found: $file");
            }
        }
    }
};
