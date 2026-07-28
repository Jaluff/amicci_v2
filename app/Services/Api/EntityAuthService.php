<?php

namespace App\Services\Api;

use App\Models\Party;
use Illuminate\Support\Facades\Hash;

class EntityAuthService
{
    /**
     * Autenticar entidad por email y contraseña.
     */
    public function authenticate(string $email, string $password): ?array
    {
        $party = Party::where('email', $email)->first();

        if (!$party || !Hash::check($password, $party->password)) {
            return null;
        }

        // Crear token Sanctum para la entidad
        $token = $party->createToken('entity-api-token')->plainTextToken;

        return [
            'token' => $token,
            'entidad_id' => $party->id,
            'entidad' => $party,
        ];
    }
}
