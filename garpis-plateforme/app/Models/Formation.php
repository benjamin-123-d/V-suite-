<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Formation extends Model
{
    use HasUuids;

    protected $fillable = [
        'code', 'intitule', 'domaine', 'description',
        'duree_heures', 'cout_reference', 'descriptif_attestation', 'actif',
    ];

    protected $casts = ['actif' => 'boolean'];

    public function sessions(): HasMany
    {
        return $this->hasMany(SessionFormation::class);
    }

    public function chapitres(): HasMany
    {
        return $this->hasMany(Chapitre::class)->orderBy('ordre');
    }
}
