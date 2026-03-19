<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Migrate legacy values to the new enum
        DB::statement("UPDATE orders SET payment_status = 'paid_cash' WHERE payment_status = 'paid'");
        DB::statement("UPDATE orders SET payment_status = 'unpaid' WHERE payment_status = 'pending'");

        // Drop old check constraint if exists, then add the new one
        DB::statement("ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_payment_status_check");
        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_payment_status_check CHECK (payment_status IN ('unpaid', 'paid_cash', 'paid_gcash', 'paid_others'))");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_payment_status_check");
    }
};
