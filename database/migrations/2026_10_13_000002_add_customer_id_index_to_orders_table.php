<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // GET /customers looks up each customer's last visit and total spend in orders;
        // without this, PostgreSQL scans the whole orders table twice per customer.
        Schema::table('orders', function (Blueprint $table) {
            $table->index('customer_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['customer_id']);
        });
    }
};
