<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SpotCashDebugSeeder extends Seeder
{
    /**
     * Run detailed diagnostics for Spot Cash Approval issue.
     */
    public function run()
    {
        $this->command->info('🔍 SPOT CASH DEBUG - Checking Database State');
        $this->command->info('================================================');
        
        // 1. Check if approval_status columns exist in tblpayment
        $this->command->info('\n📋 CHECK 1: tblpayment columns');
        $columns = DB::select("SHOW COLUMNS FROM tblpayment");
        $columnNames = array_map(fn($col) => $col->Field, $columns);
        
        $requiredColumns = ['approval_status', 'approved_by', 'approved_at', 'approval_remarks'];
        foreach ($requiredColumns as $col) {
            if (in_array($col, $columnNames)) {
                $this->command->info("  ✅ Column '{$col}' EXISTS");
            } else {
                $this->command->error("  ❌ Column '{$col}' MISSING - Run migration!");
            }
        }
        
        // 2. Check most recent payments
        $this->command->info('\n📋 CHECK 2: Most recent payments');
        $recentPayments = DB::table('tblpayment')
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();
        
        if ($recentPayments->isEmpty()) {
            $this->command->warn('  ⚠️ No payments found in tblpayment');
        } else {
            foreach ($recentPayments as $payment) {
                $p = (array) $payment;
                $id = $p['Id'] ?? 'N/A';
                $clientid = $p['clientid'] ?? 'N/A';
                $status = $p['approval_status'] ?? 'NULL';
                $this->command->info("  Payment ID: {$id}, Client: {$clientid}, Status: {$status}");
            }
        }
        
        // 3. Check recent clients with their payment terms
        $this->command->info('\n📋 CHECK 3: Recent clients with payment terms');
        $recentClients = DB::table('tblclient')
            ->leftJoin('tblpaymentterm', 'tblclient.PaymentTermId', '=', 'tblpaymentterm.Id')
            ->select('tblclient.id', 'tblclient.LastName', 'tblclient.PaymentTermId', 'tblpaymentterm.Term')
            ->orderBy('tblclient.id', 'desc')
            ->limit(10)
            ->get();
        
        foreach ($recentClients as $client) {
            $c = (array) $client;
            $term = $c['Term'] ?? 'NULL';
            $this->command->info("  Client ID: {$c['id']}, Name: {$c['LastName']}, Term: {$term}");
        }
        
        // 4. Check Spotcash clients specifically
        $this->command->info('\n📋 CHECK 4: Spotcash clients and their payments');
        $spotcashClients = DB::table('tblclient')
            ->leftJoin('tblpaymentterm', 'tblclient.PaymentTermId', '=', 'tblpaymentterm.Id')
            ->select('tblclient.id', 'tblclient.LastName')
            ->where('tblpaymentterm.Term', 'Spotcash')
            ->orderBy('tblclient.id', 'desc')
            ->limit(5)
            ->get();
        
        if ($spotcashClients->isEmpty()) {
            $this->command->warn('  ⚠️ No Spotcash clients found');
        } else {
            foreach ($spotcashClients as $client) {
                $c = (array) $client;
                $this->command->info("  Client ID: {$c['id']}, Name: {$c['LastName']}");
                
                // Check their payments
                $payments = DB::table('tblpayment')
                    ->where('clientid', $c['id'])
                    ->get();
                
                if ($payments->isEmpty()) {
                    $this->command->warn("    ⚠️ No payments for this client");
                } else {
                    foreach ($payments as $payment) {
                        $p = (array) $payment;
                        $status = $p['approval_status'] ?? 'NULL';
                        $this->command->info("    Payment ID: {$p['Id']}, Status: {$status}");
                    }
                }
            }
        }
        
        // 5. Check pending approval query
        $this->command->info('\n📋 CHECK 5: Pending approval query result');
        $pendingCount = DB::table('tblpayment')
            ->where('approval_status', 'Pending')
            ->where(function ($q) {
                $q->where('voidstatus', '<>', 1)
                    ->orWhereNull('voidstatus');
            })
            ->count();

        $this->command->info("  Found {$pendingCount} pending payments");
        
        $this->command->info('\n✅ Debug complete. Review results above.');
    }
}
