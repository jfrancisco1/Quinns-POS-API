<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

abstract class BaseService
{
    /** Local timezone clients send date-only from/to filters in. */
    protected const LOCAL_TIMEZONE = 'Asia/Manila';

    protected abstract function model(): string;

    /** Converts local "YYYY-MM-DD" from/to into UTC boundaries covering the full local day. */
    protected function localDayRangeUtc(string $from, string $to): array
    {
        $start = Carbon::parse($from, self::LOCAL_TIMEZONE)->startOfDay()->setTimezone('UTC');
        $end   = Carbon::parse($to, self::LOCAL_TIMEZONE)->endOfDay()->setTimezone('UTC');

        return [$start, $end];
    }

    protected function tenantScope(): Builder
    {
        $user = Auth::user();
        $model = $this->model();

        $tenant_id = $user?->tenant_id ?? 1;
        $branch_id = $user?->branch_id ?? 1;
        $role = $user?->role ?? 'admin';

        $query = $model::where('tenant_id', $tenant_id);

        // staff and delivery are scoped to their branch only
        // admin sees all branches
        if (in_array($role, ['staff', 'delivery'])) {
            $query->where('branch_id', $branch_id);
        }

        return $query;
    }

    protected function authorizeTenant(Model $model): void
    {
        $user = Auth::user();
        if (!$user)
            return; // skip auth check for now

        if ($model->tenant_id !== $user->tenant_id) {
            abort(403, 'Unauthorized');
        }

        if ($user->role === 'staff' && $model->branch_id !== $user->branch_id) {
            abort(403, 'Unauthorized');
        }
    }
}
