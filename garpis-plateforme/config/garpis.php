<?php

/**
 * Parametres propres a GARPIS.
 * Tout ce qui figure sur un document officiel est ici, pas en dur dans les vues.
 */
return [
    'raison_sociale'      => env('GARPIS_RAISON_SOCIALE', 'ONG GARPIS'),
    'nom_long'            => "Groupe d'Action pour la Réduction de la Pauvreté et des Inégalités Sociales",
    'enregistrement'      => env('GARPIS_ENREGISTREMENT', '2024/3754/DEP-ATL-LITT/SG/SAG - ASSOC du 16 Mars 2024'),
    'ifu'                 => env('GARPIS_IFU', ''),
    'email'               => env('GARPIS_EMAIL', 'garpis62@gmail.com'),
    'telephone'           => env('GARPIS_TELEPHONE', '+229 96 66 71 94'),
    'signataire'          => env('GARPIS_SIGNATAIRE', 'Mme Alida ALINGO TOHIO'),
    'fonction_signataire' => env('GARPIS_FONCTION_SIGNATAIRE', 'Présidente'),

    'url_verification'    => rtrim(env('GARPIS_URL_VERIFICATION', env('APP_URL')), '/'),

    // Plafond de remise accordable par le Directeur des etudes sans validation.
    'plafond_remise_de'   => (int) env('GARPIS_PLAFOND_REMISE_DE', 15),

    // Duplicata d'attestation.
    'montant_duplicata'   => (int) env('GARPIS_MONTANT_DUPLICATA', 2000),

    // Lien de paiement fourni par vous. Vide = paiement en ligne desactive,
    // le duplicata se regle alors au comptoir comme n'importe quel encaissement.
    'lien_paiement'       => env('GARPIS_LIEN_PAIEMENT', ''),

    'ia' => [
        'cle'              => env('ANTHROPIC_API_KEY', ''),
        'modele'           => env('GARPIS_IA_MODELE', 'claude-sonnet-4-6'),
        'plafond_mensuel'  => (int) env('GARPIS_IA_PLAFOND_MENSUEL_XOF', 15000),
    ],
];
