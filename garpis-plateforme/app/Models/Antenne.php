<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Antenne extends Model
{
    use HasUuids;

    protected $fillable = ['nom', 'code_court', 'ville', 'adresse', 'telephone', 'actif'];

    protected $casts = ['actif' => 'boolean'];

    public function sessions(): HasMany
    {
        return $this->hasMany(SessionFormation::class);
    }

    public function utilisateurs(): HasMany
    {
        return $this->hasMany(Utilisateur::class);
    }
}
