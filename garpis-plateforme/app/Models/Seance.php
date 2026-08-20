<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Seance extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = ['session_id', 'date_seance', 'heure_debut', 'heure_fin'];

    protected $casts = ['date_seance' => 'date'];

    public function session(): BelongsTo
    {
        return $this->belongsTo(SessionFormation::class, 'session_id');
    }
}
