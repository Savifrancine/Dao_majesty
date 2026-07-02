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
            'identification_nationale' => $entreprise?->ifu ?? '22339447666',
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
     * Aperçu HTML d'un document du dossier (DossierDocument).
     * Récupère les données du document et affiche la preview selon son type.
     */
    public function previewDocument(\App\Models\DossierDocument $document)
    {
        $dossier = $document->dossier;
        $typeDoc = $document->typeDocument;
        $entreprise = $dossier?->entreprise ?? null;

        if (!$typeDoc) {
            return abort(404, 'Type de document non trouvé');
        }

        // Selon le type, charger les données appropriées
        if ($typeDoc->nom === 'Formulaire de renseignements sur le candidat') {
            $pdfData = [
                'nom_candidat' => $entreprise?->nom ?? 'MAJESTY SERVICES ET EQUIPEMENTS SARL',
                'groupement_membres' => '',
                'pays_candidat' => $entreprise?->pays ?? 'Benin',
                'identification_nationale' => $entreprise?->ifu ?? '22339447666',
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
        
        if ($typeDoc->nom === 'Déclaration de garantie') {
            $pdfData = [
                'societe' => $entreprise?->nom ?? 'Société',
                'date' => now()->format('Y-m-d'),
                'declarant' => $entreprise?->responsable ?? 'Déclarant',
                'fonction' => $entreprise?->fonction_responsable ?? 'Fonction',
                'reference' => $dossier?->ref ?? '',
                'signatureDataUri' => null,
            ];
            return view('documents.declaration_pdf', $pdfData);
        }

        // Pour les autres types, afficher un message
        return view('documents.preview_generic', [
            'document' => $document,
            'typeDoc' => $typeDoc,
            'message' => 'Aperçu du document : ' . $typeDoc->nom
        ]);
    }
}
