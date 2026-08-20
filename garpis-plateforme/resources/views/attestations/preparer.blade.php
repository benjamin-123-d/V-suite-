@extends('layouts.app')
@section('titre', 'Attestations')

@section('contenu')
  <div class="entete">
    <div class="entete__sur"><span class="code">{{ $session->code }}</span> · {{ $session->libelle }}</div>
    <h1>Attestations</h1>
    <p>Seuls les apprenants soldés peuvent recevoir une attestation. Cette règle ne souffre pas d'exception.</p>
  </div>

  @php
    $eligibles = $inscriptions->filter(fn ($i) => $i->solde_calcule <= 0 && ! $i->attestation);
    $bloquees  = $inscriptions->filter(fn ($i) => $i->solde_calcule > 0);
    $delivrees = $inscriptions->filter(fn ($i) => (bool) $i->attestation);
  @endphp

  <div class="grille grille--3" style="margin-bottom:16px">
    <div class="stat">
      <div class="stat__label">Prêtes à délivrer</div>
      <div class="stat__valeur" style="color:var(--or)">{{ $eligibles->count() }}</div>
    </div>
    <div class="stat {{ $bloquees->count() ? 'stat--alerte' : '' }}">
      <div class="stat__label">Bloquées par un solde</div>
      <div class="stat__valeur">{{ $bloquees->count() }}</div>
    </div>
    <div class="stat">
      <div class="stat__label">Déjà délivrées</div>
      <div class="stat__valeur">{{ $delivrees->count() }}</div>
    </div>
  </div>

  <form method="POST" action="{{ route('attestations.generer', $session) }}">
    @csrf
    <div class="carte carte--serree">
      <div class="carte__titre">
        <h3>Apprenants de la session</h3>
        @if ($eligibles->isNotEmpty())
          <button class="bouton bouton--plein">Délivrer la sélection</button>
        @endif
      </div>

      <div class="enveloppe-tableau">
        <table class="tableau">
          <thead>
            <tr>
              <th style="width:38px"></th>
              <th>Apprenant</th><th>Solde</th><th class="nombre">Présence</th><th>Attestation</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($inscriptions as $i)
              <tr>
                <td>
                  @if ($i->solde_calcule <= 0 && ! $i->attestation)
                    <input type="checkbox" name="inscriptions[]" value="{{ $i->id }}" checked style="width:auto">
                  @endif
                </td>
                <td>
                  <div class="ligne-perso__nom">{{ $i->apprenant->nomComplet() }}</div>
                  <div class="ligne-perso__meta">{{ $i->apprenant->telephone }}</div>
                </td>
                <td>
                  @if ($i->solde_calcule <= 0)
                    <span class="pastille pastille--solde">Soldé</span>
                  @else
                    <span class="pastille pastille--du">
                      <span class="pastille__nb">{{ \App\Services\Montant::format($i->solde_calcule) }}</span> F dus
                    </span>
                  @endif
                </td>
                <td class="nombre">{{ $i->taux }} %</td>
                <td>
                  @if ($i->attestation)
                    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
                      <a href="{{ route('attestations.pdf', $i->attestation) }}" class="code">{{ $i->attestation->numero }}</a>
                      @php $tel = preg_replace('/[^0-9]/', '', $i->apprenant->telephone); @endphp
                      <a href="https://wa.me/{{ $tel }}?text={{ rawurlencode('Bonjour, votre attestation GARPIS est prête. Vérifiez-la sur ' . $i->attestation->urlVerification()) }}"
                         target="_blank" rel="noopener" class="bouton bouton--vide">WhatsApp</a>
                    </div>
                  @elseif ($i->solde_calcule <= 0)
                    <span class="verrou verrou--ouvert">@include('partials.icone', ['n' => 'cadenas-ouvert']) Prête</span>
                  @else
                    <span class="verrou verrou--ferme"
                          title="Le cadenas ne s'ouvre que lorsque le solde est apuré">
                      @include('partials.icone', ['n' => 'cadenas']) Bloquée
                    </span>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </form>

  @if ($bloquees->isNotEmpty())
    <div class="message message--alerte">
      @include('partials.icone', ['n' => 'alerte'])
      <div>
        {{ $bloquees->count() }} apprenant{{ $bloquees->count() > 1 ? 's' : '' }} ne peu{{ $bloquees->count() > 1 ? 'vent' : 't' }} pas
        recevoir d'attestation tant que le solde n'est pas réglé — matériel compris.
        Pour un cas de prise en charge par un tiers, passez par une remise motivée
        ou enregistrez le règlement du tiers : la trace reste, la règle tient.
      </div>
    </div>
  @endif
@endsection
