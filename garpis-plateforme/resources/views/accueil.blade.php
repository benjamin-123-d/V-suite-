@extends('layouts.app')
@section('titre', 'Accueil')

@section('contenu')

  <div class="entete">
    <div class="entete__sur">{{ now()->translatedFormat('l j F Y') }}</div>
    <h1>Bonjour {{ auth()->user()->prenoms }}</h1>
    <p>Voici où en est le centre aujourd'hui.</p>
  </div>

  @if ($iaActive)
    <div class="assistant">
      <div class="assistant__haut">
        <p class="assistant__titre">Posez votre question</p>
        <p class="assistant__sous">L'assistant lit les données du centre. Il ne modifie rien.</p>

        <div class="assistant__saisie">
          <input type="text" id="question" placeholder="Combien d'apprenants inscrits ce mois ?"
                 autocomplete="off">
          <button class="bouton bouton--vif" id="envoyer">Demander</button>
        </div>

        <div class="assistant__exemples">
          <button class="puce">Total des impayés</button>
          <button class="puce">Qui n'a pas soldé cette session ?</button>
          <button class="puce">Encaissements du mois</button>
        </div>
      </div>
      <div class="assistant__reponse" id="reponse" hidden></div>
    </div>
  @endif

  <div class="grille grille--3" style="margin-bottom:16px">
    <div class="stat">
      <div class="stat__label">Encaissé ce mois</div>
      <div class="stat__valeur">{{ \App\Services\Montant::format($encaisseMois) }}<span class="stat__unite">FCFA</span></div>
      <div class="stat__note">dont {{ \App\Services\Montant::format($encaisseJour) }} aujourd'hui</div>
    </div>

    <div class="stat {{ $impayes > 0 ? 'stat--alerte' : '' }}">
      <div class="stat__label">Reste à encaisser</div>
      <div class="stat__valeur">{{ \App\Services\Montant::format($impayes) }}<span class="stat__unite">FCFA</span></div>
      <div class="stat__note">{{ $nbImpayes }} apprenant{{ $nbImpayes > 1 ? 's' : '' }} concerné{{ $nbImpayes > 1 ? 's' : '' }}</div>
    </div>

    <div class="stat">
      <div class="stat__label">Apprenants actifs</div>
      <div class="stat__valeur">{{ $apprenantsActifs }}</div>
      <div class="stat__note">{{ $sessionsEnCours }} session{{ $sessionsEnCours > 1 ? 's' : '' }} en cours</div>
    </div>

    <div class="stat">
      <div class="stat__label">Attestations délivrées</div>
      <div class="stat__valeur">{{ $attestations }}</div>
      <div class="stat__note">vérifiables par QR code</div>
    </div>
  </div>

  @if ($aPreparer->isNotEmpty())
    <div class="carte carte--serree">
      <div class="carte__titre">
        <h3>Sessions qui se terminent cette semaine</h3>
        <span class="pastille pastille--neutre">{{ $aPreparer->count() }}</span>
      </div>
      <div class="enveloppe-tableau">
        <table class="tableau">
          <tbody>
            @foreach ($aPreparer as $s)
              <tr>
                <td>
                  <div class="ligne-perso__nom">{{ $s->libelle }}</div>
                  <span class="code">{{ $s->code }}</span>
                </td>
                <td class="nombre">fin le {{ \Carbon\Carbon::parse($s->date_fin)->format('d/m') }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div style="padding:12px 18px;font-size:13px;color:var(--encre-doux);border-top:1px solid var(--trait)">
        Pensez à solder les comptes avant de préparer les attestations.
      </div>
    </div>
  @endif

  <div class="actions">
    <a href="{{ route('apprenants.formulaire') }}" class="bouton bouton--plein">
      @include('partials.icone', ['n' => 'plus']) Nouvel apprenant
    </a>
    <a href="{{ route('sessions.index') }}" class="bouton bouton--vide">Voir les sessions</a>
  </div>

@endsection

@push('scripts')
<script>
(function () {
  const champ   = document.getElementById('question');
  const bouton  = document.getElementById('envoyer');
  const sortie  = document.getElementById('reponse');
  if (!champ) return;

  document.querySelectorAll('.puce').forEach(p => {
    p.addEventListener('click', () => { champ.value = p.textContent.trim(); demander(); });
  });

  bouton.addEventListener('click', demander);
  champ.addEventListener('keydown', e => { if (e.key === 'Enter') demander(); });

  async function demander() {
    const question = champ.value.trim();
    if (!question) return;

    sortie.hidden = false;
    sortie.textContent = 'Recherche dans les données du centre…';
    bouton.disabled = true;

    try {
      const r = await fetch('{{ route('assistant') }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify({ question }),
      });
      const data = await r.json();
      sortie.textContent = data.reponse || "Aucune réponse.";
    } catch (e) {
      sortie.textContent = "La demande n'a pas abouti. Vérifiez votre connexion et réessayez.";
    } finally {
      bouton.disabled = false;
    }
  }
})();
</script>
@endpush
