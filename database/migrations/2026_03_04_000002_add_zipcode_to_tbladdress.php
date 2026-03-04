<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds zipcode column to tbladdress table if missing.
     * This column is required for storing zip/postal codes of cities/municipalities.
     */
    public function up(): void
    {
        if (!Schema::hasTable('tbladdress')) {
            return;
        }

        // Check if column already exists
        if (Schema::hasColumn('tbladdress', 'zipcode')) {
            return;
        }

        Schema::table('tbladdress', function (Blueprint $table) {
            $table->string('zipcode', 10)->nullable()->after('citymun_code')
                ->comment('Zip/Postal code for cities/municipalities');
        });

        // Add index for faster zip code lookups
        Schema::table('tbladdress', function (Blueprint $table) {
            $table->index('zipcode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('tbladdress')) {
            return;
        }

        Schema::table('tbladdress', function (Blueprint $table) {
            if (Schema::hasColumn('tbladdress', 'zipcode')) {
                $table->dropColumn('zipcode');
            }
        });
    }
};
