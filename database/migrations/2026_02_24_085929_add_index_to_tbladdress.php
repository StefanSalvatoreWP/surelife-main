<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Use raw SQL so we can specify prefix lengths for TEXT/VARCHAR columns
        // MySQL requires prefix length for TEXT columns in indexes
        try {
            DB::statement('CREATE INDEX idx_tbladdress_code_type ON tbladdress (code(50), address_type(20))');
        } catch (\Exception $e) {
            // Index may already exist - ignore duplicate key name error
            if (!str_contains($e->getMessage(), 'Duplicate key name')) {
                throw $e;
            }
        }
    }

    public function down(): void
    {
        try {
            DB::statement('DROP INDEX idx_tbladdress_code_type ON tbladdress');
        } catch (\Exception $e) {
            // Ignore if index doesn't exist
        }
    }
};
