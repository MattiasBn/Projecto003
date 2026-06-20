<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        // Aqui podes meter os teus domínios oficiais de produção quando os lançares
        'https://matia-sistemas.com',
    ],

    // O SEGREDO ESTÁ AQUI: Aceita QUALQUER porta do localhost automaticamente!
    'allowed_origins_patterns' => [
        '/^http:\/\/localhost(:\d+)?$/',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];