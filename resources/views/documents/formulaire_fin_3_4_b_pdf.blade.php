<div style="font-family:Arial, sans-serif; font-size:11px; color:#000; line-height:1.4;">
    <h2 style="text-align:center; margin-bottom:4px;">Formulaire FIN 3.4 (b)</h2>
    <h3 style="text-align:center; margin-top:0; margin-bottom:14px;">Modèle de lettre de confirmation de la capacité financière</h3>

    <div style="font-size:11px; margin-bottom:14px;">
        Lettre de confirmation de la capacité financière émise par un établissement de crédit ou un organisme financier reconnu.
    </div>

    @php
        $attachments = $content['files'] ?? [];
        $imageAttachments = [];
        if ($attachments && is_array($attachments)) {
            foreach ($attachments as $f) {
                $ext = strtolower(pathinfo($f['chemin_fichier'] ?? '', PATHINFO_EXTENSION));
                if (in_array($ext, ['png', 'jpg', 'jpeg', 'gif'], true)) {
                    $imageAttachments[] = $f;
                }
            }
        }
    @endphp

    @if(count($imageAttachments) > 0)
        <div style="margin-top:10px;">
            @foreach($imageAttachments as $f)
                @php
                    $full = storage_path('app/public/' . ltrim($f['chemin_fichier'] ?? '', '/'));
                    $mime = file_exists($full) ? mime_content_type($full) : null;
                    $data = file_exists($full) ? base64_encode(file_get_contents($full)) : null;
                @endphp
                @if($data && str_starts_with($mime, 'image/'))
                    <div style="margin-bottom:14px; page-break-inside:avoid;">
                        <img src="data:{{ $mime }};base64,{{ $data }}" alt="{{ basename($f['chemin_fichier'] ?? '') }}" style="max-width:100%; height:auto; border:1px solid #ccc;" />
                    </div>
                @endif
            @endforeach
        </div>
    @else
        <div style="margin-top:10px; font-style:italic; color:#666; padding:12px; background:#f5f5f5; border:1px solid #ddd;">
            Aucune lettre de confirmation de capacité financière fournie.
        </div>
    @endif
</div>
