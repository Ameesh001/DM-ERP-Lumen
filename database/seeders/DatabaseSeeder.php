<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            AuthUserTypesSeeder::class,
            CountriesSeeder::class,
            AuthModulesSeeder::class,
            UsersSeeder::class,
            AffiliationSeeder::class,
            UserHierarchyLevels::class,
            StdFinalResultSeeder::class,
            StudentsStatus::class
        ]);        
    }
}
