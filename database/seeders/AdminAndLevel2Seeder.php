<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminAndLevel2Seeder extends Seeder
{
    /**
     * Create Admin (Level 1) and Main Branch Staff (Level 2) accounts
     */
    public function run()
    {
        $this->command->info('🔧 Creating Admin and Level 2 accounts...');
        $this->command->info('');

        // Get region and branch for assignments
        $region = DB::table('tblregion')->first();
        $branch = DB::table('tblbranch')->where('RegionId', $region->Id)->first();

        if (!$region || !$branch) {
            $this->command->error('❌ No region or branch found. Please run region and branch seeders first.');
            return;
        }

        // Define accounts to create
        $accounts = [
            [
                'role_id' => 1,
                'role_name' => 'Administrator',
                'username' => 'admin',
                'full_name' => 'System Administrator',
                'email' => 'admin@surelife.local',
                'access_key' => 'a8821dd1f',
                'description' => 'Full system access - Level 1'
            ],
            [
                'role_id' => 2,
                'role_name' => 'Main Branch Staff',
                'username' => 'level2',
                'full_name' => 'Main Branch Staff',
                'email' => 'level2@surelife.local',
                'access_key' => 'a8821dd1f',
                'description' => 'Management operations - Level 2'
            ],
        ];

        foreach ($accounts as $account) {
            try {
                // Check if user already exists
                $existingUser = DB::table('tbluser')
                    ->where('UserName', $account['username'])
                    ->first();

                $userId = null;
                
                if ($existingUser) {
                    $userId = $existingUser->id;
                    $this->command->warn("⚠️ User {$account['username']} already exists (ID: {$userId}). Checking staff record...");
                } else {
                    // Create user account
                    $userId = DB::table('tbluser')->insertGetId([
                        'UserName' => $account['username'],
                        'Password' => sha1('password123'), // Default password
                        'RoleId' => $account['role_id'],
                        'AccessKey' => $account['access_key'],
                        'CreatedBy' => 1, // System
                        'DateCreated' => Carbon::now(),
                    ]);
                    $this->command->info("✅ Created user: {$account['username']} (ID: {$userId})");
                }

                // Check if staff record exists
                $existingStaff = DB::table('tblstaff')
                    ->where('UserId', $userId)
                    ->first();

                if ($existingStaff) {
                    $this->command->warn("⚠️ Staff record for {$account['username']} already exists. Skipping...");
                    continue;
                }

                // Create staff record
                $nameParts = explode(' ', $account['full_name']);
                $firstName = $nameParts[0];
                $lastName = count($nameParts) > 1 ? end($nameParts) : 'Staff';
                $middleName = count($nameParts) > 2 ? $nameParts[1] : '';

                DB::table('tblstaff')->insert([
                    'UserId' => $userId,
                    'IdNumber' => 'TEST-' . strtoupper($account['username']) . '-' . date('Y'),
                    'LastName' => $lastName,
                    'FirstName' => $firstName,
                    'MiddleName' => $middleName,
                    'Gender' => 'Male',
                    'BirthDate' => '1990-01-01',
                    'CivilStatus' => 'Single',
                    'Occupation' => $account['role_name'],
                    'EmailAddress' => $account['email'],
                    'RegionId' => $region->Id,
                    'BranchId' => $branch->Id,
                    'DateCreated' => Carbon::now(),
                ]);

                $this->command->info("✅ Created staff record for: {$account['username']}");

            } catch (\Exception $e) {
                $this->command->error("❌ Failed to create {$account['username']}: " . $e->getMessage());
            }
        }

        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════');
        $this->command->info('🎯 ACCOUNTS CREATED SUCCESSFULLY!');
        $this->command->info('═══════════════════════════════════════════');
        $this->command->info('');
        $this->command->info('📋 DATABASE RECORDS:');
        $this->command->info('');
        $this->command->info('   🔑 Admin Account (Level 1):');
        $this->command->info('      Database Username: admin');
        $this->command->info('      Role: Administrator (ID: 1)');
        $this->command->info('      Access Key: a8821dd1f');
        $this->command->info('');
        $this->command->info('   🔑 Level 2 Account (Main Branch Staff):');
        $this->command->info('      Database Username: level2');
        $this->command->info('      Role: Main Branch Staff (ID: 2)');
        $this->command->info('      Access Key: a8821dd1f');
        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════');
        $this->command->info('🔐 LOGIN INSTRUCTIONS');
        $this->command->info('═══════════════════════════════════════════');
        $this->command->info('');
        $this->command->info('   URL: /login');
        $this->command->info('');
        $this->command->info('   ⚠️  IMPORTANT: Add SLC prefix to username!');
        $this->command->info('');
        $this->command->info('   🔑 Admin Login:');
        $this->command->info('      Username: SLCadmin');
        $this->command->info('      Password: password123');
        $this->command->info('      Access Key: a8821dd1f');
        $this->command->info('');
        $this->command->info('   🔑 Level 2 Login:');
        $this->command->info('      Username: SLClevel2');
        $this->command->info('      Password: password123');
        $this->command->info('      Access Key: a8821dd1f');
        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════');
        $this->command->info('⚠️  NOTES:');
        $this->command->info('═══════════════════════════════════════════');
        $this->command->info('   • SLC prefix is REQUIRED for staff login');
        $this->command->info('   • System removes SLC prefix during validation');
        $this->command->info('   • Access Key is required for both accounts');
        $this->command->info('   • Change passwords after first login');
        $this->command->info('');
    }
}
