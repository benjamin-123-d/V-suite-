<?php

namespace App\Http\Controllers;

use App\Models\Antenne;
use App\Models\Formation;
use App\Models\SessionFormation;
use App\Services\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SessionController extends Controller
{
    public function index(Request $request)
    {
        $q = SessionFormation::with('formation', 'antenne')->withCount('inscriptions');

        $u = $request->user();
        if (! $u->voitToutesAntennes()) {
            $q->where('antenne_id', $u->antenne_id);
        }

        if ($statut = $request->query('statut')) {
            $q->where('statut', $statut);
        }

        return view('sessions.index', [
            'sessions' => $q->orderByDesc('date_debut')->paginate(20)->withQueryString(),
            'statut' => $statut,
        ]);
    }

    public function formulaire()
    {
        return view('sessions.creer', [
            'formations' => Formation::where('actif', true)->orderBy('intitule')->get(),
            'antennes' => Antenne::where('actif', true)->orderBy('nom')->get(),
        ]);
    }

    public function enregistrer(Request $request)
    {
        $donnees = $request->validate([
            'formation_id' => ['required', 'uuid', 'exists:formations,id'],
            'antenne_id' => ['required', 'uuid', 'exists:antennes,id'],
            'libelle' => ['required', 'string', 'max:120'],
            'type' => ['required', 'in:BASE,PROFESSIONNELLE'],
            'date_debut' => ['required', 'date'],
            'date_fin' => ['required', 'date', 'after_or_equal:date_debut'],
            'jours_horaires' => ['nullable', 'string', 'max:120'],
            'lieu' => ['nullable', 'string', 'max:120'],
            'cout' => ['required', 'integer', 'min:0'],
            'capacite_max' => ['nullable', 'integer', 'min:1'],
        ]);

        $formation = Formation::findOrFail($donnees['formation_id']);
        $antenne = Antenne::findOrFail($donnees['antenne_id']);

        $session = SessionFormation::create($donnees + [
            'code' => SessionFormation::prochainCode($formation, $antenne, (int) date('Y', strtotime($donnees['date_debut']))),
            'cree_par' => $request->user()->id,
        ]);

        Audit::tracer('SESSION_CREEE', 'session', $session->id, null, ['code' => $session->code]);

        return redirect()->route('sessions.fiche', $session)->with('succes', "Session {$session->code} ouverte.");
    }

    public function fiche(SessionFormation $session)
    {
        $session->load('formation', 'antenne', 'inscriptions.apprenant', 'inscriptions.attestation');

        $soldes = DB::table('v_solde_inscription')
            ->whereIn('inscription_id', $session->inscriptions->pluck('id'))
            ->get()
            ->keyBy('inscription_id');

        $encaisse = (int) DB::table('paiements')
            ->whereIn('inscription_id', $session->inscriptions->pluck('id'))
            ->where('annule', false)
            ->sum('montant');

        return view('sessions.fiche', compact('session', 'soldes', 'encaisse'));
    }

    /** Duplication : ouvrir la session suivante sans tout ressaisir. */
    public function dupliquer(Request $request, SessionFormation $session)
    {
        $nouvelle = $session->replicate(['code', 'statut', 'created_at', 'updated_at']);
        $nouvelle->code = SessionFormation::prochainCode($session->formation, $session->antenne, (int) now()->format('Y'));
        $nouvelle->libelle = 'Copie de '.$session->libelle;
        $nouvelle->statut = 'PLANIFIEE';
        $nouvelle->cree_par = $request->user()->id;
        $nouvelle->save();

        return redirect()->route('sessions.fiche', $nouvelle)
            ->with('succes', 'Session dupliquée. Ajustez les dates avant d\'inscrire.');
    }
}
