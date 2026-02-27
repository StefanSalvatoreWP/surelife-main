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
        ]);
        
        // Test data seeders (optional - only for development/testing)
        // Uncomment below if you need test data
        // $this->call([
        //     TestClientSeeder::class,
        //     TestUsersSeeder::class,
        // ]);
    }
}
