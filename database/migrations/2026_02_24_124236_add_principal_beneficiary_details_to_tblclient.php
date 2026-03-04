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
            if (!Schema::hasColumn('tblclient', 'principalbeneficiaryrelation')) {
                $table->string('principalbeneficiaryrelation')->nullable()->after('principalbeneficiaryage');
            }
            if (!Schema::hasColumn('tblclient', 'principalbeneficiaryid_path')) {
                $table->string('principalbeneficiaryid_path')->nullable()->after('principalbeneficiaryrelation');
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
            $table->dropColumn(['principalbeneficiaryrelation', 'principalbeneficiaryid_path']);
        });
    }
};
