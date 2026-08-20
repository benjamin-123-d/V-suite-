@extends('layouts.app')
@section('titre', 'Impayés')

@section('contenu')
  <div class="entete">
    <div class="entete__sur">Soldes non réglés</div>
    <h1>Impayés</h1>
    <p>{{ \App\Services\Montant::formatFcfa($total) }} restent à encaisser.</p>
  </div>

  @if ($lignes->isEmpty())
    <div class="carte vide">
      <h3>Tout est soldé</h3>
      <p>Aucun apprenant ne doit d'argent au centre.</p>
    </div>
  @else
    <div class="carte carte--serree">
      <div class="enveloppe-tableau">
        <table class="tableau">
          <thead>
            <tr><th>Apprenant</th><th>Session</th><th class="nombre">Versé</th><th class="nombre">Reste</th><th></th></tr>
          </thead>
          <tbody>
            @foreach ($lignes as $l)
              <tr>
                <td>
                  <div class="ligne-perso__nom">{{ $l->nom }} {{ $l->prenoms }}</div>
                  <div class="ligne-perso__meta">{{ $l->telephone }}</div>
                </td>
                <td>
                  {{ $l->intitule }}<br>
                  <span class="code">{{ $l->session_code }}</span>
                </td>
                <td class="nombre">{{ \App\Services\Montant::format((int) $l->total_verse) }}</td>
                <td class="nombre" style="color:var(--laterite)">{{ \App\Services\Montant::format((int) $l->solde) }}</td>
                <td style="text-align:right;white-space:nowrap">
                  @php
                    $message = rawurlencode(
                      "Bonjour {$l->prenoms}, GARPIS Formation. Il reste "
                      . \App\Services\Montant::format((int) $l->solde)
                      . " FCFA à régler pour votre formation ({$l->session_code}). Merci de passer au centre. "
                      . config('garpis.telephone')
                    );
                    $tel = preg_replace('/[^0-9]/', '', $l->telephone);
                  @endphp
                  <a href="https://wa.me/{{ $tel }}?text={{ $message }}" target="_blank" rel="noopener"
                     class="bouton bouton--vide">Relancer</a>
                  <a href="{{ route('paiements.formulaire', $l->inscription_id) }}" class="bouton bouton--vif">Encaisser</a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    {{ $lignes->links() }}
  @endif
@endsection
