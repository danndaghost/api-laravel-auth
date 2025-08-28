<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        // Asignar rol por defecto
        $user->assignRole('viewer');

        return response()->json([
            'message' => 'Usuario registrado correctamente',
            'user'    => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'Credenciales incorrectas'
                ], 401);
            }

            $token = $user->createToken('auth_token');

            if (!$token) {
                Log::error('Failed to create token for user: ' . $user->id);
                return response()->json([
                    'message' => 'Error al generar el token'
                ], 500);
            }

            return response()->json([
                'user' => $user,
                'access_token' => $token->accessToken,
                'token_type' => 'Bearer',
            ]);

        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'message' => 'Error en el proceso de login',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function me(Request $request)
    {
        return response()->json([
            'user'  => $request->user(),
            'roles' => $request->user()->getRoleNames(),
            'permissions' => $request->user()->getAllPermissions()->pluck('name'),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'message' => 'Sesión cerrada correctamente',
        ]);
    }
}