<?php

return [
    /*
    |--------------------------------------------------------------------------
    | GLPI API URL
    |--------------------------------------------------------------------------
    |
    | URL lengkap ke GLPI REST API. Gunakan environment variable GLPI_API_URL
    | Pastikan ini valid dan bisa diakses dari server production.
    |
    */

    'api_url' => env('GLPI_API_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | GLPI App Token
    |--------------------------------------------------------------------------
    |
    | Token aplikasi GLPI. Gunakan environment variable GLPI_APP_TOKEN
    |
    */

    'api_app_token' => env('GLPI_APP_TOKEN', ''),

    /*
    |--------------------------------------------------------------------------
    | GLPI API User
    |--------------------------------------------------------------------------
    |
    | User akun khusus API.
    |
    */

    'api_user' => env('GLPI_API_USER', ''),

    /*
    |--------------------------------------------------------------------------
    | GLPI App Password
    |--------------------------------------------------------------------------
    |
    | Password akun khusus API.
    |
    */

    'api_password' => env('GLPI_API_PASS', ''),
];