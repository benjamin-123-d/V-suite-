{{--
  Attestation officielle — A4 paysage (297 x 210 mm), rendu par dompdf.
  Coordonnees en millimetres, calees sur le fond fourni par GARPIS :
  le decor (bordure, en-tete, signature manuscrite) fait partie de l'image.
  QR code en BAS A GAUCHE, code de verification lisible en clair au-dessous.
--}}
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<style>
  @page { margin: 0; }
  body { margin: 0; padding: 0; width: 297mm; height: 210mm; position: relative;
         font-family: "Times New Roman", Georgia, serif; color: #1a1a1a; }

  .fond { position: absolute; top: 0; left: 0; width: 297mm; height: 210mm; }

  .nom { position: absolute; top: 74mm; left: 29mm; width: 239mm; text-align: center;
         font-size: 26pt; font-weight: bold; color: #0b3d0b; letter-spacing: .5pt; }

  /* Le trait ornemental du fond se situe a 44,6 % : le corps demarre en dessous. */
  .corps { position: absolute; top: 100mm; left: 38mm; width: 221mm; text-align: center; }
  .ligne { font-size: 14pt; line-height: 1.5; margin: 0 0 3.5mm 0; }
  .descriptif { font-size: 11.5pt; line-height: 1.45; margin: 4mm 8mm 0 8mm; }
  .formule { font-size: 11pt; font-style: italic; margin-top: 5mm; }

  .lieu-date { position: absolute; top: 158mm; right: 34mm; font-size: 11.5pt; font-style: italic; }

  .numero { position: absolute; top: 136mm; left: 20mm; width: 57mm; text-align: center;
            font-size: 8pt; color: #333; }

  .bloc-qr { position: absolute; top: 147mm; left: 20mm; width: 57mm; text-align: center; }
  .bloc-qr img { width: 30mm; height: 30mm; }
  .bloc-qr .code { font-family: "Courier New", monospace; font-size: 9pt;
                   font-weight: bold; letter-spacing: 1pt; margin-top: 1mm; }
  .bloc-qr .aide { font-size: 6.5pt; color: #444; line-height: 1.3; margin-top: .8mm; }

  .revoque { position: absolute; top: 88mm; left: 0; width: 297mm; text-align: center;
             font-size: 80pt; font-weight: bold; color: #E0B4B0; letter-spacing: 10pt; }
</style>
</head>
<body>

  @if (! empty($fond_data_uri))
    <img class="fond" src="{{ $fond_data_uri }}" alt="">
  @endif

  <div class="numero">N° {{ $numero_attestation ?? '' }}</div>

  <div class="nom">{{ $nom_complet ?? '' }}</div>

  <div class="corps">
    <p class="ligne">
      A suivi avec assiduité et succès la formation en :
      <strong>{{ $intitule_formation ?? '' }}</strong>
    </p>

    <p class="ligne">
      Organisée par GARPIS Formation, durant la {{ $libelle_session ?? '' }},
      s'étant tenue à {{ $ville_session ?? '' }}.
    </p>

    @if (! empty($descriptif_competences))
      <p class="descriptif">{{ $descriptif_competences }}</p>
    @endif

    <p class="formule">
      En foi de quoi, la présente attestation lui est délivrée pour servir et valoir ce que de droit.
    </p>
  </div>

  <div class="lieu-date">Fait à {{ $ville_signature ?? '' }}, le {{ $date_delivrance ?? '' }}</div>

  <div class="bloc-qr">
    <img src="{{ $qr_data_uri }}" alt="">
    <div class="code">{{ $code_verification ?? '' }}</div>
    <div class="aide">Vérifiez l'authenticité sur<br>{{ $url_verification_courte ?? '' }}</div>
  </div>

  @if (! empty($revoquee))
    <div class="revoque">ANNULÉE</div>
  @endif

</body>
</html>
