<?php

namespace Database\Seeders;

/**
 * ============================================================================
 * TEST CLIENT SEEDER - CREDENTIALS QUICK REFERENCE
 * ============================================================================
 * 
 * ┌─────────────────────────────────────────────────────────────────────┐
 * │  USERNAME       │  PASSWORD      │  ACCESS KEY    │  LOAN STATUS     │
 * ├─────────────────────────────────────────────────────────────────────┤
 * │  TESTCLIENT001  │  password123   │  a8821dd1f     │  ✅ ELIGIBLE      │
 * │  TESTCLIENT002  │  password123   │  a8821dd1f     │  ✅ ELIGIBLE      │
 * │  TESTCLIENT003  │  password123   │  a8821dd1f     │  ❌ NOT ELIGIBLE  │
 * └─────────────────────────────────────────────────────────────────────┘
 * 
 * LOGIN URL: /login
 * NOTE: Use Contract Number as Username
 * 
 * ELIGIBILITY DETAILS:
 * - TESTCLIENT001: Status=3 (Approved), 100% paid → 45% loan tier
 * - TESTCLIENT002: Status=3 (Approved), 80% paid  → 40% loan tier  
 * - TESTCLIENT003: Status=1 (Pending) → Cannot apply for loan
 * 
 * ============================================================================
 */

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TestClientSeeder extends Seeder
{
    /**
     * Create test clients: 2 eligible for loan, 1 not eligible
     */
    public function run(): void
    {
        $this->command->info('🔧 Creating test clients...');

        // Get required reference data
        $region = DB::table('tblregion')->first();
        $branch = DB::table('tblbranch')->where('RegionId', $region->Id ?? 4)->first();
        $package = DB::table('tblpackage')->first();
        $paymentTerm = DB::table('tblpaymentterm')->first();
        
        // Get address data
        $province = DB::table('refprovince')->first();
        $city = DB::table('refcitymun')->where('provCode', $province->provCode ?? '0128')->first();
        $barangay = DB::table('refbrgy')->where('citymunCode', $city->citymunCode ?? '012801')->first();

        // ============ CLIENT 1: ELIGIBLE (100% paid, Status=3) ============
        $this->createClient([
            'contract_number' => 'TESTCLIENT001',
            'lastname' => 'ELIGIBLE',
            'firstname' => 'CLIENT_ONE',
            'status' => 3, // Approved/Active
            'package_price' => 5000.00,
            'total_paid' => 5000.00, // 100% paid - eligible for 45% tier
            'tier' => '100% (45% loan tier)',
        ], $region, $branch, $package, $paymentTerm, $province, $city, $barangay);

        // ============ CLIENT 2: ELIGIBLE (80% paid, Status=3) ============
        $this->createClient([
            'contract_number' => 'TESTCLIENT002',
            'lastname' => 'ELIGIBLE',
            'firstname' => 'CLIENT_TWO',
            'status' => 3, // Approved/Active
            'package_price' => 5000.00,
            'total_paid' => 4000.00, // 80% paid - eligible for 40% tier
            'tier' => '80% (40% loan tier)',
        ], $region, $branch, $package, $paymentTerm, $province, $city, $barangay);

        // ============ CLIENT 3: NOT ELIGIBLE (Status=1 Pending) ============
        $this->createClient([
            'contract_number' => 'TESTCLIENT003',
            'lastname' => 'NOTELIGIBLE',
            'firstname' => 'CLIENT_ONE',
            'status' => 1, // Pending - NOT eligible regardless of payments
            'package_price' => 5000.00,
            'total_paid' => 5000.00, // Even 100% paid, still not eligible due to status
            'tier' => 'NOT ELIGIBLE (Status=Pending)',
        ], $region, $branch, $package, $paymentTerm, $province, $city, $barangay);

        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════════════════');
        $this->command->info('🎯 TEST CLIENTS CREATED SUCCESSFULLY!');
        $this->command->info('═══════════════════════════════════════════════════════════════');
        $this->command->info('');
        $this->command->info('📊 LOAN ELIGIBILITY SUMMARY:');
        $this->command->info('');
        $this->command->info('✅ ELIGIBLE FOR LOAN:');
        $this->command->info('   1. TESTCLIENT001 - 100% paid, Status=3 (Approved)');
        $this->command->info('      → Tier: 45% loanable (₱2,250 from ₱5,000 contract)');
        $this->command->info('');
        $this->command->info('   2. TESTCLIENT002 - 80% paid, Status=3 (Approved)');
        $this->command->info('      → Tier: 40% loanable (₱2,000 from ₱5,000 contract)');
        $this->command->info('');
        $this->command->info('❌ NOT ELIGIBLE FOR LOAN:');
        $this->command->info('   3. TESTCLIENT003 - Status=1 (Pending)');
        $this->command->info('      → Reason: Pending clients cannot apply for loan');
        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════════════════');
        $this->command->info('🔐 LOGIN CREDENTIALS (All use same password)');
        $this->command->info('═══════════════════════════════════════════════════════════════');
        $this->command->info('');
        $this->command->info('   Password: password123');
        $this->command->info('   Access Key: a8821dd1f');
        $this->command->info('');
        $this->command->info('   Usernames (Contract Numbers):');
        $this->command->info('   - TESTCLIENT001');
        $this->command->info('   - TESTCLIENT002');
        $this->command->info('   - TESTCLIENT003');
        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════════════════');
    }

    private function createClient(array $data, $region, $branch, $package, $paymentTerm, $province, $city, $barangay): void
    {
        // Check if exists
        $existing = DB::table('tblclient')->where('ContractNumber', $data['contract_number'])->first();
        if ($existing) {
            $this->command->warn("⚠️ Client {$data['contract_number']} already exists. Skipping...");
            return;
        }

        // Create user account
        $userId = DB::table('tbluser')->insertGetId([
            'UserName' => $data['contract_number'],
            'Password' => sha1('password123'),
            'RoleId' => 7,
            'AccessKey' => 'a8821dd1f',
            'CreatedBy' => 1,
            'DateCreated' => Carbon::now(),
        ]);

        // Create client
        $clientId = DB::table('tblclient')->insertGetId([
            // CONTRACT
            'ContractNumber' => $data['contract_number'],
            'UserId' => $userId,
            'RegionId' => $region->Id ?? 4,
            'BranchId' => $branch->Id ?? 1,
            'PackageID' => $package->Id ?? 1,
            'PackagePrice' => $data['package_price'],
            'PaymentTermId' => $paymentTerm->Id ?? 1,
            'PaymentTermAmount' => 416.67,
            'RecruitedBy' => null,
            
            // PERSONAL
            'LastName' => $data['lastname'],
            'FirstName' => $data['firstname'],
            'MiddleName' => 'TEST',
            'Gender' => 'Male',
            'BirthDate' => '1990-01-15',
            'Age' => 35,
            'BirthPlace' => 'Manila',
            'CivilStatus' => 'Married',
            'Religion' => 'Roman Catholic',
            'Occupation' => 'Software Developer',
            'BestPlaceToCollect' => 'Office',
            'BestTimeToCollect' => '09:00:00',
            
            // ADDRESS
            'Street' => '123 Test Street',
            'Province' => $province->provDesc ?? 'ILOCOS NORTE',
            'City' => $city->citymunDesc ?? 'LAOAG CITY',
            'Barangay' => $barangay->brgyDesc ?? 'BARANGAY 1',
            'ZipCode' => '2900',
            
            // HOME ADDRESS
            'HomeProvince' => $province->provDesc ?? 'ILOCOS NORTE',
            'HomeCity' => $city->citymunDesc ?? 'LAOAG CITY',
            'HomeBarangay' => $barangay->brgyDesc ?? 'BARANGAY 1',
            'homezipcode' => '2900',
            'HomeStreet' => '456 Home Street',
            'HomeNumber' => '456',
            
            // CONTACT
            'MobileNumber' => '09171234567',
            'EmailAddress' => strtolower($data['contract_number']) . '@surelife.test',
            
            // BENEFICIARIES
            'PrincipalBeneficiaryName' => 'JANE ' . $data['lastname'],
            'PrincipalBeneficiaryAge' => 32,
            'principalbeneficiaryrelation' => 'Spouse',
            'principalbeneficiaryid_path' => null,
            'Secondary1Name' => 'CHILD ' . $data['lastname'],
            'Secondary1Age' => 10,
            'Secondary2Name' => 'CHILD TWO ' . $data['lastname'],
            'Secondary2Age' => 8,
            
            // STATUS & FINANCIAL
            'Status' => $data['status'],
            'FSAComsRem' => 0,
            'PercentComm' => 0,
            'CommAmount' => 0,
            'PercentTAC' => 0,
            'TACAmount' => 0,
            'NetRem' => 0,
            'TAP' => 0,
            'HasPaymentRecord' => 1,
            'DateCreated' => Carbon::now(),
            'CreatedBy' => 1,
        ]);

        // Create payments to reach the total_paid amount
        $this->createPayments($clientId, $data['total_paid']);

        $this->command->info("✅ Created: {$data['contract_number']} - {$data['tier']}");
    }

    private function createPayments(int $clientId, float $totalAmount): void
    {
        if ($totalAmount <= 0) return;

        // Create payments in chunks to simulate realistic payment history
        $paymentCount = (int)($totalAmount / 416.67); // Monthly payment amount
        $remaining = $totalAmount;
        
        for ($i = 1; $i <= $paymentCount && $remaining > 0; $i++) {
            $amount = min(416.67, $remaining);
            
            DB::table('tblpayment')->insert([
                'ClientId' => $clientId,
                'ORNo' => 'T' . str_pad((string)$clientId, 4, '0', STR_PAD_LEFT) . $i,
                'AmountPaid' => $amount,
                'NetPayment' => $amount,
                'Date' => Carbon::now()->subMonths($paymentCount - $i)->format('Y-m-d'),
                'PaymentType' => 1, // Cash
                'IsCleared' => 1,
                'Status' => 1,
                'DateCreated' => Carbon::now()->subMonths($paymentCount - $i),
                'CreatedBy' => 1,
            ]);
            
            $remaining -= $amount;
        }
    }
}
