<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role === 'superadmin') {
            return $next($request);
        }

        $tenant = $user->tenant;

        if (! $tenant || ! $tenant->is_active) {
            return response()->json(['message' => 'Tenant account is inactive.'], 403);
        }

        if ($tenant->isTrialExpired()) {
            return response()->json(['message' => 'Your free trial has ended. Please upgrade your plan to continue.'], 403);
        }

        return $next($request);
    }
}
