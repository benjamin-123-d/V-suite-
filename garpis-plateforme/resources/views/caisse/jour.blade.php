@extends('layouts.app')
@section('titre', 'Caisse du jour')

@section('contenu')
  <div class="entete">
    <div class="entete__sur">Caisse commune — position du {{ \Carbon\Carbon::parse($jour)->format('d/m/Y') }}</div>
    <h1>Caisse du jour</h1>
    <p>Chaque encaisseur clôture sa position ; la Directrice valide la consolidation.</p>
  </div>

  <div class="grille grille--3" style="margin-bottom:16px">
    <div class="stat">
      <div class="stat__label">Total encaissé</div>
      <div class="stat__valeur">{{ \App\Services\Montant::format($total) }}<span class="stat__unite">F</span></div>
    </div>
    <div class="stat">
      <div class="stat__label">Espèces</div>
      <div class="stat__valeur">{{ \App\Services\Montant::format($especes) }}<span class="stat__unite">F</span></div>
      <div class="stat__note">à compter dans la caisse</div>
    </div>
    <div class="stat">
      <div class="stat__label">Mobile money</div>
      <div class="stat__valeur">{{ \App\Services\Montant::format((int) ($parMode['MTN_MOMO'] ?? 0) + (int) ($parMode['MOOV_MONEY'] ?? 0)) }}<span class="stat__unite">F</span></div>
    </div>
    <div class="stat">
      <div class="stat__label">Autres modes</div>
      <div class="stat__valeur">{{ \App\Services\Montant::format((int) ($parMode['VIREMENT'] ?? 0) + (int) ($parMode['CHEQUE'] ?? 0) + (int) ($parMode['EN_LIGNE'] ?? 0)) }}<span class="stat__unite">F</span></div>
    </div>
  </div>

  <div class="carte carte--serree">
    <div class="carte__titre"><h3>Détail par encaisseur</h3></div>
    @if ($parEncaisseur->isEmpty())
      <div class="vide"><p>Aucun encaissement enregistré ce jour.</p></div>
    @else
      <div class="enveloppe-tableau">
        <table class="tableau">
          <thead><tr><th>Encaisseur</th><th>Antenne</th><th>Mode</th><th class="nombre">Opérations</th><th class="nombre">Total</th></tr></thead>
          <tbody>
            @foreach ($parEncaisseur as $l)
              <tr>
                <td class="ligne-perso__nom">{{ $l->prenoms }} {{ $l->nom }}</td>
                <td>{{ $l->ville }}</td>
                <td>{{ \App\Models\Paiement::MODES[$l->mode] ?? $l->mode }}</td>
                <td class="nombre">{{ $l->nb }}</td>
                <td class="nombre">{{ \App\Services\Montant::format((int) $l->total) }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>

  <div class="carte">
    <h2>Clôturer ma position</h2>
    @if ($cloture)
      <div class="message message--succes">
        @include('partials.icone', ['n' => 'coche'])
        <div>
          Position clôturée : {{ \App\Services\Montant::format($cloture->compte_especes) }} F comptés,
          écart de {{ \App\Services\Montant::format(abs($cloture->ecart)) }} F.
          {{ $cloture->validee ? 'Validée par la Directrice.' : 'En attente de validation.' }}
        </div>
      </div>
    @else
      <form method="POST" action="{{ route('caisse.cloturer') }}">
        @csrf
        <input type="hidden" name="jour" value="{{ $jour }}">
        <input type="hidden" name="theorique_especes" value="{{ $especes }}">

        <div class="champ">
          <label for="compte_especes">Espèces comptées dans la caisse (FCFA)</label>
          <input id="compte_especes" class="saisie-montant" type="number" name="compte_especes"
                 inputmode="numeric" min="0" step="1" required>
          <p class="champ__aide" style="text-align:center">
            Le théorique du jour est de {{ \App\Services\Montant::format($especes) }} F.
          </p>
        </div>

        <div class="champ">
          <label for="justification">Justification de l'écart</label>
          <input id="justification" type="text" name="justification"
                 placeholder="Obligatoire si le compte diffère du théorique">
        </div>

        <button class="bouton bouton--plein bouton--large">Clôturer la journée</button>
      </form>
    @endif
  </div>
@endsection
