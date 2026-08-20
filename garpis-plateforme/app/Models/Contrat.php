<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contrat extends Model
{
    use HasUuids;

    protected $fillable = ['tiers_id', 'numero', 'objet', 'montant_total', 'date_debut', 'date_fin', 'statut'];

    protected $casts = ['date_debut' => 'date', 'date_fin' => 'date'];

    public function tiers(): BelongsTo
    {
        return $this->belongsTo(Tiers::class, 'tiers_id');
    }

    public function reglements(): HasMany
    {
        return $this->hasMany(ReglementTiers::class);
    }

    public function echeances(): HasMany
    {
        return $this->hasMany(EcheanceContrat::class)->orderBy('rang');
    }

    public function totalRegle(): int
    {
        return (int) $this->reglements()->sum('montant');
    }

    public function solde(): int
    {
        return $this->montant_total - $this->totalRegle();
    }
}
