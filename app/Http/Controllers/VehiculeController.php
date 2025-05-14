<?php

namespace App\Http\Controllers;

use App\Models\Vehicule;
use App\Models\Marque;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class VehiculeController extends Controller
{
    public function index()
{
    $user = Auth::user();

    if ($user->isClient()) {
        // Client - Afficher tous les véhicules 
        return response()->json(
            Vehicule::with('marque', 'entreprise')
                ->orderBy('created_at', 'desc')
                ->get()
        );
    } else {
        // Entreprise - Afficher seulement ses véhicules
        $entreprise = $user->entreprise;

        if (!$entreprise) {
            return response()->json(['message' => 'Aucune entreprise trouvée'], 404);
        }

        return response()->json(
            $entreprise->vehicules()
                ->with('marque')
                ->orderBy('created_at', 'desc')
                ->get()
        );
    }
}

    public function store(Request $request)
    {
        $user = Auth::user();
        $entreprise = $user->entreprise;

        if (!$entreprise) {
            throw ValidationException::withMessages([
                'entreprise' => ['Vous devez avoir une entreprise pour ajouter des véhicules'],
            ]);
        }

        $request->validate([
            'marque_id' => 'required|exists:marques,id',
            'modele' => 'required|string|max:255',
            'immatriculation' => 'required|string|unique:vehicules',
            'annee' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'couleur' => 'required|string|max:50',
            'boite_vitesse' => 'required|in:manuelle,automatique',
            'type_carburant' => 'required|in:essence,diesel,hybride,electrique',
            'nombre_places' => 'required|integer|min:1|max:20',
            'climatisation' => 'required|boolean',
            'gps' => 'required|boolean',
            'kilometrage' => 'required|integer|min:0',
            'prix_journalier' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'disponibilite' => 'required|in:disponible,reserve,en_maintenance',
            'images' => 'required|array|min:1|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'video' => 'nullable|file|mimes:mp4,mov,avi|max:51200',
        ]);

        $data = $request->except(['images', 'video']);

        // Vérification de la marque
        $marque = Marque::find($request->marque_id);
        if ($marque->entreprise_id !== $entreprise->id) {
            throw ValidationException::withMessages([
                'marque_id' => ['Cette marque ne vous appartient pas'],
            ]);
        }

        // Gestion des images
        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('vehicules', 'public');
                $images[] = Storage::url($path);
            }
            $data['images'] = $images;
        }

        // Gestion de la vidéo
        if ($request->hasFile('video')) {
            $path = $request->file('video')->store('vehicules/videos', 'public');
            $data['video'] = Storage::url($path);
        }

        $vehicule = $entreprise->vehicules()->create($data);

        return response()->json([
            'message' => 'Véhicule ajouté avec succès',
            'vehicule' => $vehicule->load('marque')
        ], 201);
    }

    public function show($id)
    {
        $user = Auth::user();
        
        if ($user->isClient()) {
            // Client - Peut voir n'importe quel véhicule
            $vehicule = Vehicule::with('marque', 'entreprise')
                ->findOrFail($id);
        } else {
            // Entreprise - Ne peut voir que ses propres véhicules
            $vehicule = Vehicule::with('marque', 'entreprise')
                ->where('entreprise_id', $user->entreprise->id)
                ->findOrFail($id);
        }

        return response()->json($vehicule);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $entreprise = $user->entreprise;

        if (!$entreprise) {
            throw ValidationException::withMessages([
                'entreprise' => ['Vous devez avoir une entreprise pour modifier des véhicules'],
            ]);
        }

        $vehicule = $entreprise->vehicules()->findOrFail($id);

        $request->validate([
            'marque_id' => 'sometimes|exists:marques,id',
            'modele' => 'sometimes|string|max:255',
            'immatriculation' => 'sometimes|string|unique:vehicules,immatriculation,' . $vehicule->id,
            'annee' => 'sometimes|integer|min:1900|max:' . (date('Y') + 1),
            'couleur' => 'sometimes|string|max:50',
            'boite_vitesse' => 'sometimes|in:manuelle,automatique',
            'type_carburant' => 'sometimes|in:essence,diesel,hybride,electrique',
            'nombre_places' => 'sometimes|integer|min:1|max:20',
            'climatisation' => 'required|boolean',
            'gps' => 'required|boolean',
            'kilometrage' => 'sometimes|integer|min:0',
            'prix_journalier' => 'sometimes|numeric|min:0',
            'description' => 'nullable|string',
            'disponibilite' => 'sometimes|in:disponible,reserve,en_maintenance',
            'images' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'video' => 'nullable|file|mimes:mp4,mov,avi|max:51200',
        ]);

        $data = $request->except(['images', 'video']);

        // Vérifier la marque si elle est modifiée
        if ($request->has('marque_id')) {
            $marque = Marque::find($request->marque_id);
            if ($marque->entreprise_id !== $entreprise->id) {
                throw ValidationException::withMessages([
                    'marque_id' => ['Cette marque ne vous appartient pas'],
                ]);
            }
        }

        // Gestion des booléens
        $data['climatisation'] = (bool) $request->climatisation;
        $data['gps'] = (bool) $request->gps;

        // Gestion des images
        if ($request->hasFile('images')) {
            // Supprimer les anciennes images
            if ($vehicule->images) {
                foreach ($vehicule->images as $image) {
                    $oldImagePath = str_replace('/storage', 'public', $image);
                    Storage::delete($oldImagePath);
                }
            }

            $images = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('vehicules', 'public');
                $images[] = Storage::url($path);
            }
            $data['images'] = $images;
        }
        if ($request->hasFile('video')) {
            // Supprimer l'ancienne vidéo si elle existe
            if ($vehicule->video) {
                $oldVideoPath = str_replace('/storage', 'public', $vehicule->video);
                Storage::delete($oldVideoPath);
            }

            $path = $request->file('video')->store('vehicules/videos', 'public');
            $data['video'] = Storage::url($path);
        }
        $vehicule->update($data);

        return response()->json([
            'message' => 'Véhicule mis à jour avec succès',
            'vehicule' => $vehicule->load('marque')
        ]);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $entreprise = $user->entreprise;

        if (!$entreprise) {
            throw ValidationException::withMessages([
                'entreprise' => ['Vous devez avoir une entreprise pour supprimer des véhicules'],
            ]);
        }

        $vehicule = $entreprise->vehicules()->findOrFail($id);

        // Supprimer les images associées
        if ($vehicule->images) {
            foreach ($vehicule->images as $image) {
                $imagePath = str_replace('/storage', 'public', $image);
                Storage::delete($imagePath);
            }
        }
        if ($vehicule->video) {
            $videoPath = str_replace('/storage', 'public', $vehicule->video);
            Storage::delete($videoPath);
        }
        $vehicule->delete();

        return response()->json(['message' => 'Véhicule supprimé avec succès']);
    }
}