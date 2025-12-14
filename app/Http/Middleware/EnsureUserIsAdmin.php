<?php

namespace App\Http\Middleware;

use App\Constants\Messages;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = auth('api')->user();

        if (!$user?->fresh()?->isAdmin()) {
            return response()->json(['error' => Messages::UNAUTHORIZED_ACCESS], 403);
        }

        return $next($request);
    }
}
