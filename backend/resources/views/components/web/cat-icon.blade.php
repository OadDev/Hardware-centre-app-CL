@props(['icon' => '', 'size' => 23])
@php
$paths = [
    'electrical' => '<path d="M13 2 4 14h6l-1 8 9-12h-6l1-8z"/>',
    'plumbing' => '<path d="M12 3s6 6.5 6 10.5a6 6 0 0 1-12 0C6 9.5 12 3 12 3z"/>',
    'paint' => '<rect x="3" y="4" width="13" height="6" rx="1.5"/><path d="M16 7h3a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1h-7"/><path d="M11 11v3a1 1 0 0 0 1 1 1 1 0 0 1 1 1v4"/>',
    'tools' => '<path d="M15.5 4a4 4 0 0 0-5 5L4 15.5 8.5 20l6.5-6.5a4 4 0 0 0 5-5l-2.7 2.7-2.3-.5-.5-2.3z"/>',
    'fasteners' => '<path d="M9 3h6l-1 4h-4z"/><path d="M10 7h4v13l-2 2-2-2z"/><path d="M10 11h4M10 15h4"/>',
    'safety' => '<path d="M4 15a8 8 0 0 1 16 0"/><rect x="2.5" y="15" width="19" height="4" rx="1.5"/><path d="M12 7v3M9.5 7.5h5"/>',
    'construction' => '<rect x="4" y="3" width="16" height="18" rx="1.5"/><path d="M8.5 7h2M13.5 7h2M8.5 11h2M13.5 11h2M8.5 15h2M13.5 15h2"/>',
    'adhesives' => '<path d="M9 3h6v3l1.5 12a2 2 0 0 1-2 2h-5a2 2 0 0 1-2-2L9 6z"/><path d="M9 6h6"/>',
    'sanitary' => '<path d="M3 8h7v4H3z"/><path d="M10 9h5a3 3 0 0 1 3 3v1"/><path d="M18 14v4"/>',
    'agriculture' => '<path d="M12 21v-7"/><path d="M12 14c0-4 3-7 7-7 0 4-3 7-7 7z"/><path d="M12 16c0-3-2.5-5-6-5 0 3 2.5 5 6 5z"/>',
    'industrial' => '<circle cx="12" cy="12" r="3.2"/><path d="M12 3v2.5M12 18.5V21M21 12h-2.5M5.5 12H3M18 6l-1.8 1.8M7.8 16.2 6 18M18 18l-1.8-1.8M7.8 7.8 6 6"/>',
];
$fill = $icon === 'more' ? '#1F2937' : 'none';
$stroke = $icon === 'more' ? 'none' : '#1F2937';
$body = $icon === 'more'
    ? '<circle cx="6" cy="12" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="18" cy="12" r="2"/>'
    : ($paths[$icon] ?? '<circle cx="12" cy="12" r="9"/>');
@endphp
<svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="{{ $fill }}" stroke="{{ $stroke }}" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">{!! $body !!}</svg>
