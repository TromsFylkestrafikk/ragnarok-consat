<?php

return [
    /*
     |--------------------------------------------------------------------------
     | Remote file system disk for Consat
     |--------------------------------------------------------------------------
     |
     | This uses the same options as given in laravel's config/filesystems.php
     |
     */
    'remote_disk' => [
        'driver' => 'sftp',
        'host' =>  env('CONSAT_HOST'),
        'username' => env('CONSAT_USERNAME'),
        'password' => env('CONSAT_PASSWORD'),
        'port' => (int) env('CONSAT_PORT', 22),
        'root' => env('CONSAT_ROOT', '/'),
        'timeout' => 30,
        'visibility' => 'public',
        'directory_visibility' => 'public',
    ],

    /*
     |--------------------------------------------------------------------------
     | Disk name used for temporary files
     |--------------------------------------------------------------------------
     */
    'tmp_disk' => 'tmp',

    /*
     |--------------------------------------------------------------------------
     | Max age on imported csv files
     |--------------------------------------------------------------------------
     |
     | Here you can specify max age of imported csv files. The age is given as a
     | ISO-8601 duration.
     */
    'max_age' => [
        // 'CallDetails.csv' => 'P30D',
    ],
];
