@extends('layouts.app')
@section('titre', $session->libelle)

@section('contenu')
  <div class="entete">
    <div class="entete__sur"><span class="code">{{ $session->code }}</span> · {{ $session->libelleType() }}</div>
    <h1>{{ $session->libelle }}</h1>
    <p>
      {{ $session->formation->intitule }} — {{ $session->antenne->ville }}
      · du {{ $session->date_debut->format('d/m/Y') }} au {{ $session->date_fin->format('d/m/Y') }}
    </p>
  </div>

  <div class="grille grille--3" style="margin-bottom:16px">
    <div class="stat">
      <div class="stat__label">Inscrits</div>
      <div class="stat__valeur">{{ $session->inscriptions->count() }}</div>
    </div>
    <div class="stat">
      <div class="stat__label">Encaissé</div>
      <div class="stat__valeur">{{ \App\Services\Montant::format($encaisse) }}<span class="stat__unite">F</span></div>
    </div>
    @php $resteSession = $soldes->sum(fn ($s) => max(0, $s->solde)); @endphp
    <div class="stat {{ $resteSession > 0 ? 'stat--alerte' : '' }}">
      <div class="stat__label">Reste à encaisser</div>
      <div class="stat__valeur">{{ \App\Services\Montant::format($resteSession) }}<span class="stat__unite">F</span></div>
    </div>
    <div class="stat">
      <div class="stat__label">Coût par apprenant</div>
      <div class="stat__valeur">{{ \App\Services\Montant::format($session->cout) }}<span class="stat__unite">F</span></div>
    </div>
  </div>

  <div class="actions" style="margin-bottom:16px">
    <a href="{{ route('sessions.inscrire', $session) }}" class="bouton bouton--plein">
      @include('partials.icone', ['n' => 'plus']) Inscrire un apprenant
    </a>
    @if (auth()->user()->a('DIRECTRICE', 'DIR_ETUDES', 'SECRETAIRE'))
      <a href="{{ route('attestations.preparer', $session) }}" class="bouton bouton--vide">Attestations</a>
    @endif
    @if (auth()->user()->a('DIRECTRICE', 'DIR_ETUDES'))
      <form method="POST" action="{{ route('sessions.dupliquer', $session) }}">
        @csrf
        <button class="bouton bouton--vide">Dupliquer la session</button>
      </form>
    @endif
  </div>

  <div class="carte carte--serree">
    <div class="carte__titre"><h3>Apprenants inscrits</h3></div>

    @if ($session->inscriptions->isEmpty())
      <div class="vide">
        <h3>Personne n'est encore inscrit</h3>
        <p>Inscrivez le premier apprenant pour commencer le suivi des paiements.</p>
        <a href="{{ route('sessions.inscrire', $session) }}" class="bouton bouton--plein">Inscrire un apprenant</a>
      </div>
    @else
      <div class="enveloppe-tableau">
        <table class="tableau">
          <thead>
            <tr><th>Apprenant</th><th class="nombre">Versé</th><th>Solde</th><th>Attestation</th><th></th></tr>
          </thead>
          <tbody>
            @foreach ($session->inscriptions as $i)
              @php
                $s = $soldes[$i->id] ?? null;
                $solde = (int) ($s->solde ?? 0);
                $soldee = $solde <= 0;
              @endphp
              <tr>
                <td>
                  <div class="ligne-perso">
                    <div class="ligne-perso__rond">{{ mb_substr($i->apprenant->nom, 0, 1) }}{{ mb_substr($i->apprenant->prenoms, 0, 1) }}</div>
                    <div>
                      <a href="{{ route('apprenants.fiche', $i->apprenant) }}" class="ligne-perso__nom">{{ $i->apprenant->nomComplet() }}</a>
                      <div class="ligne-perso__meta">{{ $i->apprenant->telephone }}</div>
                    </div>
                  </div>
                </td>
                <td class="nombre">{{ \App\Services\Montant::format((int) ($s->total_verse ?? 0)) }}</td>
                <td>
                  @if ($soldee)
                    <span class="pastille pastille--solde">Soldé</span>
                  @else
                    <span class="pastille pastille--du">
                      <span class="pastille__nb">{{ \App\Services\Montant::format($solde) }}</span> F dus
                    </span>
                  @endif
                </td>
                <td>
                  @if ($i->attestation)
                    <a href="{{ route('attestations.pdf', $i->attestation) }}" class="code">{{ $i->attestation->numero }}</a>
                  @elseif ($soldee)
                    <span class="verrou verrou--ouvert">
                      @include('partials.icone', ['n' => 'cadenas-ouvert']) Prête
                    </span>
                  @else
                    <span class="verrou verrou--ferme" title="Le solde doit être apuré avant toute attestation">
                      @include('partials.icone', ['n' => 'cadenas']) Bloquée
                    </span>
                  @endif
                </td>
                <td style="text-align:right">
                  @if (! $soldee && auth()->user()->a('DIRECTRICE', 'DIR_ETUDES', 'SECRETAIRE'))
                    <a href="{{ route('paiements.formulaire', $i) }}" class="bouton bouton--vif">Encaisser</a>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
@endsection
