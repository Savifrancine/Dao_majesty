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

    /**
     * Aperçu HTML du formulaire de renseignements sur le candidat.
     * Permet de prévisualiser le template sans générer de PDF.
     */
    public function previewFormulaire($dossierId = null)
    {
        $dossier = null;
        if ($dossierId) {
            $dossier = \App\Models\Dossier::find($dossierId);
        }

        $entreprise = $dossier?->entreprise ?? null;

        $pdfData = [
            'nom_candidat' => $entreprise?->nom ?? 'MAJESTY SERVICES ET EQUIPEMENTS SARL',
            'groupement_membres' => '',
            'pays_candidat' => $entreprise?->pays ?? 'Benin',
            'identification_nationale' => $entreprise?->rccm ?? '22339447666',
            'annee_enregistrement' => $entreprise?->annee_enregistrement ?? '',
            'adresse_officielle' => $entreprise?->adresse_officielle ?? $entreprise?->adresse ?? 'kohebo',
            'nom_representant' => $entreprise?->responsable ?? 'Onésime Tchaby',
            'fonction_representant' => $entreprise?->fonction_responsable ?? 'PDG',
            'adresse_representant' => '',
            'telephone_representant' => $entreprise?->telephone ?? '0179779797',
            'email_representant' => $entreprise?->email ?? '-',
            'dossier' => $dossier,
            'entreprise' => $entreprise,
            'drp_number' => $dossier?->ref ?? 'S_DLCSSA_' . str_pad($dossier?->id ?? 0, 6, '0', STR_PAD_LEFT),
        ];

        return view('documents.formulaire_renseignements_candidat_pdf', $pdfData);
    }

    /**
     * Aperçu d'un document du dossier (DossierDocument).
     *
     * Les pièces "fichier téléversé" (RCCM, attestations, etc.) ouvrent
     * directement le fichier joint. Les documents générés (formulaires,
     * bordereaux, déclarations...) sont rendus avec exactement le même
     * gabarit que celui utilisé pour le PDF final du dossier, afin que
     * l'aperçu corresponde toujours au document réel.
     */
    public function previewDocument(\App\Models\DossierDocument $document)
    {
        $document->load(['typeDocument.champs', 'bordereau.lignes', 'valeurs', 'fichiers']);
        $typeDoc = $document->typeDocument;

        if (!$typeDoc) {
            return abort(404, 'Type de document non trouvé');
        }

        if (\App\Models\TypeDocument::isUploadOnlyName($typeDoc->nom, $typeDoc->type_formulaire)) {
            $file = $document->fichiers()->latest()->first();
            if ($file && !empty($file->chemin_fichier)) {
                $path = storage_path('app/public/' . $file->chemin_fichier);
                if (file_exists($path)) {
                    return response()->file($path);
                }
            }

            return view('documents.preview_generic', [
                'document' => $document,
                'typeDoc' => $typeDoc,
                'message' => 'Aucun fichier joint pour ce document.',
            ]);
        }

        $dossier = \App\Models\Dossier::with(['entreprise', 'signataires', 'typeDossier'])
            ->find($document->dossier_id);
        $pageGardeDataUri = null;

        $html = view('dossiers.pdf', compact('dossier', 'pageGardeDataUri'))->with([
            'documents' => collect([$document]),
            'renderMode' => 'doc',
        ])->render();

        $orientation = \App\Models\TypeDocument::isLandscapeTableName($typeDoc->nom) ? 'landscape' : 'portrait';

        try {
            $dompdf = new Dompdf(['isRemoteEnabled' => true]);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', $orientation);
            $dompdf->render();

            return response($dompdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="apercu.pdf"');
        } catch (\Throwable $e) {
            return response($html)->header('Content-Type', 'text/html');
        }
    }
}
