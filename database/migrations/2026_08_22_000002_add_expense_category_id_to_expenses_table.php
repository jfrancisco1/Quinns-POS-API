<?php

use App\Models\ExpenseCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('expense_category_id')->nullable()->after('note')
                ->constrained('expense_categories')->restrictOnDelete();
        });

        foreach (DB::table('tenants')->pluck('id') as $tenantId) {
            $existing = DB::table('expense_categories')->where('tenant_id', $tenantId)->pluck('id', 'name');

            foreach (ExpenseCategory::DEFAULT_NAMES as $i => $name) {
                if (! $existing->has($name)) {
                    DB::table('expense_categories')->insert([
                        'tenant_id'  => $tenantId,
                        'name'       => $name,
                        'is_active'  => true,
                        'sort_order' => $i,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $hasUncategorizedExpenses = DB::table('expenses')
                ->where('tenant_id', $tenantId)
                ->whereNull('expense_category_id')
                ->exists();

            if ($hasUncategorizedExpenses) {
                $uncategorizedId = $existing->get(ExpenseCategory::UNCATEGORIZED_NAME)
                    ?? DB::table('expense_categories')->insertGetId([
                        'tenant_id'  => $tenantId,
                        'name'       => ExpenseCategory::UNCATEGORIZED_NAME,
                        'is_active'  => true,
                        'sort_order' => count(ExpenseCategory::DEFAULT_NAMES),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                DB::table('expenses')
                    ->where('tenant_id', $tenantId)
                    ->whereNull('expense_category_id')
                    ->update(['expense_category_id' => $uncategorizedId]);
            }
        }

        Schema::table('expenses', function (Blueprint $table) {
            $table->unsignedBigInteger('expense_category_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('expense_category_id');
        });
    }
};
