<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BranchResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use App\Models\Branch;

class BranchController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $user = Auth::user();

        $branches = Branch::where('tenant_id', $user->tenant_id)
            ->where('is_active', true)
            ->get();

        return BranchResource::collection($branches);
    }
}
