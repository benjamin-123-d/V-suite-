@extends('layouts.app')
@section('titre', 'Apprenants')

@section('contenu')
  <div class="entete">
    <div class="entete__sur">Fiches du centre</div>
    <h1>Apprenants</h1>
  </div>

  <div class="carte">
    <form method="GET" style="display:flex;gap:9px">
      <input type="search" name="q" value="{{ $recherche }}" placeholder="Nom, prénom ou téléphone">
      <button class="bouton bouton--plein">@include('partials.icone', ['n' => 'recherche']) Chercher</button>
    </form>
  </div>

  <div class="actions" style="margin-bottom:16px">
    <a href="{{ route('apprenants.formulaire') }}" class="bouton bouton--plein">
      @include('partials.icone', ['n' => 'plus']) Nouvelle fiche
    </a>
  </div>

  @if ($apprenants->isEmpty())
    <div class="carte vide">
      <h3>{{ $recherche ? 'Aucun résultat' : 'Aucune fiche pour le moment' }}</h3>
      <p>{{ $recherche ? 'Essayez avec le numéro de téléphone.' : 'Créez la première fiche pour commencer.' }}</p>
      <a href="{{ route('apprenants.formulaire') }}" class="bouton bouton--plein">Créer une fiche</a>
    </div>
  @else
    <div class="carte carte--serree">
      <div class="enveloppe-tableau">
        <table class="tableau">
          <thead><tr><th>Apprenant</th><th>Téléphone</th><th>Ville</th></tr></thead>
          <tbody>
            @foreach ($apprenants as $a)
              <tr>
                <td>
                  <div class="ligne-perso">
                    <div class="ligne-perso__rond">{{ mb_substr($a->nom, 0, 1) }}{{ mb_substr($a->prenoms, 0, 1) }}</div>
                    <div>
                      <a href="{{ route('apprenants.fiche', $a) }}" class="ligne-perso__nom">{{ $a->nomComplet() }}</a>
                      <div class="ligne-perso__meta">{{ $a->profession ?: '—' }}</div>
                    </div>
                  </div>
                </td>
                <td>{{ $a->telephone }}</td>
                <td>{{ $a->ville ?: '—' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    {{ $apprenants->links() }}
  @endif
@endsection
