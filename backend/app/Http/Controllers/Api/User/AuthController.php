<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    //Inscription d'un Utilisateur
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string'],
            'email' => ['required', 'string', 'email', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        //creation d'utilisateur
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Créer le token Sanctum
        
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Utilisateur créé avec succès',
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ], 201);
    }

    //Se connecter
    
    public function login(Request $request)
    {
        //Validation
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        //Gestion d'erreur de validation
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        //trouver l'utilisateur par email
        $user = User::where('email', $request->email)->first();

        //Verifier la connection
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Identifiants incorrects'
            ], 401);
        }

        // Supprimer les anciens tokens
        $user->tokens()->delete();

        // Créer un nouveau token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Connexion réussie',
            'user' => $user->only(['id', 'name', 'email', 'is_admin']),
            'token' => $token,
            'token_type' => 'Bearer'
        ]);
    }
    
    //Deconnecter
    
    public function logout(Request $request)
    {
        // Supprimer le token actuel
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Déconnexion réussie'
        ]);
    }
}
