<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Vérifier une attestation · GARPIS Formation</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700&family=Fraunces:opsz,wght@9..144,500;9..144,600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/garpis.css') }}">
</head>
<body>
<div class="ecran-connexion">
  <div class="boite-connexion">
    <div class="boite-connexion__marque">
      <img src="{{ asset('assets/logo_garpis.png') }}" alt="">
      <h1>Vérifier une attestation</h1>
      <p>Saisissez le code imprimé sous le QR code, en bas à gauche du document.</p>
    </div>

    <div class="carte">
      <form onsubmit="event.preventDefault(); location.href = '/a/' + document.getElementById('code').value.trim().toUpperCase();">
        <div class="champ">
          <label for="code">Code de vérification</label>
          <input id="code" type="text" placeholder="K7M2-P4XQ" required autofocus
                 style="font-family:var(--police-code);text-transform:uppercase;letter-spacing:2px;text-align:center;font-size:19px">
        </div>
        <button class="bouton bouton--plein bouton--large">Vérifier</button>
      </form>
    </div>

    <p style="text-align:center;font-size:12.5px;color:var(--encre-doux)">
      La vérification est gratuite. {{ config('garpis.raison_sociale') }}
    </p>
  </div>
</div>
</body>
</html>
