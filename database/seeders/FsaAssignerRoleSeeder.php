<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FsaAssignerRoleSeeder extends Seeder
{
    /**
     * Seed the FSA Assigner role and its Official Receipts menu privilege.
     *
     * Role:  FSA Assigner (level 50)
     * Menu:  Official Receipts (rolelevel 50)
     *
     * Why Level 50?
     * The navbar uses `$userRoleLevel <= $menuRoleLevel`.
     * Existing menus range from 1 to 15. If we use 5, the user satisfies 5 <= 15
     * and sees extra menus. By using an exceptionally high number like 50,
     * it evaluates to FALSE for everything (50 <= 15) EXCEPT the specific
     * Official Receipts entry we create here (50 <= 50).
     */
    public function run(): void
    {
        // Insert FSA Assigner role (skip if already exists)
        $roleExists = DB::table('tblrole')
            ->where('role', 'FSA Assigner')
            ->where('level', 50)
            ->exists();

        if (!$roleExists) {
            // Remove the old level 5 role if it exists to clean up
            DB::table('tblrole')->where('role', 'FSA Assigner')->where('level', 5)->delete();

            DB::table('tblrole')->insert([
                'role' => 'FSA Assigner',
                'level' => 50,
            ]);
            $this->command->info('✅ FSA Assigner role (level 50) inserted into tblrole.');
        } else {
            $this->command->info('⏭️  FSA Assigner role already exists, skipping.');
        }

        // Insert Official Receipts menu privilege for level 50 (skip if already exists)
        $menuExists = DB::table('tblmenu')
            ->where('menuitem', 'Official Receipts')
            ->where('rolelevel', 50)
            ->exists();

        if (!$menuExists) {
            // Remove the old level 5 menu if it exists
            DB::table('tblmenu')->where('menuitem', 'Official Receipts')->where('rolelevel', 5)->delete();

            DB::table('tblmenu')->insert([
                'menuitem' => 'Official Receipts',
                'rolelevel' => 50,
            ]);
            $this->command->info('✅ Official Receipts (rolelevel 50) inserted into tblmenu.');
        } else {
            $this->command->info('⏭️  Official Receipts menu entry already exists, skipping.');
        }
    }
}
