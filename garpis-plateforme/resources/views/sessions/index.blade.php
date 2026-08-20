@extends('layouts.app')
@section('titre', 'Sessions')

@section('contenu')
  <div class="entete">
    <div class="entete__sur">Formations en cours et passées</div>
    <h1>Sessions</h1>
  </div>

  <div class="actions" style="margin-bottom:16px">
    @if (auth()->user()->a('DIRECTRICE', 'DIR_ETUDES'))
      <a href="{{ route('sessions.formulaire') }}" class="bouton bouton--plein">
        @include('partials.icone', ['n' => 'plus']) Ouvrir une session
      </a>
    @endif
    <a href="{{ route('sessions.index') }}" class="bouton bouton--vide {{ ! $statut ? 'bouton--vif' : '' }}">Toutes</a>
    <a href="{{ route('sessions.index', ['statut' => 'EN_COURS']) }}" class="bouton bouton--vide">En cours</a>
    <a href="{{ route('sessions.index', ['statut' => 'TERMINEE']) }}" class="bouton bouton--vide">Terminées</a>
  </div>

  @if ($sessions->isEmpty())
    <div class="carte vide">
      <h3>Aucune session ici</h3>
      <p>Une session, c'est une formation avec des dates, un lieu et des apprenants.</p>
      @if (auth()->user()->a('DIRECTRICE', 'DIR_ETUDES'))
        <a href="{{ route('sessions.formulaire') }}" class="bouton bouton--plein">Ouvrir la première</a>
      @endif
    </div>
  @else
    <div class="carte carte--serree">
      <div class="enveloppe-tableau">
        <table class="tableau">
          <thead>
            <tr>
              <th>Session</th><th>Antenne</th><th>Dates</th>
              <th class="nombre">Inscrits</th><th>État</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($sessions as $s)
              <tr>
                <td>
                  <a href="{{ route('sessions.fiche', $s) }}" class="ligne-perso__nom">{{ $s->libelle }}</a>
                  <div><span class="code">{{ $s->code }}</span> · {{ $s->formation->intitule }}</div>
                </td>
                <td>{{ $s->antenne->ville }}</td>
                <td style="white-space:nowrap">
                  {{ $s->date_debut->format('d/m/y') }} → {{ $s->date_fin->format('d/m/y') }}
                </td>
                <td class="nombre">{{ $s->inscriptions_count }}@if ($s->capacite_max)<span style="color:var(--encre-doux);font-weight:400">/{{ $s->capacite_max }}</span>@endif</td>
                <td><span class="pastille pastille--{{ $s->statut === 'TERMINEE' ? 'grise' : 'neutre' }}">{{ $s->libelleStatut() }}</span></td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    {{ $sessions->links() }}
  @endif
@endsection
