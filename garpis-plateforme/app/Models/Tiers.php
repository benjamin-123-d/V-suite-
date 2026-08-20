<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tiers extends Model
{
    use HasUuids;

    protected $table = 'tiers';

    protected $fillable = ['raison_sociale', 'type', 'contact_nom', 'contact_tel', 'contact_email', 'actif'];

    protected $casts = ['actif' => 'boolean'];

    public function contrats(): HasMany
    {
        return $this->hasMany(Contrat::class, 'tiers_id');
    }
}
