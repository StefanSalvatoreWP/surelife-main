<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates tblloanrequest table if it doesn't exist (for original DB compatibility)
     */
    public function up(): void
    {
        if (Schema::hasTable('tblloanrequest')) {
            return;
        }

        Schema::create('tblloanrequest', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->unsignedBigInteger('clientid');
            $table->unsignedBigInteger('contract_id')->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->decimal('net_loan_amount', 10, 2)->default(0);
            $table->decimal('processing_fee', 10, 2)->default(0);
            $table->decimal('interest_rate', 5, 2)->default(1.25);
            $table->decimal('monthlyamount', 10, 2)->default(0);
            $table->integer('term_months')->default(12);
            $table->decimal('total_repayable', 10, 2)->default(0);
            $table->dateTime('daterequested')->nullable();
            $table->string('status', 50)->default('Pending');
            $table->text('remarks')->nullable();
            $table->string('code', 50)->nullable();
            $table->boolean('waiver_signed')->default(false);
            $table->dateTime('waiver_signed_date')->nullable();
            $table->integer('premium_paid_percent')->default(0);
            $table->dateTime('datecreated')->nullable();
            $table->unsignedBigInteger('createdby')->nullable();
            $table->unsignedBigInteger('approvedby')->nullable();
            $table->dateTime('approveddate')->nullable();
            $table->dateTime('completeddate')->nullable();

            $table->index('clientid');
            $table->index('contract_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tblloanrequest');
    }
};
