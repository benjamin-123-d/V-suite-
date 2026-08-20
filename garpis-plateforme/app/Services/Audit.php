<?php

namespace App\Services;

use App\Models\JournalAudit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Journal d'audit. Toute operation financiere, toute attestation et toute
 * modification d'utilisateur y passe — dans la meme transaction que l'ecriture
 * metier, sinon on obtient des ecritures sans trace le jour ou ca compte.
 */
class Audit
{
    public static function tracer(string $action, string $entite, ?string $entiteId = null, ?array $avant = null, ?array $apres = null): void
    {
        JournalAudit::create([
            'utilisateur_id' => Auth::id(),
            'action' => $action,
            'entite' => $entite,
            'entite_id' => $entiteId,
            'avant' => $avant,
            'apres' => $apres,
            'ip' => Request::ip(),
        ]);
    }
}
