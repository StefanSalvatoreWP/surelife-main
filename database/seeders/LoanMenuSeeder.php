<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LoanMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Adds Loans menu item to tblmenu for Request dropdown
     */
    public function run(): void
    {
        // Check if Loans menu already exists
        $exists = DB::table('tblmenu')->where('MenuItem', 'Loans')->exists();
        
        if (!$exists) {
            DB::table('tblmenu')->insert([
                'MenuItem' => 'Loans',
                'RoleLevel' => 3, // Same level as Staff, Client, Branch
            ]);
            $this->command->info('Loans menu item added successfully.');
        } else {
            $this->command->info('Loans menu item already exists.');
        }
    }
}
