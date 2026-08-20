@php
  $traits = [
    'accueil'    => '<path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/>',
    'sessions'   => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 11h18"/>',
    'apprenants' => '<circle cx="9" cy="8" r="3.2"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M17 11.2a3 3 0 1 0-1.6-5.5M17.5 20a5.6 5.6 0 0 0-2.2-4.4"/>',
    'caisse'     => '<rect x="2.5" y="6" width="19" height="12" rx="2"/><circle cx="12" cy="12" r="2.6"/><path d="M6 12h.01M18 12h.01"/>',
    'impayes'    => '<path d="M12 3v18"/><path d="M17 6.5H9.8a2.8 2.8 0 0 0 0 5.6h4.4a2.8 2.8 0 0 1 0 5.6H6.5"/>',
    'journal'    => '<path d="M5 3h11l4 4v14H5z"/><path d="M9 12h7M9 16h7M9 8h3"/>',
    'sortie'     => '<path d="M14 4h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-4"/><path d="M10 16l-4-4 4-4M6 12h9"/>',
    'coche'      => '<circle cx="12" cy="12" r="9"/><path d="m8.5 12 2.5 2.5 4.5-5"/>',
    'alerte'     => '<circle cx="12" cy="12" r="9"/><path d="M12 7.5v5.5M12 16.2h.01"/>',
    'plus'       => '<path d="M12 5v14M5 12h14"/>',
    'cadenas'    => '<rect x="4.5" y="10.5" width="15" height="10" rx="2"/><path d="M8 10.5V7a4 4 0 0 1 8 0v3.5"/>',
    'cadenas-ouvert' => '<rect x="4.5" y="10.5" width="15" height="10" rx="2"/><path d="M8 10.5V7a4 4 0 0 1 7.5-2"/>',
    'recu'       => '<path d="M5 3h14v18l-3-2-2 2-2-2-2 2-2-2-3 2z"/><path d="M9 8h6M9 12h6"/>',
    'whatsapp'   => '<path d="M20 11.8A8 8 0 1 1 8.5 4.6"/><path d="M20.5 4.5 12 12"/>',
    'recherche'  => '<circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.5 4.5"/>',
  ];
@endphp
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
  {!! $traits[$n] ?? '' !!}
</svg>
