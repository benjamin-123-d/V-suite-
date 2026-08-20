<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemandeDuplicata extends Model
{
    use HasUuids;

    protected $table = 'demandes_duplicata';

    protected $fillable = [
        'attestation_id', 'jeton', 'montant', 'statut',
        'initiee_par', 'initiee_le', 'expire_le', 'paye_le', 'reference_paiement',
    ];

    protected $casts = [
        'initiee_le' => 'datetime',
        'expire_le' => 'datetime',
        'paye_le' => 'datetime',
    ];

    public function attestation(): BelongsTo
    {
        return $this->belongsTo(Attestation::class);
    }

    public function estExploitable(): bool
    {
        return $this->statut === 'PAYE';
    }
}
