<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User; 
use Illuminate\Support\Facades\Hash; 
use Spatie\Permission\Models\Role;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required', // Nom de l'appareil client (ex: 'mobile_app', 'web_browser')
        ]);

        if (! Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Les identifiants fournis sont incorrects.'],
            ]);
        }

        $user = $request->user();

        // Créer un token d'API
        $token = $user->createToken($request->device_name)->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
            return response()->json(['message' => 'Déconnexion réussie'], 200);
        }
        return response()->json(['message' => 'Aucun utilisateur authentifié pour la déconnexion.'], 401);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'required|string|max:255|unique:users,phone',
            'adresse' => 'required|string|max:255',
            'photo' => 'nullable|string|max:255',
            'password' => 'required|string|min:8|confirmed', // 'confirmed' nécessite un champ password_confirmation
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'adresse' => $request->adresse,
            'photo' => $request->photo,
            'password' => Hash::make($request->password),
        ]);

        // Assigner le rôle 'client' par défaut
        $clientRole = Role::where('name', 'client')->first();
        if ($clientRole) {
            $user->assignRole($clientRole);
        } else {
            // Gérer le cas où le rôle 'client' n'existe pas (log, créer, etc.)
            // Pour l'instant, nous allons juste loguer une erreur.
            \Log::error("Le rôle 'client' n'existe pas lors de l'enregistrement de l'utilisateur.");
        }

        // Vous pouvez choisir de générer un token ici ou laisser l'utilisateur se connecter après l'enregistrement
        $token = $user->createToken('registration_token')->plainTextToken;

        return response()->json(['message' => 'Compte créé avec succès!', 'user' => $user->load('roles'), 'token' => $token], 201);
    }
}