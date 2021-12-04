<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthUserTypesSeeder extends Seeder
{
    /* ---------------------------------
    USER TYPES SEEDER:
    ////// Inserting default user types in auth_user_types /////
    -------------------------------------*/
    public function run()
    {
        DB::table('auth_user_types')->insert([
            ['id' => 1,
                'user_type' => 'IT Superadmin',
                'is_enable' => 1
            ], 
            [
                'id' => 2,
                'user_type' => 'Org Superadmin',
                'is_enable' => 1
            ],
            [
                'id' => 3,
                'user_type' => 'Users',
                'is_enable' => 1
            ]
        ]);
    }
}
