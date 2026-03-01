<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds approval workflow columns to tblpayment table.
     */
    public function up(): void
    {
        // Check if columns exist before adding them
        $columns = DB::select("SHOW COLUMNS FROM tblpayment");
        $columnNames = array_map(fn($col) => $col->Field, $columns);
        
        Schema::table('tblpayment', function (Blueprint $table) use ($columnNames) {
            if (!in_array('approval_status', $columnNames)) {
                $table->string('approval_status', 20)->nullable()->after('remarks')
                    ->comment('Pending, Approved, Rejected');
            }
            
            if (!in_array('approved_by', $columnNames)) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('approval_status')
                    ->comment('User ID who approved/rejected');
            }
            
            if (!in_array('approved_at', $columnNames)) {
                $table->timestamp('approved_at')->nullable()->after('approved_by')
                    ->comment('Timestamp of approval/rejection');
            }
            
            if (!in_array('approval_remarks', $columnNames)) {
                $table->text('approval_remarks')->nullable()->after('approved_at')
                    ->comment('Remarks for approval/rejection');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tblpayment', function (Blueprint $table) {
            $table->dropColumn(['approval_status', 'approved_by', 'approved_at', 'approval_remarks']);
        });
    }
};
