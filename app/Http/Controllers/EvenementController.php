<?php

namespace App\Http\Controllers;

use App\Models\Evenement;
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
     *     path="/api/evenements",
     *     summary="Ajouter un evenement",
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
     *         response=201,
     *         description="Evenement es crée"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $imagaPath = null;

        $evenement = new Evenement();
        $evenement->titre = $request->titre;
        $evenement->description = $request->description;
        $evenement->place = $request->place;
        $evenement->date_event = $request->date_event;
        $evenement->heure = $request->heure;


        $Photo = $request->image;
        $filePhoto = time().'.'.$Photo->getClientOriginalName();
        
        $request->image->move('image_events',$filePhoto);

        $evenement->image = $filePhoto;
        $evenement->user_id = $request->user_id;
        $evenement->categorie_id = $request->categorie_id;
        $evenement->save();

        return response()->json([
            'status'=> 'success',
            'message'=> "Event Created",
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Evenement $evenement)
    {
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
