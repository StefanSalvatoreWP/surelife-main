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
        Schema::table('tblclient', function (Blueprint $table) {
            $table->string('HomeRegion', 100)->nullable()->after('Street');
            $table->string('HomeProvince', 100)->nullable()->after('HomeRegion');
            $table->string('HomeCity', 100)->nullable()->after('HomeProvince');
            $table->string('HomeBarangay', 100)->nullable()->after('HomeCity');
            $table->string('HomeZipCode', 10)->nullable()->after('HomeBarangay');
            $table->string('HomeStreet', 100)->nullable()->after('HomeZipCode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tblclient', function (Blueprint $table) {
            $table->dropColumn([
                'HomeRegion',
                'HomeProvince',
                'HomeCity',
                'HomeBarangay',
                'HomeZipCode',
                'HomeStreet'
            ]);
        });
    }
};
