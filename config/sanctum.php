<?php

use Laravel\Sanctum\Sanctum;

return [
    /*
    |--------------------------------------------------------------------------
    | Stateful Domains
    |--------------------------------------------------------------------------
    |
    | A lista de domínios que podem usar a autenticação baseada em sessão (cookies).
    | Adicione aqui o endereço do seu frontend quando ele estiver no ar.
    |
    */
    'stateful' => [
        'localhost',
        '127.0.0.1',
        '127.0.0.1:8000', // Endereço do seu backend Laravel
        'localhost:5173', // Endereço padrão do seu frontend Vue.js com Vite
        '127.0.0.1:5173',
    ],

    /*
    |--------------------------------------------------------------------------
    | Expiration Minutes
    |--------------------------------------------------------------------------
    |
    | Define a validade em minutos para os tokens de API.
    | Não afeta as sessões de login normais.
    |
    */
    'expiration' => null,

    /*
    |--------------------------------------------------------------------------
    | Sanctum Middleware
    |--------------------------------------------------------------------------
    */
    'middleware' => [
        'verify_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
        'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
    ],

];
