<?php
return array(

    'default' => env('DB_CONNECTION', 'mysql'),

    'connections' => array(
        # primary database connection
        'mysql' => array(
            'driver'    => env('DB_CONNECTION'),
            'host'      => env('DB_HOST', 'localhost'),
            'port'      => env('DB_PORT', 27017),
            'database'  => env('DB_DATABASE'),
            'username'  => env('DB_USERNAME'),
            'password'  => env('DB_PASSWORD'),
        ),
        # secondary database connection NoSQL
        'mongodb' => array(
            'driver'   => env('MONGO_DB_CONNECTION'),
            'host'     => env('MONGO_DB_HOST', 'localhost'),
            'port'     => env('MONGO_DB_PORT', 27017),
            'database' => env('MONGO_DB_DATABASE'),
            'username' => env('MONGO_DB_USERNAME'),
            'password' => env('MONGO_DB_PASSWORD'),
            'options'  => [ 
                // 'database' => 'admin' 
            ],
        )
    ),
);
