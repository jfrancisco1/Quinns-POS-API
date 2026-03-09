<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'customer_nickname',
                'customer_mobile',
                'customer_address',
                'customer_notes',
                'customer_delivery_fee',
            ]);

            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete()->after('order_number');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');

            $table->string('customer_nickname')->after('order_number');
            $table->string('customer_mobile')->after('customer_nickname');
            $table->string('customer_address')->default('')->after('customer_mobile');
            $table->text('customer_notes')->default('')->after('customer_address');
            $table->decimal('customer_delivery_fee', 8, 2)->default(75)->after('customer_notes');
        });
    }
};
