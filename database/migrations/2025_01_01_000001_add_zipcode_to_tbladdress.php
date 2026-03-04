<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds zipcode column to tbladdress table.
     * Note: Data population is handled by AddressSeeder
     */
    public function up(): void
    {
        // Skip if table doesn't exist (original DB may not have it)
        if (!Schema::hasTable('tbladdress')) {
            return;
        }
        
        // Check if column exists first (idempotent)
        $exists = DB::select("SHOW COLUMNS FROM tbladdress LIKE 'zipcode'");
        
        if (empty($exists)) {
            DB::statement("ALTER TABLE tbladdress ADD COLUMN zipcode VARCHAR(10) NULL AFTER citymun_code");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('tbladdress')) {
            return;
        }
        DB::statement("ALTER TABLE tbladdress DROP COLUMN zipcode");
    }
};
