<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AffiliationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('affiliation_list')->insert([
            [    
                'id' => 1,
                'affiliation_name' => 'Darulmadinah Board',
                'is_enable' => 1
            ], 
            [
                'id' => 2,
                'affiliation_name' => 'Sindh Board',
                'is_enable' => 1
            ],
            [
                'id' => 3,
                'affiliation_name' => 'Punjab Board',
                'is_enable' => 1
            ]
        ]);
        
    }
}
