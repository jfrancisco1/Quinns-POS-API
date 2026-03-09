<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('order_status_history');
        Schema::dropIfExists('payment_history');
    }

    public function down(): void
    {
        // intentionally not recreating — history tables are no longer needed
    }
};
