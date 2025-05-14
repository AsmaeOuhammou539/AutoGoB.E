<?php

namespace Database\Seeders;

use App\Models\Vehicule;
use App\Models\Entreprise;
use App\Models\Marque;
use Illuminate\Database\Seeder;

class VehiculeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $entreprises = Entreprise::all();
        $marques = Marque::all();

        foreach ($entreprises as $entreprise) {
            foreach ($marques->where('entreprise_id', $entreprise->id) as $marque) {
                Vehicule::create([
                    'entreprise_id' => $entreprise->id,
                    'marque_id' => $marque->id,
                    'modele' => 'Modèle ' . fake()->word(),
                    'immatriculation' => strtoupper(fake()->bothify('??-###-??')),
                    'annee' => fake()->numberBetween(2015, 2023),
                    'couleur' => fake()->safeColorName(),
                    'boite_vitesse' => fake()->randomElement(['manuelle', 'automatique']),
                    'type_carburant' => fake()->randomElement(['essence', 'diesel', 'hybride', 'electrique']),
                    'nombre_places' => fake()->numberBetween(2, 7),
                    'climatisation' => fake()->boolean(),
                    'gps' => fake()->boolean(),
                    'kilometrage' => fake()->numberBetween(0, 200000),
                    'prix_journalier' => fake()->randomFloat(2, 100, 500),
                    'description' => fake()->sentence(),
                    'disponibilite' => fake()->randomElement(['disponible', 'reserve', 'en_maintenance']),
                    'images' => json_encode([
                        '/storage/vehicules/image1.jpg',
                        '/storage/vehicules/image2.jpg'
                    ]),
                    'video' => '/storage/vehicules/videos/demo.mp4',
                ]);
            }
        }
    }
}
