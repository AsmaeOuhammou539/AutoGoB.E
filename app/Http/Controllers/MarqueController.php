<?php

namespace App\Http\Controllers;

use App\Models\Marque;
use App\Models\Entreprise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class MarqueController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $entreprise = $user->entreprise;
        
        if (!$entreprise) {
            return response()->json(['message' => 'Aucune entreprise trouvée'], 404);
        }

        return response()->json($entreprise->marques);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $entreprise = $user->entreprise;

        if (!$entreprise) {
            throw ValidationException::withMessages([
                'entreprise' => ['Vous devez avoir une entreprise pour ajouter des marques'],
            ]);
        }

        $request->validate([
            'nom' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->except('logo');

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('marques','public');

            $data['logo'] = Storage::url($path);
        }

        $marque = $entreprise->marques()->create($data);

        return response()->json([
            'message' => 'Marque ajoutée avec succès',
            'marque' => $marque
        ], 201);
    }

}