<?php

namespace App\Http\Controllers;

use App\Models\Attestation;
use App\Models\Inscription;
use App\Models\SessionFormation;
use App\Services\Audit;
use App\Services\GenerateurDocument;
use App\Services\Numerotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttestationController extends Controller
{
    /** Ecran de generation en lot, sur une session terminee. */
    public function preparer(SessionFormation $session)
    {
        $session->load('formation', 'antenne');

        $inscriptions = $session->inscriptions()
            ->with('apprenant', 'attestation')
            ->get()
            ->map(function (Inscription $i) {
                $i->solde_calcule = $i->solde();
                $i->taux = $i->tauxPresence();

                return $i;
            });

        return view('attestations.preparer', compact('session', 'inscriptions'));
    }

    /**
     * BLOCAGE STRICT : aucune attestation si le solde est superieur a zero.
     * Pas de derogation, y compris pour la Directrice. Le controle est ici,
     * cote serveur — pas seulement sur un bouton grise dans la vue.
     */
    public function generer(Request $request, SessionFormation $session)
    {
        $donnees = $request->validate([
            'inscriptions' => ['required', 'array', 'min:1'],
            'inscriptions.*' => ['uuid'],
        ]);

        $bloquees = DB::table('v_solde_inscription')
            ->whereIn('inscription_id', $donnees['inscriptions'])
            ->where('solde', '>', 0)
            ->pluck('solde', 'inscription_id');

        if ($bloquees->isNotEmpty()) {
            return back()->with('erreur',
                $bloquees->count().' apprenant(s) ont encore un solde à régler. '.
                "Traitez le cas par une remise tracée ou un règlement tiers, jamais en contournant la règle.");
        }

        $exercice = (int) now()->format('Y');
        $creees = 0;

        foreach ($donnees['inscriptions'] as $id) {
            $inscription = Inscription::with('apprenant', 'session.formation', 'session.antenne')->find($id);

            if (! $inscription || $inscription->attestation) {
                continue;   // deja delivree : on ne genere pas deux fois
            }

            DB::transaction(function () use ($inscription, $exercice, $request, &$creees) {
                $formation = $inscription->session->formation;
                $apprenant = $inscription->apprenant;

                $attestation = Attestation::create([
                    'inscription_id' => $inscription->id,
                    'numero' => Numerotation::prochainNumeroAttestation($formation->code, $exercice),
                    'code_verification' => Numerotation::codeVerification(),
                    'donnees_figees' => [
                        'nom_complet' => mb_strtoupper($apprenant->nomComplet()),
                        'accord' => $apprenant->accordSuivi(),
                        'intitule_formation' => $formation->intitule,
                        'libelle_session' => $inscription->session->libelle,
                        'ville_session' => $inscription->session->antenne->ville,
                        'duree_heures' => $formation->duree_heures,
                        'date_debut' => $inscription->session->date_debut?->format('d/m/Y'),
                        'date_fin' => $inscription->session->date_fin?->format('d/m/Y'),
                        'descriptif_competences' => $formation->descriptif_attestation,
                        'ville_signature' => $inscription->session->antenne->ville,
                        'date_delivrance' => now()->translatedFormat('j F Y'),
                        'antenne_nom' => $inscription->session->antenne->nom,
                    ],
                    'delivree_le' => now(),
                    'delivree_par' => $request->user()->id,
                ]);

                // Le numero figure aussi dans les donnees figees : le PDF ne doit
                // dependre d'aucune jointure au moment de la reimpression.
                $figees = $attestation->donnees_figees;
                $figees['numero_attestation'] = $attestation->numero;
                $figees['code_verification'] = $attestation->code_verification;
                $figees['url_verification_courte'] = preg_replace('#^https?://#', '', config('garpis.url_verification'));
                $attestation->update(['donnees_figees' => $figees]);

                Audit::tracer('ATTESTATION_DELIVREE', 'attestation', $attestation->id, null, ['numero' => $attestation->numero]);
                $creees++;
            });
        }

        return redirect()->route('attestations.preparer', $session)
            ->with('succes', $creees.' attestation(s) délivrée(s).');
    }

    public function pdf(Attestation $attestation)
    {
        return GenerateurDocument::attestationPdf($attestation)
            ->stream(str_replace('/', '_', $attestation->numero).'.pdf');
    }

    public function telecharger(Attestation $attestation)
    {
        return GenerateurDocument::attestationPdf($attestation)
            ->download(str_replace('/', '_', $attestation->numero).'.pdf');
    }

    /** Revocation : Directrice uniquement, motif obligatoire. */
    public function revoquer(Request $request, Attestation $attestation)
    {
        $donnees = $request->validate([
            'motif' => ['required', 'string', 'min:5', 'max:255'],
        ], [], ['motif' => 'motif']);

        $attestation->update([
            'revoquee' => true,
            'revoquee_le' => now(),
            'revoquee_par' => $request->user()->id,
            'revocation_motif' => $donnees['motif'],
        ]);

        Audit::tracer('ATTESTATION_REVOQUEE', 'attestation', $attestation->id, null, $donnees);

        return back()->with('succes', "Attestation {$attestation->numero} révoquée. La page publique l'indique immédiatement.");
    }
}
