<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->is_active) {
            return response()->json(['message' => 'Account is inactive.'], 403);
        }

        $user->loadMissing('tenant');

        if ($user->role !== 'superadmin') {
            if (! $user->tenant || ! $user->tenant->is_active) {
                return response()->json(['message' => 'Tenant account is inactive.'], 403);
            }

            if ($user->tenant->isTrialExpired()) {
                return response()->json(['message' => 'Your free trial has ended. Please upgrade your plan to continue.'], 403);
            }
        }

        $tokenResult = $user->createToken('api-token');

        return response()->json([
            'token' => $tokenResult->plainTextToken,
            'expires_at' => $tokenResult->accessToken->expires_at?->toDateTimeString(),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'role' => $user->role,
                'tenant_id' => $user->tenant_id,
                'branch_id' => $user->branch_id,
                'must_change_password' => $user->must_change_password,
            ],
            'tenant' => $user->tenant ? [
                'id' => $user->tenant->id,
                'name' => $user->tenant->name,
                'plan' => $user->tenant->plan,
                'trial_ends_at' => $user->tenant->trial_ends_at?->toDateTimeString(),
            ] : null,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $token = $user->currentAccessToken();
        $user->loadMissing('tenant');

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'role' => $user->role,
            'tenant_id' => $user->tenant_id,
            'branch_id' => $user->branch_id,
            'must_change_password' => $user->must_change_password,
            'token_expires_at' => $token->expires_at?->toDateTimeString(),
            'tenant' => $user->tenant ? [
                'id' => $user->tenant->id,
                'name' => $user->tenant->name,
                'plan' => $user->tenant->plan,
                'trial_ends_at' => $user->tenant->trial_ends_at?->toDateTimeString(),
            ] : null,
        ]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8'],
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The provided password is incorrect.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
            'must_change_password' => false,
        ]);

        return response()->json(['message' => 'Password changed successfully.']);
    }
}
