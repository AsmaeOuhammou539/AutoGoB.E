<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Entreprise;

class Marque extends Model
{
    use HasFactory;

    protected $fillable = [
        'entreprise_id',
        'nom',
        'logo'
    ];

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }
}