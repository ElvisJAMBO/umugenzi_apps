<?php

namespace App\Http\Controllers;

use App\Models\Evenement;
use App\Models\Typeticket;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class EvenementController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/evenements",
     *     summary="Récupérer toutes les evenements",
     *     @OA\Response(
     *         response=200,
     *         description="Liste des evenements",
     *         @OA\JsonContent(type="array", @OA\Items(type="string"))
     *     )
     * )
     */
    public function index()
    {
        $evenements = Evenement::with('user','category')
        ->get();

        return response()->json($evenements);
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
     * path="/api/evenements",
     * summary="Ajouter un evenement et ses types de tickets",
     * @OA\RequestBody(
     * required=true,
     * @OA\MediaType(
     * mediaType="multipart/form-data",
     * @OA\Schema(
     * required={"titre", "description", "place", "date_event", "heure", "image", "user_id", "categorie_id", "typetickets"},
     * @OA\Property(property="titre", type="string", description="Titre de l'événement"),
     * @OA\Property(property="description", type="string", description="Description de l'événement"),
     * @OA\Property(property="place", type="string", description="Lieu de l'événement"),
     * @OA\Property(property="date_event", type="string", format="date", description="Date de l'événement (YYYY-MM-DD)"),
     * @OA\Property(property="heure", type="string", description="Heure de l'événement"),
     * @OA\Property(property="image", type="string", format="binary", description="Fichier image"),
     * @OA\Property(property="user_id", type="integer", description="ID de l'utilisateur"),
     * @OA\Property(property="categorie_id", type="integer", description="ID de la catégorie"),
     * @OA\Property(
     * property="typetickets",
     * type="string",
     * description="JSON stringified array of ticket types.",
     * example="[{""nom"":""VIP"",""prix"":150.0,""quantite"":10},{""nom"":""Standard"",""prix"":50.0,""quantite"":100}]"
     * )
     * )
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Événement et tickets créés avec succès."
     * ),
     * @OA\Response(
     * response=422,
     * description="Données de validation manquantes ou invalides."
     * ),
     * @OA\Response(
     * response=500,
     * description="Erreur serveur lors de la création."
     * )
     * )
     */
    public function store(Request $request)
    {
        // Utilisation d'une transaction pour garantir que toutes les opérations
        // se terminent avec succès.
        DB::beginTransaction();

        try {
            // Décodage de la chaîne JSON en tableau
            $typetickets = json_decode($request->input('typetickets'), true);

            // Fusionner le tableau décodé avec le reste de la requête
            $request->merge(['typetickets' => $typetickets]);
            
            $validatedData = $request->validate([
                'titre' => 'required|string|max:255',
                'description' => 'required|string',
                'place' => 'required|string',
                'date_event' => 'required|date',
                'heure' => 'required|string',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'user_id' => 'required|integer|exists:users,id',
                'categorie_id' => 'required|integer|exists:categories,id',
                'typetickets' => 'required|array',
                'typetickets.*.nom' => 'required|string|max:255',
                'typetickets.*.prix' => 'required|numeric|min:0',
                'typetickets.*.quantite' => 'required|integer|min:1',
            ]);

            
            $evenement = new Evenement();
            $evenement->titre = $validatedData['titre'];
            $evenement->description = $validatedData['description'];
            $evenement->place = $validatedData['place'];
            $evenement->date_event = $validatedData['date_event'];
            $evenement->heure = $validatedData['heure'];

            
            $imageName = time().'.'.$request->image->getClientOriginalExtension();
            $request->image->move(public_path('image_events'), $imageName);
            $evenement->image = $imageName;

            $evenement->user_id = $validatedData['user_id'];
            $evenement->categorie_id = $validatedData['categorie_id'];
            $evenement->save();

            foreach ($validatedData['typetickets'] as $typeticketData) {
                $typeticket = new Typeticket();
                $typeticket->nom = $typeticketData['nom'];
                $typeticket->prix = $typeticketData['prix'];
                $typeticket->evenement_id = $evenement->id;
                $typeticket->save();

                $ticket = new Ticket();
                $ticket->typeticket_id = $typeticket->id;
                $ticket->quantite = $typeticketData['quantite'];
                $ticket->save();
            }

            // Si tout s'est bien passé, on valide la transaction
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Événement et tickets créés avec succès.'
            ], 201);

        } catch (\Exception $e) {
            // En cas d'erreur, on annule la transaction
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la création de l\'événement ou des tickets.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/evenements/{id}",
     *     summary="Détailles de l'evenement",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Détailles de l'evenement"
     *     )
     * )
     */
    public function show(Evenement $evenement)
    {
        $evenement->load('user', 'category', 'typetickets');
        return response()->json($evenement);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Evenement $evenement)
    {
        return response()->json($evenement);
    }

    /**
     * @OA\Put(
     *     path="/api/evenements/{id}",
     *     summary="Mise à jour de l'evenement",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"titre","description","place","date_event","heure","image","user_id","categorie_id"},
     *             @OA\Property(property="titre", type="text"),
     *             @OA\Property(property="description", type="text"),
     *             @OA\Property(property="place", type="text"),
     *             @OA\Property(property="date_event", type="date"),
     *             @OA\Property(property="heure", type="text"),
     *             @OA\Property(property="image", type="text"),
     *             @OA\Property(property="user_id", type="text"),
     *             @OA\Property(property="categorie_id", type="text")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Evenement est mise à jour"
     *     ),
    *      @OA\Response(
    *          response=404,
    *          description="Event not found"
    *      )
     * )
     */
    public function update(Request $request, Evenement $evenement)
    {
        $evenement->titre = $request->titre;
        $evenement->description = $request->description;
        $evenement->place = $request->place;
        $evenement->date_event = $request->date_event;
        $evenement->heure = $request->heure;
        $evenement->user_id = $request->user_id;
        $evenement->categorie_id = $request->categorie_id;
        $evenement->save();

        return response()->json([
            'status'=> 'success',
            'message'=> "Event Updated",
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/evenements/{id}",
     *     summary="Supprimer un evenement",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Evenement est supprimé"
     *     )
     * )
     */
    public function destroy(Evenement $evenement)
    {
        $evenement->delete();
        return response()->json("Event Deleted");
    }
}
