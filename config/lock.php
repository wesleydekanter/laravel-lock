<?php
return [

    /*
    |--------------------------------------------------------------------------
    | Lock Driver
    |--------------------------------------------------------------------------
    |
    | The lock driver
    |
    | Supported: "cache", "mysql", "filesystem"
    |
    */

    'driver' => env('LOCK_DRIVER', 'cache'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem lock driver
    |--------------------------------------------------------------------------
    |
    | The filesystem lock driver maintains locks by creating files and obtaining
    | an exclusive lock on them by the operating system. By default, these files
    | are stored in the storage path.
    |
    | WARNING:  This driver will not work if you have a pool of webservers, since
    |           files will only be created on a single webserver!
    |
    */
    'filesystem' => [

        'folder' => env('LOCK_FILESYSTEM_FOLDER', storage_path('framework/lock/')),

    ],

    /*
    |--------------------------------------------------------------------------
    | MySQL lock driver
    |--------------------------------------------------------------------------
    |
    | The database driver uses the MySQL GET_LOCK() implementation for acquiring
    | and releasing locks.
    |
    */
    'mysql' => [

        /**
         * Specify which connection should be used for the locks. Uses the default
         * connection by default.
         */
        'connection' => env('LOCK_MYSQL_CONNECTION', null),
    ]

];