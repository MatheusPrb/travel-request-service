<?php

namespace App\Http\Middleware;

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

        if (!$user->isAdmin()) {
            return response()->json(['error' => 'Acesso negado. Apenas administradores podem realizar esta ação.'], 403);
        }

        return $next($request);
    }
}
