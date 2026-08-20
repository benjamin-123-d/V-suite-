<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attestation extends Model
{
    use HasUuids;

    protected $fillable = [
        'inscription_id', 'numero', 'code_verification', 'donnees_figees',
        'delivree_le', 'delivree_par', 'revoquee', 'revoquee_le',
        'revoquee_par', 'revocation_motif',
    ];

    protected $casts = [
        'donnees_figees' => 'array',
        'delivree_le' => 'datetime',
        'revoquee_le' => 'datetime',
        'revoquee' => 'boolean',
    ];

    public function inscription(): BelongsTo
    {
        return $this->belongsTo(Inscription::class);
    }

    public function urlVerification(): string
    {
        return config('garpis.url_verification').'/a/'.$this->code_verification;
    }
}
