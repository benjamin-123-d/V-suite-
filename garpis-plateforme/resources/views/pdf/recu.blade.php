{{-- Reçu de paiement — A5 portrait. Un exemplaire apprenant, une souche caisse. --}}
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<style>
  @page { margin: 10mm; }
  body { font-family: Helvetica, Arial, sans-serif; font-size: 9.5pt; color: #111; margin: 0; }
  .exemplaire { text-align: right; font-size: 7pt; color: #888; letter-spacing: 1pt; }
  .entete { width: 100%; border-bottom: 2px solid #0b6b2f; padding-bottom: 3mm; }
  .entete td { vertical-align: middle; }
  .logo { width: 20mm; }
  .nom-ong { font-size: 11pt; font-weight: bold; color: #0b6b2f; }
  .legal { font-size: 6.8pt; color: #444; line-height: 1.35; }
  .titre { text-align: center; margin: 5mm 0 1mm; font-size: 14pt; font-weight: bold; letter-spacing: 2pt; }
  .numero { text-align: center; font-size: 10pt; font-weight: bold; color: #0b6b2f; margin-bottom: 4mm; }
  table.infos { width: 100%; border-collapse: collapse; margin-bottom: 4mm; }
  table.infos td { padding: 1.6mm 2mm; border-bottom: 1px solid #e2e2e2; }
  table.infos td.k { width: 38%; color: #555; }
  table.infos td.v { font-weight: bold; }
  .montant { border: 2px solid #0b6b2f; padding: 3mm; text-align: center; margin-bottom: 3mm; }
  .chiffres { font-size: 17pt; font-weight: bold; color: #0b6b2f; }
  .lettres { font-size: 8pt; font-style: italic; margin-top: 1.5mm; }
  table.solde { width: 100%; border-collapse: collapse; margin-bottom: 4mm; }
  table.solde td { width: 33.33%; text-align: center; padding: 2mm 1mm; border: 1px solid #ddd; }
  .lbl { font-size: 7pt; color: #666; text-transform: uppercase; }
  .val { font-weight: bold; font-size: 10.5pt; }
  .du { color: #b00; }
  table.pied { width: 100%; margin-top: 4mm; font-size: 8.5pt; }
  table.pied td { vertical-align: top; }
  .signature { margin-top: 12mm; border-top: 1px solid #333; width: 45mm;
               text-align: center; font-size: 7.5pt; padding-top: 1mm; }
  .mention { text-align: center; font-size: 6.8pt; color: #666; margin-top: 6mm;
             border-top: 1px dashed #bbb; padding-top: 2mm; }
  .annule { text-align: center; color: #b00; font-size: 13pt; font-weight: bold;
            border: 2px solid #b00; padding: 2mm; margin-bottom: 3mm; }
</style>
</head>
<body>

  <div class="exemplaire">{{ $exemplaire }}</div>

  <table class="entete">
    <tr>
      <td class="logo">
        @if (! empty($logo_data_uri))<img src="{{ $logo_data_uri }}" style="width:18mm">@endif
      </td>
      <td>
        <div class="nom-ong">{{ config('garpis.raison_sociale') }} — GARPIS Formation</div>
        <div class="legal">
          N° d'enregistrement : {{ config('garpis.enregistrement') }}<br>
          IFU : {{ config('garpis.ifu') }} &nbsp;•&nbsp; {{ config('garpis.email') }}
          &nbsp;•&nbsp; {{ config('garpis.telephone') }}
        </div>
      </td>
    </tr>
  </table>

  @if ($paiement->annule)
    <div class="annule">REÇU ANNULÉ</div>
  @endif

  <div class="titre">REÇU DE PAIEMENT</div>
  <div class="numero">N° {{ $recu->numero }}</div>

  <table class="infos">
    <tr><td class="k">Reçu de</td><td class="v">{{ $apprenant?->nomComplet() ?? '—' }}</td></tr>
    <tr><td class="k">Téléphone</td><td class="v">{{ $apprenant?->telephone ?? '—' }}</td></tr>
    <tr><td class="k">Au titre de</td>
        <td class="v">{{ $paiement->objet === 'MATERIEL' ? 'Matériel de formation' : ($paiement->objet === 'DUPLICATA' ? "Duplicata d'attestation" : 'Frais de formation') }}</td></tr>
    @if ($session)
      <tr><td class="k">Formation / Session</td>
          <td class="v">{{ $session->formation->intitule }} — {{ $session->code }}</td></tr>
    @endif
    <tr><td class="k">Mode de paiement</td>
        <td class="v">{{ $paiement->libelleMode() }}@if ($paiement->reference_transaction) — réf. {{ $paiement->reference_transaction }}@endif</td></tr>
  </table>

  <div class="montant">
    <div class="chiffres">{{ \App\Services\Montant::format($paiement->montant) }} FCFA</div>
    <div class="lettres">Arrêté à la somme de : {{ \App\Services\Montant::enLettres($paiement->montant) }}</div>
  </div>

  @if ($detail)
    <table class="solde">
      <tr>
        <td><span class="lbl">Total dû</span><br>
            <span class="val">{{ \App\Services\Montant::format((int) $detail->du_formation + (int) $detail->du_materiel) }}</span></td>
        <td><span class="lbl">Total versé</span><br>
            <span class="val">{{ \App\Services\Montant::format((int) $detail->total_verse) }}</span></td>
        <td><span class="lbl">Reste à payer</span><br>
            <span class="val du">{{ \App\Services\Montant::format(max(0, (int) $detail->solde)) }}</span></td>
      </tr>
    </table>
  @endif

  <table class="pied">
    <tr>
      <td>
        Encaissé par : <strong>{{ $paiement->encaisseur?->nomComplet() ?? '—' }}</strong><br>
        Le {{ $paiement->paye_le->format('d/m/Y') }} à {{ $paiement->paye_le->format('H\hi') }}
      </td>
      <td style="text-align:right">
        {{ $inscription?->antenne?->ville ?? '' }}, le {{ $paiement->paye_le->format('d/m/Y') }}
        <div class="signature">Cachet et signature</div>
      </td>
    </tr>
  </table>

  <div class="mention">
    Reçu généré électroniquement par la plateforme GARPIS — valable sans signature manuscrite.<br>
    Toute somme versée reste acquise au centre conformément au contrat de formation signé.
  </div>

</body>
</html>
