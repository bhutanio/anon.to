<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Hash Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default hash driver that will be used to hash
    | passwords for your application. By default, the bcrypt algorithm is
    | used; however, you remain free to modify this option if you wish.
    |
    | Supported: "bcrypt", "argon"
    |
    */

    'driver' => 'bcrypt',

    /*
    |--------------------------------------------------------------------------
    | Bcrypt Hashing Options
    |--------------------------------------------------------------------------
    |
    | Here you may configure the cost setting for the bcrypt algorithm used
    | by Laravel. This controls how many CPU cycles are spent hashing a
    | single password. It is recommended to increase this value over time.
    |
    */

    'bcrypt' => [
        'rounds' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Argon Hashing Options
    |--------------------------------------------------------------------------
    |
    | Here you may configure the options for the Argon2 algorithm used by
    | Laravel. These will be passed directly to the "password_hash"
    | function in PHP. You should consult the official documentation.
    |
    */

    'argon' => [
        'memory' => 1024,
        'threads' => 2,
        'time' => 2,
    ],

];
