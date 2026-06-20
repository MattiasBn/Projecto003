<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;

use App\Services\SystemMailService;

Route::get('/', function () {
    return response()->json([
        'status'  => 'online',
        'service' => 'Mixa API Gateway', // Nome fictício do teu Gateway, ninguém sabe que é Laravel
        'version' => '1.0.0'
    ]);
});

