<?php

namespace App\Http\Controllers;

use App\Models\Attestation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Page publique de verification, hors authentification.
 * La verification est et reste GRATUITE, y compris pour une attestation revoquee.
 * Aucune donnee de contact ni financiere n'est exposee ici.
 */
class VerificationController extends Controller
{
    public function afficher(Request $request, string $code)
    {
        $code = strtoupper(trim($code));

        $attestation = Attestation::with('inscription.session.formation', 'inscription.session.antenne')
            ->where('code_verification', $code)
            ->first();

        // Journalise aussi les codes inconnus : utile pour reperer un scan de masse.
        DB::table('scans_verification')->insert([
            'attestation_id' => $attestation?->id,
            'code_tente' => $code,
            'ip_anonymisee' => $this->anonymiser($request->ip()),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
            'scanne_le' => now(),
        ]);

        return response()
            ->view('public.verification', compact('attestation', 'code'))
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    public function formulaire()
    {
        return response()
            ->view('public.recherche')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    /** Ne conserve que les trois premiers octets d'une IPv4. */
    private function anonymiser(?string $ip): ?string
    {
        if (! $ip) {
            return null;
        }

        $parties = explode('.', $ip);

        return count($parties) === 4
            ? implode('.', array_slice($parties, 0, 3)).'.0'
            : substr($ip, 0, 12).'…';
    }
}
