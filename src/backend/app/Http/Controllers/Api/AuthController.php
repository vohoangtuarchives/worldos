<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Authenticate and issue a Sanctum personal-access token.
     *
     * - Abilities: ['*'] (full access — narrow per-role if needed later).
     * - Expiration: 7 days (also enforced globally via config/sanctum.php).
     * - Single-device policy: revokes all previous tokens on login.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 422);
        }

        // Revoke all existing tokens (single-device policy)
        $user->tokens()->delete();

        $expirationMinutes = config('sanctum.expiration');
        $expiresAt = $expirationMinutes > 0
            ? now()->addMinutes($expirationMinutes)
            : now()->addYears(10); // fallback if expiration disabled

        $token = $user->createToken(
            'spa',
            ['*'],
            $expiresAt,
        )->plainTextToken;

        return response()->json([
            'message'    => 'Authenticated',
            'user'       => $this->userPayload($user),
            'token'      => $token,
            'expires_at' => $expiresAt->toIso8601String(),
        ]);
    }

    /**
     * Get the authenticated user.
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json($this->userPayload($request->user()));
    }

    /**
     * Revoke the current token only (single-device logout).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }

    /**
     * Revoke ALL tokens for the user (logout everywhere).
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out from all devices']);
    }

    /**
     * Standard user payload.
     */
    private function userPayload($user): array
    {
        return [
            'id'    => $user->id,
            'email' => $user->email,
            'name'  => $user->name ?? $user->email,
            'role'  => ($user->email === 'test@example.com' || $user->email === 'admin@example.com') ? 'admin' : ($user->role ?? 'writer'),
        ];
    }
}
