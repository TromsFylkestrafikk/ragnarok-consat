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
        'port' => (int) env('CONSAT_PORT', 22),
        'root' => env('CONSAT_ROOT', '/'),
        'timeout' => 30,
        'visibility' => 'public',
        'directory_visibility' => 'public',
    ],
];
