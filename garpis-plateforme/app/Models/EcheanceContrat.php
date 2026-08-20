<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class EcheanceContrat extends Model
{
    use HasUuids;

    protected $table = 'echeances_contrat';

    public $timestamps = false;

    protected $fillable = ['contrat_id', 'rang', 'montant', 'date_echeance'];

    protected $casts = ['date_echeance' => 'date'];

    public function estDepassee(): bool
    {
        return $this->date_echeance->isPast();
    }
}
