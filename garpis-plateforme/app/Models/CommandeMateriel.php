<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommandeMateriel extends Model
{
    use HasUuids;

    protected $table = 'commandes_materiel';

    protected $fillable = ['inscription_id', 'libelle', 'montant_du', 'commande_le', 'cree_par'];

    protected $casts = ['commande_le' => 'date'];

    public function inscription(): BelongsTo
    {
        return $this->belongsTo(Inscription::class);
    }
}
