<?php

namespace App\Services;

use App\Models\Attestation;
use App\Models\Recu;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;

/**
 * Production des PDF officiels.
 * Le fond de l'attestation et le QR sont injectes en data-uri : aucun appel
 * reseau pendant le rendu, aucun chemin disque a casser en production.
 */
class GenerateurDocument
{
    public static function qrDataUri(string $contenu, int $taille = 400): string
    {
        $resultat = Builder::create()
            ->writer(new PngWriter())
            ->data($contenu)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::Medium)
            ->size($taille)
            ->margin(0)
            ->build();

        return $resultat->getDataUri();
    }

    public static function imageDataUri(string $cheminPublic): string
    {
        $chemin = public_path($cheminPublic);

        if (! is_file($chemin)) {
            return '';
        }

        $type = str_ends_with(strtolower($chemin), '.png') ? 'image/png' : 'image/jpeg';

        return 'data:'.$type.';base64,'.base64_encode(file_get_contents($chemin));
    }

    public static function attestationPdf(Attestation $attestation)
    {
        $donnees = $attestation->donnees_figees;
        $donnees['qr_data_uri'] = self::qrDataUri($attestation->urlVerification());
        $donnees['fond_data_uri'] = self::imageDataUri('assets/fond_attestation.jpg');
        $donnees['revoquee'] = $attestation->revoquee;

        return Pdf::loadView('pdf.attestation', $donnees)
            ->setPaper('a4', 'landscape')
            ->setOption('isRemoteEnabled', true);
    }

    public static function recuPdf(Recu $recu, string $exemplaire = 'ORIGINAL — APPRENANT')
    {
        $recu->load('paiement.inscription.apprenant', 'paiement.inscription.session.formation', 'paiement.encaisseur');
        $paiement = $recu->paiement;
        $inscription = $paiement->inscription;
        $detail = $inscription?->detailSolde();

        return Pdf::loadView('pdf.recu', [
            'recu' => $recu,
            'paiement' => $paiement,
            'inscription' => $inscription,
            'apprenant' => $inscription?->apprenant,
            'session' => $inscription?->session,
            'detail' => $detail,
            'exemplaire' => $exemplaire,
            'logo_data_uri' => self::imageDataUri('assets/logo_garpis.png'),
        ])->setPaper('a5', 'portrait');
    }
}
