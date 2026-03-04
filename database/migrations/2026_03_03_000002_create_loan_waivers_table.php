<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates loan_waivers table if it doesn't exist (for original DB compatibility)
     */
    public function up(): void
    {
        if (Schema::hasTable('loan_waivers')) {
            return;
        }

        Schema::create('loan_waivers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loan_request_id');
            $table->dateTime('signed_date');
            $table->text('signature_data')->nullable();
            $table->string('client_name')->nullable();
            $table->string('contract_number')->nullable();
            $table->timestamps();

            $table->index('loan_request_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_waivers');
    }
};
