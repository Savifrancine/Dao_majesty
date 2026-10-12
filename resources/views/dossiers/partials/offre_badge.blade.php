@php
    $typeOffreLabel = trim($dossier->type_offre ?? '');
    $typeOffreLower = mb_strtolower($typeOffreLabel);
    if ($typeOffreLabel === '') {
        $offreIcon = null;
        $offreColor = '#64748b';
        $offreBg = '#f1f5f9';
    } elseif (str_contains($typeOffreLower, 'financ')) {
        $offreIcon = '💰';
        $offreColor = '#166534';
        $offreBg = '#dcfce7';
    } elseif (str_contains($typeOffreLower, 'techniq')) {
        $offreIcon = '🔧';
        $offreColor = '#9a3412';
        $offreBg = '#ffedd5';
    } elseif (str_contains($typeOffreLower, 'administrat')) {
        $offreIcon = '📋';
        $offreColor = '#5b21b6';
        $offreBg = '#ede9fe';
    } else {
        $offreIcon = null;
        $offreColor = '#1e40af';
        $offreBg = '#dbeafe';
    }
@endphp
@if($typeOffreLabel !== '')
    <span style="display:inline-block; padding:3px 10px; border-radius:999px; font-size:0.78rem; font-weight:600; color:{{ $offreColor }}; background:{{ $offreBg }};">
        @if($offreIcon){{ $offreIcon }} @endif{{ $typeOffreLabel }}
    </span>
@else
    <span style="color:#94a3b8;">—</span>
@endif
