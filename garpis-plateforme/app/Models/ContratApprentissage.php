<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContratApprentissage extends Model
{
    use HasUuids;

    protected $table = 'contrats_apprentissage';

    protected $fillable = [
        'inscription_id', 'numero', 'nom_representant', 'qualite_representant',
        'duree_texte', 'echeancier_texte', 'fonction_signataire',
        'clause_image_signee', 'scan_signe_url',
    ];

    protected $casts = ['clause_image_signee' => 'boolean'];

    public function inscription(): BelongsTo
    {
        return $this->belongsTo(Inscription::class);
    }
}
