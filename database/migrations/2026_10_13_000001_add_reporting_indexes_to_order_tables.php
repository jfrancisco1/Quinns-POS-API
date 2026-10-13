<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Date-range filters: one branch (staff, or admin with branch_id) and all branches (admin).
            $table->index(['tenant_id', 'branch_id', 'created_at']);
            $table->index(['tenant_id', 'created_at']);
        });

        // PostgreSQL does not index foreign key columns automatically.
        Schema::table('order_items', function (Blueprint $table) {
            $table->index('order_number');
        });

        Schema::table('payment_history', function (Blueprint $table) {
            $table->index('order_number');
        });

        Schema::table('order_status_history', function (Blueprint $table) {
            $table->index('order_number');
        });
    }

    public function down(): void
    {
        Schema::table('order_status_history', function (Blueprint $table) {
            $table->dropIndex(['order_number']);
        });

        Schema::table('payment_history', function (Blueprint $table) {
            $table->dropIndex(['order_number']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['order_number']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'created_at']);
            $table->dropIndex(['tenant_id', 'branch_id', 'created_at']);
        });
    }
};
