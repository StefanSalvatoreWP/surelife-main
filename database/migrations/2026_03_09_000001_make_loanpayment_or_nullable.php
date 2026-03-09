<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Make ORNo and ORId nullable in tblloanpayment to allow loan payments without OR.
     */
    public function up(): void
    {
        Schema::table('tblloanpayment', function (Blueprint $table) {
            $table->string('orno', 50)->nullable()->change();
            $table->unsignedBigInteger('orid')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tblloanpayment', function (Blueprint $table) {
            $table->string('orno', 50)->nullable(false)->change();
            $table->unsignedBigInteger('orid')->nullable(false)->change();
        });
    }
};
