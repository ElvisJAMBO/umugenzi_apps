<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class RoleController extends Controller
{
    // public function __construct()
    // {
        
    //     $this->middleware('auth:sanctum');
    //     $this->middleware('can:view roles')->only(['index', 'show']);
    //     $this->middleware('can:create roles')->only('store');
    //     $this->middleware('can:edit roles')->only('update');
    //     $this->middleware('can:delete roles')->only('destroy');
    // }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::with('permissions')->get(); // Inclut les permissions associées à chaque rôle
        return response()->json($roles, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'array', // Tableau d'IDs ou de noms de permissions
            'permissions.*' => 'string|exists:permissions,name', // Chaque élément doit être une permission existante
        ]);

        $role = Role::create(['name' => $request->name]);

        if ($request->has('permissions')) {
            $role->givePermissionTo($request->permissions);
        }

        return response()->json($role->load('permissions'), Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        return response()->json($role->load('permissions'), Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role->update(['name' => $request->name]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions); // Synchronise les permissions
        }

        return response()->json($role->load('permissions'), Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        $role->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Assign a role to a user.
     * Cette méthode n'est pas standard pour un contrôleur de ressource, mais utile pour l'administration.
     */
    public function assignRoleToUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_name' => 'required|exists:roles,name',
        ]);

        $user = \App\Models\User::find($request->user_id);
        $user->assignRole($request->role_name);

        return response()->json(['message' => 'Rôle assigné avec succès'], Response::HTTP_OK);
    }

    /**
     * Revoke a role from a user.
     * Cette méthode n'est pas standard pour un contrôleur de ressource, mais utile pour l'administration.
     */
    public function revokeRoleFromUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_name' => 'required|exists:roles,name',
        ]);

        $user = \App\Models\User::find($request->user_id);
        $user->removeRole($request->role_name);

        return response()->json(['message' => 'Rôle révoqué avec succès'], Response::HTTP_OK);
    }
}