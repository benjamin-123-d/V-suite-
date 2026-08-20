<?php

namespace App\Services;

use App\Models\Utilisateur;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Assistant interne, EN LECTURE SEULE.
 *
 * Garde-fous non negociables :
 *  - aucun acces SQL libre : uniquement les outils declares ci-dessous ;
 *  - le perimetre du role est applique dans executerOutil(), pas dans le prompt.
 *    Un prompt se contourne, un WHERE ne se contourne pas ;
 *  - la cle API reste cote serveur ;
 *  - toutes les conversations sont journalisees.
 */
class AssistantIa
{
    private const CONSIGNE = <<<'TXT'
Tu es l'assistant interne de GARPIS Formation, centre de formation professionnelle
au Bénin (antennes de Cotonou et d'Abomey-Calavi).

RÈGLES DE RÉPONSE
- Donne le chiffre ou le fait EN PREMIER. Pas d'introduction, pas de reformulation
  de la question. Développe seulement si on te le demande.
- Indique toujours la source et la date du chiffre.
- Montants en francs CFA, format "25 000 FCFA".
- Si un outil ne renvoie rien, dis-le franchement. N'invente jamais un chiffre.
- Tu es en LECTURE SEULE : tu peux rédiger un message de relance, mais c'est
  l'utilisateur qui l'envoie. Ne prétends jamais avoir effectué une action.
TXT;

    public static function actif(): bool
    {
        return (bool) config('garpis.ia.cle');
    }

    public static function outils(): array
    {
        return [
            [
                'name' => 'compter_apprenants',
                'description' => "Compte les apprenants inscrits sur une période, une antenne ou une formation.",
                'input_schema' => ['type' => 'object', 'properties' => [
                    'date_debut' => ['type' => 'string'],
                    'date_fin' => ['type' => 'string'],
                    'antenne' => ['type' => 'string'],
                    'formation' => ['type' => 'string'],
                ]],
            ],
            [
                'name' => 'lister_impayes',
                'description' => "Liste les inscriptions dont le solde est supérieur à zéro.",
                'input_schema' => ['type' => 'object', 'properties' => [
                    'session_code' => ['type' => 'string'],
                    'antenne' => ['type' => 'string'],
                    'limite' => ['type' => 'integer'],
                ]],
            ],
            [
                'name' => 'chiffre_affaires_periode',
                'description' => "Total encaissé sur une période, ventilé par mode de paiement.",
                'input_schema' => ['type' => 'object', 'required' => ['date_debut', 'date_fin'], 'properties' => [
                    'date_debut' => ['type' => 'string'],
                    'date_fin' => ['type' => 'string'],
                    'antenne' => ['type' => 'string'],
                ]],
            ],
            [
                'name' => 'rechercher_apprenant',
                'description' => "Retrouve un apprenant par nom, prénom ou téléphone.",
                'input_schema' => ['type' => 'object', 'required' => ['recherche'], 'properties' => [
                    'recherche' => ['type' => 'string'],
                ]],
            ],
            [
                'name' => 'statistiques_session',
                'description' => "Effectif, encaissé, solde restant et avancement d'une session.",
                'input_schema' => ['type' => 'object', 'required' => ['session_code'], 'properties' => [
                    'session_code' => ['type' => 'string'],
                ]],
            ],
        ];
    }

    public static function repondre(Utilisateur $utilisateur, string $question): array
    {
        if (! self::actif()) {
            return ['reponse' => "L'assistant n'est pas configuré. Ajoutez ANTHROPIC_API_KEY dans les variables d'environnement.", 'outils' => []];
        }

        $messages = [['role' => 'user', 'content' => $question]];
        $outilsAppeles = [];

        // Borne le nombre d'allers-retours : evite une boucle d'outils infinie.
        for ($tour = 0; $tour < 5; $tour++) {
            $reponse = Http::withHeaders([
                'x-api-key' => config('garpis.ia.cle'),
                'anthropic-version' => config('services.anthropic.version'),
                'content-type' => 'application/json',
            ])->timeout(60)->post(config('services.anthropic.endpoint'), [
                'model' => config('garpis.ia.modele'),
                'max_tokens' => 1500,
                'system' => self::CONSIGNE,
                'tools' => self::outils(),
                'messages' => $messages,
            ]);

            if ($reponse->failed()) {
                return ['reponse' => "L'assistant est momentanément indisponible.", 'outils' => $outilsAppeles];
            }

            $corps = $reponse->json();
            $blocs = $corps['content'] ?? [];
            $demandes = array_values(array_filter($blocs, fn ($b) => ($b['type'] ?? '') === 'tool_use'));

            if (empty($demandes)) {
                $texte = collect($blocs)->where('type', 'text')->pluck('text')->implode("\n");
                self::journaliser($utilisateur, $question, $texte, $outilsAppeles, $corps['usage'] ?? []);

                return ['reponse' => $texte, 'outils' => $outilsAppeles];
            }

            $messages[] = ['role' => 'assistant', 'content' => $blocs];

            $resultats = [];
            foreach ($demandes as $d) {
                // *** Le perimetre du role est applique ICI. ***
                $donnees = self::executerOutil($d['name'], $d['input'] ?? [], $utilisateur);
                $outilsAppeles[] = ['outil' => $d['name'], 'entree' => $d['input'] ?? []];
                $resultats[] = [
                    'type' => 'tool_result',
                    'tool_use_id' => $d['id'],
                    'content' => json_encode($donnees, JSON_UNESCAPED_UNICODE),
                ];
            }
            $messages[] = ['role' => 'user', 'content' => $resultats];
        }

        return ['reponse' => "Je n'arrive pas à répondre à cette question avec les données disponibles.", 'outils' => $outilsAppeles];
    }

    private static function executerOutil(string $nom, array $params, Utilisateur $u): array
    {
        // Une secretaire reste sur son antenne, meme si elle en demande une autre.
        $antenne = $u->voitToutesAntennes()
            ? ($params['antenne'] ?? null)
            : $u->antenne?->code_court;

        $filtreAntenne = function ($q, string $colonne = 'antenne_id') use ($antenne) {
            if ($antenne) {
                $id = DB::table('antennes')->where('code_court', $antenne)->orWhere('ville', $antenne)->value('id');
                if ($id) {
                    $q->where($colonne, $id);
                }
            }

            return $q;
        };

        switch ($nom) {
            case 'compter_apprenants':
                $q = DB::table('inscriptions');
                $filtreAntenne($q);
                if (! empty($params['date_debut'])) {
                    $q->where('date_inscription', '>=', $params['date_debut']);
                }
                if (! empty($params['date_fin'])) {
                    $q->where('date_inscription', '<=', $params['date_fin']);
                }

                return ['nombre' => $q->count(), 'antenne' => $antenne ?? 'toutes', 'arrete_au' => now()->format('d/m/Y H:i')];

            case 'chiffre_affaires_periode':
                if (! $u->a('DIRECTRICE', 'DIR_ETUDES', 'COMPTABLE')) {
                    return ['erreur' => "Vous n'avez pas accès aux données financières consolidées."];
                }
                $q = DB::table('paiements')->where('annule', false)
                    ->whereBetween('paye_le', [$params['date_debut'], $params['date_fin'].' 23:59:59']);
                $filtreAntenne($q);

                return [
                    'total' => (int) (clone $q)->sum('montant'),
                    'par_mode' => (clone $q)->select('mode', DB::raw('SUM(montant) AS total'))
                        ->groupBy('mode')->pluck('total', 'mode'),
                    'antenne' => $antenne ?? 'toutes',
                    'arrete_au' => now()->format('d/m/Y H:i'),
                ];

            case 'lister_impayes':
                $q = DB::table('v_solde_inscription AS v')
                    ->join('inscriptions AS i', 'i.id', '=', 'v.inscription_id')
                    ->join('apprenants AS a', 'a.id', '=', 'i.apprenant_id')
                    ->join('session_formation AS s', 's.id', '=', 'i.session_id')
                    ->where('v.solde', '>', 0)
                    ->select('a.nom', 'a.prenoms', 'a.telephone', 's.code AS session', 'v.solde');
                $filtreAntenne($q, 'i.antenne_id');
                if (! empty($params['session_code'])) {
                    $q->where('s.code', $params['session_code']);
                }

                return ['impayes' => $q->limit($params['limite'] ?? 20)->get(), 'arrete_au' => now()->format('d/m/Y H:i')];

            case 'rechercher_apprenant':
                $r = '%'.mb_strtolower($params['recherche']).'%';

                return ['resultats' => DB::table('apprenants')
                    ->whereRaw('LOWER(nom) LIKE ?', [$r])
                    ->orWhereRaw('LOWER(prenoms) LIKE ?', [$r])
                    ->orWhere('telephone', 'like', $r)
                    ->limit(10)->get(['id', 'nom', 'prenoms', 'telephone', 'ville'])];

            case 'statistiques_session':
                $s = DB::table('session_formation')->where('code', $params['session_code'])->first();
                if (! $s) {
                    return ['erreur' => 'Session introuvable.'];
                }
                $ids = DB::table('inscriptions')->where('session_id', $s->id)->pluck('id');

                return [
                    'session' => $s->code,
                    'libelle' => $s->libelle,
                    'effectif' => $ids->count(),
                    'capacite' => $s->capacite_max,
                    'solde_restant' => (int) DB::table('v_solde_inscription')->whereIn('inscription_id', $ids)->sum('solde'),
                    'arrete_au' => now()->format('d/m/Y H:i'),
                ];

            default:
                return ['erreur' => 'Outil inconnu.'];
        }
    }

    private static function journaliser(Utilisateur $u, string $question, string $reponse, array $outils, array $usage): void
    {
        DB::table('conversations_ia')->insert([
            'id' => (string) Str::uuid(),
            'utilisateur_id' => $u->id,
            'question' => $question,
            'reponse' => $reponse,
            'outils_appeles' => json_encode($outils, JSON_UNESCAPED_UNICODE),
            'tokens_entree' => $usage['input_tokens'] ?? null,
            'tokens_sortie' => $usage['output_tokens'] ?? null,
            'survenu_le' => now(),
        ]);
    }
}
