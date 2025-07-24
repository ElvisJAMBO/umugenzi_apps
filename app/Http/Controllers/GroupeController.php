<?php

namespace App\Http\Controllers;

use App\Models\Groupe;
use Illuminate\Http\Request;

class GroupeController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/groupes",
     * summary="Récupérer toutes les groupes",
     * @OA\Response(
     * response=200,
     * description="Liste des groupes",
     * )
     * )
     */
    public function index()
    {
        $groupes = Groupe::get();

        return response()->json($groupes);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * @OA\Post(
     *     path="/api/groupes",
     *     summary="Crée nouveau groupe",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id"},
     *             @OA\Property(property="user_id", type="text")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Groupe est crée"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $groupe = new Groupe();
        $groupe->name = 'hello';
        $groupe->user_id = $request->user_id;
        $groupe->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Group Created'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Groupe $groupe)
    {
        return response()->json($groupe);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Groupe $groupe)
    {
        //
    }

    /**
     * @OA\Put(
     *     path="/api/groupes/{id}",
     *     summary="Modifier groupe",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "user_id"},
     *             @OA\Property(property="name", type="text"),
     *             @OA\Property(property="user_id", type="text")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Groupe est mise à jour"
     *     )
     * )
     */
    public function update(Request $request, Groupe $groupe)
    {
        $groupe->name = $request->name;
        $groupe->user_id = $request->user_id;
        $groupe->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Group Updated'
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/groupes/{id}",
     *     summary="Supprimer le groupe",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Groupe est supprimé"
     *     )
     * )
     */
    public function destroy(Groupe $groupe)
    {
        $groupe->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Group Deleted'
        ]);
    }
}
