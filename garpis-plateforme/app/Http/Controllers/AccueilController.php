<?php

namespace App\Http\Controllers;

use App\Services\AssistantIa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccueilController extends Controller
{
    public function index(Request $request)
    {
        $u = $request->user();
        $antenneId = $u->voitToutesAntennes() ? $request->query('antenne') : $u->antenne_id;

        $filtre = fn ($q, $col = 'antenne_id') => $antenneId ? $q->where($col, $antenneId) : $q;

        $debutMois = now()->startOfMonth();

        $encaisseMois = (int) $filtre(DB::table('paiements')->where('annule', false)
            ->where('paye_le', '>=', $debutMois))->sum('montant');

        $encaisseJour = (int) $filtre(DB::table('paiements')->where('annule', false)
            ->whereDate('paye_le', today()))->sum('montant');

        $impayes = (int) $filtre(DB::table('v_solde_inscription AS v')
            ->join('inscriptions AS i', 'i.id', '=', 'v.inscription_id')
            ->where('v.solde', '>', 0), 'i.antenne_id')->sum('v.solde');

        $nbImpayes = $filtre(DB::table('v_solde_inscription AS v')
            ->join('inscriptions AS i', 'i.id', '=', 'v.inscription_id')
            ->where('v.solde', '>', 0), 'i.antenne_id')->count();

        $sessionsEnCours = $filtre(DB::table('session_formation')->where('statut', 'EN_COURS'))->count();

        $apprenantsActifs = $filtre(DB::table('inscriptions')->whereIn('statut', ['INSCRIT', 'EN_COURS']))->count();

        $attestations = DB::table('attestations')->where('revoquee', false)->count();

        // Sessions qui se terminent sous 7 jours : il faut preparer les attestations.
        $aPreparer = $filtre(DB::table('session_formation')
            ->where('statut', 'EN_COURS')
            ->whereBetween('date_fin', [today(), today()->addDays(7)]))
            ->get(['code', 'libelle', 'date_fin']);

        return view('accueil', compact(
            'encaisseMois', 'encaisseJour', 'impayes', 'nbImpayes',
            'sessionsEnCours', 'apprenantsActifs', 'attestations', 'aPreparer'
        ) + ['iaActive' => AssistantIa::actif()]);
    }

    public function demanderIa(Request $request)
    {
        $donnees = $request->validate(['question' => ['required', 'string', 'max:2000']]);

        $resultat = AssistantIa::repondre($request->user(), $donnees['question']);

        return response()->json($resultat);
    }
}
