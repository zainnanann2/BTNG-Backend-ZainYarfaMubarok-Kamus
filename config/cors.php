<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS)
    |--------------------------------------------------------------------------
    |
    | Klien API (aplikasi mobile, SPA, atau Postman di browser) memanggil
    | domain yang berbeda dari domain API. Tanpa konfigurasi ini, browser
    | akan memblokir respons walaupun server sudah membalas 200.
    |
    | Untuk produksi, ganti "*" pada allowed_origins dengan domain klien
    | yang sebenarnya, misalnya ['https://kamus.example.com'].
    |
    */

    'paths' => ['api/*', 'docs/*', 'docs/api.json'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
