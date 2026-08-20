@extends('layouts.app')
@section('titre', 'Ouvrir une session')

@section('contenu')
  <div class="entete">
    <div class="entete__sur">Nouvelle session</div>
    <h1>Ouvrir une session</h1>
    <p>Le code de session est attribué automatiquement à l'enregistrement.</p>
  </div>

  <form method="POST" action="{{ route('sessions.enregistrer') }}">
    @csrf
    <div class="carte">
      <div class="grille grille--2">
        <div class="champ">
          <label for="formation_id">Formation</label>
          <select id="formation_id" name="formation_id" required>
            <option value="">Choisir…</option>
            @foreach ($formations as $f)
              <option value="{{ $f->id }}" data-cout="{{ $f->cout_reference }}" @selected(old('formation_id') === $f->id)>
                {{ $f->intitule }}
              </option>
            @endforeach
          </select>
          @error('formation_id') <p class="erreur-champ">{{ $message }}</p> @enderror
        </div>

        <div class="champ">
          <label for="antenne_id">Antenne</label>
          <select id="antenne_id" name="antenne_id" required>
            @foreach ($antennes as $a)
              <option value="{{ $a->id }}" @selected(old('antenne_id') === $a->id)>{{ $a->nom }} — {{ $a->ville }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="champ">
        <label for="libelle">Nom de la session</label>
        <input id="libelle" type="text" name="libelle" value="{{ old('libelle') }}"
               placeholder="Session de Juillet 2026" required>
        <p class="champ__aide">C'est ce texte qui apparaîtra sur l'attestation.</p>
        @error('libelle') <p class="erreur-champ">{{ $message }}</p> @enderror
      </div>

      <div class="champ">
        <label for="type">Type</label>
        <select id="type" name="type" required>
          <option value="BASE">Session de base</option>
          <option value="PROFESSIONNELLE">Session professionnelle</option>
        </select>
      </div>

      <div class="grille grille--2">
        <div class="champ">
          <label for="date_debut">Début</label>
          <input id="date_debut" type="date" name="date_debut" value="{{ old('date_debut') }}" required>
          @error('date_debut') <p class="erreur-champ">{{ $message }}</p> @enderror
        </div>
        <div class="champ">
          <label for="date_fin">Fin</label>
          <input id="date_fin" type="date" name="date_fin" value="{{ old('date_fin') }}" required>
          @error('date_fin') <p class="erreur-champ">{{ $message }}</p> @enderror
        </div>
      </div>

      <div class="grille grille--2">
        <div class="champ">
          <label for="cout">Coût de la formation (FCFA)</label>
          <input id="cout" type="number" name="cout" value="{{ old('cout') }}" min="0" step="1" required>
        </div>
        <div class="champ">
          <label for="capacite_max">Places (facultatif)</label>
          <input id="capacite_max" type="number" name="capacite_max" value="{{ old('capacite_max') }}" min="1">
        </div>
      </div>

      <div class="grille grille--2">
        <div class="champ">
          <label for="jours_horaires">Jours et horaires</label>
          <input id="jours_horaires" type="text" name="jours_horaires"
                 value="{{ old('jours_horaires') }}" placeholder="Lundi et mercredi, 15h–18h">
        </div>
        <div class="champ">
          <label for="lieu">Lieu</label>
          <input id="lieu" type="text" name="lieu" value="{{ old('lieu') }}" placeholder="Siège, Fidjrossè">
        </div>
      </div>

      <div class="actions">
        <button class="bouton bouton--plein">Ouvrir la session</button>
        <a href="{{ route('sessions.index') }}" class="bouton bouton--vide">Annuler</a>
      </div>
    </div>
  </form>
@endsection

@push('scripts')
<script>
  // Pre-remplit le cout avec le tarif de reference de la formation choisie.
  document.getElementById('formation_id').addEventListener('change', function () {
    const cout = this.selectedOptions[0]?.dataset.cout;
    const champ = document.getElementById('cout');
    if (cout && !champ.value) champ.value = cout;
  });
</script>
@endpush
