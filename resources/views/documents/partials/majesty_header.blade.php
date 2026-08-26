@php
    $logoDataUri = '';
    $logo = optional($entreprise ?? null)->logo ?? null;

    // Priorité : logo de l'entreprise (chemin stocké en base de données)
    if ($logo) {
        if (str_starts_with($logo, 'data:') || str_starts_with($logo, 'http')) {
            $logoDataUri = $logo;
        } else {
            $candidate = storage_path('app/public/' . ltrim($logo, '/'));
            if (file_exists($candidate)) {
                $ext = pathinfo($candidate, PATHINFO_EXTENSION);
                $data = base64_encode(file_get_contents($candidate));
                $logoDataUri = 'data:image/' . $ext . ';base64,' . $data;
            }
        }
    }

    // fallback : storage/app/public/entreprises/logos/logo.jpeg (emplacement existant)
    if (!$logoDataUri) {
        $fallback = storage_path('app/public/entreprises/logos/logo.jpeg');
        if (file_exists($fallback)) {
            $ext = pathinfo($fallback, PATHINFO_EXTENSION);
            $data = base64_encode(file_get_contents($fallback));
            $logoDataUri = 'data:image/' . $ext . ';base64,' . $data;
        }
    }

    // fallback secondaire : storage/app/public/entreprises/logo.jpeg
    if (!$logoDataUri) {
        $fallback = storage_path('app/public/entreprises/logo.jpeg');
        if (file_exists($fallback)) {
            $ext = pathinfo($fallback, PATHINFO_EXTENSION);
            $data = base64_encode(file_get_contents($fallback));
            $logoDataUri = 'data:image/' . $ext . ';base64,' . $data;
        }
    }

    // fallback alternatif (public/logo.jpeg)
    if (!$logoDataUri) {
        $fallback2 = public_path('logo.jpeg');
        if (file_exists($fallback2)) {
            $ext = pathinfo($fallback2, PATHINFO_EXTENSION);
            $data = base64_encode(file_get_contents($fallback2));
            $logoDataUri = 'data:image/' . $ext . ';base64,' . $data;
        }
    }

    // construction du tag HTML de logo
    if ($logoDataUri) {
        $majestyImgTag = '<img src="' . $logoDataUri . '" style="height:100px; width:auto; display:block;" alt="Logo">';
    } else {
        $majestyImgTag = '<div style="color:#888; font-size:10px; text-align:center; width:130px; height:100px; border:1px solid #ccc; display:flex; align-items:center; justify-content:center;">LOGO MANQUANT</div>';
    }
@endphp
<div style="font-family: Calibri, Segoe UI, Arial, sans-serif; color:#1f78d1; width:100%; margin-bottom:16px;">
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            <td style="width:115px; vertical-align:top; padding-right:14px;">{!! $majestyImgTag !!}</td>
            <td style="vertical-align:top; text-align:center;">
                <div style="font-size:19px; font-weight:700; line-height:1.15; margin-bottom:2px;">MAJESTY SERVICES ET EQUIPEMENTS</div>
                <div style="font-size:11px; line-height:1; margin-bottom:2px;">Médical et Laboratoires – Equipements Pétroliers – Environnement - Eaux - Agroalimentaire</div>
                <div style="font-size:11px; line-height:1; margin-bottom:2px;">Maintenance – Service Apres Vente</div>
                <div style="width:85%; margin:2px auto 4px auto; border-top:1px solid #1f78d1;"></div>
                <div style="font-size:10.5px; line-height:1; margin-bottom:2px;">06 BP 358 – Tel : +229 01 97 77 25 04 - C/763 Kowegbo, Cotonou – Republique du Benin</div>
                <div style="font-size:10.5px; line-height:1;">IFU : 3 2013 0035 5315 – RCCM : 13 B 9860 - email : contact@majestyse.com / majestyse@gmail.com</div>
            </td>
        </tr>
    </table>
</div>
