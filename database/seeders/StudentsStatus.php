<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentsStatus extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
          DB::table('students_status')->insert([
            [    
                'status'      => 'Studying',
                'description' => 'Student is currently studying'
            ], 
            [    
                'status'      => 'Pass Out',
                'description' => 'Student Passed out from campus'
            ], 
            [    
                'status'      => 'Suspended',
                'description' => 'Student was suspended'
            ], 
            [    
                'status'      => 'Left',
                'description' => 'Student Left campus'
            ], 
            [    
                'status'      => 'Transfer - Hold',
                'description' => 'Student Status is hold because of trasnfer process'
            ], 
            [    
                'status'      => 'Left - Hold',
                'description' => 'Student status is hold because of Left process'
            ], 
            
        ]);
    }
}
