<?php

namespace App\Support;

use App\Models\FormulaireMat;
use Dompdf\Dompdf;

class FormulaireMatPdf
{
    public static function render(FormulaireMat $formulaireMat): string
    {
        $formulaireMat->loadMissing('signataire');

        $signataireDataUri = null;
        if (!empty($formulaireMat->signataire?->signature_path)) {
            $signaturePath = storage_path('app/public/' . ltrim($formulaireMat->signataire->signature_path, '/'));
            if (file_exists($signaturePath)) {
                $mime = mime_content_type($signaturePath) ?: 'image/png';
                $signataireDataUri = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($signaturePath));
            }
        }

        $html = view('formulaire_mat.pdf', compact('formulaireMat', 'signataireDataUri'))->render();

        $dompdf = new Dompdf(['isRemoteEnabled' => true]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}
