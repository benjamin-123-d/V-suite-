<?php

namespace App\Http\Controllers;

use App\Models\Apprenant;
use App\Services\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprenantController extends Controller
{
    public function index(Request $request)
    {
        $q = Apprenant::query();

        if ($recherche = $request->query('q')) {
            $motif = '%'.mb_strtolower($recherche).'%';
            $q->where(fn ($sq) => $sq
                ->whereRaw('LOWER(nom) LIKE ?', [$motif])
                ->orWhereRaw('LOWER(prenoms) LIKE ?', [$motif])
                ->orWhere('telephone', 'like', $motif));
        }

        $u = $request->user();
        if (! $u->voitToutesAntennes()) {
            $q->where('antenne_origine_id', $u->antenne_id);
        }

        return view('apprenants.index', [
            'apprenants' => $q->orderBy('nom')->paginate(25)->withQueryString(),
            'recherche' => $request->query('q'),
        ]);
    }

    public function formulaire()
    {
        return view('apprenants.creer');
    }

    public function enregistrer(Request $request)
    {
        $donnees = $request->validate([
            'nom' => ['required', 'string', 'max:80'],
            'prenoms' => ['required', 'string', 'max:120'],
            'sexe' => ['nullable', 'in:F,M'],
            'date_naissance' => ['nullable', 'date', 'before:today'],
            'telephone' => ['required', 'string', 'max:30'],
            'telephone_2' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:120'],
            'adresse' => ['nullable', 'string', 'max:200'],
            'ville' => ['nullable', 'string', 'max:80'],
            'niveau_etudes' => ['nullable', 'string', 'max:80'],
            'profession' => ['nullable', 'string', 'max:80'],
            'contact_urgence_nom' => ['nullable', 'string', 'max:120'],
            'contact_urgence_tel' => ['nullable', 'string', 'max:30'],
            'ignorer_doublon' => ['nullable', 'boolean'],
        ]);

        // Une personne qui revient pour une 2e formation ne doit pas creer une 2e fiche.
        if (! $request->boolean('ignorer_doublon')) {
            $doublons = Apprenant::doublonsProbables($donnees['telephone'], $donnees['nom'], $donnees['prenoms']);

            if ($doublons->isNotEmpty()) {
                return back()
                    ->withInput()
                    ->with('doublons', $doublons)
                    ->with('alerte', 'Une fiche existante correspond peut-être à cette personne. Vérifiez avant de créer un doublon.');
            }
        }

        $apprenant = Apprenant::create($donnees + ['antenne_origine_id' => $request->user()->antenne_id]);

        Audit::tracer('APPRENANT_CREE', 'apprenant', $apprenant->id, null, ['nom' => $apprenant->nomComplet()]);

        return redirect()->route('apprenants.fiche', $apprenant)
            ->with('succes', 'Fiche créée.');
    }

    public function fiche(Apprenant $apprenant)
    {
        $apprenant->load('inscriptions.session.formation', 'inscriptions.attestation');

        $soldes = DB::table('v_solde_inscription')
            ->whereIn('inscription_id', $apprenant->inscriptions->pluck('id'))
            ->pluck('solde', 'inscription_id');

        $paiements = DB::table('paiements AS p')
            ->join('recus AS r', 'r.paiement_id', '=', 'p.id')
            ->whereIn('p.inscription_id', $apprenant->inscriptions->pluck('id'))
            ->orderByDesc('p.paye_le')
            ->get(['p.id', 'p.montant', 'p.mode', 'p.paye_le', 'p.annule', 'r.numero', 'r.id AS recu_id']);

        return view('apprenants.fiche', compact('apprenant', 'soldes', 'paiements'));
    }
}
