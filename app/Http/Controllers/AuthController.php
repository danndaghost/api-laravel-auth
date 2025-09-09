<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetMail;

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
            Log::info('Login attempt started for email: ' . $request->email);
            
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            Log::info('Validation passed, searching for user');
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                Log::info('User not found for email: ' . $request->email);
                return response()->json([
                    'message' => 'Credenciales incorrectas'
                ], 401);
            }

            Log::info('User found, checking password');
            if (!Hash::check($request->password, $user->password)) {
                Log::info('Password check failed for user: ' . $user->id);
                return response()->json([
                    'message' => 'Credenciales incorrectas'
                ], 401);
            }

            Log::info('Password check passed, loading user relations');
            // Cargar roles y permisos del usuario
            $user->load(['roles.permissions', 'permissions']);
            $user->all_permissions = $user->getAllPermissions();

            Log::info('User relations loaded, creating token');
            $token = $user->createToken('auth_token');

            if (!$token) {
                Log::error('Failed to create token for user: ' . $user->id);
                return response()->json([
                    'message' => 'Error al generar el token'
                ], 500);
            }

            Log::info('Token created successfully for user: ' . $user->id);
            return response()->json([
                'user' => $user,
                'access_token' => $token->accessToken,
                'token_type' => 'Bearer',
            ]);

        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
            Log::error('Login error trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'message' => 'Error en el proceso de login',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function me(Request $request)
    {
        $user = $request->user();
        
        // Cargar roles y permisos
        $user->load(['roles.permissions', 'permissions']);
        $user->all_permissions = $user->getAllPermissions();
        
        return response()->json($user);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'message' => 'Sesión cerrada correctamente',
        ]);
    }

    public function requestPasswordReset(Request $request)
    {
        try {
            Log::info('Password reset request started for email: ' . $request->email);
            
            $request->validate([
                'email' => 'required|email',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                Log::info('User not found for password reset: ' . $request->email);
                // Por seguridad, siempre retornamos el mismo mensaje
                return response()->json([
                    'message' => 'Si el correo existe en nuestro sistema, recibirás un enlace de recuperación.'
                ]);
            }

            // Generar token de reset
            $token = Str::random(64);
            
            // Guardar el token en la tabla password_resets
            DB::table('password_resets')->updateOrInsert(
                ['email' => $request->email],
                [
                    'email' => $request->email,
                    'token' => Hash::make($token),
                    'created_at' => now()
                ]
            );

            // Crear URL de reset
            $resetUrl = env('FRONTEND_URL', 'http://localhost:3000') . '/reset-password/confirm?token=' . $token . '&email=' . urlencode($request->email);
            
            // Enviar email de recuperación
            try {
                Mail::to($request->email)->send(new PasswordResetMail(
                    $resetUrl,
                    $user ? $user->name : null,
                    $request->email
                ));
                
                Log::info('Password reset email sent successfully to: ' . $request->email);
            } catch (\Exception $e) {
                Log::error('Failed to send password reset email: ' . $e->getMessage());
                // Continuar sin fallar, por si hay problemas con el servicio de email
            }

            // Loggear la URL para desarrollo
            Log::info('Password reset URL generated: ' . $resetUrl);

            return response()->json([
                'message' => 'Si el correo existe en nuestro sistema, recibirás un enlace de recuperación.',
                // Solo para desarrollo - remover en producción
                'reset_url' => config('app.debug') ? $resetUrl : null
            ]);

        } catch (\Exception $e) {
            Log::error('Password reset request error: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al procesar la solicitud'
            ], 500);
        }
    }

    public function confirmPasswordReset(Request $request)
    {
        try {
            Log::info('Password reset confirmation started');
            
            $request->validate([
                'token' => 'required',
                'email' => 'required|email',
                'password' => 'required|min:8|confirmed',
            ]);

            // Buscar el token en la tabla password_resets
            $resetRecord = DB::table('password_resets')
                ->where('email', $request->email)
                ->first();

            if (!$resetRecord) {
                Log::info('No reset record found for email: ' . $request->email);
                return response()->json([
                    'message' => 'Token de recuperación inválido o expirado.'
                ], 400);
            }

            // Verificar que el token coincida
            if (!Hash::check($request->token, $resetRecord->token)) {
                Log::info('Invalid token for email: ' . $request->email);
                return response()->json([
                    'message' => 'Token de recuperación inválido o expirado.'
                ], 400);
            }

            // Verificar que el token no haya expirado (24 horas)
            if (now()->diffInHours($resetRecord->created_at) > 24) {
                Log::info('Expired token for email: ' . $request->email);
                
                // Eliminar token expirado
                DB::table('password_resets')->where('email', $request->email)->delete();
                
                return response()->json([
                    'message' => 'El token de recuperación ha expirado. Solicita uno nuevo.'
                ], 400);
            }

            // Buscar el usuario
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                Log::info('User not found for password reset confirmation: ' . $request->email);
                return response()->json([
                    'message' => 'Usuario no encontrado.'
                ], 404);
            }

            // Actualizar la contraseña
            $user->password = Hash::make($request->password);
            $user->remember_token = Str::random(60);
            $user->save();

            // Eliminar el token usado
            DB::table('password_resets')->where('email', $request->email)->delete();

            // Revocar todos los tokens existentes del usuario
            $user->tokens()->delete();

            Log::info('Password reset completed successfully for user: ' . $user->id);

            return response()->json([
                'message' => 'Contraseña restablecida exitosamente.'
            ]);

        } catch (\Exception $e) {
            Log::error('Password reset confirmation error: ' . $e->getMessage());
            Log::error('Password reset confirmation error trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'message' => 'Error al restablecer la contraseña'
            ], 500);
        }
    }

    public function changePassword(Request $request)
    {
        try {
            Log::info('Change password attempt for user: ' . Auth::id());
            
            $request->validate([
                'current_password' => 'required',
                'password' => 'required|string|min:6|confirmed',
            ]);

            $user = Auth::user();

            // Verificar que la contraseña actual sea correcta
            if (!Hash::check($request->current_password, $user->password)) {
                Log::info('Invalid current password for user: ' . $user->id);
                return response()->json([
                    'message' => 'La contraseña actual es incorrecta.'
                ], 400);
            }

            // Verificar que la nueva contraseña sea diferente a la actual
            if (Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'La nueva contraseña debe ser diferente a la actual.'
                ], 400);
            }

            // Actualizar la contraseña
            $user->password = Hash::make($request->password);
            $user->remember_token = Str::random(60);
            $user->save();

            Log::info('Password changed successfully for user: ' . $user->id);

            return response()->json([
                'message' => 'Contraseña cambiada exitosamente.',
                'user' => $user->load('roles.permissions')
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::info('Validation error in change password: ' . json_encode($e->errors()));
            return response()->json([
                'message' => 'Datos de validación incorrectos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Change password error: ' . $e->getMessage());
            Log::error('Change password error trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }
}