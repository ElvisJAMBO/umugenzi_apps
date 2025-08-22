<?php

namespace App\Http\Controllers;

use App\Models\User; // Assurez-vous d'importer le modèle User
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash; // Pour hacher les mots de passe
use Spatie\Permission\Models\Role; // Pour gérer les rôles
use App\Mail\UserCreatedNotification;
use Illuminate\Support\Facades\Mail;

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

    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Récupérer toutes les utilisateurs",
     *     @OA\Response(
     *         response=200,
     *         description="Liste des utilisateurs",
     *         @OA\JsonContent(type="array", @OA\Items(type="string"))
     *     )
     * )
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

    /**
     * @OA\Post(
     *     path="/api/users",
     *     summary="Ajouter un utilisateur",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","phone","adresse"},
     *             @OA\Property(property="name", type="text"),
     *             @OA\Property(property="email", type="text"),
     *             @OA\Property(property="phone", type="text"),
     *             @OA\Property(property="adresse", type="text")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User est crée"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->adresse = $request->adresse;
        $user->password = Hash::make("Abcd@1234");
        $user->save();

        if ($request->has('roles')) {
            $user->syncRoles($request->roles); // Assigne les rôles spécifiés
        } else {
            // Si aucun rôle n'est spécifié par l'admin, on peut choisir un rôle par défaut ici aussi
            // Par exemple, si l'admin oublie d'assigner un rôle, on pourrait lui donner 'client' ou 'user'
            $user->assignRole('agent'); // Ou un autre rôle par défaut si l'admin ne spécifie rien
        }

        Mail::to($user->email)->send(new UserCreatedNotification($user));

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

    /**
     * @OA\Put(
     *     path="/api/users/{id}",
     *     summary="Mise à jour de l'utilisateur",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","phone","adresse"},
     *             @OA\Property(property="name", type="text"),
     *             @OA\Property(property="email", type="text"),
     *             @OA\Property(property="phone", type="text"),
     *             @OA\Property(property="adresse", type="text")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User est mise à jour"
     *     ),
    *      @OA\Response(
    *          response=404,
    *          description="User not found"
    *      )
     * )
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

    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     summary="Supprimer un utilisateur",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="User est supprimé"
     *     )
     * )
     */
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }


    /**
     * @OA\Post(
     *     path="/api/storeAdmin",
     *     summary="Ajouter un admin",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","phone","adresse"},
     *             @OA\Property(property="name", type="text"),
     *             @OA\Property(property="email", type="text"),
     *             @OA\Property(property="phone", type="text"),
     *             @OA\Property(property="adresse", type="text")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User est crée"
     *     )
     * )
     */
    public function storeAdmin(Request $request)
    {
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->adresse = $request->adresse;
        $user->password = Hash::make("Abcd@1234");
        $user->save();

        

        return response()->json($user);
    }
}