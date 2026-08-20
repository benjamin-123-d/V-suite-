@extends('layouts.app')
@section('titre', $apprenant->nomComplet())

@section('contenu')
  <div class="entete">
    <div class="entete__sur">Fiche apprenant</div>
    <h1>{{ $apprenant->nomComplet() }}</h1>
    <p>{{ $apprenant->telephone }}@if ($apprenant->ville) · {{ $apprenant->ville }}@endif</p>
  </div>

  <div class="carte carte--serree">
    <div class="carte__titre"><h3>Formations suivies</h3></div>

    @if ($apprenant->inscriptions->isEmpty())
      <div class="vide">
        <h3>Aucune inscription</h3>
        <p>Inscrivez cette personne depuis la fiche d'une session.</p>
        <a href="{{ route('sessions.index') }}" class="bouton bouton--plein">Voir les sessions</a>
      </div>
    @else
      <div class="enveloppe-tableau">
        <table class="tableau">
          <thead><tr><th>Session</th><th>Solde</th><th>Attestation</th><th></th></tr></thead>
          <tbody>
            @foreach ($apprenant->inscriptions as $i)
              @php $solde = (int) ($soldes[$i->id] ?? 0); @endphp
              <tr>
                <td>
                  <a href="{{ route('sessions.fiche', $i->session) }}" class="ligne-perso__nom">{{ $i->session->formation->intitule }}</a>
                  <div><span class="code">{{ $i->session->code }}</span></div>
                </td>
                <td>
                  @if ($solde <= 0)
                    <span class="pastille pastille--solde">Soldé</span>
                  @else
                    <span class="pastille pastille--du"><span class="pastille__nb">{{ \App\Services\Montant::format($solde) }}</span> F dus</span>
                  @endif
                </td>
                <td>
                  @if ($i->attestation)
                    @if ($i->attestation->revoquee)
                      <span class="pastille pastille--du">Révoquée</span>
                    @else
                      <a href="{{ route('attestations.pdf', $i->attestation) }}" class="code">{{ $i->attestation->numero }}</a>
                    @endif
                  @elseif ($solde <= 0)
                    <span class="verrou verrou--ouvert">@include('partials.icone', ['n' => 'cadenas-ouvert']) Prête</span>
                  @else
                    <span class="verrou verrou--ferme">@include('partials.icone', ['n' => 'cadenas']) Bloquée</span>
                  @endif
                </td>
                <td style="text-align:right">
                  @if ($solde > 0 && auth()->user()->a('DIRECTRICE', 'DIR_ETUDES', 'SECRETAIRE'))
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

  <div class="carte carte--serree">
    <div class="carte__titre"><h3>Paiements</h3></div>
    @if ($paiements->isEmpty())
      <div class="vide"><p>Aucun paiement enregistré.</p></div>
    @else
      <div class="enveloppe-tableau">
        <table class="tableau">
          <thead><tr><th>Reçu</th><th>Date</th><th>Mode</th><th class="nombre">Montant</th><th></th></tr></thead>
          <tbody>
            @foreach ($paiements as $p)
              <tr @if ($p->annule) style="opacity:.5;text-decoration:line-through" @endif>
                <td><span class="code">{{ $p->numero }}</span></td>
                <td>{{ \Carbon\Carbon::parse($p->paye_le)->format('d/m/Y') }}</td>
                <td>{{ \App\Models\Paiement::MODES[$p->mode] ?? $p->mode }}</td>
                <td class="nombre">{{ \App\Services\Montant::format($p->montant) }}</td>
                <td style="text-align:right">
                  <a href="{{ route('recus.pdf', $p->recu_id) }}" class="bouton bouton--vide">Reçu</a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
@endsection
