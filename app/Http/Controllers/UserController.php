<?php

namespace App\Http\Controllers;

use App\Models\User; // Assurez-vous d'importer le modèle User
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash; // Pour hacher les mots de passe
use Spatie\Permission\Models\Role; // Pour gérer les rôles

class UserController extends Controller
{
    // public function __construct()
    // {
        
    //     $this->middleware('auth:sanctum');
    //     $this->middleware('can:view users')->only(['index', 'show']);
    //     $this->middleware('can:create users')->only('store');
    //     $this->middleware('can:edit users')->only('update');
    //     $this->middleware('can:delete users')->only('destroy');
    // }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Inclure les rôles et permissions de chaque utilisateur
        $users = User::with('roles', 'permissions')->get();
        return response()->json($users, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage (pour l'admin).
     */
    public function store(Request $request)
    {
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->adresse = $request->adresse;
        $user->password = Hash::make($request->password);
        $user->save();

        if ($request->has('roles')) {
            $user->syncRoles($request->roles); // Assigne les rôles spécifiés
        } else {
            // Si aucun rôle n'est spécifié par l'admin, on peut choisir un rôle par défaut ici aussi
            // Par exemple, si l'admin oublie d'assigner un rôle, on pourrait lui donner 'client' ou 'user'
            $user->assignRole('agent'); // Ou un autre rôle par défaut si l'admin ne spécifie rien
        }

        return response()->json($user->load('roles', 'permissions'), Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return response()->json($user->load('roles', 'permissions'), Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:255|unique:users,phone',
            'adresse' => 'required|string|max:255',
            'photo' => 'required|string|max:255',
            'password' => 'sometimes|string|min:8',
            'roles' => 'sometimes|array',
            'roles.*' => 'string|exists:roles,name',
        ]);

        $user->fill($request->only(['name', 'email']));

        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        if ($request->has('roles')) {
            $user->syncRoles($request->roles); // Synchronise les rôles de l'utilisateur
        }

        return response()->json($user->load('roles', 'permissions'), Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function storeAdmin(Request $request)
    {
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->adresse = $request->adresse;
        $user->password = Hash::make($request->password);
        $user->save();

        if ($request->has('roles')) {
            $user->syncRoles($request->roles); // Assigne les rôles spécifiés
        } else {
            // Si aucun rôle n'est spécifié par l'admin, on peut choisir un rôle par défaut ici aussi
            // Par exemple, si l'admin oublie d'assigner un rôle, on pourrait lui donner 'client' ou 'user'
            $user->assignRole('admin'); // Ou un autre rôle par défaut si l'admin ne spécifie rien
        }

        return response()->json($user->load('roles', 'permissions'), Response::HTTP_CREATED);
    }
}