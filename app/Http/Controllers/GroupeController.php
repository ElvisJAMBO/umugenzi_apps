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
     * @OA\Parameter(
     * name="q",
     * in="query",
     * description="Terme de recherche pour filtrer les groupes par nom",
     * required=false,
     * @OA\Schema(type="string")
     * ),
     * @OA\Response(
     * response=200,
     * description="Liste des groupes paginée",
     * @OA\JsonContent(
     * @OA\Property(
     * property="data",
     * type="array",
     * @OA\Items(
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="nom", type="string", example="Groupe A"),
     * )
     * ),
     * @OA\Property(property="links", type="object"),
     * @OA\Property(property="meta", type="object")
     * )
     * )
     * )
     */
    public function index()
{
    $query = request('q');

    $groupes = Groupe::query();

    if ($query) {
        $groupes->where('name', 'like', "%{$query}%");
    }

    $groupes = $groupes->latest()->paginate(10);

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
            'message' => 'Group Created',
            'data' => $groupe
        ]);
    }

    /**
     * Display the specified resource.
     */

    /**
     * @OA\Get(
     * path="/api/groupes/{groupe}",
     * summary="Récupérer un groupe et ses joueurs",
     * description="Récupère les détails d'un groupe spécifique avec tous les joueurs qui lui sont associés.",
     * @OA\Parameter(
     * name="groupe",
     * in="path",
     * required=true,
     * description="ID du groupe",
     * @OA\Schema(
     * type="integer"
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Détails du groupe et de ses joueurs",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(
     * property="id",
     * type="integer",
     * example=1
     * ),
     * @OA\Property(
     * property="nom",
     * type="string",
     * example="Nom du groupe"
     * ),
     * @OA\Property(
     * property="games",
     * type="array",
     * @OA\Items(
     * type="object",
     * @OA\Property(
     * property="id",
     * type="integer",
     * example=101
     * ),
     * @OA\Property(
     * property="titre",
     * type="string",
     * example="Nom du joueur"
     * )
     * )
     * )
     * )
     * ),
     * @OA\Response(
     * response=404,
     * description="Groupe non trouvé"
     * )
     * )
     */
    public function show(Groupe $groupe)
    {
        $groupe->load('games');
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

        // 1. Vérification minimale : il faut au moins deux joueurs
        if (count($games) < 2) {
            return response()->json([
                'message' => 'Il faut au moins deux joueurs pour effectuer un tirage au sort.',
                'code' => 400
            ], 400);
        }

        $tirages = [];
        $tentativesMax = 100;
        $tentativeActuelle = 0;

        do {
            $tirages = [];
            $joueursDisponibles = $games;
            $joueursCiblesPotentielles = Arr::shuffle($games);

            $reussite = true;

            foreach ($joueursDisponibles as $joueur) {
                $cibleTrouvee = false;
                $tentativesCiblePourJoueur = 0;
                $maxTentativesCiblePourJoueur = count($joueursCiblesPotentielles) * 2;

                while (!$cibleTrouvee && count($joueursCiblesPotentielles) > 0 && $tentativesCiblePourJoueur < $maxTentativesCiblePourJoueur) {
                    $randomIndex = array_rand($joueursCiblesPotentielles);
                    $cible = $joueursCiblesPotentielles[$randomIndex];

                    // Condition principale : le joueur ne peut pas tirer son propre nom
                    if ($joueur !== $cible) {
                        $tirages[$joueur] = $cible;
                        // On retire la cible de la liste des cibles potentielles pour qu'elle ne soit pas réutilisée
                        array_splice($joueursCiblesPotentielles, $randomIndex, 1);
                        $cibleTrouvee = true;
                    }
                    $tentativesCiblePourJoueur++;
                }

                if (!$cibleTrouvee) {
                    $reussite = false;
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

        return response()->json([
            'message' => 'Tirage au sort effectué avec succès.',
            'tirages' => $tirages,
            'code' => 200
        ]);
    }
}
