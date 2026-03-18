<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\Owner\BranchController as OwnerBranchController;
use App\Http\Controllers\Api\Owner\TenantController as OwnerTenantController;
use App\Http\Controllers\Api\Owner\UserController as OwnerUserController;

Route::prefix('v1')->group(function () {
    Route::post('login', [AuthController::class, 'login']);

    // Owner CMS routes
    Route::middleware(['auth:sanctum', 'owner'])->prefix('owner')->group(function () {
        Route::apiResource('tenants', OwnerTenantController::class);
        Route::apiResource('tenants.branches', OwnerBranchController::class)->shallow();
        Route::apiResource('tenants.users', OwnerUserController::class)->shallow()->except(['show']);
        Route::patch('tenants/{tenant}/users/{user}/toggle', [OwnerUserController::class, 'toggle']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::apiResource('branches', BranchController::class);

        Route::apiResource('customers', CustomerController::class);
        Route::apiResource('categories', CategoryController::class);
        Route::apiResource('items', ItemController::class);
        Route::apiResource('orders', OrderController::class);
        Route::apiResource('expenses', ExpenseController::class);
        Route::middleware('admin')->group(function () {
            Route::get('reports/sales', [ReportController::class, 'sales']);
            Route::get('reports/sales-by-item', [ReportController::class, 'salesByItem']);
            Route::get('reports/sales-by-payment-type', [ReportController::class, 'salesByPaymentType']);
        });
    });
});

Route::get('/run-seeder', function () {
    Artisan::call('db:seed', ['--force' => true]);
    return response()->json(['message' => 'Seeder ran successfully!']);
});

Route::get('/run-user-seeder', function () {
    Artisan::call('db:seed', ['--class' => 'UserSeeder', '--force' => true]);
    return response()->json(['message' => 'UserSeeder ran successfully!']);
});

Route::get('/run-owner-seeder', function () {
    Artisan::call('db:seed', ['--class' => 'OwnerSeeder', '--force' => true]);
    return response()->json(['message' => 'OwnerSeeder ran successfully!']);
});
