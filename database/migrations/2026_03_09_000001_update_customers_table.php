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
        Schema::table('customers', function (Blueprint $table) {
            $table->renameColumn('name', 'nickname');
            $table->renameColumn('phone', 'mobile');
            $table->dropUnique(['email']);
            $table->dropColumn('email');
            $table->decimal('delivery_fee', 8, 2)->default(0)->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->renameColumn('nickname', 'name');
            $table->renameColumn('mobile', 'phone');
            $table->string('email')->nullable()->unique()->after('phone');
            $table->dropColumn('delivery_fee');
        });
    }
};
