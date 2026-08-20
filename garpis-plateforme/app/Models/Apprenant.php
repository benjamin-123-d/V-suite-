<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Apprenant extends Model
{
    use HasUuids;

    protected $fillable = [
        'nom', 'prenoms', 'sexe', 'date_naissance', 'photo_url',
        'telephone', 'telephone_2', 'email', 'adresse', 'ville',
        'niveau_etudes', 'profession', 'contact_urgence_nom', 'contact_urgence_tel',
        'piece_type', 'piece_numero', 'piece_scan_url', 'antenne_origine_id',
    ];

    protected $casts = ['date_naissance' => 'date'];

    public function inscriptions(): HasMany
    {
        return $this->hasMany(Inscription::class);
    }

    public function nomComplet(): string
    {
        return trim($this->nom.' '.$this->prenoms);
    }

    /** Accord grammatical des documents officiels. */
    public function accordSuivi(): string
    {
        return $this->sexe === 'F' ? 'la nommée' : 'le nommé';
    }

    public function pronom(): string
    {
        return $this->sexe === 'F' ? 'elle' : 'il';
    }

    /**
     * Doublons probables : meme telephone, ou nom + prenoms tres proches.
     * Appele avant toute creation de fiche.
     */
    public static function doublonsProbables(string $telephone, string $nom, string $prenoms)
    {
        return static::query()
            ->where('telephone', $telephone)
            ->orWhere(function ($q) use ($nom, $prenoms) {
                $q->whereRaw('LOWER(nom) = ?', [mb_strtolower($nom)])
                  ->whereRaw('LOWER(prenoms) LIKE ?', [mb_strtolower(mb_substr($prenoms, 0, 4)).'%']);
            })
            ->limit(5)
            ->get();
    }
}
