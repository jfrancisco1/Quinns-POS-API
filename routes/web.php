<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/docs', function () {
    return view('documentation');
});

// Superadmin CMS — catch-all so Vue Router handles navigation
Route::get('/cms/{any?}', function () {
    return view('cms');
})->where('any', '.*');

// Admin Panel — catch-all so Vue Router handles navigation
Route::get('/admin/{any?}', fn () => view('admin'))->where('any', '.*');
