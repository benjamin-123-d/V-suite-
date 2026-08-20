<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Paiement extends Model
{
    use HasUuids;

    protected $table = 'paiements';

    protected $fillable = [
        'inscription_id', 'commande_materiel_id', 'demande_duplicata_id', 'antenne_id',
        'objet', 'montant', 'mode', 'reference_transaction', 'paye_le', 'encaisse_par',
        'annule', 'annule_le', 'annule_par', 'annule_motif',
    ];

    protected $casts = [
        'paye_le' => 'datetime',
        'annule_le' => 'datetime',
        'annule' => 'boolean',
    ];

    public const MODES = [
        'ESPECES' => 'Espèces',
        'MTN_MOMO' => 'MTN MoMo',
        'MOOV_MONEY' => 'Moov Money',
        'VIREMENT' => 'Virement',
        'CHEQUE' => 'Chèque',
        'EN_LIGNE' => 'Paiement en ligne',
    ];

    public function inscription(): BelongsTo
    {
        return $this->belongsTo(Inscription::class);
    }

    public function recu(): HasOne
    {
        return $this->hasOne(Recu::class);
    }

    public function encaisseur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'encaisse_par');
    }

    public function libelleMode(): string
    {
        return self::MODES[$this->mode] ?? $this->mode;
    }
}
