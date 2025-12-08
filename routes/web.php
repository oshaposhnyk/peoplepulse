<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('app');
})->name('home');

// API routes are in routes/api.php
// All application logic handled by Vue 3 SPA via REST API
