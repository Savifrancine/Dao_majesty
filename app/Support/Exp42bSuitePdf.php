<?php

namespace App\Support;

use App\Models\Dossier;
use App\Models\FormulaireExp42BSuite;
use Dompdf\Dompdf;

class Exp42bSuitePdf
{
    public static function render(FormulaireExp42BSuite $formulaire): string
    {
        $formulaire->loadMissing('entreprise', 'signataire', 'dossier.entreprise', 'dossier.signataires');
        $dossier = $formulaire->dossier;

        $html = view('formulaire_exp_4_2_b_suite.pdf', [
            'formulaireExp42BSuite' => $formulaire,
            'dossier' => $dossier,
            'logoDataUri' => self::logoDataUri($dossier, $formulaire),
        ])->render();

        $dompdf = new Dompdf(['isRemoteEnabled' => true]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    private static function logoDataUri(?Dossier $dossier, FormulaireExp42BSuite $formulaire): string
    {
        $logo = optional($dossier?->entreprise)->logo ?? optional($formulaire->entreprise)->logo ?? null;
        if ($logo) {
            if (str_starts_with($logo, 'data:') || str_starts_with($logo, 'http')) {
                return $logo;
            }
            $uri = self::fileDataUri(storage_path('app/public/' . ltrim($logo, '/')));
            if ($uri) {
                return $uri;
            }
        }

        $fallbacks = [
            storage_path('app/public/entreprises/logos/logo.jpeg'),
            storage_path('app/public/entreprises/logo.jpeg'),
            public_path('logo.jpeg'),
        ];
        foreach ($fallbacks as $path) {
            $uri = self::fileDataUri($path);
            if ($uri) {
                return $uri;
            }
        }

        return '';
    }

    private static function fileDataUri(string $path): ?string
    {
        if (!file_exists($path)) {
            return null;
        }

        return 'data:image/' . pathinfo($path, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($path));
    }
}
