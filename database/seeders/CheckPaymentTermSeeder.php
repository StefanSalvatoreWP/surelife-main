<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CheckPaymentTermSeeder extends Seeder
{
    /**
     * Check what payment term ID 107 is
     */
    public function run()
    {
        $this->command->info('🔍 Checking Payment Term ID 107');
        
        $term = DB::table('tblpaymentterm')->where('Id', 107)->first();
        
        if ($term) {
            $this->command->info("Payment Term ID 107: '{$term->Term}'");
            $this->command->info("Package ID: {$term->packageid}");
            $this->command->info("Price: {$term->price}");
            
            // Check if this is Spotcash
            if ($term->Term === 'Spotcash') {
                $this->command->info('✅ This IS a Spotcash term');
            } else {
                $this->command->error('❌ This is NOT Spotcash - it is: ' . $term->Term);
                $this->command->info('The spot cash approval will NOT trigger for this term!');
            }
        } else {
            $this->command->error('❌ Payment term ID 107 not found!');
        }
        
        // Show all terms for package 22
        $this->command->info('\n📋 All payment terms for Package 22:');
        $terms = DB::table('tblpaymentterm')
            ->where('packageid', 22)
            ->get();
        
        foreach ($terms as $t) {
            $this->command->info("  ID: {$t->Id}, Term: '{$t->Term}', Price: {$t->price}");
        }
    }
}
