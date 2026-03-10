<?php

/**
 * ============================================================================
 * RESET TEST CLIENT TO ELIGIBLE STATE
 * ============================================================================
 * 
 * Resets test clients based on TestClientSeeder pattern:
 * - TESTCLIENT001: GOLD tier (Status=3, 100% paid) - 45% loanable
 * - TESTCLIENT002: SILVER tier (Status=3, 80% paid) - 40% loanable
 * - TESTCLIENT003: BRONZE tier (Status=3, 60% paid) - 30% loanable
 * - TESTCLIENT004: NOT ELIGIBLE (Status=1 Pending) - for pre-eligibility testing
 * 
 * Usage: php database/tools/resetTool/reset_to_eligible.php
 * 
 * ============================================================================
 */

require_once __DIR__ . '/../../../vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ResetToEligible
{
    /**
     * Reset test clients - 2 eligible, 1 not eligible (per TestClientSeeder)
     */
    public function reset(): void
    {
        echo "🔧 Resetting test clients...\n";
        echo "═══════════════════════════════════════════════════════════════\n\n";
        
        $testClients = [
            [
                'contract_number' => 'TESTCLIENT001',
                'status' => 3, // Approved - GOLD TIER
                'tier' => '100%',
                'eligible' => true,
                'tier_name' => 'Gold',
            ],
            [
                'contract_number' => 'TESTCLIENT002',
                'status' => 3, // Approved - SILVER TIER
                'tier' => '80%',
                'eligible' => true,
                'tier_name' => 'Silver',
            ],
            [
                'contract_number' => 'TESTCLIENT003',
                'status' => 3, // Approved - BRONZE TIER
                'tier' => '60%',
                'eligible' => true,
                'tier_name' => 'Bronze',
            ],
            [
                'contract_number' => 'TESTCLIENT004',
                'status' => 1, // Pending - NOT ELIGIBLE
                'tier' => null, // No tier - not eligible
                'eligible' => false,
                'tier_name' => 'Not Eligible',
            ],
        ];
        
        foreach ($testClients as $clientData) {
            $this->resetClient($clientData);
        }
        
        echo "\n═══════════════════════════════════════════════════════════════\n";
        echo "✅ TEST CLIENTS RESET COMPLETE!\n";
        echo "═══════════════════════════════════════════════════════════════\n\n";
        
        echo "📊 ELIGIBILITY STATUS:\n";
        echo "   🥇 TESTCLIENT001: Status=3 (Approved), 100% tier → GOLD (45% loanable)\n";
        echo "   🥈 TESTCLIENT002: Status=3 (Approved), 80% tier → SILVER (40% loanable)\n";
        echo "   🥉 TESTCLIENT003: Status=3 (Approved), 60% tier → BRONZE (30% loanable)\n";
        echo "   ❌ TESTCLIENT004: Status=1 (Pending) → NOT ELIGIBLE\n\n";
        
        echo "🔐 LOGIN CREDENTIALS:\n";
        echo "   Username: TESTCLIENT001/002/003\n";
        echo "   Password: password123\n";
        echo "   Access Key: a8821dd1f\n\n";
    }
    
    /**
     * Reset individual client
     */
    private function resetClient(array $data): void
    {
        echo "🔄 Resetting: {$data['contract_number']}\n";
        
        try {
            $client = DB::table('tblclient')
                ->where('ContractNumber', $data['contract_number'])
                ->first();
            
            if (!$client) {
                echo "   ⚠️  Client not found. Run TestClientSeeder first.\n";
                return;
            }
            
            $clientId = $client->Id;
            $packagePrice = $client->PackagePrice ?? 5000;
            
            // Step 1: Delete loan payments
            $loanRequests = DB::table('tblloanrequest')
                ->where('clientid', $clientId)
                ->get();
            
            $loanPaymentCount = 0;
            foreach ($loanRequests as $loan) {
                $deleted = DB::table('tblloanpayment')
                    ->where('loanrequestid', $loan->Id)
                    ->delete();
                $loanPaymentCount += $deleted;
            }
            
            // Step 2: Delete loan requests
            $deletedRequests = DB::table('tblloanrequest')
                ->where('clientid', $clientId)
                ->delete();
            
            // Step 3: Delete existing premium payments
            DB::table('tblpayment')
                ->where('ClientId', $clientId)
                ->delete();
            
            // Step 4: Create premium payments based on tier (only for eligible clients)
            if ($data['eligible'] && $data['tier']) {
                $paymentAmount = $this->getPaymentAmountForTier($packagePrice, $data['tier']);
                $this->createPremiumPayments($clientId, $paymentAmount);
                echo "   ✅ Created ₱{$paymentAmount} premium payments ({$data['tier']} tier)\n";
            } else {
                echo "   ✅ No payments created (NOT ELIGIBLE - Status=Pending)\n";
            }
            
            // Step 5: Update client status
            DB::table('tblclient')
                ->where('Id', $clientId)
                ->update([
                    'Status' => $data['status'],
                    'Remarks' => null,
                ]);
            
            $statusText = $data['status'] === 3 ? 'APPROVED (Eligible)' : 'PENDING (Not Eligible)';
            
            echo "   ✅ Deleted {$deletedRequests} loan requests\n";
            echo "   ✅ Deleted {$loanPaymentCount} loan payments\n";
            echo "   ✅ Status set to {$statusText}\n";
            
        } catch (\Exception $e) {
            echo "   ❌ Error: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    /**
     * Get payment amount for tier eligibility
     */
    private function getPaymentAmountForTier(float $packagePrice, string $tier): float
    {
        $percentages = [
            '60%' => 0.60,
            '80%' => 0.80,
            '100%' => 1.00,
        ];
        
        $percentage = $percentages[$tier] ?? 0.60;
        return $packagePrice * $percentage;
    }
    
    /**
     * Create premium payment records (simplified - no OR records needed)
     */
    private function createPremiumPayments(int $clientId, float $totalAmount): void
    {
        $paymentCount = 5;
        $amountPerInstallment = $totalAmount / $paymentCount;
        
        for ($i = 1; $i <= $paymentCount; $i++) {
            $orNumber = 'R' . str_pad((string)$clientId, 4, '0', STR_PAD_LEFT) . $i;
            
            DB::table('tblpayment')->insert([
                'ClientId' => $clientId,
                'ORId' => null,
                'ORNo' => $orNumber,
                'AmountPaid' => $amountPerInstallment,
                'NetPayment' => $amountPerInstallment,
                'Date' => Carbon::now()->subMonths($paymentCount - $i)->format('Y-m-d'),
                'PaymentType' => 1, // Cash (integer)
                'IsCleared' => 1,
                'Status' => 1,
                'DateCreated' => Carbon::now()->subMonths($paymentCount - $i),
                'CreatedBy' => 1,
            ]);
        }
    }
}

// Run the tool
if (php_sapi_name() === 'cli') {
    if (file_exists(__DIR__ . '/../../../bootstrap/app.php')) {
        $app = require_once __DIR__ . '/../../../bootstrap/app.php';
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();
    }
    
    $tool = new ResetToEligible();
    $tool->reset();
} else {
    die("This script must be run from the command line.\n");
}
