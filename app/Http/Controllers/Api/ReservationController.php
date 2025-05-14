<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function checkAvailability(Request $request)
    {
        $request->validate([
            'vehicule_id' => 'required|exists:vehicules,id',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
        ]);

        // Récupérer toutes les réservations actives pour ce véhicule
        $reservations = Reservation::where('vehicule_id', $request->vehicule_id)
            ->whereNotIn('statut', ['annulée', 'refusée', 'terminée'])
            ->get();

        // Préparer la liste de toutes les dates indisponibles
        $unavailableDates = [];

        foreach ($reservations as $reservation) {
            $start = Carbon::parse($reservation->date_debut);
            $end = Carbon::parse($reservation->date_fin);
            
            // Ajouter toutes les dates de cette réservation
            for ($date = $start; $date->lte($end); $date->addDay()) {
                $unavailableDates[] = $date->format('Y-m-d');
            }
        }

        // Vérifier si la période demandée est disponible
        $requestStart = Carbon::parse($request->date_debut);
        $requestEnd = Carbon::parse($request->date_fin);
        $isAvailable = true;

        foreach ($unavailableDates as $date) {
            $unavailableDate = Carbon::parse($date);
            if ($unavailableDate->between($requestStart, $requestEnd)) {
                $isAvailable = false;
                break;
            }
        }

        return response()->json([
            'disponible' => $isAvailable,
            'message' => $isAvailable ? 'Disponible' : 'Non disponible pour certaines dates',
            'dates_indisponibles' => array_values(array_unique($unavailableDates))
        ]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'vehicule_id' => 'required|exists:vehicules,id',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
        ]);

        $reservation = Reservation::create([
            'user_id' => auth()->id(),
            'vehicule_id' => $request->vehicule_id,
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
            'statut' => 'en_attente',
        ]);

        return response()->json($reservation, 201);

    }
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isClient()) {
            $reservations = $user->reservations()->with(['vehicule.marque', 'user'])->get();
        } else {
            // Pour les entreprises : leurs véhicules réservés
            $reservations = Reservation::whereHas('vehicule', function ($q) use ($user) {
                $q->where('entreprise_id', $user->entreprise->id);
            })->with(['user', 'vehicule.marque'])->get();
        }

        return response()->json($reservations);
    }
    

    public function updateStatus(Request $request, Reservation $reservation)
    {
        $request->validate(['statut' => 'required|in:confirmée,refuser,annulée,terminée']);

        // Vérifier que l'utilisateur est bien propriétaire du véhicule
        // if ($request->user()->entreprise->id !== $reservation->vehicule->entreprise_id) {
        //     abort(403, 'Unauthorized');
        // }

        $reservation->update(['statut' => $request->statut]);

        // Ici tu pourrais ajouter une notification au client

        return response()->json($reservation);
    }
}
