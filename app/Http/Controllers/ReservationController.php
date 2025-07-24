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
        $ticket = Ticket::find($request->ticket_id);

        if($ticket->quantite < $request->quantite) {
            return response()->json(['error' => 'Quantite de tickets non disponible.'], 400);
        }

        $reservation = Reservation::create([
            'user_id' => $request->user_id,
            'ticket_id' => $ticket->id,
            'quantite' => $request->quantite,
        ]);

        $ticket->quantite -= $request->quantite;
        $ticket->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Reservation Created'
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
