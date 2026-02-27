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
            $table->string('principalbeneficiaryrelation')->nullable()->after('principalbeneficiaryage');
            $table->string('principalbeneficiaryid_path')->nullable()->after('principalbeneficiaryrelation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tblclient', function (Blueprint $table) {
            $table->dropColumn(['principalbeneficiaryrelation', 'principalbeneficiaryid_path']);
        });
    }
};
