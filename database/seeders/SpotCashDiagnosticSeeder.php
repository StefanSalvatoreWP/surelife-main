<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class SpotCashDiagnosticSeeder extends Seeder
{
    /**
     * Run diagnostic tests for Spot Cash Approval workflow.
     * This will identify why payments aren't showing in approval list.
     */
    public function run()
    {
        $this->command->info('🔍 SPOT CASH DIAGNOSTIC TEST');
        $this->command->info('================================');
        
        // Test 1: Check Payment Terms
        $this->command->info('\n📋 Test 1: Checking Payment Terms...');
        $paymentTerms = DB::table('tblpaymentterm')->get();
        
        $this->command->info('Found ' . count($paymentTerms) . ' payment terms:');
        foreach ($paymentTerms as $term) {
            $this->command->info("  - ID: {$term->Id}, Term: '{$term->Term}'");
        }
        
        $spotcashTerm = $paymentTerms->firstWhere('Term', 'Spotcash') ?? $paymentTerms->firstWhere('Term', 'Spot-Cash');
        if (!$spotcashTerm) {
            $this->command->error('❌ CRITICAL: No payment term with exact name "Spotcash" found!');
            $this->command->error('   This will break the spot cash approval workflow.');
        } else {
            $this->command->info("✅ Spotcash term found: ID = {$spotcashTerm->Id}");
        }
        
        // Test 2: Check Recent Clients with Spotcash
        $this->command->info('\n📋 Test 2: Checking Recent Clients with Spotcash...');
        $recentClients = DB::table('tblclient')
            ->leftJoin('tblpaymentterm', 'tblclient.PaymentTermId', '=', 'tblpaymentterm.Id')
            ->select('tblclient.*', 'tblpaymentterm.Term')
            ->where('tblpaymentterm.Term', 'Spotcash')
            ->orWhere('tblpaymentterm.Term', 'Spot-Cash')
            ->orderBy('tblclient.id', 'desc')
            ->limit(5)
            ->get();
            
        if ($recentClients->isEmpty()) {
            $this->command->warn('⚠️ No clients with Spotcash payment term found.');
        } else {
            $this->command->info('Found ' . count($recentClients) . ' recent clients with Spotcash:');
            foreach ($recentClients as $client) {
                $this->command->info("  - Client ID: {$client->id}, Name: {$client->LastName}, {$client->FirstName}");
                $this->command->info("    PaymentTermId: {$client->PaymentTermId}, Term: {$client->Term}");
            }
        }
        
        // Test 3: Check Payments for Spotcash Clients
        $this->command->info('\n📋 Test 3: Checking Payments for Spotcash Clients...');
        
        if ($recentClients->isNotEmpty()) {
            foreach ($recentClients as $client) {
                $payments = DB::table('tblpayment')
                    ->where('clientid', $client->id)
                    ->orderBy('id', 'desc')
                    ->get();
                    
                if ($payments->isEmpty()) {
                    $this->command->warn("⚠️ Client {$client->id} has NO payments!");
                } else {
                    $this->command->info("Client {$client->id} payments:");
                    foreach ($payments as $payment) {
                        $status = $payment->approval_status ?? 'NULL';
                        $this->command->info("  - Payment ID: {$payment->id}, Amount: {$payment->amountpaid}");
                        $this->command->info("    approval_status: {$status}, voidstatus: {$payment->voidstatus}");
                    }
                }
            }
        }
        
        // Test 4: Check Spot Cash Approval Query
        $this->command->info('\n📋 Test 4: Testing Spot Cash Approval Query...');
        $pendingPayments = DB::table('tblpayment')
            ->select('tblpayment.*', 'tblclient.LastName', 'tblclient.FirstName')
            ->leftJoin('tblclient', 'tblpayment.clientid', '=', 'tblclient.id')
            ->where('tblpayment.approval_status', 'Pending')
            ->where('tblpayment.voidstatus', '<>', 1)
            ->orderBy('tblpayment.datecreated', 'desc')
            ->get();
            
        $this->command->info('Payments with approval_status = "Pending": ' . count($pendingPayments));
        
        if ($pendingPayments->isEmpty()) {
            $this->command->warn('⚠️ No pending payments found in the approval queue.');
        } else {
            foreach ($pendingPayments as $payment) {
                $this->command->info("  - Payment ID: {$payment->id}, Client: {$payment->LastName}");
            }
        }
        
        // Test 5: Summary
        $this->command->info('\n📋 DIAGNOSTIC SUMMARY:');
        $this->command->info('================================');
        
        $hasSpotcashTerm = $spotcashTerm !== null;
        $hasSpotcashClients = $recentClients->isNotEmpty();
        $hasPendingPayments = $pendingPayments->isNotEmpty();
        
        if (!$hasSpotcashTerm) {
            $this->command->error('❌ ISSUE: Missing "Spotcash" or "Spot-Cash" payment term in database');
            $this->command->info('   FIX: Add payment term with Term = "Spotcash" or "Spot-Cash"');
        }
        
        if ($hasSpotcashClients && !$hasPendingPayments) {
            $this->command->error('❌ ISSUE: Clients have Spotcash but payments have approval_status = NULL');
            $this->command->info('   This means the fix was applied AFTER these clients were created.');
            $this->command->info('   FIX: Only NEW clients created AFTER the code fix will trigger approval.');
        }
        
        if ($hasSpotcashTerm && !$hasSpotcashClients) {
            $this->command->warn('⚠️ No clients with Spotcash term found. Create a test client to verify workflow.');
        }
        
        if ($hasPendingPayments) {
            $this->command->info('✅ Spot Cash Approval workflow is working!');
        }
        
        $this->command->info('\n🎯 NEXT STEPS:');
        $this->command->info('1. Create a NEW client with Spotcash payment term');
        $this->command->info('2. Submit the form');
        $this->command->info('3. Check if you see message: "Spot cash payment submitted for approval"');
        $this->command->info('4. Go to Transactions → Spot Cash Approval');
        $this->command->info('5. Payment should appear with "Pending" status');
    }
}
