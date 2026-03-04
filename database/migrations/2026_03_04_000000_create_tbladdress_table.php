<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates tbladdress table for unified Philippine address system.
     * This table stores Regions, Provinces, Cities/Municipalities, and Barangays.
     */
    public function up(): void
    {
        if (Schema::hasTable('tbladdress')) {
            return;
        }

        Schema::create('tbladdress', function (Blueprint $table) {
            $table->id();
            $table->string('address_type', 20)->comment('region, province, citymun, barangay');
            $table->string('code', 255)->nullable();
            $table->string('psgc_code', 255)->nullable()->comment('Philippine Standard Geographic Code');
            $table->text('description')->nullable();
            $table->string('parent_code', 255)->nullable();
            $table->string('region_code', 255)->nullable();
            $table->string('province_code', 255)->nullable();
            $table->string('citymun_code', 255)->nullable();
            $table->integer('level')->nullable()->comment('1=region, 2=province, 3=citymun, 4=barangay');

            $table->index('address_type');
            $table->index('code');
            $table->index('parent_code');
            $table->index('level');
            $table->index('region_code');
            $table->index('province_code');
            $table->index('citymun_code');
        });

        // Seed regions data
        $regions = [
            ['01', '010000000', 'REGION I (ILOCOS REGION)'],
            ['02', '020000000', 'REGION II (CAGAYAN VALLEY)'],
            ['03', '030000000', 'REGION III (CENTRAL LUZON)'],
            ['04', '040000000', 'REGION IV-A (CALABARZON)'],
            ['17', '170000000', 'REGION IV-B (MIMAROPA)'],
            ['05', '050000000', 'REGION V (BICOL REGION)'],
            ['06', '060000000', 'REGION VI (WESTERN VISAYAS)'],
            ['07', '070000000', 'REGION VII (CENTRAL VISAYAS)'],
            ['08', '080000000', 'REGION VIII (EASTERN VISAYAS)'],
            ['09', '090000000', 'REGION IX (ZAMBOANGA PENINSULA)'],
            ['10', '100000000', 'REGION X (NORTHERN MINDANAO)'],
            ['11', '110000000', 'REGION XI (DAVAO REGION)'],
            ['12', '120000000', 'REGION XII (SOCCSKSARGEN)'],
            ['13', '130000000', 'NATIONAL CAPITAL REGION (NCR)'],
            ['14', '140000000', 'CORDILLERA ADMINISTRATIVE REGION (CAR)'],
            ['15', '150000000', 'AUTONOMOUS REGION IN MUSLIM MINDANAO (ARMM)'],
            ['16', '160000000', 'REGION XIII (CARAGA)'],
        ];

        foreach ($regions as $region) {
            DB::table('tbladdress')->insert([
                'address_type' => 'region',
                'code' => $region[0],
                'psgc_code' => $region[1],
                'description' => $region[2],
                'level' => 1,
            ]);
        }

        // Import from ref tables if they exist
        $this->importFromRefTables();
    }

    /**
     * Import data from refprovince, refcitymun, refbrgy tables if they exist
     */
    private function importFromRefTables(): void
    {
        // Import provinces
        if (Schema::hasTable('refprovince')) {
            $provinces = DB::table('refprovince')->get();
            foreach ($provinces as $prov) {
                DB::table('tbladdress')->insert([
                    'address_type' => 'province',
                    'code' => $prov->provCode ?? $prov->code ?? null,
                    'psgc_code' => $prov->psgcCode ?? null,
                    'description' => $prov->provDesc ?? $prov->description ?? null,
                    'parent_code' => $prov->regCode ?? null,
                    'region_code' => $prov->regCode ?? null,
                    'level' => 2,
                ]);
            }
        }

        // Import cities/municipalities
        if (Schema::hasTable('refcitymun')) {
            $cities = DB::table('refcitymun')->get();
            foreach ($cities as $city) {
                DB::table('tbladdress')->insert([
                    'address_type' => 'citymun',
                    'code' => $city->citymunCode ?? $city->code ?? null,
                    'psgc_code' => $city->psgcCode ?? null,
                    'description' => $city->citymunDesc ?? $city->description ?? null,
                    'parent_code' => $city->provCode ?? null,
                    'region_code' => substr($city->provCode ?? '', 0, 2),
                    'province_code' => $city->provCode ?? null,
                    'level' => 3,
                ]);
            }
        }

        // Import barangays
        if (Schema::hasTable('refbrgy')) {
            $barangays = DB::table('refbrgy')->get();
            foreach ($barangays as $brgy) {
                DB::table('tbladdress')->insert([
                    'address_type' => 'barangay',
                    'code' => $brgy->brgyCode ?? $brgy->code ?? null,
                    'psgc_code' => $brgy->brgyCode ?? null,
                    'description' => $brgy->brgyDesc ?? $brgy->description ?? null,
                    'parent_code' => $brgy->citymunCode ?? null,
                    'region_code' => $brgy->regCode ?? null,
                    'province_code' => $brgy->provCode ?? null,
                    'citymun_code' => $brgy->citymunCode ?? null,
                    'level' => 4,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbladdress');
    }
};
