<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Bootstrap Admin Password
    |--------------------------------------------------------------------------
    |
    | Used only when creating or seeding the settings table for the first time.
    | The live login check reads the hashed password from the database.
    | After the password is stored, you can remove this value from .env.
    |
    */

    'password' => env('ADMIN_PASSWORD'),

];
