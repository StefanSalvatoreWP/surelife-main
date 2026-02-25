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
        // Disable strict mode to allow invalid dates (e.g. 0000-00-00) during table rebuild
        DB::statement("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION'");

        // Check if the primary key exists before trying to add it, or just set AUTO_INCREMENT
        // Only modify to AUTO_INCREMENT, assuming PK exists (since error says "Multiple primary key defined")
        DB::statement("ALTER TABLE tblclient MODIFY Id BIGINT(20) NOT NULL AUTO_INCREMENT");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverting this is tricky without knowing exact previous state, 
        // but assuming it was just BIGINT NOT NULL without auto-inc/PK
        DB::statement("ALTER TABLE tblclient MODIFY Id BIGINT(20) NOT NULL");
        // Dropping primary key might error if it didn't exist, so we leave it or try-catch
        try {
            DB::statement("ALTER TABLE tblclient DROP PRIMARY KEY");
        } catch (\Exception $e) {
            // Ignore if no PK existed
        }
    }
};
