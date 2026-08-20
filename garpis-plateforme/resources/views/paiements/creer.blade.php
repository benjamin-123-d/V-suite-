@extends('layouts.app')
@section('titre', 'Encaisser')

@section('contenu')
  <div class="entete">
    <div class="entete__sur">{{ $inscription->session->formation->intitule }} · <span class="code">{{ $inscription->session->code }}</span></div>
    <h1>Encaisser</h1>
    <p>{{ $inscription->apprenant->nomComplet() }} — {{ $inscription->apprenant->telephone }}</p>
  </div>

  <div class="grille grille--3" style="margin-bottom:16px">
    <div class="stat">
      <div class="stat__label">Total dû</div>
      <div class="stat__valeur">{{ \App\Services\Montant::format((int) $detail->du_formation + (int) $detail->du_materiel) }}<span class="stat__unite">F</span></div>
      @if ((int) $detail->du_materiel > 0)
        <div class="stat__note">dont {{ \App\Services\Montant::format((int) $detail->du_materiel) }} F de matériel</div>
      @endif
    </div>
    <div class="stat">
      <div class="stat__label">Déjà versé</div>
      <div class="stat__valeur">{{ \App\Services\Montant::format((int) $detail->total_verse) }}<span class="stat__unite">F</span></div>
    </div>
    <div class="stat stat--alerte">
      <div class="stat__label">Reste à payer</div>
      <div class="stat__valeur">{{ \App\Services\Montant::format((int) $detail->solde) }}<span class="stat__unite">F</span></div>
    </div>
  </div>

  <form method="POST" action="{{ route('paiements.enregistrer', $inscription) }}">
    @csrf
    <div class="carte">
      <div class="champ">
        <label for="montant">Montant reçu</label>
        <input id="montant" class="saisie-montant" type="number" name="montant" inputmode="numeric"
               value="{{ old('montant', max(0, (int) $detail->solde)) }}" min="1" step="1" required autofocus>
        <p class="champ__aide" style="text-align:center">En francs CFA, sans virgule ni espace.</p>
        @error('montant') <p class="erreur-champ">{{ $message }}</p> @enderror
      </div>

      <div class="champ">
        <label for="mode">Mode de paiement</label>
        <select id="mode" name="mode" required>
          @foreach ($modes as $cle => $libelle)
            @if ($cle !== 'EN_LIGNE')
              <option value="{{ $cle }}">{{ $libelle }}</option>
            @endif
          @endforeach
        </select>
      </div>

      <div class="champ">
        <label for="reference_transaction">Référence de la transaction</label>
        <input id="reference_transaction" type="text" name="reference_transaction"
               value="{{ old('reference_transaction') }}" placeholder="Facultatif pour les espèces">
      </div>

      <div class="champ">
        <label for="objet">Au titre de</label>
        <select id="objet" name="objet" required>
          <option value="FORMATION">Frais de formation</option>
          <option value="MATERIEL">Matériel</option>
        </select>
      </div>

      <div class="actions">
        <button class="bouton bouton--plein bouton--large">
          @include('partials.icone', ['n' => 'recu']) Enregistrer et éditer le reçu
        </button>
      </div>
      <p class="champ__aide" style="text-align:center;margin-top:10px">
        Le numéro de reçu est attribué automatiquement, dans la série unique du centre.
      </p>
    </div>
  </form>
@endsection
