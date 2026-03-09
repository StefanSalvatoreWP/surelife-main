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
        Schema::table('tblsms', function (Blueprint $table) {
            // Status column already exists, add only new fields
            $table->text('gateway_response')->nullable()->after('Status');
            $table->timestamp('sent_at')->nullable()->after('gateway_response');
            $table->string('reference_type', 50)->nullable()->after('sent_at')->comment('payment, loan, spotcash');
            $table->unsignedBigInteger('reference_id')->nullable()->after('reference_type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tblsms', function (Blueprint $table) {
            $table->dropColumn(['gateway_response', 'sent_at', 'reference_type', 'reference_id', 'created_at', 'updated_at']);
        });
    }
};
