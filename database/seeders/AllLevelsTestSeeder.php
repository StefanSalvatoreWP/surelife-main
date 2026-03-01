<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * All Levels Test Seeder
 * 
 * Creates test accounts for ALL role levels (1-20) with AccessKey support.
 * This seeder provides a complete reference for testing any role in the system.
 * 
 * Login Format: SLC + username (e.g., SLCadmin, SLClevel2)
 * Password: password123
 * Access Key: a8821dd1f
 */
class AllLevelsTestSeeder extends Seeder
{
    /**
     * Run the database seeds for ALL user roles.
     * Creates test accounts with AccessKey support for complete testing coverage.
     */
    public function run()
    {
        $this->command->info('🧪 Creating test accounts for ALL role levels...');
        $this->command->info('');

        // Get region and branch for assignments
        $region = DB::table('tblregion')->first();
        $branch = DB::table('tblbranch')->where('RegionId', $region->Id)->first();

        if (!$region || !$branch) {
            $this->command->error('❌ No region or branch found. Please run region and branch seeders first.');
            return;
        }

        // Define test users for ALL roles (1-20, excluding Client=7 which uses different table)
        $testUsers = [
            // LEVEL 1 - ADMINISTRATOR
            [
                'role_id' => 1,
                'role_name' => 'Administrator',
                'username' => 'admin',
                'full_name' => 'System Administrator',
                'description' => 'Full system access - Highest level',
                'level' => 1
            ],

            // LEVEL 2 - MANAGEMENT
            [
                'role_id' => 2,
                'role_name' => 'Main Branch Staff',
                'username' => 'level2',
                'full_name' => 'Main Branch Staff',
                'description' => 'Management operations - Level 2',
                'level' => 2
            ],
            [
                'role_id' => 11,
                'role_name' => 'New Sales Manager',
                'username' => 'level2sales',
                'full_name' => 'New Sales Manager',
                'description' => 'Manages new sales operations - Level 2',
                'level' => 2
            ],
            [
                'role_id' => 16,
                'role_name' => 'Old Sales Manager',
                'username' => 'level2oldsales',
                'full_name' => 'Old Sales Manager',
                'description' => 'Manages legacy sales - Level 2',
                'level' => 2
            ],
            [
                'role_id' => 20,
                'role_name' => 'Approver',
                'username' => 'level2approver',
                'full_name' => 'Approval Manager',
                'description' => 'Can approve operations - Level 2',
                'level' => 2
            ],

            // LEVEL 3 - SUPERVISORS
            [
                'role_id' => 3,
                'role_name' => 'FSM',
                'username' => 'level3fsm',
                'full_name' => 'Field Sales Manager',
                'description' => 'Field Sales Manager - Level 3',
                'level' => 3
            ],
            [
                'role_id' => 8,
                'role_name' => 'FSD',
                'username' => 'level3fsd',
                'full_name' => 'Field Sales Director',
                'description' => 'Field Sales Director - Level 3',
                'level' => 3
            ],
            [
                'role_id' => 9,
                'role_name' => 'IFSD',
                'username' => 'level3ifsd',
                'full_name' => 'Independent Field Sales Director',
                'description' => 'Independent Field Sales Director - Level 3',
                'level' => 3
            ],
            [
                'role_id' => 12,
                'role_name' => 'Collection Manager',
                'username' => 'level3collection',
                'full_name' => 'Collection Manager',
                'description' => 'Manages collections - Level 3',
                'level' => 3
            ],
            [
                'role_id' => 17,
                'role_name' => 'Human Resource',
                'username' => 'level3hr',
                'full_name' => 'Human Resource Officer',
                'description' => 'HR operations - Level 3',
                'level' => 3
            ],

            // LEVEL 4 - SPECIALISTS
            [
                'role_id' => 4,
                'role_name' => 'FSGA',
                'username' => 'level4fsga',
                'full_name' => 'Field Sales Group Administrator',
                'description' => 'Field Sales Group Administrator - Level 4',
                'level' => 4
            ],
            [
                'role_id' => 13,
                'role_name' => 'Auditor',
                'username' => 'level4auditor',
                'full_name' => 'Internal Auditor',
                'description' => 'Internal audits - Level 4',
                'level' => 4
            ],
            [
                'role_id' => 18,
                'role_name' => 'Accounting',
                'username' => 'level4accounting',
                'full_name' => 'Accounting Staff',
                'description' => 'Accounting operations - Level 4',
                'level' => 4
            ],

            // LEVEL 5 - FIELD AGENTS
            [
                'role_id' => 5,
                'role_name' => 'Old Scheme FSA',
                'username' => 'level5oldfsa',
                'full_name' => 'Old Scheme Field Sales Agent',
                'description' => 'Field Sales Agent (Old) - Level 5',
                'level' => 5
            ],
            [
                'role_id' => 6,
                'role_name' => 'New Scheme FSA',
                'username' => 'level5newfsa',
                'full_name' => 'New Scheme Field Sales Agent',
                'description' => 'Field Sales Agent (New) - Level 5',
                'level' => 5
            ],
            [
                'role_id' => 14,
                'role_name' => 'Verifier',
                'username' => 'level5verifier',
                'full_name' => 'Document Verifier',
                'description' => 'Document verification - Level 5',
                'level' => 5
            ],

            // LEVEL 6+ - OPERATIONAL STAFF
            [
                'role_id' => 10,
                'role_name' => 'Cashier',
                'username' => 'level6cashier',
                'full_name' => 'Branch Cashier',
                'description' => 'Cash transactions - Level 6',
                'level' => 6
            ],
            [
                'role_id' => 15,
                'role_name' => 'Collector',
                'username' => 'level6collector',
                'full_name' => 'Field Collector',
                'description' => 'Field collections - Level 6',
                'level' => 6
            ],
        ];

        $createdUsers = [];
        $skippedUsers = [];

        foreach ($testUsers as $userData) {
            try {
                // Check if user already exists
                $existingUser = DB::table('tbluser')
                    ->where('UserName', $userData['username'])
                    ->first();

                $userId = null;

                if ($existingUser) {
                    $userId = $existingUser->id;
                    $skippedUsers[] = $userData['username'];
                    $this->command->warn("⚠️  User {$userData['username']} already exists (ID: {$userId})");
                } else {
                    // Create user account with AccessKey
                    $userId = DB::table('tbluser')->insertGetId([
                        'UserName' => $userData['username'],
                        'Password' => sha1('password123'),
                        'RoleId' => $userData['role_id'],
                        'AccessKey' => 'a8821dd1f',
                        'CreatedBy' => 1,
                        'DateCreated' => Carbon::now(),
                    ]);
                    $this->command->info("✅ Created: {$userData['username']} ({$userData['role_name']})");
                }

                // Check if staff record exists
                $existingStaff = DB::table('tblstaff')
                    ->where('UserId', $userId)
                    ->first();

                if (!$existingStaff) {
                    // Create staff record
                    $nameParts = explode(' ', $userData['full_name']);
                    $firstName = $nameParts[0];
                    $lastName = count($nameParts) > 1 ? end($nameParts) : 'Staff';
                    $middleName = count($nameParts) > 2 ? $nameParts[1] : '';

                    DB::table('tblstaff')->insert([
                        'UserId' => $userId,
                        'IdNumber' => strtoupper(substr($userData['username'], 0, 10)) . '-2024',
                        'LastName' => $lastName,
                        'FirstName' => $firstName,
                        'MiddleName' => $middleName,
                        'Gender' => 'Male',
                        'BirthDate' => '1990-01-01',
                        'CivilStatus' => 'Single',
                        'Occupation' => $userData['role_name'],
                        'Position' => 1,
                        'EmailAddress' => $userData['username'] . '@surelife.local',
                        'RegionId' => $region->Id,
                        'BranchId' => $branch->Id,
                        'DateCreated' => Carbon::now(),
                    ]);
                }

                $createdUsers[] = [
                    'username' => $userData['username'],
                    'role_id' => $userData['role_id'],
                    'role_name' => $userData['role_name'],
                    'full_name' => $userData['full_name'],
                    'description' => $userData['description'],
                    'level' => $userData['level'],
                    'user_id' => $userId,
                ];

            } catch (\Exception $e) {
                $this->command->error("❌ Failed to create {$userData['username']}: " . $e->getMessage());
            }
        }

        // Display comprehensive summary
        $this->displaySummary($createdUsers, $skippedUsers);
    }

    /**
     * Display comprehensive summary of all accounts
     */
    private function displaySummary($users, $skipped)
    {
        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════════════════════════════════╗');
        $this->command->info('║          ALL LEVELS TEST ACCOUNTS - COMPLETE REFERENCE               ║');
        $this->command->info('╚══════════════════════════════════════════════════════════════════════╝');
        $this->command->info('');

        // Group by level
        $byLevel = [];
        foreach ($users as $user) {
            $byLevel[$user['level']][] = $user;
        }
        ksort($byLevel);

        foreach ($byLevel as $level => $levelUsers) {
            $this->command->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->command->info("🔷 LEVEL {$level} ACCOUNTS");
            $this->command->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            
            foreach ($levelUsers as $user) {
                $this->command->info("");
                $this->command->info("   📌 Role ID: {$user['role_id']} - {$user['role_name']}");
                $this->command->info("   👤 {$user['full_name']}");
                $this->command->info("   📝 {$user['description']}");
                $this->command->info("");
                $this->command->info("   🔐 LOGIN CREDENTIALS:");
                $this->command->info("      Username: SLC{$user['username']}");
                $this->command->info("      Password: password123");
                $this->command->info("      Access Key: a8821dd1f");
                $this->command->info("      Database User: {$user['username']}");
                $this->command->info("");
            }
        }

        $this->command->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->command->info("📊 SUMMARY");
        $this->command->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->command->info("   Total Accounts: " . count($users));
        $this->command->info("   Skipped (already exist): " . count($skipped));
        $this->command->info("   Access Key (ALL): a8821dd1f");
        $this->command->info("   Password (ALL): password123");
        $this->command->info("");

        $this->command->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->command->info("⚠️  IMPORTANT LOGIN INSTRUCTIONS");
        $this->command->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->command->info("   1. URL: /login");
        $this->command->info("   2. Username format: SLC + username (e.g., SLCadmin, SLClevel2)");
        $this->command->info("   3. Password: password123");
        $this->command->info("   4. Access Key: a8821dd1f");
        $this->command->info("   5. Click Login");
        $this->command->info("");
        $this->command->info("   ⚠️  SLC prefix is REQUIRED for all staff accounts!");
        $this->command->info("   ⚠️  System removes SLC prefix during validation");
        $this->command->info("");

        $this->command->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->command->info("🚀 USAGE");
        $this->command->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->command->info("   Run: php artisan db:seed --class=AllLevelsTestSeeder");
        $this->command->info("   Reference: Check this output for any role's credentials");
        $this->command->info("");

        $this->command->info('✅ All levels test seeder completed!');
    }
}
