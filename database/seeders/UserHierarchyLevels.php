<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserHierarchyLevels extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('user_hierarchy_levels')->insert([
            [    
                'id' => 1,
                'level_name' => 'Organization Level'
            ], 
            [
                'id' => 2,
                'level_name' => 'Country Level'
            ],
            [
                'id' => 3,
                'level_name' => 'State Level'
            ],
            [
                'id' => 4,
                'level_name' => 'Region Level'
            ],
            [
                'id' => 5,
                'level_name' => 'City Level'
            ],
            [
                'id' => 6,
                'level_name' => 'Campus Level'
            ],
        ]);
    }
}
