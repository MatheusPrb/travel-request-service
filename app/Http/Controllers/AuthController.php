<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\PromoteUserToAdminRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\AuthResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);


        return response()->json(new AuthResource($user), 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'error' => 'Credenciais inválidas'
            ], 401);
        }

        return $this->respondWithToken($token);
    }

    public function me(): JsonResponse
    {
        return response()->json(new AuthResource(auth('api')->user()));
    }

    public function logout(): JsonResponse
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json([
            'message' => 'Logout realizado com sucesso'
        ]);
    }

    public function refresh(): JsonResponse
    {
        return $this->respondWithToken(JWTAuth::refresh(JWTAuth::getToken()));
    }

    public function promoteToAdmin(PromoteUserToAdminRequest $request): JsonResponse
    {
        $user = User::findOrFail($request->user_id);

        if ($user->isAdmin()) {
            return response()->json(['message' => 'Usuário já é administrador.'], 200);
        }

        $user->makeAdmin();

        return response()->json(['message' => 'Usuário promovido a administrador com sucesso.'], 200);
    }

    protected function respondWithToken(string $token): JsonResponse
    {
        return response()->json([
            'token' => $token,
        ]);
    }
}

