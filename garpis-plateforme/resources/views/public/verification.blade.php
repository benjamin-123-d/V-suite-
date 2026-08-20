<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Vérification d'attestation · GARPIS Formation</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700&family=Fraunces:opsz,wght@9..144,500;9..144,600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/garpis.css') }}">
</head>
<body>
<div class="verif">
  <div class="verif__carte">

    @php
      $valide = $attestation && ! $attestation->revoquee;
      $donnees = $attestation?->donnees_figees ?? [];
    @endphp

    @if ($valide)
      <div class="verif__bandeau verif__bandeau--ok">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
             stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="9"/><path d="m8.5 12 2.5 2.5 4.5-5"/>
        </svg>
        <h1>Attestation authentique</h1>
        <p>Délivrée par GARPIS Formation</p>
      </div>
    @elseif ($attestation)
      <div class="verif__bandeau verif__bandeau--ko">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
             stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="9"/><path d="m9 9 6 6M15 9l-6 6"/>
        </svg>
        <h1>Attestation annulée</h1>
        <p>Ce document a été révoqué par le centre et n'est plus valable.</p>
      </div>
    @else
      <div class="verif__bandeau verif__bandeau--ko">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
             stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="9"/><path d="M12 7.5v5.5M12 16.2h.01"/>
        </svg>
        <h1>Attestation non reconnue</h1>
        <p>Aucun document ne correspond au code {{ $code }}.</p>
      </div>
    @endif

    <img class="verif__logo" src="{{ asset('assets/logo_garpis.png') }}" alt="ONG GARPIS">

    @if ($attestation)
      <dl>
        <dt>Titulaire</dt><dd>{{ $donnees['nom_complet'] ?? '' }}</dd>
        <dt>Formation</dt><dd>{{ $donnees['intitule_formation'] ?? '' }}</dd>
        <dt>Session</dt><dd>{{ $donnees['libelle_session'] ?? '' }}</dd>
        @if (! empty($donnees['duree_heures']))
          <dt>Durée</dt><dd>{{ $donnees['duree_heures'] }} heures</dd>
        @endif
        <dt>Période</dt><dd>Du {{ $donnees['date_debut'] ?? '' }} au {{ $donnees['date_fin'] ?? '' }}</dd>
        <dt>Antenne</dt><dd>{{ $donnees['antenne_nom'] ?? '' }}</dd>
        <dt>Délivrée le</dt><dd>{{ $donnees['date_delivrance'] ?? '' }}</dd>
        <dt>N° d'attestation</dt><dd class="code" style="font-size:14px">{{ $attestation->numero }}</dd>
      </dl>
    @endif

    <div class="verif__pied">
      Cette page confirme uniquement l'existence et la validité d'une attestation délivrée
      par GARPIS Formation. Aucune donnée de contact ni information financière n'y est publiée.
      La vérification est gratuite.<br><br>
      Un doute ? Appelez le <strong>{{ config('garpis.telephone') }}</strong>
      ou écrivez à <a href="mailto:{{ config('garpis.email') }}">{{ config('garpis.email') }}</a>.<br><br>
      {{ config('garpis.raison_sociale') }} — N° {{ config('garpis.enregistrement') }}
    </div>

  </div>
</div>
</body>
</html>
