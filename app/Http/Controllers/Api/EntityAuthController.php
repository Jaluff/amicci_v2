<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\EntityLoginRequest;
use App\Http\Requests\Api\EntityResetPasswordRequest;
use App\Http\Requests\Api\EntitySendResetLinkEmailRequest;
use App\Services\Api\EntityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EntityAuthController extends Controller
{
    public function __construct(
        protected EntityAuthService $authService
    ) {}

    public function login(EntityLoginRequest $request): JsonResponse
    {
        $email = $request->getLoginEmail();
        $password = $request->input('password');

        $authData = $this->authService->authenticate($email, $password);

        if (!$authData) {
            return response()->json(['error' => 'Credenciales inválidas'], 401);
        }

        return response()->json($authData);
    }

    public function sendResetLinkEmail(EntitySendResetLinkEmailRequest $request): JsonResponse
    {
        $email = $request->validated()['email'];
        $this->authService->sendResetLinkEmail($email);

        return response()->json([
            'message' => 'Enlace de restablecimiento enviado al correo.'
        ], 200);
    }

    public function reset(EntityResetPasswordRequest $request): JsonResponse
    {
        $data = $request->validated();
        $success = $this->authService->resetPassword(
            $data['email'],
            $data['token'],
            $data['password']
        );

        if (!$success) {
            return response()->json([
                'error' => 'Token inválido, expirado o correo no encontrado.'
            ], 400);
        }

        return response()->json([
            'message' => 'Contraseña actualizada exitosamente.'
        ], 200);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada exitosamente']);
    }
}
