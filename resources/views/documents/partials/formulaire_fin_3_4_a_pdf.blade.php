<div style="font-family:Arial, sans-serif; font-size:11px; color:#000; line-height:1.4; text-align:center;">
    <h2 style="text-align:center; margin-bottom:4px; font-size:13px; font-weight:700;">Formulaire FIN 3.4 (a)</h2>
    <h3 style="text-align:center; margin-top:0; margin-bottom:14px; font-size:11px;">Modèle d'attestation de capacité financière</h3>

    {{-- Descriptive text removed as requested --}}

    @php
        $attachments = $attachments ?? collect();
        $imageAttachments = $attachments->filter(function ($f) {
            $ext = strtolower(pathinfo($f->chemin_fichier, PATHINFO_EXTENSION));
            return in_array($ext, ['png', 'jpg', 'jpeg', 'gif'], true);
        });
    @endphp

    @if($imageAttachments->isNotEmpty())
        <div style="margin-top:10px;">
            @foreach($imageAttachments as $f)
                @php
                    $full = storage_path('app/public/' . ltrim($f->chemin_fichier, '/'));
                    $mime = file_exists($full) ? mime_content_type($full) : null;
                    $data = file_exists($full) ? base64_encode(file_get_contents($full)) : null;
                @endphp
                @if($data && $mime && str_starts_with($mime, 'image/'))
                    <div style="margin-bottom:14px; page-break-inside:avoid;">
                        <img src="data:{{ $mime }};base64,{{ $data }}" alt="{{ basename($f->chemin_fichier) }}" style="max-width:100%; height:auto; border:1px solid #ccc; padding:4px;" />
                    </div>
                @endif
            @endforeach
        </div>
    @else
        <div style="margin-top:10px; font-style:italic; color:#666; padding:12px; background:#f5f5f5; border:1px solid #ddd; border-radius:4px;">
            Aucun document d'attestation de capacité financière fourni.
        </div>
    @endif
</div>
