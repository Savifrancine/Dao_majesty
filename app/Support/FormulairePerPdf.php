<?php

namespace App\Support;

use App\Models\FormulairePER;
use Dompdf\Dompdf;

class FormulairePerPdf
{
    public static function render(FormulairePER $formulaire_per): string
    {
        $html = view('formulaire_per.pdf', compact('formulaire_per'))->render();

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();

        return $dompdf->output();
    }
}
