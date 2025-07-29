<?php

namespace App\Http\Controllers;

use App\Models\Groupe;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

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


    /**
     * @OA\Get(
     *     path="/api/groupe/{id}/tirage",
     *     summary="Résultats de tirage au sort",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Voici le résultat"
     *     )
     * )
     */
    public function effectuerTirageAuSort($groupeId)
    {
        
        $groupe = Groupe::with('games')->findOrFail($groupeId);
        
        $games = $groupe->games->pluck('candidat')->toArray();
        

        // 1. Vérifier si le nombre de joueurs est pair
        if (count($games) % 2 !== 0) {
            return response()->json([
                'message' => 'Le nombre de joueurs doit être pair pour effectuer le tirage au sort.',
                'code' => 400
            ], 400);
        }

        if (count($games) < 2) {
            return response()->json([
                'message' => 'Il faut au moins deux joueurs.',
                'code' => 400
            ], 400);
        }

        $tirages = [];
        $tentativesMax = 100; // Pour éviter les boucles infinies en cas de cas complexe
        $tentativeActuelle = 0;

        do {
            $tirages = [];
            $gamesDisponibles = $games;
            $gamesCibles = Arr::shuffle($games); // Mélangez les noms cibles

            $reussite = true;
            foreach ($gamesDisponibles as $game) {
                $cibleTrouvee = false;
                $tentativesCiblePourJoueur = 0;
                $maxTentativesCiblePourJoueur = count($gamesCibles) * 2;

                while(!$cibleTrouvee && count($gamesCibles) > 0 && $tentativesCiblePourJoueur < $maxTentativesCiblePourJoueur)
                {
                    $randomIndex = array_rand($gamesCibles);
                    $cible = $gamesCibles[$randomIndex];

                    if ($game !== $cible) {
                        $tirages[$game] = $cible;
                        array_splice($gamesCibles, $randomIndex, 1); 
                        $cibleTrouvee = true;
                    }
                    $tentativesCiblePourJoueur++;
                }
                
                if (!$cibleTrouvee) {
                    $reussite = false; // Impossible de trouver une cible pour ce joueur
                    break;
                }
            }
            $tentativeActuelle++;
        } while (!$reussite && $tentativeActuelle < $tentativesMax);


        if (!$reussite) {
            return response()->json([
                'message' => 'Impossible de générer un tirage valide après plusieurs tentatives. Veuillez réessayer.',
                'code' => 500
            ], 500);
        }

        // Afficher les résultats du tirage
        return response()->json([
            'message' => 'Tirage au sort effectué avec succès.',
            'tirages' => $tirages,
            'code' => 200
        ]);
    }
}
