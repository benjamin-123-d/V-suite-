@extends('layouts.app')
@section('titre', 'Inscrire un apprenant')

@section('contenu')
  <div class="entete">
    <div class="entete__sur"><span class="code">{{ $session->code }}</span></div>
    <h1>Inscrire un apprenant</h1>
    <p>{{ $session->libelle }} — {{ $session->formation->intitule }}</p>
  </div>

  <form method="POST" action="{{ route('sessions.inscrire', $session) }}">
    @csrf
    <div class="carte">
      <div class="champ">
        <label for="apprenant_id">Apprenant</label>
        <select id="apprenant_id" name="apprenant_id" required>
          <option value="">Rechercher dans les fiches existantes…</option>
          @foreach ($apprenants as $a)
            <option value="{{ $a->id }}">{{ $a->nomComplet() }} — {{ $a->telephone }}</option>
          @endforeach
        </select>
        <p class="champ__aide">
          Cette personne n'a pas encore de fiche ?
          <a href="{{ route('apprenants.formulaire') }}">Créez-la d'abord</a>.
        </p>
        @error('apprenant_id') <p class="erreur-champ">{{ $message }}</p> @enderror
      </div>

      <div class="champ">
        <label for="montant_du">Montant dû (FCFA)</label>
        <input id="montant_du" type="number" name="montant_du"
               value="{{ old('montant_du', $session->cout) }}" min="0" step="1" required>
        <p class="champ__aide">Repris du coût de la session. Modifiable au cas par cas.</p>
      </div>

      <div class="grille grille--2">
        <div class="champ">
          <label for="remise_montant">Remise accordée (FCFA)</label>
          <input id="remise_montant" type="number" name="remise_montant"
                 value="{{ old('remise_montant', 0) }}" min="0" step="1">
          <p class="champ__aide">
            Au-delà de {{ config('garpis.plafond_remise_de') }} %, la Directrice devra valider.
          </p>
        </div>
        <div class="champ">
          <label for="remise_motif">Motif de la remise</label>
          <input id="remise_motif" type="text" name="remise_motif" value="{{ old('remise_motif') }}"
                 placeholder="Prise en charge par la mairie">
          @error('remise_motif') <p class="erreur-champ">{{ $message }}</p> @enderror
        </div>
      </div>

      <div class="actions">
        <button class="bouton bouton--plein">Inscrire</button>
        <a href="{{ route('sessions.fiche', $session) }}" class="bouton bouton--vide">Annuler</a>
      </div>
    </div>
  </form>
@endsection
