<?php

namespace App\Http\Controllers;

use App\Services\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Caisse commune, avec positions par antenne et par encaisseur.
 * Cloture en deux temps : chaque secretaire cloture sa position,
 * puis la Directrice valide la consolidation.
 */
class CaisseController extends Controller
{
    public function jour(Request $request)
    {
        $u = $request->user();
        $jour = $request->query('jour', today()->toDateString());
        $antenneId = $u->voitToutesAntennes() ? $request->query('antenne') : $u->antenne_id;

        $q = DB::table('paiements AS p')
            ->join('utilisateurs AS e', 'e.id', '=', 'p.encaisse_par')
            ->join('antennes AS a', 'a.id', '=', 'p.antenne_id')
            ->whereDate('p.paye_le', $jour)
            ->where('p.annule', false);

        if ($antenneId) {
            $q->where('p.antenne_id', $antenneId);
        }

        $parEncaisseur = (clone $q)
            ->select('e.nom', 'e.prenoms', 'a.ville', 'p.mode',
                     DB::raw('SUM(p.montant) AS total'), DB::raw('COUNT(*) AS nb'))
            ->groupBy('e.nom', 'e.prenoms', 'a.ville', 'p.mode')
            ->orderBy('e.nom')
            ->get();

        $parMode = (clone $q)
            ->select('p.mode AS mode', DB::raw('SUM(p.montant) AS total'))
            ->groupBy('p.mode')->pluck('total', 'mode');

        $especes = (int) ($parMode['ESPECES'] ?? 0);

        $cloture = DB::table('clotures_caisse')
            ->where('utilisateur_id', $u->id)
            ->where('jour', $jour)
            ->first();

        return view('caisse.jour', compact('parEncaisseur', 'parMode', 'especes', 'jour', 'cloture') + [
            'total' => (int) $parMode->sum(),
        ]);
    }

    public function cloturer(Request $request)
    {
        $donnees = $request->validate([
            'jour' => ['required', 'date'],
            'theorique_especes' => ['required', 'integer', 'min:0'],
            'compte_especes' => ['required', 'integer', 'min:0'],
            'justification' => ['nullable', 'string', 'max:255'],
        ]);

        $ecart = $donnees['compte_especes'] - $donnees['theorique_especes'];

        if ($ecart !== 0 && empty($donnees['justification'])) {
            return back()->withInput()->with('erreur', "Un écart de caisse doit être justifié avant clôture.");
        }

        $u = $request->user();

        DB::table('clotures_caisse')->updateOrInsert(
            ['antenne_id' => $u->antenne_id, 'utilisateur_id' => $u->id, 'jour' => $donnees['jour']],
            [
                'id' => (string) Str::uuid(),
                'theorique_especes' => $donnees['theorique_especes'],
                'compte_especes' => $donnees['compte_especes'],
                'ecart' => $ecart,
                'justification' => $donnees['justification'] ?? null,
                'validee' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        Audit::tracer('CAISSE_CLOTUREE', 'cloture_caisse', null, null, $donnees + ['ecart' => $ecart]);

        return back()->with('succes', 'Position clôturée. Elle attend la validation de la Directrice.');
    }

    public function valider(Request $request, string $cloture)
    {
        DB::table('clotures_caisse')->where('id', $cloture)->update([
            'validee' => true,
            'validee_par' => $request->user()->id,
            'updated_at' => now(),
        ]);

        Audit::tracer('CAISSE_VALIDEE', 'cloture_caisse', $cloture);

        return back()->with('succes', 'Clôture validée.');
    }
}
