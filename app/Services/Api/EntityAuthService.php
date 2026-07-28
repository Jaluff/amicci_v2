<?php

namespace App\Services\Api;

use App\Models\Party;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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
            'entidad' => [
                'id' => $party->id,
                'name' => $party->name,
                'email' => $party->email,
                'entidad_nombre' => $party->name,
                'correo' => $party->email,
            ],
        ];
    }

    /**
     * Enviar enlace de restablecimiento de contraseña por correo.
     */
    public function sendResetLinkEmail(string $email, ?string $redirectUrl = null): bool
    {
        $token = Str::random(60);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => $token,
                'created_at' => Carbon::now()
            ]
        );

        $baseUrl = $redirectUrl ?: env('CLIENT_APP_URL', env('FRONTEND_URL', 'https://transporteamicci.com.ar/amicci-web'));
        $baseUrl = rtrim($baseUrl, '/');

        $resetUrl = "{$baseUrl}/password/reset/{$token}?email=" . urlencode($email);

        Mail::send('emails.password_reset', ['url' => $resetUrl], function ($message) use ($email) {
            $message->to($email)
                ->subject('Restablecimiento de Contraseña - Amicci');
        });

        return true;
    }

    /**
     * Restablecer la contraseña de una entidad dada.
     */
    public function resetPassword(string $email, string $token, string $password): bool
    {
        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('token', $token)
            ->first();

        if (!$passwordReset || Carbon::parse($passwordReset->created_at)->addHours(1)->isPast()) {
            return false;
        }

        $party = Party::where('email', $email)->first();

        if (!$party) {
            return false;
        }

        $party->update([
            'password' => Hash::make($password)
        ]);

        DB::table('password_reset_tokens')->where('email', $email)->delete();

        return true;
    }
}
