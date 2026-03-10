<?php

/**
 * ============================================================================
 * TEST CLIENT RESET TOOL
 * ============================================================================
 * 
 * Resets test client data to default state without loan history
 * - Clears loan requests and payments
 * - Resets client status to APPROVED (eligible for loan)
 * - Preserves basic client information
 * - Ensures client is ELIGIBLE for new loan request
 * 
 * Usage: php database/tools/resetTool/reset_test_clients.php
 * 
 * ============================================================================
 */

require_once __DIR__ . '/../../../vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class TestClientResetTool
{
    /**
     * Reset test clients to default state - ELIGIBLE FOR LOAN
     */
    public function reset(): void
    {
        echo "🔧 Starting test client reset...\n";
        echo "═══════════════════════════════════════════════════════════════\n\n";
        
        // Test client contract numbers
        $testClients = ['TESTCLIENT001', 'TESTCLIENT002', 'TESTCLIENT003', 'TESTCLIENT004'];
        
        foreach ($testClients as $contractNumber) {
            $this->resetClient($contractNumber);
        }
        
        echo "\n═══════════════════════════════════════════════════════════════\n";
        echo "✅ TEST CLIENT RESET COMPLETE!\n";
        echo "═══════════════════════════════════════════════════════════════\n\n";
        
        echo "📊 RESET SUMMARY:\n";
        echo "   • Cleared all loan requests\n";
        echo "   • Cleared all loan payments\n";
        echo "   • Reset client status to APPROVED (eligible for loan)\n";
        echo "   • Preserved basic client data\n";
        echo "   • Clients are now ELIGIBLE for new loan request\n\n";
        
        echo "🔐 LOGIN CREDENTIALS:\n";
        echo "   Username: TESTCLIENT001/002/003\n";
        echo "   Password: password123\n";
        echo "   Access Key: a8821dd1f\n\n";
    }
    
    /**
     * Reset individual client - keep ELIGIBLE for loan
     */
    private function resetClient(string $contractNumber): void
    {
        echo "🔄 Resetting client: {$contractNumber}\n";
        
        try {
            // Get client info
            $client = DB::table('tblclient')
                ->where('ContractNumber', $contractNumber)
                ->first();
                
            if (!$client) {
                echo "   ⚠️  Client not found. Skipping...\n";
                return;
            }
            
            // Step 1: Delete loan payments (must be done before loan requests)
            $loanRequests = DB::table('tblloanrequest')
                ->where('clientid', $client->Id)
                ->get();
                
            foreach ($loanRequests as $loan) {
                DB::table('tblloanpayment')
                    ->where('loanrequestid', $loan->Id)
                    ->delete();
            }
            
            // Step 2: Delete loan requests
            $deletedRequests = DB::table('tblloanrequest')
                ->where('clientid', $client->Id)
                ->delete();
            
            // Step 3: Reset client status to APPROVED (Status = 3) - ELIGIBLE FOR LOAN
            // Do NOT reset to Pending (Status = 1) as that makes them ineligible
            DB::table('tblclient')
                ->where('Id', $client->Id)
                ->update([
                    'Status' => 3, // APPROVED - eligible for loan
                    'Remarks' => null,
                    'FSAComsRem' => 0,
                    'PercentComm' => 0,
                    'CommAmount' => 0,
                    'PercentTAC' => 0,
                    'TACAmount' => 0,
                    'NetRem' => 0,
                    'TAP' => 0,
                ]);
            
            echo "   ✅ Deleted {$deletedRequests} loan requests\n";
            echo "   ✅ Reset client status to APPROVED (eligible for loan)\n";
            
        } catch (\Exception $e) {
            echo "   ❌ Error: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
}

// Run the reset tool
if (php_sapi_name() === 'cli') {
    // Initialize Laravel if available
    if (file_exists(__DIR__ . '/../../../bootstrap/app.php')) {
        $app = require_once __DIR__ . '/../../../bootstrap/app.php';
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();
    }
    
    $tool = new TestClientResetTool();
    $tool->reset();
} else {
    die("This script must be run from the command line.\n");
}
