<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Vehicule;

class Reservation extends Model
{
    protected $fillable = [
        'user_id',
        'vehicule_id',
        'date_debut',
        'date_fin',
        'statut'
    ];
    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
    ];
    public function user() {
        return $this->belongsTo(User::class);
    }
    
    public function vehicule() {
        return $this->belongsTo(Vehicule::class);
    }

    public function getNbrJoursAttribute()
    {
        $jours = $this->date_debut->diffInDays($this->date_fin) + 1;
        return $jours ;
    }
    public function getPrixTotalAttribute()
    {
        $jours = $this->date_debut->diffInDays($this->date_fin) + 1;
        return $jours * $this->vehicule->prix_journalier;
    }
    protected $appends = ['nbr_jours', 'prix_total'];
}
