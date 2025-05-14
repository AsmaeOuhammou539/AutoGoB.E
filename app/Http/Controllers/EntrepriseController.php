<?php

namespace App\Http\Controllers;

use App\Models\Entreprise;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class EntrepriseController extends Controller
{
    /**
     * Enregistrer une nouvelle entreprise
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Vérifier que l'utilisateur a le rôle entreprise
        if ($user->role !== 'entreprise') {
            throw ValidationException::withMessages([
                'role' => ['Seuls les utilisateurs avec le rôle entreprise peuvent créer une entreprise.'],
            ]);
        }

        // Vérifier que l'utilisateur n'a pas déjà une entreprise
        if ($user->entreprise) {
            throw ValidationException::withMessages([
                'entreprise' => ['Vous avez déjà une entreprise enregistrée.'],
            ]);
        }

        $request->validate([
            'nom' => 'required|string|max:255',
            'adresse' => 'nullable|string',
            'ville' => 'nullable|string',
            'description' => 'nullable|string',
            'telephone' => 'required|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->except('logo');

        // Gestion du logo
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logos', 'public'); 

            $data['logo'] = Storage::url($path);
        }

        $entreprise = $user->entreprise()->create($data);

        return response()->json([
            'message' => 'Entreprise créée avec succès',
            'entreprise' => $entreprise
        ], 201);
    }

    /**
     * Mettre à jour les informations de l'entreprise
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'entreprise') {
            throw ValidationException::withMessages([
                'role' => ['Seuls les utilisateurs avec le rôle entreprise peuvent modifier une entreprise.'],
            ]);
        }

        $entreprise = $user->entreprise;

        if (!$entreprise) {
            throw ValidationException::withMessages([
                'entreprise' => ['Aucune entreprise trouvée pour cet utilisateur.'],
            ]);
        }

        $request->validate([
            'nom' => 'sometimes|string|max:255',
            'adresse' => 'nullable|string',
            'ville' => 'nullable|string',
            'description' => 'nullable|string',
            'telephone' => 'sometimes|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Mettre à jour les champs
        $entreprise->fill($request->except('logo'));

        // Gestion du logo
        if ($request->hasFile('logo')) {
            // Supprimer l'ancien logo s'il existe
            if ($entreprise->logo) {
                $oldLogoPath = str_replace('/storage', 'public', $entreprise->logo);
                Storage::delete($oldLogoPath);
            }

            $path = $request->file('logo')->store('logos', 'public');

            $entreprise->logo = Storage::url($path);
        }

        $entreprise->save();

        return response()->json([
            'message' => 'Entreprise mise à jour avec succès',
            'entreprise' => $entreprise
        ]);
    }

    /**
     * Récupérer les informations de l'entreprise de l'utilisateur connecté
     */
    public function show()
    {
        $user = Auth::user();

        if ($user->role !== 'entreprise') {
            throw ValidationException::withMessages([
                'role' => ['Seuls les utilisateurs avec le rôle entreprise peuvent accéder à cette fonctionnalité.'],
            ]);
        }

        $entreprise = $user->entreprise;

        if (!$entreprise) {
            return response()->json(['message' => 'Aucune entreprise trouvée'], 404);
        }

        return response()->json($entreprise);
    }

    /**
     * Supprimer l'entreprise
     */
    public function destroy()
    {
        $user = Auth::user();

        if ($user->role !== 'entreprise') {
            throw ValidationException::withMessages([
                'role' => ['Seuls les utilisateurs avec le rôle entreprise peuvent supprimer une entreprise.'],
            ]);
        }

        $entreprise = $user->entreprise;

        if (!$entreprise) {
            throw ValidationException::withMessages([
                'entreprise' => ['Aucune entreprise trouvée pour cet utilisateur.'],
            ]);
        }

        // Supprimer le logo s'il existe
        if ($entreprise->logo) {
            $logoPath = str_replace('/storage', 'public', $entreprise->logo);
            Storage::delete($logoPath);
        }

        $entreprise->delete();

        return response()->json(['message' => 'Entreprise supprimée avec succès']);
    }
}