@extends('layouts.app')
@section('titre', 'Nouvelle fiche')

@section('contenu')
  <div class="entete">
    <div class="entete__sur">Fiche apprenant</div>
    <h1>Nouvelle fiche</h1>
  </div>

  @if (session('doublons'))
    <div class="carte" style="border-color:#EBD8AC;background:var(--or-pale)">
      <h3>Ces fiches existent déjà</h3>
      <p style="font-size:13.5px;margin-bottom:12px">
        Vérifiez qu'il ne s'agit pas de la même personne. Une personne qui revient pour
        une deuxième formation garde sa fiche d'origine.
      </p>
      @foreach (session('doublons') as $d)
        <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-top:1px solid #EBD8AC">
          <span><b>{{ $d->nomComplet() }}</b> — {{ $d->telephone }}</span>
          <a href="{{ route('apprenants.fiche', $d) }}" class="bouton bouton--vide">Ouvrir</a>
        </div>
      @endforeach
    </div>
  @endif

  <form method="POST" action="{{ route('apprenants.enregistrer') }}">
    @csrf
    @if (session('doublons'))
      <input type="hidden" name="ignorer_doublon" value="1">
    @endif

    <div class="carte">
      <div class="grille grille--2">
        <div class="champ">
          <label for="nom">Nom</label>
          <input id="nom" type="text" name="nom" value="{{ old('nom') }}" required autofocus>
          @error('nom') <p class="erreur-champ">{{ $message }}</p> @enderror
        </div>
        <div class="champ">
          <label for="prenoms">Prénoms</label>
          <input id="prenoms" type="text" name="prenoms" value="{{ old('prenoms') }}" required>
          @error('prenoms') <p class="erreur-champ">{{ $message }}</p> @enderror
        </div>
      </div>

      <div class="grille grille--2">
        <div class="champ">
          <label for="telephone">Téléphone</label>
          <input id="telephone" type="tel" name="telephone" value="{{ old('telephone') }}" required
                 placeholder="+229 97 00 00 00">
          <p class="champ__aide">Sert aussi à repérer les doublons.</p>
          @error('telephone') <p class="erreur-champ">{{ $message }}</p> @enderror
        </div>
        <div class="champ">
          <label for="sexe">Sexe</label>
          <select id="sexe" name="sexe">
            <option value="">Non précisé</option>
            <option value="F" @selected(old('sexe') === 'F')>Féminin</option>
            <option value="M" @selected(old('sexe') === 'M')>Masculin</option>
          </select>
          <p class="champ__aide">Utilisé pour l'accord sur l'attestation.</p>
        </div>
      </div>

      <div class="grille grille--2">
        <div class="champ">
          <label for="date_naissance">Date de naissance</label>
          <input id="date_naissance" type="date" name="date_naissance" value="{{ old('date_naissance') }}">
        </div>
        <div class="champ">
          <label for="ville">Ville</label>
          <input id="ville" type="text" name="ville" value="{{ old('ville') }}">
        </div>
      </div>

      <div class="champ">
        <label for="adresse">Adresse</label>
        <input id="adresse" type="text" name="adresse" value="{{ old('adresse') }}">
      </div>

      <div class="grille grille--2">
        <div class="champ">
          <label for="profession">Profession</label>
          <input id="profession" type="text" name="profession" value="{{ old('profession') }}">
        </div>
        <div class="champ">
          <label for="niveau_etudes">Niveau d'études</label>
          <input id="niveau_etudes" type="text" name="niveau_etudes" value="{{ old('niveau_etudes') }}">
        </div>
      </div>

      <div class="grille grille--2">
        <div class="champ">
          <label for="contact_urgence_nom">Personne à prévenir</label>
          <input id="contact_urgence_nom" type="text" name="contact_urgence_nom" value="{{ old('contact_urgence_nom') }}">
        </div>
        <div class="champ">
          <label for="contact_urgence_tel">Son téléphone</label>
          <input id="contact_urgence_tel" type="tel" name="contact_urgence_tel" value="{{ old('contact_urgence_tel') }}">
        </div>
      </div>

      <div class="actions">
        <button class="bouton bouton--plein">
          {{ session('doublons') ? 'Créer quand même la fiche' : 'Créer la fiche' }}
        </button>
        <a href="{{ route('apprenants.index') }}" class="bouton bouton--vide">Annuler</a>
      </div>
    </div>
  </form>
@endsection
