<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Marque;
use App\Models\Vehicule;


class Entreprise extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nom',
        'adresse',
        'ville',
        'description',
        'telephone',
        'logo'
    ];

    // Relation avec l'utilisateur (One-to-One)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function marques()
    {
        return $this->hasMany(Marque::class);
    }
    public function vehicules()
    {
        return $this->hasMany(Vehicule::class);
    }
}
