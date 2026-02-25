<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function showDeclarationForm()
    {
        return view('documents.declaration_form');
    }

    /**
     * Retourne le fragment de formulaire (partial) pour inclusion dans un modal.
     */
    public function showDeclarationPartial(Request $request)
    {
        $dossier = null;
        if ($request->has('dossier_id')) {
            $dossier = \App\Models\Dossier::find($request->input('dossier_id'));
        }

        $docId = $request->input('doc_id');

        return view('documents.partials.declaration_form_partial', compact('dossier', 'docId'));
    }

    public function generateDeclarationPDF(Request $request)
    {
        $data = $request->validate([
            'societe' => ['required','string','max:255'],
            'date' => ['required','date'],
            'declarant' => ['required','string','max:255'],
            'fonction' => ['nullable','string','max:255'],
            'reference' => ['nullable','string','max:255'],
            'signature' => ['nullable','file','mimes:png,jpg,jpeg', 'max:5120'],
        ]);

        $signatureDataUri = null;
        if ($request->hasFile('signature')) {
            $path = $request->file('signature')->store('tmp/signatures', 'public');
            $full = storage_path('app/public/' . $path);
            if (file_exists($full)) {
                $mime = mime_content_type($full) ?: 'image/png';
                $content = file_get_contents($full);
                $signatureDataUri = 'data:' . $mime . ';base64,' . base64_encode($content);
            }
        }

        $viewData = array_merge($data, ['signatureDataUri' => $signatureDataUri]);

        $html = view('documents.declaration_pdf', $viewData)->render();

        try {
            $dompdf = new Dompdf(['isRemoteEnabled' => true]);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            return response($dompdf->stream('declaration_garantie.pdf', ['Attachment' => false]), 200)
                ->header('Content-Type', 'application/pdf');
        } catch (\Throwable $e) {
            // Fallback: return HTML view
            return view('documents.declaration_pdf', $viewData);
        }
    }
}
