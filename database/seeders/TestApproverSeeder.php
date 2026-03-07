<?php

namespace Database\Seeders;

/**
 * ============================================================================
 * TEST APPROVER SEEDER - CREDENTIALS QUICK REFERENCE
 * ============================================================================
 * 
 * ┌─────────────────────────────────────────────────────────────────────┐
 * │  USERNAME        │  PASSWORD      │  ACCESS KEY    │  ROLE         │
 * ├─────────────────────────────────────────────────────────────────────┤
 * │  TESTAPPROVER    │  password123   │  a8821dd1f     │  Approver     │
 * └─────────────────────────────────────────────────────────────────────┘
 * 
 * LOGIN URL: /login
 * 
 * PERMISSIONS:
 * - Can VERIFY loan requests (Level 2 <= RoleLevel 15)
 * - Can APPROVE loan requests (Level 2 <= RoleLevel 15)
 * - Can DELETE loan requests (Level 2 <= RoleLevel 15)
 * - Can access all menus visible to Level 2 and below
 * 
 * ============================================================================
 */

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TestApproverSeeder extends Seeder
{
    /**
     * Create test approver user
     */
    public function run(): void
    {
        $this->command->info('🔧 Creating test approver...');

        // Get Approver role (Id: 20, Level: 2)
        $approverRole = DB::table('tblrole')->where('role', 'Approver')->first();
        
        if (!$approverRole) {
            $this->command->error('❌ Approver role not found in tblrole. Please ensure role exists.');
            return;
        }

        // Check if test approver already exists
        $existing = DB::table('tbluser')->where('UserName', 'TESTAPPROVER')->first();
        if ($existing) {
            $this->command->warn('⚠️ Test approver already exists. Skipping...');
            $this->printCredentials();
            return;
        }

        // Create user account with Approver role
        $userId = DB::table('tbluser')->insertGetId([
            'UserName' => 'TESTAPPROVER',
            'Password' => sha1('password123'),
            'RoleId' => $approverRole->Id, // RoleId: 20
            'AccessKey' => 'a8821dd1f',
            'CreatedBy' => 1,
            'DateCreated' => Carbon::now(),
        ]);

        // Create staff record (optional - for full staff functionality)
        $branch = DB::table('tblbranch')->first();
        
        DB::table('tblstaff')->insert([
            'UserId' => $userId,
            'LastName' => 'APPROVER',
            'FirstName' => 'TEST',
            'MiddleName' => 'USER',
            'BranchId' => $branch->Id ?? 1,
            'Position' => 'Loan Approver',
            'ActiveStatus' => 1, // Active
            'DateAccomplished' => Carbon::now(),
        ]);

        $this->command->info('✅ Test approver created successfully!');
        $this->printCredentials();
    }

    private function printCredentials(): void
    {
        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════════════════');
        $this->command->info('🎯 TEST APPROVER CREDENTIALS');
        $this->command->info('═══════════════════════════════════════════════════════════════');
        $this->command->info('');
        $this->command->info('   Username: TESTAPPROVER');
        $this->command->info('   Password: password123');
        $this->command->info('   Access Key: a8821dd1f');
        $this->command->info('   Role: Approver (Level 2)');
        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════════════════');
        $this->command->info('🔐 APPROVAL PERMISSIONS');
        $this->command->info('═══════════════════════════════════════════════════════════════');
        $this->command->info('');
        $this->command->info('   ✅ Can VERIFY loan requests');
        $this->command->info('   ✅ Can APPROVE loan requests');
        $this->command->info('   ✅ Can DELETE loan requests');
        $this->command->info('');
        $this->command->info('   Loan Flow: Pending → Verified → Approved');
        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════════════════');
    }
}
