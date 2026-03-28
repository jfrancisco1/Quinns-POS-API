<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop in reverse dependency order
        Schema::dropIfExists('order_status_history');
        Schema::dropIfExists('payment_history');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');

        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('order_number')->primary();
            $table->foreignUuid('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('fulfillment_type');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->timestamp('created_at_client')->nullable();
            $table->string('payment_status')->default('unpaid');
            $table->string('order_status')->default('in_progress');
            $table->timestamps();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
        });

        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_payment_status_check CHECK (payment_status IN ('unpaid', 'paid_cash', 'paid_gcash', 'paid_others'))");

        Schema::create('order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('order_number');
            $table->foreign('order_number')->references('order_number')->on('orders')->cascadeOnDelete();
            $table->string('item_id');
            $table->string('label');
            $table->integer('qty');
            $table->decimal('price', 10, 2);
        });

        Schema::create('payment_history', function (Blueprint $table) {
            $table->id();
            $table->uuid('order_number');
            $table->foreign('order_number')->references('order_number')->on('orders')->cascadeOnDelete();
            $table->string('from_status');
            $table->string('to_status');
            $table->timestamp('changed_at');
        });

        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id();
            $table->uuid('order_number');
            $table->foreign('order_number')->references('order_number')->on('orders')->cascadeOnDelete();
            $table->string('from_status');
            $table->string('to_status');
            $table->timestamp('changed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_history');
        Schema::dropIfExists('payment_history');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
