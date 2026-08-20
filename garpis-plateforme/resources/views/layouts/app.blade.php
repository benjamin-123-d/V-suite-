<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#0E4A2B">
<title>@yield('titre', 'Accueil') · GARPIS Formation</title>
<link rel="manifest" href="/manifest.json">
<link rel="icon" href="/assets/logo_garpis.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700&family=Fraunces:opsz,wght@9..144,500;9..144,600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/garpis.css') }}">
</head>
<body>
<div class="app">

  @php
    $u = auth()->user();
    $onglet = fn ($motif) => request()->routeIs($motif) ? 'rail__lien--actif' : '';
  @endphp

  <nav class="rail">
    <div class="rail__marque">
      <img src="{{ asset('assets/logo_garpis.png') }}" alt="">
      <div>
        <b>GARPIS</b>
        <span>Formation</span>
      </div>
    </div>

    <a href="{{ route('accueil') }}" class="rail__lien {{ $onglet('accueil') }}">
      @include('partials.icone', ['n' => 'accueil']) Accueil
    </a>
    <a href="{{ route('sessions.index') }}" class="rail__lien {{ $onglet('sessions.*') }}">
      @include('partials.icone', ['n' => 'sessions']) Sessions
    </a>
    <a href="{{ route('apprenants.index') }}" class="rail__lien {{ $onglet('apprenants.*') }}">
      @include('partials.icone', ['n' => 'apprenants']) Apprenants
    </a>

    <div class="rail__section">Argent</div>
    <a href="{{ route('caisse.jour') }}" class="rail__lien {{ $onglet('caisse.*') }}">
      @include('partials.icone', ['n' => 'caisse']) Caisse du jour
    </a>
    <a href="{{ route('impayes') }}" class="rail__lien {{ $onglet('impayes') }}">
      @include('partials.icone', ['n' => 'impayes']) Impayés
    </a>

    @if ($u->a('DIRECTRICE', 'COMPTABLE'))
      <div class="rail__section">Contrôle</div>
      <a href="{{ route('audit') }}" class="rail__lien {{ $onglet('audit') }}">
        @include('partials.icone', ['n' => 'journal']) Journal
      </a>
    @endif

    <div class="rail__pied">
      <div class="jeton">
        <div class="jeton__rond">{{ $u->initiales() }}</div>
        <div>
          <div class="jeton__nom">{{ $u->prenoms }}</div>
          <div class="jeton__role">{{ collect($u->codesRoles())->map(fn ($c) => \App\Models\Role::tous()[$c] ?? $c)->implode(', ') }}</div>
        </div>
      </div>
      <form method="POST" action="{{ route('deconnexion') }}" style="margin-top:8px">
        @csrf
        <button class="rail__lien" style="width:100%;background:none;border:none;cursor:pointer;font-family:var(--police)">
          @include('partials.icone', ['n' => 'sortie']) Se déconnecter
        </button>
      </form>
    </div>
  </nav>

  <main class="zone">
    <div class="contenu">

      @if (session('succes'))
        <div class="message message--succes">@include('partials.icone', ['n' => 'coche']) <div>{{ session('succes') }}</div></div>
      @endif
      @if (session('erreur'))
        <div class="message message--erreur">@include('partials.icone', ['n' => 'alerte']) <div>{{ session('erreur') }}</div></div>
      @endif
      @if (session('alerte'))
        <div class="message message--alerte">@include('partials.icone', ['n' => 'alerte']) <div>{{ session('alerte') }}</div></div>
      @endif

      @yield('contenu')
    </div>
  </main>

  @isset($encaisseJour)
    <div class="ruban">
      <span>Encaissé aujourd'hui <b>{{ \App\Services\Montant::format($encaisseJour) }} F</b></span>
      <a href="{{ route('caisse.jour') }}">Voir la caisse →</a>
    </div>
  @endisset

  <nav class="barre-onglets">
    <a href="{{ route('accueil') }}" class="{{ request()->routeIs('accueil') ? 'actif' : '' }}">
      @include('partials.icone', ['n' => 'accueil']) Accueil
    </a>
    <a href="{{ route('sessions.index') }}" class="{{ request()->routeIs('sessions.*') ? 'actif' : '' }}">
      @include('partials.icone', ['n' => 'sessions']) Sessions
    </a>
    <a href="{{ route('apprenants.index') }}" class="{{ request()->routeIs('apprenants.*') ? 'actif' : '' }}">
      @include('partials.icone', ['n' => 'apprenants']) Apprenants
    </a>
    <a href="{{ route('caisse.jour') }}" class="{{ request()->routeIs('caisse.*') ? 'actif' : '' }}">
      @include('partials.icone', ['n' => 'caisse']) Caisse
    </a>
    <a href="{{ route('impayes') }}" class="{{ request()->routeIs('impayes') ? 'actif' : '' }}">
      @include('partials.icone', ['n' => 'impayes']) Impayés
    </a>
  </nav>

</div>
@stack('scripts')
</body>
</html>
