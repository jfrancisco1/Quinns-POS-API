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
        Schema::table('orders', function (Blueprint $table) {
            // The driver delivering this order; null = in the pool, any driver can claim it.
            $table->foreignId('assigned_driver_id')->nullable()->after('branch_id')->constrained('users')->nullOnDelete();
            // unassigned → assigned → delivered (failed returns it to unassigned)
            $table->string('delivery_status')->default('unassigned')->after('assigned_driver_id');
            $table->timestamp('assigned_at')->nullable()->after('delivery_status');
            $table->string('delivery_failed_reason')->nullable()->after('assigned_at');

            $table->index(['tenant_id', 'assigned_driver_id', 'order_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'assigned_driver_id', 'order_status']);
            $table->dropConstrainedForeignId('assigned_driver_id');
            $table->dropColumn(['delivery_status', 'assigned_at', 'delivery_failed_reason']);
        });
    }
};
