<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Address data seeder - runs first to populate Philippine address data
        $this->call([
            AddressSeeder::class,
            ReferenceTablesSeeder::class, // Ensure reference tables are always populated
            TestClientSeeder::class, // Test accounts with OR records for loan testing
        ]);
    }
}
