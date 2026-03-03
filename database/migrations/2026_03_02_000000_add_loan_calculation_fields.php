<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds loan calculation fields to existing tblloanrequest
     * Creates loan_waivers table for digital waiver storage
     */
    public function up(): void
    {
        // Add missing fields to existing tblloanrequest
        Schema::table('tblloanrequest', function (Blueprint $table) {
            if (!Schema::hasColumn('tblloanrequest', 'processing_fee')) {
                $table->decimal('processing_fee', 10, 2)->default(0)->after('amount');
            }
            if (!Schema::hasColumn('tblloanrequest', 'interest_rate')) {
                $table->decimal('interest_rate', 5, 2)->default(1.25)->after('processing_fee');
            }
            if (!Schema::hasColumn('tblloanrequest', 'term_months')) {
                $table->integer('term_months')->default(12)->after('interest_rate');
            }
            if (!Schema::hasColumn('tblloanrequest', 'total_repayable')) {
                $table->decimal('total_repayable', 10, 2)->default(0)->after('term_months');
            }
            if (!Schema::hasColumn('tblloanrequest', 'waiver_signed')) {
                $table->boolean('waiver_signed')->default(false)->after('total_repayable');
            }
            if (!Schema::hasColumn('tblloanrequest', 'waiver_signed_date')) {
                $table->dateTime('waiver_signed_date')->nullable()->after('waiver_signed');
            }
            if (!Schema::hasColumn('tblloanrequest', 'premium_paid_percent')) {
                $table->integer('premium_paid_percent')->default(0)->after('waiver_signed_date');
            }
            if (!Schema::hasColumn('tblloanrequest', 'contract_id')) {
                $table->unsignedBigInteger('contract_id')->nullable()->after('clientid');
                $table->index('contract_id');
            }
            if (!Schema::hasColumn('tblloanrequest', 'net_loan_amount')) {
                $table->decimal('net_loan_amount', 10, 2)->default(0)->after('amount');
            }
        });

        // Create loan_waivers table if not exists
        if (!Schema::hasTable('loan_waivers')) {
            Schema::create('loan_waivers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('loan_request_id');
                $table->dateTime('signed_date');
                $table->text('signature_data')->nullable(); // Base64 signature image
                $table->string('client_name')->nullable();
                $table->string('contract_number')->nullable();
                $table->timestamps();

                $table->index('loan_request_id');
                // Foreign key removed - tblloanrequest may use different Id type
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tblloanrequest', function (Blueprint $table) {
            $columns = [
                'processing_fee',
                'interest_rate',
                'term_months',
                'total_repayable',
                'waiver_signed',
                'waiver_signed_date',
                'premium_paid_percent',
                'contract_id',
                'net_loan_amount'
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('tblloanrequest', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('loan_waivers');
    }
};
