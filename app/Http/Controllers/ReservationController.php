<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Ticket;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reservations = Reservation::get();

        return response()->json($reservations);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validation de base des données
        $data = $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
            'quantite' => 'required|integer|min:1',
            // Validation conditionnelle pour l'invité
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            // user_id est requis seulement si non authentifié (pour l'API)
            // Note: Si vous utilisez l'ID de l'utilisateur connecté, ce champ n'est pas nécessaire dans le formulaire.
        ]);


        // 2. Vérification de la disponibilité du ticket
        $ticket = Ticket::find($data['ticket_id']);

        if($ticket->quantite < $data['quantite']) {
            return response()->json(['error' => 'Quantité de tickets non disponible.'], 400);
        }
        
        // 3. Détermination des données de l'utilisateur
        $reservationData = [
            'ticket_id' => $ticket->id,
            'quantite' => $data['quantite'],
        ];

        if (auth()->check()) {
            // Utilisateur connecté : on prend son ID et on ignore les champs name/email/phone
            $reservationData['user_id'] = auth()->id();
            $reservationData['name'] = null;
            $reservationData['email'] = null;
            $reservationData['phone'] = null;
        } else {
            // Utilisateur invité : user_id est null, on utilise les champs name/email/phone du formulaire
            $reservationData['user_id'] = null; // C'est optionnel car $fillable accepte null, mais plus clair
            $reservationData['name'] = $data['name'] ?? null;
            $reservationData['email'] = $data['email'] ?? null;
            $reservationData['phone'] = $data['phone'] ?? null;
            
            // **NOTE IMPORTANTE :** Vous devriez ajouter une validation pour vous assurer que ces champs sont
            // requis si l'utilisateur n'est PAS connecté.
        }


        // 4. Création de la réservation
        $reservation = Reservation::create($reservationData);

        // 5. Mise à jour de la quantité de tickets
        $ticket->quantite -= $data['quantite'];
        $ticket->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Réservation créée',
            'reservation' => $reservation // Utile pour le front-end
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Reservation $reservation)
    {
        return response()->json($reservation);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reservation $reservation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reservation $reservation)
    {
        $ticket = $reservation->ticket_id;
        $ticketn = Ticket::findOrFail($ticket);
        $nombre =  $reservation->quantite;

        if($ticketn->quantite < $request->quantite) {
            return response()->json(['error' => 'Quantite de tickets non disponible.'], 400);
        }

        $reservation->user_id = $request->user_id;
        $reservation->ticket_id = $request->ticket_id;
        $reservation->quantite = $request->quantite;
        $reservation->update();

        $ticketn->quantite = $nombre + $ticketn->quantite - $request->quantite;
        $ticketn->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Reservation Updated'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reservation $reservation)
    {
        $reservation->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Reservation deleted'
        ]);
    }
}
