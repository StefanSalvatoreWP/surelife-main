<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('tbluser')) {
            return;
        }
        Schema::table('tbluser', function (Blueprint $table) {
            if (!Schema::hasColumn('tbluser', 'AccessKey')) {
                $table->string('AccessKey', 255)->nullable()->after('RoleId');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('tbluser')) {
            return;
        }
        Schema::table('tbluser', function (Blueprint $table) {
            $table->dropColumn(['AccessKey']);
        });
    }
};
