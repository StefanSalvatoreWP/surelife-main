<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TestClientSeeder extends Seeder
{
    /**
     * Run the database seeds for testing client home page.
     * Creates a test client with various payment types.
     */
    public function run()
    {
        // Step 1: Create User Account for Client
        $userId = DB::table('tbluser')->insertGetId([
            'UserName' => 'testclient',
            'Password' => sha1('password123'), // Login uses sha1
            'RoleId' => 7, // Client role
            'CreatedBy' => 1, // Admin user ID
        ]);

        // Step 2: Get Package and Term IDs
        $package = DB::table('tblpackage')->where('Active', 1)->first();
        $paymentTerm = DB::table('tblpaymentterm')
            ->where('PackageId', $package->Id)
            ->where('Term', 'Monthly') // Use Monthly term for proper balance testing
            ->first();

        // Fallback to any non-spotcash term if Monthly not found
        if (!$paymentTerm) {
            $paymentTerm = DB::table('tblpaymentterm')
                ->where('PackageId', $package->Id)
                ->where('Term', '!=', 'Spotcash')
                ->first();
        }

        // Step 3: Get Region and Branch
        $region = DB::table('tblregion')->first();
        $branch = DB::table('tblbranch')->where('RegionId', $region->Id)->first();

        // Step 4: Get Staff (for FSA assignment)
        $staff = DB::table('tbluser')->where('RoleId', '!=', 7)->first();

        // Step 5: Create Client
        $clientId = DB::table('tblclient')->insertGetId([
            'UserId' => $userId,
            'ContractNumber' => 'TEST-' . time(),
            'LastName' => 'Dela Cruz',
            'FirstName' => 'Juan',
            'MiddleName' => 'Santos',
            'Gender' => 'Male',
            'BirthDate' => '1980-05-15',
            'CivilStatus' => 'Married',
            'Occupation' => 'Engineer',
            'PackageID' => $package->Id,
            'PaymentTermId' => $paymentTerm->Id,
            'RegionId' => $region->Id,
            'BranchId' => $branch->Id,
            'Status' => '3', // Approved
            'Remarks' => 'Approved',
            'FSAComsRem' => 0, // Required field
        ]);

        // Step 6: Get OR Batch for Payments
        $orBatch = DB::table('tblorbatch')->first();
        if (!$orBatch) {
            $orBatchId = DB::table('tblorbatch')->insertGetId([
                'SeriesCode' => 'TEST',
                'RegionId' => $region->Id,
                'BranchId' => $branch->Id,
            ]);
            $orBatch = DB::table('tblorbatch')->find($orBatchId);
        }

        // Step 7: Create Various Payments
        $payments = [
            // Payment 1: Standard Payment (Down Payment) - 4 months ago
            [
                'ClientId' => $clientId,
                'ORNo' => '10001',
                'AmountPaid' => 10800.00, // First big payment
                'Date' => Carbon::now()->subMonths(4)->format('Y-m-d'),
                'VoidStatus' => '0',
                'Remarks' => 'Standard',
            ],
            // Payment 2: Standard Payment - 3 months ago
            [
                'ClientId' => $clientId,
                'ORNo' => '10002',
                'AmountPaid' => 990.00,
                'Date' => Carbon::now()->subMonths(3)->format('Y-m-d'),
                'VoidStatus' => '0',
                'Remarks' => 'Standard',
            ],
            // Payment 3: Partial Payment - 2.5 months ago
            [
                'ClientId' => $clientId,
                'ORNo' => '10003',
                'AmountPaid' => 500.00,
                'Date' => Carbon::now()->subMonths(2)->subDays(15)->format('Y-m-d'),
                'VoidStatus' => '0',
                'Remarks' => 'Partial',
            ],
            // Payment 4: Custom Add Payment - 2 months ago
            [
                'ClientId' => $clientId,
                'ORNo' => '10004',
                'AmountPaid' => 1500.00,
                'Date' => Carbon::now()->subMonths(2)->format('Y-m-d'),
                'VoidStatus' => '0',
                'Remarks' => 'Custom Add Payment',
            ],
            // Payment 5: Standard Payment (VOIDED) - 1.5 months ago
            [
                'ClientId' => $clientId,
                'ORNo' => '10005',
                'AmountPaid' => 990.00,
                'Date' => Carbon::now()->subMonths(1)->subDays(15)->format('Y-m-d'),
                'VoidStatus' => '1', // VOIDED
                'Remarks' => 'Standard',
            ],
            // Payment 6: Others Payment - 1 month ago
            [
                'ClientId' => $clientId,
                'ORNo' => '10006',
                'AmountPaid' => 200.00,
                'Date' => Carbon::now()->subMonths(1)->format('Y-m-d'),
                'VoidStatus' => '0',
                'Remarks' => 'Penalty',
            ],
            // Payment 7: Standard Payment - 2 weeks ago
            [
                'ClientId' => $clientId,
                'ORNo' => '10007',
                'AmountPaid' => 990.00,
                'Date' => Carbon::now()->subDays(14)->format('Y-m-d'),
                'VoidStatus' => '0',
                'Remarks' => 'Standard',
            ],
            // Payment 8: Partial Payment (VOIDED) - 1 week ago
            [
                'ClientId' => $clientId,
                'ORNo' => '10008',
                'AmountPaid' => 400.00,
                'Date' => Carbon::now()->subDays(7)->format('Y-m-d'),
                'VoidStatus' => '1', // VOIDED
                'Remarks' => 'Partial',
            ],
            // Payment 9: Standard Payment (NULL remarks - defaults to Standard) - 3 days ago
            [
                'ClientId' => $clientId,
                'ORNo' => '10009',
                'AmountPaid' => 990.00,
                'Date' => Carbon::now()->subDays(3)->format('Y-m-d'),
                'VoidStatus' => '0',
                'Remarks' => null, // NULL - should display as "Standard"
            ],
            // Payment 10: Custom Add Payment - Today
            [
                'ClientId' => $clientId,
                'ORNo' => '10010',
                'AmountPaid' => 2000.00,
                'Date' => Carbon::now()->format('Y-m-d'),
                'VoidStatus' => '0',
                'Remarks' => 'Custom Add Payment',
            ],
        ];

        // Insert all payments
        foreach ($payments as $payment) {
            DB::table('tblpayment')->insert($payment);
        }

        // Output success message
        $this->command->info('✅ Test client 1 created successfully!');
        $this->command->info('');
        $this->command->info('📋 Login Credentials:');
        $this->command->info('   Username: testclient');
        $this->command->info('   Password: password123');
        $this->command->info('');
        $this->command->info('👤 Client Details:');
        $this->command->info('   Name: Juan Santos Dela Cruz');
        $this->command->info('   Contract: TEST-' . (time() - 1));
        $this->command->info('   Package: ' . $package->Package);
        $this->command->info('   Term: Monthly (₱ 990.00)');
        $this->command->info('');
        $this->command->info('💰 Payment Summary:');
        $this->command->info('   Total Payments: 10');
        $this->command->info('   - Standard: 4 (1 voided)');
        $this->command->info('   - Partial: 2 (1 voided)');
        $this->command->info('   - Custom Add Payment: 3');
        $this->command->info('   - Others (Penalty): 1');
        $this->command->info('   - NULL Remarks (displays as Standard): 1');
        $this->command->info('   - Voided Payments: 2');
        $this->command->info('');

        // ============================================
        // TEST CLIENT 2: WITH REMAINING BALANCE
        // ============================================
        $userId2 = DB::table('tbluser')->insertGetId([
            'UserName' => 'testclient2',
            'Password' => sha1('password123'),
            'RoleId' => 7, // Client role
            'CreatedBy' => 1,
        ]);

        $clientId2 = DB::table('tblclient')->insertGetId([
            'UserId' => $userId2,
            'ContractNumber' => 'TEST-' . (time() + 1),
            'LastName' => 'Reyes',
            'FirstName' => 'Maria',
            'MiddleName' => 'Garcia',
            'Gender' => 'Female',
            'BirthDate' => '1985-03-20',
            'CivilStatus' => 'Single',
            'Occupation' => 'Teacher',
            'PackageID' => $package->Id,
            'PaymentTermId' => $paymentTerm->Id,
            'RegionId' => $region->Id,
            'BranchId' => $branch->Id,
            'Status' => '3', // Approved
            'Remarks' => 'Approved',
            'FSAComsRem' => 0,
        ]);

        // Partial Payments - Client has balance remaining
        $payments2 = [
            // Down payment - 6 months ago
            [
                'ClientId' => $clientId2,
                'ORNo' => '20001',
                'AmountPaid' => 5000.00,
                'Date' => Carbon::now()->subMonths(6)->format('Y-m-d'),
                'VoidStatus' => '0',
                'Remarks' => 'Standard',
            ],
            // Regular monthly payment - 5 months ago
            [
                'ClientId' => $clientId2,
                'ORNo' => '20002',
                'AmountPaid' => 990.00,
                'Date' => Carbon::now()->subMonths(5)->format('Y-m-d'),
                'VoidStatus' => '0',
                'Remarks' => 'Standard',
            ],
            // Regular monthly payment - 4 months ago
            [
                'ClientId' => $clientId2,
                'ORNo' => '20003',
                'AmountPaid' => 990.00,
                'Date' => Carbon::now()->subMonths(4)->format('Y-m-d'),
                'VoidStatus' => '0',
                'Remarks' => 'Standard',
            ],
            // Partial payment - 3 months ago
            [
                'ClientId' => $clientId2,
                'ORNo' => '20004',
                'AmountPaid' => 500.00,
                'Date' => Carbon::now()->subMonths(3)->format('Y-m-d'),
                'VoidStatus' => '0',
                'Remarks' => 'Partial',
            ],
            // Regular monthly payment - 2 months ago
            [
                'ClientId' => $clientId2,
                'ORNo' => '20005',
                'AmountPaid' => 990.00,
                'Date' => Carbon::now()->subMonths(2)->format('Y-m-d'),
                'VoidStatus' => '0',
                'Remarks' => 'Standard',
            ],
        ];

        foreach ($payments2 as $payment) {
            DB::table('tblpayment')->insert($payment);
        }

        $this->command->info('✅ Test client 2 created successfully!');
        $this->command->info('');
        $this->command->info('📋 Login Credentials:');
        $this->command->info('   Username: testclient2');
        $this->command->info('   Password: password123');
        $this->command->info('');
        $this->command->info('👤 Client Details:');
        $this->command->info('   Name: Maria Garcia Reyes');
        $this->command->info('   Contract: TEST-' . time());
        $this->command->info('   Package: ' . $package->Package . ' (₱ 10,800.00)');
        $this->command->info('   Term: Monthly (₱ 990.00)');
        $this->command->info('');
        $this->command->info('💰 Payment Summary:');
        $this->command->info('   Total Paid: ₱ 8,470.00');
        $this->command->info('   Balance Remaining: ₱ 2,330.00');
        $this->command->info('   Status: ACTIVE (With Balance)');
        $this->command->info('   Last Payment: 2 months ago');
        $this->command->info('');

        // ============================================
        // TEST CLIENT 3: LAPSED STATUS
        // ============================================
        $userId3 = DB::table('tbluser')->insertGetId([
            'UserName' => 'testclient3',
            'Password' => sha1('password123'),
            'RoleId' => 7, // Client role
            'CreatedBy' => 1,
        ]);

        $clientId3 = DB::table('tblclient')->insertGetId([
            'UserId' => $userId3,
            'ContractNumber' => 'TEST-' . (time() + 2),
            'LastName' => 'Santos',
            'FirstName' => 'Pedro',
            'MiddleName' => 'Martinez',
            'Gender' => 'Male',
            'BirthDate' => '1975-11-10',
            'CivilStatus' => 'Married',
            'Occupation' => 'Driver',
            'PackageID' => $package->Id,
            'PaymentTermId' => $paymentTerm->Id,
            'RegionId' => $region->Id,
            'BranchId' => $branch->Id,
            'Status' => '3', // Approved
            'Remarks' => 'Approved',
            'FSAComsRem' => 0,
        ]);

        // Very old payments - Client should be LAPSED
        $payments3 = [
            // Down payment - 12 months ago
            [
                'ClientId' => $clientId3,
                'ORNo' => '30001',
                'AmountPaid' => 3000.00,
                'Date' => Carbon::now()->subMonths(12)->format('Y-m-d'),
                'VoidStatus' => '0',
                'Remarks' => 'Standard',
            ],
            // Monthly payment - 11 months ago
            [
                'ClientId' => $clientId3,
                'ORNo' => '30002',
                'AmountPaid' => 990.00,
                'Date' => Carbon::now()->subMonths(11)->format('Y-m-d'),
                'VoidStatus' => '0',
                'Remarks' => 'Standard',
            ],
            // Monthly payment - 10 months ago
            [
                'ClientId' => $clientId3,
                'ORNo' => '30003',
                'AmountPaid' => 990.00,
                'Date' => Carbon::now()->subMonths(10)->format('Y-m-d'),
                'VoidStatus' => '0',
                'Remarks' => 'Standard',
            ],
            // Last payment - 7 months ago (over 180 days = LAPSED)
            [
                'ClientId' => $clientId3,
                'ORNo' => '30004',
                'AmountPaid' => 500.00,
                'Date' => Carbon::now()->subMonths(7)->format('Y-m-d'),
                'VoidStatus' => '0',
                'Remarks' => 'Partial',
            ],
        ];

        foreach ($payments3 as $payment) {
            DB::table('tblpayment')->insert($payment);
        }

        $this->command->info('✅ Test client 3 created successfully!');
        $this->command->info('');
        $this->command->info('📋 Login Credentials:');
        $this->command->info('   Username: testclient3');
        $this->command->info('   Password: password123');
        $this->command->info('');
        $this->command->info('👤 Client Details:');
        $this->command->info('   Name: Pedro Martinez Santos');
        $this->command->info('   Contract: TEST-' . (time() + 1));
        $this->command->info('   Package: ' . $package->Package . ' (₱ 10,800.00)');
        $this->command->info('   Term: Monthly (₱ 990.00)');
        $this->command->info('');
        $this->command->info('💰 Payment Summary:');
        $this->command->info('   Total Paid: ₱ 5,480.00');
        $this->command->info('   Balance Remaining: ₱ 5,320.00');
        $this->command->info('   Status: LAPSED (No payment for 7 months)');
        $this->command->info('   Last Payment: 7 months ago');
        $this->command->info('');

        // ============================================
        // TEST CLIENT 4: REINSTATEMENT & CHANGE MODE TEST
        // Tests that Reinstatement and Change Mode payments 
        // do NOT affect outstanding balance
        // ============================================
        $userId4 = DB::table('tbluser')->insertGetId([
            'UserName' => 'testclient4',
            'Password' => sha1('password123'),
            'RoleId' => 7, // Client role
            'CreatedBy' => 1,
        ]);

        $clientId4 = DB::table('tblclient')->insertGetId([
            'UserId' => $userId4,
            'ContractNumber' => 'TEST-' . (time() + 3),
            'LastName' => 'Gonzales',
            'FirstName' => 'Ana',
            'MiddleName' => 'Lopez',
            'Gender' => 'Female',
            'BirthDate' => '1990-07-25',
            'CivilStatus' => 'Single',
            'Occupation' => 'Nurse',
            'PackageID' => $package->Id,
            'PaymentTermId' => $paymentTerm->Id,
            'RegionId' => $region->Id,
            'BranchId' => $branch->Id,
            'Status' => '3', // Approved
            'Remarks' => 'Approved',
            'FSAComsRem' => 0,
            'AppliedChangeMode' => 1, // Has applied change mode
        ]);

        // Payment breakdown for testclient4:
        // - Standard payments: ₱10,800 + ₱990 + ₱990 + ₱990 + ₱990 + ₱500 = ₱15,260
        // - Partial payments: ₱500 (counted in balance)
        // - Reinstatement: ₱250 (should NOT count toward balance)
        // - Change Mode: ₱50 (should NOT count toward balance)
        // - Custom: ₱1,000 (SHOULD count toward balance)
        // 
        // Total Package Price (Monthly term): ₱990 x 60 = ₱59,400
        // Valid payments (Standard + Partial + Custom): ₱15,260 + ₱1,000 = ₱16,260
        // Expected Balance: ₱59,400 - ₱16,260 = ₱43,140
        //
        // If Reinstatement and Change Mode WERE counted, balance would be:
        // ₱59,400 - ₱16,560 = ₱42,840 (which would be WRONG)

        $payments4 = [
            // Payment 1: Standard Payment (Down Payment) - 6 months ago
            [
                'ClientId' => $clientId4,
                'ORNo' => '40001',
                'AmountPaid' => 10800.00, // Big down payment
                'Date' => Carbon::now()->subMonths(6)->format('Y-m-d'),
                'VoidStatus' => '0',
                'Remarks' => 'Standard',
                'Installment' => 10.91,
            ],
            // Payment 2: Standard Payment - 5 months ago
            [
                'ClientId' => $clientId4,
                'ORNo' => '40002',
                'AmountPaid' => 990.00,
                'Date' => Carbon::now()->subMonths(5)->format('Y-m-d'),
                'VoidStatus' => '0',
                'Remarks' => 'Standard',
                'Installment' => 11.91,
            ],
            // Payment 3: REINSTATEMENT - 4 months ago (Should NOT affect balance)
            [
                'ClientId' => $clientId4,
                'ORNo' => '40003',
                'AmountPaid' => 250.00,
                'Date' => Carbon::now()->subMonths(4)->format('Y-m-d'),
                'VoidStatus' => '0',
                'Remarks' => 'Reinstatement', // Should NOT be counted in balance
            ],
            // Payment 4: Standard Payment - 4 months ago
            [
                'ClientId' => $clientId4,
                'ORNo' => '40004',
                'AmountPaid' => 990.00,
                'Date' => Carbon::now()->subMonths(4)->addDays(5)->format('Y-m-d'),
                'VoidStatus' => '0',
                'Remarks' => 'Standard',
                'Installment' => 12.91,
            ],
            // Payment 5: CHANGE MODE - 3 months ago (Should NOT affect balance)
            [
                'ClientId' => $clientId4,
                'ORNo' => '40005',
                'AmountPaid' => 50.00,
                'Date' => Carbon::now()->subMonths(3)->format('Y-m-d'),
                'VoidStatus' => '0',
                'Remarks' => 'Change Mode', // Should NOT be counted in balance
            ],
            // Payment 6: Standard Payment - 3 months ago
            [
                'ClientId' => $clientId4,
                'ORNo' => '40006',
                'AmountPaid' => 990.00,
                'Date' => Carbon::now()->subMonths(3)->addDays(5)->format('Y-m-d'),
                'VoidStatus' => '0',
                'Remarks' => 'Standard',
                'Installment' => 13.91,
            ],
            // Payment 7: Partial Payment - 2 months ago
            [
                'ClientId' => $clientId4,
                'ORNo' => '40007',
                'AmountPaid' => 500.00,
                'Date' => Carbon::now()->subMonths(2)->format('Y-m-d'),
                'VoidStatus' => '0',
                'Remarks' => 'Partial',
                'Installment' => 14.91,
            ],
            // Payment 8: Custom Payment - 1 month ago (Should affect balance)
            [
                'ClientId' => $clientId4,
                'ORNo' => '40008',
                'AmountPaid' => 1000.00,
                'Date' => Carbon::now()->subMonths(1)->format('Y-m-d'),
                'VoidStatus' => '0',
                'Remarks' => 'Custom',
                'Installment' => 15.92,
            ],
            // Payment 9: Standard Payment - 2 weeks ago
            [
                'ClientId' => $clientId4,
                'ORNo' => '40009',
                'AmountPaid' => 990.00,
                'Date' => Carbon::now()->subDays(14)->format('Y-m-d'),
                'VoidStatus' => '0',
                'Remarks' => 'Standard',
                'Installment' => 16.92,
            ],
        ];

        foreach ($payments4 as $payment) {
            DB::table('tblpayment')->insert($payment);
        }

        $this->command->info('✅ Test client 4 created successfully!');
        $this->command->info('');
        $this->command->info('📋 Login Credentials:');
        $this->command->info('   Username: testclient4');
        $this->command->info('   Password: password123');
        $this->command->info('');
        $this->command->info('👤 Client Details:');
        $this->command->info('   Name: Ana Lopez Gonzales');
        $this->command->info('   Contract: TEST-' . (time() + 3));
        $this->command->info('   Package: ' . $package->Package);
        $this->command->info('   Term: Monthly (₱ 990.00)');
        $this->command->info('   AppliedChangeMode: YES');
        $this->command->info('');
        $this->command->info('💰 Payment Summary:');
        $this->command->info('   Total Payments: 9');
        $this->command->info('   - Standard: 5 (₱14,760)');
        $this->command->info('   - Partial: 1 (₱500)');
        $this->command->info('   - Custom: 1 (₱1,000)');
        $this->command->info('   - Reinstatement: 1 (₱250) ⚠️ Should NOT affect balance');
        $this->command->info('   - Change Mode: 1 (₱50) ⚠️ Should NOT affect balance');
        $this->command->info('');
        $this->command->info('📊 BALANCE TEST:');
        $this->command->info('   Package Total (Monthly x60): ₱59,400');
        $this->command->info('   Valid Payments (Standard+Partial+Custom): ₱16,260');
        $this->command->info('   ✓ EXPECTED Balance: ₱43,140');
        $this->command->info('   ✗ WRONG Balance (if counting Reinstatement/ChangeMode): ₱42,840');
        $this->command->info('');

        $this->command->info('═══════════════════════════════════════════');
        $this->command->info('');
        $this->command->info('🧪 Test Scenarios Summary:');
        $this->command->info('   ✓ CLIENT 1: Multiple payment types & voided payments');
        $this->command->info('   ✓ CLIENT 2: Active with remaining balance');
        $this->command->info('   ✓ CLIENT 3: Lapsed status (no payment > 180 days)');
        $this->command->info('   ✓ CLIENT 4: Reinstatement & Change Mode balance test');
        $this->command->info('');
        $this->command->info('🔗 Access: /client-home (after login)');
    }
}
