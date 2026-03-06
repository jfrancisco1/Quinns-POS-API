<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('payment_method', ['cash', 'gcash', 'card', 'others'])->default('cash');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->enum('order_type', ['walk-in', 'pickup-deliver'])->default('pickup-deliver');
            $table->text('note')->nullable();
            $table->enum('payment_status', ['paid', 'unpaid'])->default('unpaid');
            $table->enum('order_status', ['pending', 'in_progress', 'ready', 'completed', 'cancelled'])->default('pending');
            $table->enum('delivery_status', ['pending', 'delivered'])->nullable();
            $table->foreignId('discounts_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
