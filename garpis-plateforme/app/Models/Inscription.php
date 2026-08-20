<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class Inscription extends Model
{
    use HasUuids;

    protected $fillable = [
        'apprenant_id', 'session_id', 'antenne_id', 'date_inscription',
        'montant_du', 'remise_montant', 'remise_motif', 'remise_par',
        'remise_validee', 'statut', 'cree_par',
    ];

    protected $casts = [
        'date_inscription' => 'date',
        'remise_validee' => 'boolean',
    ];

    public function apprenant(): BelongsTo
    {
        return $this->belongsTo(Apprenant::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(SessionFormation::class, 'session_id');
    }

    public function antenne(): BelongsTo
    {
        return $this->belongsTo(Antenne::class);
    }

    public function paiements(): HasMany
    {
        return $this->hasMany(Paiement::class)->where('annule', false);
    }

    public function commandesMateriel(): HasMany
    {
        return $this->hasMany(CommandeMateriel::class);
    }

    public function attestation(): HasOne
    {
        return $this->hasOne(Attestation::class);
    }

    /**
     * Solde lu depuis la vue v_solde_inscription : source unique de verite.
     * Ne jamais recalculer un solde ailleurs dans le code.
     */
    public function solde(): int
    {
        $ligne = DB::table('v_solde_inscription')
            ->where('inscription_id', $this->id)
            ->first();

        return (int) ($ligne->solde ?? 0);
    }

    public function detailSolde(): object
    {
        return DB::table('v_solde_inscription')->where('inscription_id', $this->id)->first()
            ?? (object) ['du_formation' => 0, 'du_materiel' => 0, 'total_verse' => 0, 'solde' => 0];
    }

    public function estSoldee(): bool
    {
        return $this->solde() <= 0;
    }

    /** Taux de presence en pourcentage, arrondi. */
    public function tauxPresence(): int
    {
        $total = DB::table('presences_apprenant')->where('inscription_id', $this->id)->count();
        if ($total === 0) {
            return 0;
        }

        $presents = DB::table('presences_apprenant')
            ->where('inscription_id', $this->id)
            ->whereIn('statut', ['PRESENT', 'RETARD'])
            ->count();

        return (int) round($presents / $total * 100);
    }
}
