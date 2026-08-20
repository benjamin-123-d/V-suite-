<?php

namespace App\Http\Controllers;

use App\Models\Apprenant;
use App\Models\Inscription;
use App\Models\SessionFormation;
use App\Services\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InscriptionController extends Controller
{
    public function formulaire(SessionFormation $session, Request $request)
    {
        $session->load('formation', 'antenne');

        $dejaInscrits = $session->inscriptions()->pluck('apprenant_id');

        $apprenants = Apprenant::whereNotIn('id', $dejaInscrits)
            ->orderBy('nom')
            ->limit(200)
            ->get();

        return view('sessions.inscrire', compact('session', 'apprenants'));
    }

    public function enregistrer(Request $request, SessionFormation $session)
    {
        $donnees = $request->validate([
            'apprenant_id' => ['required', 'uuid', 'exists:apprenants,id'],
            'montant_du' => ['required', 'integer', 'min:0'],
            'remise_montant' => ['nullable', 'integer', 'min:0'],
            'remise_motif' => ['nullable', 'string', 'max:200'],
        ]);

        $remise = (int) ($donnees['remise_montant'] ?? 0);

        if ($remise > 0 && empty($donnees['remise_motif'])) {
            throw ValidationException::withMessages([
                'remise_motif' => 'Une remise sans motif ne peut pas être enregistrée.',
            ]);
        }

        // Plafond de remise du Directeur des etudes : au-dela, la Directrice valide.
        $validee = true;
        if ($remise > 0 && ! $request->user()->a('DIRECTRICE')) {
            $pourcentage = $donnees['montant_du'] > 0 ? $remise / $donnees['montant_du'] * 100 : 0;
            $validee = $pourcentage <= config('garpis.plafond_remise_de');
        }

        $inscription = Inscription::create([
            'apprenant_id' => $donnees['apprenant_id'],
            'session_id' => $session->id,
            'antenne_id' => $session->antenne_id,
            'date_inscription' => today(),
            'montant_du' => $donnees['montant_du'],
            'remise_montant' => $remise,
            'remise_motif' => $donnees['remise_motif'] ?? null,
            'remise_par' => $remise > 0 ? $request->user()->id : null,
            'remise_validee' => $validee,
            'cree_par' => $request->user()->id,
        ]);

        Audit::tracer('INSCRIPTION_CREEE', 'inscription', $inscription->id, null, $inscription->toArray());

        $message = $validee
            ? 'Apprenant inscrit.'
            : 'Apprenant inscrit. La remise dépasse le plafond de '.config('garpis.plafond_remise_de').' % : elle attend la validation de la Directrice.';

        return redirect()->route('sessions.fiche', $session)->with('succes', $message);
    }

    /** Ajout d'une dette de materiel : bloque l'attestation comme le solde de formation. */
    public function ajouterMateriel(Request $request, Inscription $inscription)
    {
        $donnees = $request->validate([
            'libelle' => ['required', 'string', 'max:150'],
            'montant_du' => ['required', 'integer', 'min:1'],
        ]);

        DB::table('commandes_materiel')->insert([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'inscription_id' => $inscription->id,
            'libelle' => $donnees['libelle'],
            'montant_du' => $donnees['montant_du'],
            'commande_le' => today(),
            'cree_par' => $request->user()->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Audit::tracer('MATERIEL_FACTURE', 'inscription', $inscription->id, null, $donnees);

        return back()->with('succes', 'Matériel facturé à l\'apprenant. Le solde est mis à jour.');
    }

    /** Validation d'une remise au-dessus du plafond : Directrice uniquement. */
    public function validerRemise(Inscription $inscription)
    {
        $inscription->update(['remise_validee' => true]);
        Audit::tracer('REMISE_VALIDEE', 'inscription', $inscription->id);

        return back()->with('succes', 'Remise validée.');
    }
}
