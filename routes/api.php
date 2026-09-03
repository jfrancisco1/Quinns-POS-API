<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\ExpenseCategoryController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\SuperAdmin\BranchController as SuperAdminBranchController;
use App\Http\Controllers\Api\SuperAdmin\TenantController as SuperAdminTenantController;
use App\Http\Controllers\Api\SuperAdmin\UserController as SuperAdminUserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('login', [AuthController::class, 'login']);

    // Superadmin CMS routes
    Route::middleware(['auth:sanctum', 'superadmin'])->prefix('superadmin')->group(function () {
        Route::apiResource('tenants', SuperAdminTenantController::class);
        Route::apiResource('tenants.branches', SuperAdminBranchController::class)->shallow();
        Route::apiResource('tenants.users', SuperAdminUserController::class)->shallow()->only(['index', 'store', 'destroy']);
        Route::get('tenants/{tenant}/users/{user}', [SuperAdminUserController::class, 'show']);
        Route::put('tenants/{tenant}/users/{user}', [SuperAdminUserController::class, 'update']);
        Route::patch('tenants/{tenant}/users/{user}', [SuperAdminUserController::class, 'update']);
        Route::patch('tenants/{tenant}/users/{user}/toggle', [SuperAdminUserController::class, 'toggle']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
        Route::apiResource('branches', BranchController::class);

        Route::apiResource('customers', CustomerController::class);
        Route::apiResource('categories', CategoryController::class);
        Route::apiResource('items', ItemController::class);
        Route::post('orders/statuses', [OrderController::class, 'statuses']);
        Route::apiResource('orders', OrderController::class);
        Route::patch('orders/{order}/payment-status', [OrderController::class, 'updatePaymentStatus']);
        Route::patch('orders/{order}/order-status', [OrderController::class, 'updateOrderStatus']);
        Route::apiResource('expenses', ExpenseController::class);
        Route::apiResource('expense-categories', ExpenseCategoryController::class);
        Route::middleware('admin')->group(function () {
            Route::get('store', [StoreController::class, 'show']);
            Route::put('store', [StoreController::class, 'update']);
            Route::patch('store', [StoreController::class, 'update']);
            Route::get('reports/sales', [ReportController::class, 'sales']);
            Route::get('reports/sales-by-item', [ReportController::class, 'salesByItem']);
            Route::get('reports/sales-by-payment-type', [ReportController::class, 'salesByPaymentType']);
            Route::get('reports/expenses-by-category', [ReportController::class, 'expensesByCategory']);
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

Route::get('/run-superadmin-seeder', function () {
    Artisan::call('db:seed', ['--class' => 'SuperAdminSeeder', '--force' => true]);

    return response()->json(['message' => 'SuperAdminSeeder ran successfully!']);
});
