<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response('<h1>Coming Soon</h1>', 200)
        ->header('Content-Type', 'text/html');
});
