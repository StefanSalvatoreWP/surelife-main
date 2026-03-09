<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UpdateTestClientOrRecords extends Seeder
{
    public function run(): void
    {
        $this->command->info('Updating OR records for test clients...');

        // Get first OR batch from system
        $batch = DB::table('tblorbatch')->first();
        
        if (!$batch) {
            $this->command->error('No OR batch found in system!');
            return;
        }

        $this->command->info("Using OR Batch ID: {$batch->Id}, SeriesCode: {$batch->SeriesCode}");

        // Update payments for all test clients
        $testClients = ['TESTCLIENT001', 'TESTCLIENT002', 'TESTCLIENT003'];
        
        foreach ($testClients as $contractNumber) {
            $client = DB::table('tblclient')->where('ContractNumber', $contractNumber)->first();
            
            if (!$client) {
                $this->command->warn("Client {$contractNumber} not found");
                continue;
            }

            $payments = DB::table('tblpayment')
                ->where('ClientId', $client->Id)
                ->whereNull('ORId')
                ->get();

            $updated = 0;
            foreach ($payments as $payment) {
                // Create OR record
                $orId = DB::table('tblofficialreceipt')->insertGetId([
                    'orbatchid' => $batch->Id,
                    'ornumber' => $payment->ORNo,
                    'Status' => 1, // Used
                ]);

                // Update payment with ORId
                DB::table('tblpayment')
                    ->where('Id', $payment->Id)
                    ->update(['ORId' => $orId]);

                $updated++;
            }

            $this->command->info("Updated {$updated} payments for {$contractNumber}");
        }

        $this->command->info('OR records updated successfully!');
    }
}
