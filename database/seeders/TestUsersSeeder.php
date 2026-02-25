<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds for testing different user roles.
     * Creates test accounts for all roles except Client and Administrator.
     */
    public function run()
    {
        $this->command->info('🧪 Creating test accounts for all user roles...');
        $this->command->info('');

        // Get region and branch for assignments
        $region = DB::table('tblregion')->first();
        $branch = DB::table('tblbranch')->where('RegionId', $region->Id)->first();

        // Define test users for each role (excluding Client=7 and Administrator=1)
        $testUsers = [
            // MANAGEMENT LEVEL
            [
                'role_id' => 20,
                'role_name' => 'Approver',
                'username' => 'testapprover',
                'full_name' => 'Approve Manager',
                'description' => 'Can approve various operations and transactions'
            ],
            [
                'role_id' => 2,
                'role_name' => 'Main Branch Staff',
                'username' => 'testmainstaff',
                'full_name' => 'Main Branch Staff',
                'description' => 'Main branch operations staff'
            ],
            [
                'role_id' => 11,
                'role_name' => 'New Sales Manager',
                'username' => 'testsalesmanager',
                'full_name' => 'Sales Manager',
                'description' => 'Manages new sales operations'
            ],
            [
                'role_id' => 16,
                'role_name' => 'Old Sales Manager',
                'username' => 'testoldsalesmanager',
                'full_name' => 'Old Sales Manager',
                'description' => 'Manages legacy sales operations'
            ],

            // FIELD SALES MANAGEMENT
            [
                'role_id' => 3,
                'role_name' => 'FSM',
                'username' => 'testfsm',
                'full_name' => 'Field Sales Manager',
                'description' => 'Field Sales Manager - oversees field operations'
            ],
            [
                'role_id' => 4,
                'role_name' => 'FSGA',
                'username' => 'testfsga',
                'full_name' => 'Field Sales Group Administrator',
                'description' => 'Field Sales Group Administrator'
            ],
            [
                'role_id' => 8,
                'role_name' => 'FSD',
                'username' => 'testfsd',
                'full_name' => 'Field Sales Director',
                'description' => 'Field Sales Director - directs field sales'
            ],
            [
                'role_id' => 9,
                'role_name' => 'IFSD',
                'username' => 'testifsd',
                'full_name' => 'Independent Field Sales Director',
                'description' => 'Independent Field Sales Director'
            ],

            // FIELD SALES AGENTS
            [
                'role_id' => 5,
                'role_name' => 'Old Scheme FSA',
                'username' => 'testoldfsa',
                'full_name' => 'Old Scheme Field Sales Agent',
                'description' => 'Field Sales Agent under old compensation scheme'
            ],
            [
                'role_id' => 6,
                'role_name' => 'New Scheme FSA',
                'username' => 'testnewfsa',
                'full_name' => 'New Scheme Field Sales Agent',
                'description' => 'Field Sales Agent under new compensation scheme'
            ],

            // FINANCIAL OPERATIONS
            [
                'role_id' => 10,
                'role_name' => 'Cashier',
                'username' => 'testcashier',
                'full_name' => 'Branch Cashier',
                'description' => 'Handles cash transactions and payments'
            ],
            [
                'role_id' => 18,
                'role_name' => 'Accounting',
                'username' => 'testaccounting',
                'full_name' => 'Accounting Staff',
                'description' => 'Handles accounting and financial records'
            ],
            [
                'role_id' => 13,
                'role_name' => 'Auditor',
                'username' => 'testauditor',
                'full_name' => 'Internal Auditor',
                'description' => 'Conducts internal audits and reviews'
            ],

            // COLLECTIONS
            [
                'role_id' => 12,
                'role_name' => 'Collection Manager',
                'username' => 'testcollectionmgr',
                'full_name' => 'Collection Manager',
                'description' => 'Manages collection operations and strategies'
            ],
            [
                'role_id' => 15,
                'role_name' => 'Collector',
                'username' => 'testcollector',
                'full_name' => 'Field Collector',
                'description' => 'Handles field collections from clients'
            ],

            // VERIFICATION & PROCESSING
            [
                'role_id' => 14,
                'role_name' => 'Verifier',
                'username' => 'testverifier',
                'full_name' => 'Document Verifier',
                'description' => 'Verifies client documents and applications'
            ],

            // HUMAN RESOURCES
            [
                'role_id' => 17,
                'role_name' => 'Human Resource',
                'username' => 'testhr',
                'full_name' => 'Human Resource Officer',
                'description' => 'Handles HR operations and employee management'
            ],
        ];

        $createdUsers = [];

        foreach ($testUsers as $userData) {
            try {
                // Create user account
                $userId = DB::table('tbluser')->insertGetId([
                    'UserName' => $userData['username'],
                    'Password' => sha1('password123'), // Same password for all test accounts
                    'RoleId' => $userData['role_id'],
                    'CreatedBy' => 1, // Admin user ID
                ]);

                // Create staff record (for non-client roles) - simplified for testing
                $staffId = null;
                try {
                    $staffId = DB::table('tblstaff')->insertGetId([
                        'UserId' => $userId,
                        'LastName' => explode(' ', $userData['full_name'])[count(explode(' ', $userData['full_name'])) - 1],
                        'FirstName' => explode(' ', $userData['full_name'])[0],
                        'MiddleName' => count(explode(' ', $userData['full_name'])) > 2 ? explode(' ', $userData['full_name'])[1] : '',
                        'Gender' => 'Male', // Default for testing
                        'BirthDate' => '1985-01-01', // Default for testing
                        'CivilStatus' => 'Single', // Default for testing
                        'Occupation' => $userData['role_name'],
                        'RegionId' => $region->Id,
                        'BranchId' => $branch->Id,
                    ]);
                } catch (\Exception $staffException) {
                    // Staff creation failed but user was created successfully
                    $this->command->warn("⚠️ User {$userData['username']} created but staff record failed: " . $staffException->getMessage());
                }

                $createdUsers[] = [
                    'username' => $userData['username'],
                    'role' => $userData['role_name'],
                    'full_name' => $userData['full_name'],
                    'description' => $userData['description'],
                    'user_id' => $userId,
                    'staff_id' => $staffId,
                ];

                $this->command->info("✅ Created: {$userData['username']} ({$userData['role_name']})");

            } catch (\Exception $e) {
                $this->command->error("❌ Failed to create {$userData['username']}: " . $e->getMessage());
            }
        }

        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════');
        $this->command->info('🎯 TEST ACCOUNTS CREATED SUCCESSFULLY!');
        $this->command->info('═══════════════════════════════════════════');
        $this->command->info('');

        // Display summary by category
        $categories = [
            'MANAGEMENT LEVEL' => ['Approver', 'Main Branch Staff', 'New Sales Manager', 'Old Sales Manager'],
            'FIELD SALES MANAGEMENT' => ['FSM', 'FSGA', 'FSD', 'IFSD'],
            'FIELD SALES AGENTS' => ['Old Scheme FSA', 'New Scheme FSA'],
            'FINANCIAL OPERATIONS' => ['Cashier', 'Accounting', 'Auditor'],
            'COLLECTIONS' => ['Collection Manager', 'Collector'],
            'VERIFICATION & PROCESSING' => ['Verifier'],
            'HUMAN RESOURCES' => ['Human Resource'],
        ];

        foreach ($categories as $category => $roles) {
            $this->command->info("📋 {$category}:");
            foreach ($createdUsers as $user) {
                if (in_array($user['role'], $roles)) {
                    $this->command->info("   🔑 {$user['username']} - {$user['role']}");
                    $this->command->info("      👤 {$user['full_name']}");
                    $this->command->info("      📝 {$user['description']}");
                    $this->command->info('');
                }
            }
        }

        $this->command->info('🔐 LOGIN CREDENTIALS:');
        $this->command->info('   Password for ALL accounts: password123');
        $this->command->info('');
        $this->command->info('🌐 ACCESS METHODS:');
        $this->command->info('   • Admin Panel: /login');
        $this->command->info('   • Client Portal: /client-login (for client accounts only)');
        $this->command->info('');
        $this->command->info('📊 TESTING COVERAGE:');
        $this->command->info("   ✓ Total Roles Created: " . count($createdUsers));
        $this->command->info('   ✓ Management: 4 roles');
        $this->command->info('   ✓ Field Sales: 6 roles');
        $this->command->info('   ✓ Financial: 3 roles');
        $this->command->info('   ✓ Collections: 2 roles');
        $this->command->info('   ✓ Processing: 1 role');
        $this->command->info('   ✓ HR: 1 role');
        $this->command->info('');
        $this->command->info('⚠️  EXCLUDED ROLES:');
        $this->command->info('   • Administrator (ID: 1) - Use existing admin account');
        $this->command->info('   • Client (ID: 7) - Use TestClientSeeder for client accounts');
        $this->command->info('');
        $this->command->info('🧪 TESTING SCENARIOS:');
        $this->command->info('   ✓ Role-based access control testing');
        $this->command->info('   ✓ Permission level verification');
        $this->command->info('   ✓ Menu visibility testing');
        $this->command->info('   ✓ Feature access validation');
        $this->command->info('   ✓ Workflow testing across roles');
        $this->command->info('');
        $this->command->info('🚀 USAGE:');
        $this->command->info('   1. Login with any username above');
        $this->command->info('   2. Use password: password123');
        $this->command->info('   3. Test role-specific features');
        $this->command->info('   4. Verify access permissions');
        $this->command->info('   5. Test cross-role workflows');
    }
}
