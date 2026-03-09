<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('order_status_history');
        Schema::dropIfExists('payment_history');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique()->nullable(); // auto-generated after insert
            $table->string('customer_nickname');
            $table->string('customer_mobile');
            $table->string('customer_address')->default('');
            $table->text('customer_notes')->default('');
            $table->decimal('customer_delivery_fee', 8, 2)->default(75);
            $table->string('fulfillment_type');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->timestamp('created_at_client')->nullable();
            $table->string('payment_status')->default('unpaid');
            $table->string('order_status')->default('in_progress');
            $table->timestamps();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('item_id');
            $table->string('label');
            $table->string('unit');
            $table->integer('qty');
            $table->decimal('price', 10, 2);
        });

        Schema::create('payment_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('from_status');
            $table->string('to_status');
            $table->timestamp('changed_at');
        });

        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
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
