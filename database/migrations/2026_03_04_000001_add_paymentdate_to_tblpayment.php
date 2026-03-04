<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds paymentdate column to tblpayment table if missing.
     * This column is required for loan eligibility checking.
     */
    public function up(): void
    {
        if (!Schema::hasTable('tblpayment')) {
            return;
        }

        // Check if column already exists
        if (Schema::hasColumn('tblpayment', 'paymentdate')) {
            return;
        }

        Schema::table('tblpayment', function (Blueprint $table) {
            $table->date('paymentdate')->nullable()->after('amountpaid')
                ->comment('Date when payment was made');
        });

        // Add index for faster queries
        Schema::table('tblpayment', function (Blueprint $table) {
            $table->index('paymentdate');
            $table->index('clientid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('tblpayment')) {
            return;
        }

        Schema::table('tblpayment', function (Blueprint $table) {
            if (Schema::hasColumn('tblpayment', 'paymentdate')) {
                $table->dropColumn('paymentdate');
            }
        });
    }
};
