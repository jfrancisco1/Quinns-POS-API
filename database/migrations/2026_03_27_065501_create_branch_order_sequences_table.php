<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_order_sequences', function (Blueprint $table) {
            $table->foreignId('branch_id')->primary()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('last_seq')->default(0);
        });

        // Seed existing branches with their current max order number
        DB::statement("
            INSERT INTO branch_order_sequences (branch_id, last_seq)
            SELECT
                branch_id,
                COALESCE(MAX(CAST(SUBSTRING(order_number FROM 5) AS BIGINT)), 0)
            FROM orders
            WHERE order_number IS NOT NULL
            GROUP BY branch_id
            ON CONFLICT (branch_id) DO UPDATE SET last_seq = EXCLUDED.last_seq
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_order_sequences');
    }
};
