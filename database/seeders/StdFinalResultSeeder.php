<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StdFinalResultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('std_final_results')->insert([
            [    
                'id' => 1,
                'title' => 'Recommended'
            ], 
            [
                'id' => 2,
                'title' => 'Not Recommended'
            ],
            [
                'id' => 3,
                'title' => 'Conditional Recommended'
            ],
            [
                'id' => 4,
                'title' => 'Absent'
            ]
        ]);
    }
}
