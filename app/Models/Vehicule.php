<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Entreprise;
use App\Models\Marque;
use App\Models\Reservation;

class Vehicule extends Model
{
    use HasFactory;

    protected $fillable = [
        'entreprise_id',
        'marque_id',
        'modele',
        'immatriculation',
        'annee',
        'couleur',
        'boite_vitesse',
        'type_carburant',
        'nombre_places',
        'nombre_portes',
        'climatisation',
        'gps',
        'kilometrage',
        'prix_journalier',
        'description',
        'disponibilite',
        'images',
        'video'
    ];

    protected $casts = [
        'images' => 'array',
        'climatisation' => 'boolean',
        'gps' => 'boolean'
    ];

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function marque()
    {
        return $this->belongsTo(Marque::class);
    }

    public function isDisponible()
    {
        return $this->disponibilite === 'disponible';
    }
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }


}