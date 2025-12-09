<?php

use Illuminate\Support\Facades\Route;

// Catch-all route for Vue Router (SPA)
// This should be at the end so it doesn't override other routes
// Exclude admin, api, livewire, vendor, and static asset routes from SPA catch-all
Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!admin|api|livewire|vendor|css|js|build|storage).*$')->name('spa');

// This makes all routes work with Vue Router history mode
// When you refresh /employees, Laravel will return app.blade.php
// and Vue Router will handle the routing on client side
// Note: System routes (/admin, /api, /livewire, /vendor, static assets) are excluded from SPA
