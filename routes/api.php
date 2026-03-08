<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\ItemController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('items', ItemController::class);
});

Route::get('/run-seeder', function () {
    Artisan::call('db:seed', ['--force' => true]);
    return response()->json(['message' => 'Seeder ran successfully!']);
});


/* Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
}); */
