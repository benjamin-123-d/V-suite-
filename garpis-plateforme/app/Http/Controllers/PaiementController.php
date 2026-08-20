<?php

namespace App\Http\Controllers;

use App\Models\Inscription;
use App\Models\Paiement;
use App\Models\Recu;
use App\Services\Audit;
use App\Services\GenerateurDocument;
use App\Services\Numerotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaiementController extends Controller
{
    public function formulaire(Inscription $inscription)
    {
        $inscription->load('apprenant', 'session.formation', 'antenne');

        return view('paiements.creer', [
            'inscription' => $inscription,
            'detail' => $inscription->detailSolde(),
            'modes' => Paiement::MODES,
        ]);
    }

    /**
     * Encaissement.
     * Trois regles tiennent dans ce endpoint : transaction unique,
     * numero de recu sous verrou, audit dans la meme transaction.
     */
    public function enregistrer(Request $request, Inscription $inscription)
    {
        $donnees = $request->validate([
            'montant' => ['required', 'integer', 'min:1'],
            'mode' => ['required', 'in:'.implode(',', array_keys(Paiement::MODES))],
            'reference_transaction' => ['nullable', 'string', 'max:120'],
            'objet' => ['required', 'in:FORMATION,MATERIEL'],
        ], [], ['montant' => 'montant', 'mode' => 'mode de paiement']);

        $this->verifierAntenne($request, $inscription->antenne_id);

        $recu = DB::transaction(function () use ($donnees, $inscription, $request) {
            $paiement = Paiement::create([
                'inscription_id' => $inscription->id,
                'antenne_id' => $inscription->antenne_id,
                'objet' => $donnees['objet'],
                'montant' => $donnees['montant'],
                'mode' => $donnees['mode'],
                'reference_transaction' => $donnees['reference_transaction'] ?? null,
                'paye_le' => now(),
                'encaisse_par' => $request->user()->id,
            ]);

            $exercice = (int) now()->format('Y');

            $recu = Recu::create([
                'paiement_id' => $paiement->id,
                'numero' => Numerotation::prochainNumeroRecu($exercice),
                'exercice' => $exercice,
            ]);

            Audit::tracer('PAIEMENT_CREE', 'paiement', $paiement->id, null, $paiement->toArray());

            return $recu;
        });

        return redirect()
            ->route('recus.pdf', $recu)
            ->with('succes', "Paiement enregistré. Reçu {$recu->numero}.");
    }

    public function pdf(Recu $recu)
    {
        return GenerateurDocument::recuPdf($recu)
            ->stream(str_replace('/', '_', $recu->numero).'.pdf');
    }

    public function telecharger(Recu $recu)
    {
        return GenerateurDocument::recuPdf($recu)
            ->download(str_replace('/', '_', $recu->numero).'.pdf');
    }

    /** Annulation : Directrice uniquement, motif obligatoire, jamais de suppression. */
    public function annuler(Request $request, Paiement $paiement)
    {
        $donnees = $request->validate([
            'motif' => ['required', 'string', 'min:5', 'max:255'],
        ], [], ['motif' => 'motif']);

        if ($paiement->annule) {
            return back()->with('erreur', 'Ce paiement est déjà annulé.');
        }

        DB::transaction(function () use ($paiement, $donnees, $request) {
            $avant = $paiement->toArray();

            $paiement->update([
                'annule' => true,
                'annule_le' => now(),
                'annule_par' => $request->user()->id,
                'annule_motif' => $donnees['motif'],
            ]);

            Audit::tracer('PAIEMENT_ANNULE', 'paiement', $paiement->id, $avant, $paiement->fresh()->toArray());
        });

        return back()->with('succes', 'Paiement annulé. Le reçu reste consultable avec la mention « annulé ».');
    }

    /** Impayes, avec relance WhatsApp prete a envoyer. */
    public function impayes(Request $request)
    {
        $u = $request->user();
        $antenneId = $u->voitToutesAntennes() ? $request->query('antenne') : $u->antenne_id;

        $q = DB::table('v_solde_inscription AS v')
            ->join('inscriptions AS i', 'i.id', '=', 'v.inscription_id')
            ->join('apprenants AS a', 'a.id', '=', 'i.apprenant_id')
            ->join('session_formation AS s', 's.id', '=', 'i.session_id')
            ->join('formations AS f', 'f.id', '=', 's.formation_id')
            ->where('v.solde', '>', 0)
            ->select('i.id AS inscription_id', 'a.nom', 'a.prenoms', 'a.telephone',
                     's.code AS session_code', 'f.intitule', 'v.solde', 'v.total_verse')
            ->orderByDesc('v.solde');

        if ($antenneId) {
            $q->where('i.antenne_id', $antenneId);
        }

        if ($session = $request->query('session')) {
            $q->where('s.code', $session);
        }

        return view('paiements.impayes', [
            'lignes' => $q->paginate(30)->withQueryString(),
            'total' => (int) $q->sum('v.solde'),
        ]);
    }

    private function verifierAntenne(Request $request, string $antenneId): void
    {
        $u = $request->user();

        if (! $u->voitToutesAntennes() && $u->antenne_id !== $antenneId) {
            abort(403, "Cette opération concerne une autre antenne que la vôtre.");
        }
    }
}
