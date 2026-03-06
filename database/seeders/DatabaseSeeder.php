<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Essential seeders
        $this->call([
            AddressSeeder::class,
            ReferenceTablesSeeder::class,
            LoanMenuSeeder::class,
            TestClientSeeder::class,
            FsaAssignerRoleSeeder::class,
        ]);
    }
}
