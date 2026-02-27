<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds zipcode column to tbladdress table.
     * Note: Data population is handled by AddressSeeder
     */
    public function up(): void
    {
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
        DB::statement("ALTER TABLE tbladdress DROP COLUMN zipcode");
    }
};
