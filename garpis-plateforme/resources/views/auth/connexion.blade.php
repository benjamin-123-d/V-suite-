<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Connexion · GARPIS Formation</title>
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
      <h1>GARPIS Formation</h1>
      <p>Gestion des apprenants, de la caisse et des attestations</p>
    </div>

    <div class="carte">
      <form method="POST" action="{{ route('connexion') }}">
        @csrf

        <div class="champ">
          <label for="email">Adresse e-mail</label>
          <input id="email" type="email" name="email" value="{{ old('email') }}"
                 required autofocus autocomplete="username">
          @error('email') <p class="erreur-champ">{{ $message }}</p> @enderror
        </div>

        <div class="champ">
          <label for="password">Mot de passe</label>
          <input id="password" type="password" name="password" required autocomplete="current-password">
          @error('password') <p class="erreur-champ">{{ $message }}</p> @enderror
        </div>

        <label style="display:flex;gap:8px;align-items:center;font-size:13.5px;margin-bottom:16px">
          <input type="checkbox" name="memoriser" value="1" style="width:auto">
          Rester connecté sur cet appareil
        </label>

        <button type="submit" class="bouton bouton--plein bouton--large">Se connecter</button>
      </form>
    </div>

    <p style="text-align:center;font-size:12.5px;color:var(--encre-doux)">
      Vous vérifiez une attestation ?
      <a href="{{ route('verification.formulaire') }}">C'est par ici</a>.
    </p>

  </div>
</div>
</body>
</html>
