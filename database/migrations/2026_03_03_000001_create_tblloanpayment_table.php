<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates tblloanpayment table if it doesn't exist (for original DB compatibility)
     */
    public function up(): void
    {
        if (Schema::hasTable('tblloanpayment')) {
            return;
        }

        Schema::create('tblloanpayment', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->unsignedBigInteger('clientid');
            $table->unsignedBigInteger('loanrequestid');
            $table->string('orno', 50)->nullable();
            $table->unsignedBigInteger('orid')->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->integer('installment')->default(1);
            $table->string('paymentmethod', 50)->nullable();
            $table->dateTime('paymentdate')->nullable();
            $table->dateTime('datecreated')->nullable();
            $table->unsignedBigInteger('createdby')->nullable();
            $table->string('status', 20)->default('active');

            $table->index('clientid');
            $table->index('loanrequestid');
            $table->index('orid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tblloanpayment');
    }
};
