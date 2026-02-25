<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tblorbatch', function (Blueprint $table) {
            $table->index(['BranchId', 'RegionId', 'Type', 'SeriesCode'], 'idx_orbatch_brts');
            $table->index('SeriesCode', 'idx_orbatch_seriescode');
        });

        Schema::table('tblofficialreceipt', function (Blueprint $table) {
            $table->index('orbatchid', 'idx_officialreceipt_orbatchid');
            $table->index(['orbatchid', 'Status'], 'idx_officialreceipt_orbatch_status');
            $table->index('ornumber', 'idx_officialreceipt_ornumber');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tblorbatch', function (Blueprint $table) {
            $table->dropIndex('idx_orbatch_brts');
            $table->dropIndex('idx_orbatch_seriescode');
        });

        Schema::table('tblofficialreceipt', function (Blueprint $table) {
            $table->dropIndex('idx_officialreceipt_orbatchid');
            $table->dropIndex('idx_officialreceipt_orbatch_status');
            $table->dropIndex('idx_officialreceipt_ornumber');
        });
    }
};
