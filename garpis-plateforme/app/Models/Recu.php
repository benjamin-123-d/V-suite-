<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recu extends Model
{
    use HasUuids;

    protected $table = 'recus';

    public $timestamps = false;

    protected $fillable = ['paiement_id', 'numero', 'exercice', 'reference_facture_normalisee'];

    protected $casts = ['emis_le' => 'datetime'];

    public function paiement(): BelongsTo
    {
        return $this->belongsTo(Paiement::class);
    }
}
