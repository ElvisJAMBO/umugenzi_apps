<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;

class GameController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/games",
     * summary="Récupérer toutes les games",
     * @OA\Response(
     * response=200,
     * description="Liste des games",
     * )
     * )
     */
    public function index()
    {
        $games = Game::get();

        return response()->json($games);
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
     *     path="/api/games",
     *     summary="Crée nouveau jeu",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"groupe_id", "candidat"},
     *             @OA\Property(property="groupe_id", type="text"),
     *             @OA\Property(property="candidat", type="text")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Jeu est crée"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $game = new Game();
        $game->groupe_id = $request->groupe_id;
        $game->candidat = $request->candidat;
        $game->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Super'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Game $game)
    {
        return response()->json($game);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Game $game)
    {
        //
    }

    /**
     * @OA\Put(
     *     path="/api/games/{id}",
     *     summary="Modifier Jeu",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"groupe_id", "candidat"},
     *             @OA\Property(property="groupe_id", type="text"),
     *             @OA\Property(property="candidat", type="text")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Jeu est mise à jour"
     *     )
     * )
     */
    public function update(Request $request, Game $game)
    {
        $game->groupe_id = $request->groupe_id;
        $game->candidat = $request->candidat;
        $game->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Super'
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/games/{id}",
     *     summary="Supprimer le Jeu",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Jeu est supprimé"
     *     )
     * )
     */
    public function destroy(Game $game)
    {
        $game->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Super'
        ]);
    }
}
