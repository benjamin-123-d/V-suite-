<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ReglementTiers extends Model
{
    use HasUuids;

    protected $table = 'reglements_tiers';

    protected $fillable = ['contrat_id', 'montant', 'mode', 'recu_le', 'saisi_par'];

    protected $casts = ['recu_le' => 'date'];
}
