<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');

        Schema::create('orders', function (Blueprint $table) {
            $table->string('order_number')->primary();
            $table->string('customer_nickname');
            $table->string('customer_mobile');
            $table->string('customer_address')->default('');
            $table->text('customer_notes')->default('');
            $table->decimal('customer_delivery_fee', 8, 2)->default(75);
            $table->string('fulfillment_type'); // walk-in | delivery
            $table->decimal('subtotal', 10, 2);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->timestamp('created_at_client')->nullable(); // SQLite createdAt
            $table->string('payment_status')->default('unpaid');
            $table->string('order_status')->default('in_progress');
            $table->timestamps();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->string('order_number');
            $table->foreign('order_number')->references('order_number')->on('orders')->cascadeOnDelete();
            $table->string('item_id'); // snapshot — not a FK
            $table->string('label');
            $table->string('unit');
            $table->integer('qty');
            $table->decimal('price', 10, 2);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
