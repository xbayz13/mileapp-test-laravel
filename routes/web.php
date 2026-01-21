<?php

use Illuminate\Support\Facades\Route;

// Vue.js SPA routes - catch all routes and return app view
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
