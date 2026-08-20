@extends('layouts.app')
@section('titre', "Journal d'audit")

@section('contenu')
  <div class="entete">
    <div class="entete__sur">Traçabilité</div>
    <h1>Journal</h1>
    <p>Toute opération financière et toute attestation laissent une trace ici.</p>
  </div>

  <div class="carte carte--serree">
    <div class="enveloppe-tableau">
      <table class="tableau">
        <thead><tr><th>Quand</th><th>Qui</th><th>Action</th><th>Objet</th></tr></thead>
        <tbody>
          @foreach ($lignes as $l)
            <tr>
              <td style="white-space:nowrap">{{ $l->survenu_le->format('d/m/Y H:i') }}</td>
              <td>{{ $l->utilisateur?->nomComplet() ?? '—' }}</td>
              <td><span class="pastille pastille--grise">{{ $l->action }}</span></td>
              <td><span class="code">{{ $l->entite }}</span></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  {{ $lignes->links() }}
@endsection
