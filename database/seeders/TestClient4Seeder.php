<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TestClient4Seeder extends Seeder
{
    /**
     * Run the database seeds for test client 4.
     * Tests that Reinstatement and Change Mode payments do NOT affect outstanding balance.
     */
    public function run()
    {
        // Get Package and Term IDs - Use Monthly term specifically
        $package = DB::table('tblpackage')->where('Active', 1)->first();
        $paymentTerm = DB::table('tblpaymentterm')
            ->where('PackageId', $package->Id)
            ->where('Term', 'Monthly')
            ->first();

        // Fallback if Monthly not found
        if (!$paymentTerm) {
            $paymentTerm = DB::table('tblpaymentterm')
                ->where('PackageId', $package->Id)
                ->where('Term', '!=', 'Spotcash')
                ->first();
        }

        // Get Region and Branch
        $region = DB::table('tblregion')->first();
        $branch = DB::table('tblbranch')->where('RegionId', $region->Id)->first();

        // Create User Account
        $userId = DB::table('tbluser')->insertGetId([
            'UserName' => 'testclient4',
            'Password' => sha1('password123'),
            'RoleId' => 7, // Client role
            'CreatedBy' => 1,
        ]);

        // Create Client with Monthly term
        $clientId = DB::table('tblclient')->insertGetId([
            'UserId' => $userId,
            'ContractNumber' => 'TEST4-' . time(),
            'LastName' => 'Gonzales',
            'FirstName' => 'Ana',
            'MiddleName' => 'Lopez',
            'Gender' => 'Female',
            'BirthDate' => '1990-07-25',
            'CivilStatus' => 'Single',
            'Occupation' => 'Nurse',
            'PackageID' => $package->Id,
            'PaymentTermId' => $paymentTerm->Id,
            'PaymentTermAmount' => $paymentTerm->Price,
            'PackagePrice' => $package->Price ?? 10800,
            'RegionId' => $region->Id,
            'BranchId' => $branch->Id,
            'Status' => '3', // Approved
            'Remarks' => 'Approved',
            'FSAComsRem' => 0,
            'AppliedChangeMode' => 1, // Has applied change mode
        ]);

        // Payment breakdown:
        // - Standard payments: ₱990 x 5 = ₱4,950
        // - Partial payments: ₱500
        // - Custom: ₱1,000
        // - Reinstatement: ₱250 (should NOT count)
        // - Change Mode: ₱50 (should NOT count)
        // 
        // Total Package Price: ₱10,800
        // Valid payments: ₱4,950 + ₱500 + ₱1,000 = ₱6,450
        // Expected Balance: ₱10,800 - ₱6,450 = ₱4,350
        //
        // If Reinstatement and Change Mode WERE counted:
        // Balance would be: ₱10,800 - ₱6,750 = ₱4,050 (WRONG)

        $termAmount = $paymentTerm->Price ?? 990;

        $payments = [
            // Payment 1: Standard Payment - 6 months ago
            [
                'ClientId' => $clientId,
                'ORNo' => '40001',
                'AmountPaid' => $termAmount,
                'Date' => Carbon::now()->subMonths(6)->format('Y-m-d'),
                'VoidStatus' => '0',
                'Remarks' => 'Standard',
                'Installment' => 1,
            ],
            // Payment 2: Standard Payment - 5 months ago
            [
                'ClientId' => $clientId,
                'ORNo' => '40002',
                'AmountPaid' => $termAmount,
                'Date' => Carbon::now()->subMonths(5)->format('Y-m-d'),
                'VoidStatus' => '0',
                'Remarks' => 'Standard',
                'Installment' => 2,
            ],
            // Payment 3: REINSTATEMENT - 4 months ago (Should NOT affect balance)
            [
                'ClientId' => $clientId,
                'ORNo' => '40003',
                'AmountPaid' => 250.00,
                'Date' => Carbon::now()->subMonths(4)->format('Y-m-d'),
                'VoidStatus' => '0',
                'Remarks' => 'Reinstatement',
            ],
            // Payment 4: Standard Payment - 4 months ago
            [
                'ClientId' => $clientId,
                'ORNo' => '40004',
                'AmountPaid' => $termAmount,
                'Date' => Carbon::now()->subMonths(4)->addDays(5)->format('Y-m-d'),
                'VoidStatus' => '0',
                'Remarks' => 'Standard',
                'Installment' => 3,
            ],
            // Payment 5: CHANGE MODE - 3 months ago (Should NOT affect balance)
            [
                'ClientId' => $clientId,
                'ORNo' => '40005',
                'AmountPaid' => 50.00,
                'Date' => Carbon::now()->subMonths(3)->format('Y-m-d'),
                'VoidStatus' => '0',
                'Remarks' => 'Change Mode',
            ],
            // Payment 6: Standard Payment - 3 months ago
            [
                'ClientId' => $clientId,
                'ORNo' => '40006',
                'AmountPaid' => $termAmount,
                'Date' => Carbon::now()->subMonths(3)->addDays(5)->format('Y-m-d'),
                'VoidStatus' => '0',
                'Remarks' => 'Standard',
                'Installment' => 4,
            ],
            // Payment 7: Partial Payment - 2 months ago
            [
                'ClientId' => $clientId,
                'ORNo' => '40007',
                'AmountPaid' => 500.00,
                'Date' => Carbon::now()->subMonths(2)->format('Y-m-d'),
                'VoidStatus' => '0',
                'Remarks' => 'Partial',
                'Installment' => 4.5,
            ],
            // Payment 8: Custom Payment - 1 month ago (Should affect balance)
            [
                'ClientId' => $clientId,
                'ORNo' => '40008',
                'AmountPaid' => 1000.00,
                'Date' => Carbon::now()->subMonths(1)->format('Y-m-d'),
                'VoidStatus' => '0',
                'Remarks' => 'Custom',
                'Installment' => 5.5,
            ],
            // Payment 9: Standard Payment - 2 weeks ago
            [
                'ClientId' => $clientId,
                'ORNo' => '40009',
                'AmountPaid' => $termAmount,
                'Date' => Carbon::now()->subDays(14)->format('Y-m-d'),
                'VoidStatus' => '0',
                'Remarks' => 'Standard',
                'Installment' => 6.5,
            ],
        ];

        foreach ($payments as $payment) {
            DB::table('tblpayment')->insert($payment);
        }

        // Calculate expected values
        $packagePrice = $package->Price ?? 10800;
        $validPayments = ($termAmount * 5) + 500 + 1000; // Standard x5 + Partial + Custom
        $expectedBalance = $packagePrice - $validPayments;
        $wrongBalance = $packagePrice - ($validPayments + 250 + 50); // If Reinstatement & Change Mode counted

        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════');
        $this->command->info('✅ Test Client 4 Created Successfully!');
        $this->command->info('═══════════════════════════════════════════');
        $this->command->info('');
        $this->command->info('📋 Login Credentials:');
        $this->command->info('   Username: testclient4');
        $this->command->info('   Password: password123');
        $this->command->info('');
        $this->command->info('👤 Client Details:');
        $this->command->info('   Name: Ana Lopez Gonzales');
        $this->command->info('   Contract: TEST4-' . time());
        $this->command->info('   Package: ' . $package->Package . ' (₱' . number_format($packagePrice, 2) . ')');
        $this->command->info('   Term: ' . $paymentTerm->Term . ' (₱' . number_format($termAmount, 2) . ')');
        $this->command->info('   AppliedChangeMode: YES');
        $this->command->info('');
        $this->command->info('💰 Payment Summary (9 total):');
        $this->command->info('   - Standard: 5 payments (₱' . number_format($termAmount * 5, 2) . ')');
        $this->command->info('   - Partial: 1 payment (₱500.00)');
        $this->command->info('   - Custom: 1 payment (₱1,000.00)');
        $this->command->info('   - Reinstatement: 1 payment (₱250.00) ⚠️ Should NOT affect balance');
        $this->command->info('   - Change Mode: 1 payment (₱50.00) ⚠️ Should NOT affect balance');
        $this->command->info('');
        $this->command->info('📊 BALANCE VERIFICATION:');
        $this->command->info('   Package Total: ₱' . number_format($packagePrice, 2));
        $this->command->info('   Valid Payments: ₱' . number_format($validPayments, 2));
        $this->command->info('   ✅ EXPECTED Balance: ₱' . number_format($expectedBalance, 2));
        $this->command->info('   ❌ WRONG Balance (if counting Reinstatement/ChangeMode): ₱' . number_format($wrongBalance, 2));
        $this->command->info('');
        $this->command->info('🧪 To Test: Search for "Gonzales, Ana" and check balance');
        $this->command->info('');
    }
}
