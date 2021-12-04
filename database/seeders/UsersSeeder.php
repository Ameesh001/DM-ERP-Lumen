<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersSeeder extends Seeder
{
    /* ---------------------------------
    USERS SEEDER:
    ////// Inserting default users in users /////
    -------------------------------------*/

    public function run()
    {
        /*DB::table('users')->insert([
            'id' => 1,
            'client_id' => NULL,
            'user_name' => 'itadmin',
            'full_name' => 'IT Admin',
            'password' => Hash::make('sysAdmin#123'),
            'phone' => '03001234567',
            'email' => 'itadmin@gmail.com',
            'address' => 'Faizan e Madinah IT',
            'user_type' => 1,
            'is_enable' => 1
        ]);*/
    }
}
