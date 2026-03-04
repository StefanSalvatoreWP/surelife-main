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
        if (!Schema::hasTable('tblclient')) {
            return;
        }
        Schema::table('tblclient', function (Blueprint $table) {
            if (!Schema::hasColumn('tblclient', 'HomeRegion')) {
                $table->string('HomeRegion', 100)->nullable()->after('Street');
            }
            if (!Schema::hasColumn('tblclient', 'HomeProvince')) {
                $table->string('HomeProvince', 100)->nullable()->after('HomeRegion');
            }
            if (!Schema::hasColumn('tblclient', 'HomeCity')) {
                $table->string('HomeCity', 100)->nullable()->after('HomeProvince');
            }
            if (!Schema::hasColumn('tblclient', 'HomeBarangay')) {
                $table->string('HomeBarangay', 100)->nullable()->after('HomeCity');
            }
            if (!Schema::hasColumn('tblclient', 'homezipcode')) {
                $table->string('homezipcode', 10)->nullable()->after('homebarangay');
            }
            if (!Schema::hasColumn('tblclient', 'HomeStreet')) {
                $table->string('HomeStreet', 100)->nullable()->after('homezipcode');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('tblclient')) {
            return;
        }
        Schema::table('tblclient', function (Blueprint $table) {
            $table->dropColumn([
                'HomeRegion',
                'HomeProvince',
                'HomeCity',
                'HomeBarangay',
                'homezipcode',
                'HomeStreet'
            ]);
        });
    }
};
