<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_history', function (Blueprint $table) {
            $table->id();
            $table->string('order_number');
            $table->foreign('order_number')->references('order_number')->on('orders')->cascadeOnDelete();
            $table->string('from_status');
            $table->string('to_status');
            $table->timestamp('changed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_history');
    }
};
