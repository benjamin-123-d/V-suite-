<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Une session = une instance datee d'une formation dans une antenne.
 * La table s'appelle session_formation : "sessions" est deja pris par Laravel.
 */
class SessionFormation extends Model
{
    use HasUuids;

    protected $table = 'session_formation';

    protected $fillable = [
        'formation_id', 'antenne_id', 'code', 'libelle', 'type',
        'date_debut', 'date_fin', 'jours_horaires', 'lieu',
        'cout', 'capacite_max', 'statut', 'cree_par',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
    ];

    public const TYPES = ['BASE' => 'Session de base', 'PROFESSIONNELLE' => 'Session professionnelle'];

    public const STATUTS = [
        'PLANIFIEE' => 'Planifiée',
        'EN_COURS' => 'En cours',
        'TERMINEE' => 'Terminée',
        'ARCHIVEE' => 'Archivée',
    ];

    public function formation(): BelongsTo
    {
        return $this->belongsTo(Formation::class);
    }

    public function antenne(): BelongsTo
    {
        return $this->belongsTo(Antenne::class);
    }

    public function inscriptions(): HasMany
    {
        return $this->hasMany(Inscription::class, 'session_id');
    }

    public function seances(): HasMany
    {
        return $this->hasMany(Seance::class, 'session_id')->orderBy('date_seance');
    }

    public function libelleType(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function libelleStatut(): string
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }

    /** Genere le prochain code de session : DEC-COT-2026-01 */
    public static function prochainCode(Formation $formation, Antenne $antenne, int $annee): string
    {
        $prefixe = "{$formation->code}-{$antenne->code_court}-{$annee}-";
        $rang = static::where('code', 'like', $prefixe.'%')->count() + 1;

        return $prefixe.str_pad((string) $rang, 2, '0', STR_PAD_LEFT);
    }
}
