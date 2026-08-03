<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * API auth. The Blade UI itself logs in via the session-based
 * App\Http\Controllers\Auth\LoginController (POST /login, cookie
 * session) — once that session exists, Sanctum's stateful-domain
 * middleware authenticates every /api/* call automatically, so me()
 * just reads $request->user().
 *
 * This POST /api/login (public, no auth:sanctum) is a separate path
 * for non-browser API clients (mobile apps, external integrations)
 * that can't use a cookie session — it issues a bearer token instead.
 */
class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (! Auth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Those credentials do not match our records.',
            ], 422);
        }

        $user = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user(),
        ]);
    }

    public function logout(Request $request)
    {
        // Bearer-token clients: revoke just the token used for this request.
        if ($token = $request->user()?->currentAccessToken()) {
            $token->delete();
        }

        // Cookie/session clients (the Blade UI): also tear down the session.
        if ($request->hasSession()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    public function logoutAll(Request $request)
    {
        $request->user()?->tokens()->delete();

        if ($request->hasSession()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out from all devices successfully',
        ]);
    }
}