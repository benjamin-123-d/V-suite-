<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalAudit extends Model
{
    protected $table = 'journal_audit';

    public $timestamps = false;

    protected $fillable = ['utilisateur_id', 'action', 'entite', 'entite_id', 'avant', 'apres', 'ip'];

    protected $casts = ['avant' => 'array', 'apres' => 'array', 'survenu_le' => 'datetime'];

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class);
    }
}
