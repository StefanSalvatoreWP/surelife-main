<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToPaymentTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tblpayment', function (Blueprint $table) {
            $table->index('clientid', 'idx_payment_clientid');
            $table->index('orid', 'idx_payment_orid');
        });

        Schema::table('tblloanrequest', function (Blueprint $table) {
            $table->index('clientid', 'idx_loanrequest_clientid');
        });

        Schema::table('tblloanpayment', function (Blueprint $table) {
            $table->index('clientid', 'idx_loanpayment_clientid');
            $table->index('loanrequestid', 'idx_loanpayment_requestid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tblpayment', function (Blueprint $table) {
            $table->dropIndex('idx_payment_clientid');
            $table->dropIndex('idx_payment_orid');
        });

        Schema::table('tblloanrequest', function (Blueprint $table) {
            $table->dropIndex('idx_loanrequest_clientid');
        });

        Schema::table('tblloanpayment', function (Blueprint $table) {
            $table->dropIndex('idx_loanpayment_clientid');
            $table->dropIndex('idx_loanpayment_requestid');
        });
    }
}
