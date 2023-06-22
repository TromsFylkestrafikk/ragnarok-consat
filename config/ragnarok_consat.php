<?php

return [
    /*
     | Remote file system disk for Consat
     | ----------------------------------
     |
     | This uses the same options as given in laravel's config/filesystems.php
     */
    'remote_disk' => [
        'driver' => 'sftp',
        'host' =>  env('CONSAT_HOST'),
        'username' => env('CONSAT_USERNAME'),
        'password' => env('CONSAT_PASSWORD'),
        'port' => env('CONSAT_PORT'),
        'timeout' => 30,
        'root' => env('CONSAT_ROOT', '/'),
    ],
];
