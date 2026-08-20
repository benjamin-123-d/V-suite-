<?php

namespace Database\Seeders;

use App\Models\Antenne;
use App\Models\Apprenant;
use App\Models\Formation;
use App\Models\Inscription;
use App\Models\Paiement;
use App\Models\Recu;
use App\Models\Role;
use App\Models\SessionFormation;
use App\Models\Utilisateur;
use App\Services\Numerotation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Jeu de demonstration : deux antennes, les cinq roles, trois formations,
 * quatre sessions dont une soldee et une avec impayes, un doublon volontaire,
 * une dette de materiel qui bloque une attestation.
 *
 * php artisan db:seed
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // --- Roles ---
        foreach (Role::tous() as $code => $libelle) {
            Role::firstOrCreate(['code' => $code], ['libelle' => $libelle]);
        }

        // --- Antennes ---
        $cotonou = Antenne::create([
            'nom' => 'Siège de Cotonou', 'code_court' => 'COT', 'ville' => 'Cotonou',
            'adresse' => 'Quartier Fidjrossè', 'telephone' => config('garpis.telephone'),
        ]);

        $calavi = Antenne::create([
            'nom' => 'Antenne d\'Abomey-Calavi', 'code_court' => 'ABC', 'ville' => 'Abomey-Calavi',
            'adresse' => 'Carrefour Tankpè',
        ]);

        // --- Utilisateurs ---
        $directrice = $this->utilisateur('ALINGO TOHIO', 'Alida', 'directrice@garpis.org', $cotonou, ['DIRECTRICE']);
        $de = $this->utilisateur('HOUNKPE', 'Serge', 'etudes@garpis.org', $cotonou, ['DIR_ETUDES', 'FORMATEUR']);
        $secretaireCot = $this->utilisateur('ADJOVI', 'Rachidatou', 'secretaire.cotonou@garpis.org', $cotonou, ['SECRETAIRE']);
        $this->utilisateur('KOSSOU', 'Bernadette', 'secretaire.calavi@garpis.org', $calavi, ['SECRETAIRE']);
        $this->utilisateur('DOSSOU', 'Marc', 'comptable@garpis.org', $cotonou, ['COMPTABLE']);

        // --- Formations ---
        $deco = Formation::create([
            'code' => 'DEC', 'intitule' => 'Décoration Événementielle',
            'domaine' => 'Métiers de l\'événementiel', 'duree_heures' => 120, 'cout_reference' => 75000,
            'descriptif_attestation' => "Au cours de cette formation, elle a acquis des compétences pratiques en conception de décors, jeux de ballon, arts de la table et fonds de scène, et a démontré un excellent esprit créatif et professionnel.",
        ]);

        $makeup = Formation::create([
            'code' => 'MKP', 'intitule' => 'Make-up et Attache de Foulard',
            'domaine' => 'Esthétique', 'duree_heures' => 40, 'cout_reference' => 35000,
        ]);

        $couture = Formation::create([
            'code' => 'COU', 'intitule' => 'Couture et Modélisme',
            'domaine' => 'Métiers du textile', 'duree_heures' => 300, 'cout_reference' => 120000,
        ]);

        foreach (['Les bases du décor', 'Jeux de ballon', 'Arts de la table', 'Fonds de scène'] as $i => $titre) {
            DB::table('chapitres')->insert([
                'id' => (string) Str::uuid(), 'formation_id' => $deco->id,
                'ordre' => $i + 1, 'intitule' => $titre,
            ]);
        }

        // --- Sessions ---
        $sessionSoldee = $this->session($deco, $cotonou, 'Session de Mars 2026', 'TERMINEE',
            now()->subMonths(5), now()->subMonths(3), 75000, $de);

        $sessionImpayes = $this->session($deco, $cotonou, 'Session de Juillet 2026', 'EN_COURS',
            now()->subMonth(), now()->addDays(5), 75000, $de);

        $this->session($makeup, $calavi, 'Session de Septembre 2026', 'PLANIFIEE',
            now()->addWeeks(2), now()->addWeeks(6), 35000, $de);

        $this->session($couture, $calavi, 'Session annuelle 2026', 'EN_COURS',
            now()->subMonths(2), now()->addMonths(6), 120000, $de);

        // --- Apprenants, dont un doublon volontaire ---
        $noms = [
            ['TOGNISSE', 'Edwige', 'F', '+22997121314'],
            ['AGOSSOU', 'Reine', 'F', '+22996223344'],
            ['KOFFI', 'Jean-Baptiste', 'M', '+22995334455'],
            ['ZANNOU', 'Mireille', 'F', '+22997445566'],
            ['GBAGUIDI', 'Sylvain', 'M', '+22996556677'],
            ['TOGNISSE', 'Edwidge', 'F', '+22997121314'],   // doublon volontaire
        ];

        $apprenants = [];
        foreach ($noms as [$nom, $prenoms, $sexe, $tel]) {
            $apprenants[] = Apprenant::create([
                'nom' => $nom, 'prenoms' => $prenoms, 'sexe' => $sexe, 'telephone' => $tel,
                'ville' => 'Cotonou', 'antenne_origine_id' => $cotonou->id,
            ]);
        }

        // Session terminee : tout le monde a solde, les attestations sont delivrables.
        foreach (array_slice($apprenants, 0, 3) as $a) {
            $i = $this->inscrire($a, $sessionSoldee, $secretaireCot, 75000);
            $this->encaisser($i, 75000, 'ESPECES', $secretaireCot);
        }

        // Session en cours : paiements partiels, une remise, une dette de materiel.
        $i1 = $this->inscrire($apprenants[3], $sessionImpayes, $secretaireCot, 75000);
        $this->encaisser($i1, 25000, 'MTN_MOMO', $secretaireCot);

        $i2 = $this->inscrire($apprenants[4], $sessionImpayes, $secretaireCot, 75000, 10000, 'Difficultés familiales');
        $this->encaisser($i2, 65000, 'ESPECES', $secretaireCot);

        // Solde de formation apure, mais materiel impaye : l'attestation reste bloquee.
        DB::table('commandes_materiel')->insert([
            'id' => (string) Str::uuid(), 'inscription_id' => $i2->id,
            'libelle' => 'Kit de décoration (ballons, tissus)', 'montant_du' => 12000,
            'commande_le' => now()->subWeeks(2), 'cree_par' => $de->id,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->command->info('Jeu de démonstration créé. Connexion : directrice@garpis.org / garpis2026');
    }

    private function utilisateur(string $nom, string $prenoms, string $email, Antenne $antenne, array $roles): Utilisateur
    {
        $u = Utilisateur::create([
            'nom' => $nom, 'prenoms' => $prenoms, 'email' => $email,
            'password' => Hash::make('garpis2026'),
            'antenne_id' => $antenne->id, 'doit_changer_mdp' => true, 'actif' => true,
        ]);

        $u->roles()->attach(Role::whereIn('code', $roles)->pluck('id'));

        return $u;
    }

    private function session(Formation $f, Antenne $a, string $libelle, string $statut, $debut, $fin, int $cout, Utilisateur $par): SessionFormation
    {
        return SessionFormation::create([
            'formation_id' => $f->id, 'antenne_id' => $a->id,
            'code' => SessionFormation::prochainCode($f, $a, (int) $debut->format('Y')),
            'libelle' => $libelle, 'type' => 'BASE',
            'date_debut' => $debut, 'date_fin' => $fin,
            'cout' => $cout, 'capacite_max' => 25, 'statut' => $statut, 'cree_par' => $par->id,
        ]);
    }

    private function inscrire(Apprenant $a, SessionFormation $s, Utilisateur $par, int $du, int $remise = 0, ?string $motif = null): Inscription
    {
        return Inscription::create([
            'apprenant_id' => $a->id, 'session_id' => $s->id, 'antenne_id' => $s->antenne_id,
            'date_inscription' => $s->date_debut, 'montant_du' => $du,
            'remise_montant' => $remise, 'remise_motif' => $motif,
            'remise_par' => $remise ? $par->id : null, 'remise_validee' => true,
            'cree_par' => $par->id,
        ]);
    }

    private function encaisser(Inscription $i, int $montant, string $mode, Utilisateur $par): void
    {
        DB::transaction(function () use ($i, $montant, $mode, $par) {
            $p = Paiement::create([
                'inscription_id' => $i->id, 'antenne_id' => $i->antenne_id,
                'objet' => 'FORMATION', 'montant' => $montant, 'mode' => $mode,
                'paye_le' => now()->subDays(random_int(1, 40)), 'encaisse_par' => $par->id,
            ]);

            $exercice = (int) now()->format('Y');

            Recu::create([
                'paiement_id' => $p->id,
                'numero' => Numerotation::prochainNumeroRecu($exercice),
                'exercice' => $exercice,
            ]);
        });
    }
}
