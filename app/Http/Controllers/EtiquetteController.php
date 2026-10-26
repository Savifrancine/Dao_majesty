<?php

namespace App\Http\Controllers;

use App\Models\Dossier;
use App\Support\OfferCategory;
use Dompdf\Dompdf;
use Illuminate\Http\Request;

class EtiquetteController extends Controller
{
    public function interneSelect()
    {
        return $this->selectView('interne', 'Enveloppe intérieure');
    }

    public function externeSelect()
    {
        return $this->selectView('externe', 'Enveloppe extérieure');
    }

    private function selectView(string $type, string $titre)
    {
        $dossiers = Dossier::orderByDesc('id')->get();

        return view('etiquettes.select_dossier', compact('dossiers', 'type', 'titre'));
    }

    public function interneGenerate(Dossier $dossier, string $variante)
    {
        abort_unless(in_array($variante, ['original', 'copie'], true), 404);

        $html = view('etiquettes.pdf', [
            'dossier' => $dossier,
            'variante' => $variante,
        ])->render();

        return $this->streamPdf($html, 'etiquette_interne_' . $variante . '.pdf');
    }

    public function externeGenerate(Dossier $dossier)
    {
        $html = view('etiquettes.pdf_externe', [
            'dossier' => $dossier,
        ])->render();

        return $this->streamPdf($html, 'etiquette_externe.pdf');
    }

    private function streamPdf(string $html, string $filename)
    {
        $dompdf = new Dompdf(['isRemoteEnabled' => true]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return response($dompdf->stream($filename, ['Attachment' => true]), 200)
            ->header('Content-Type', 'application/pdf');
    }

    public function interneForm(Dossier $dossier)
    {
        return view('etiquettes.form', [
            'dossier' => $dossier,
            'type' => 'interne',
            'titre' => 'Enveloppe intérieure',
        ]);
    }

    public function externeForm(Dossier $dossier)
    {
        return view('etiquettes.form_externe', [
            'dossier' => $dossier,
            'type' => 'externe',
            'titre' => 'Enveloppe extérieure',
        ]);
    }

    public function interneFormUpdate(Request $request, Dossier $dossier)
    {
        $data = $request->validate([
            'etiquette_a' => ['nullable', 'string', 'max:100'],
            'destinataires' => ['nullable', 'string', 'max:255'],
            'destinataire_adresse' => ['nullable', 'string', 'max:500'],
            'titre_dossier' => ['nullable', 'string', 'max:255'],
            'reference_dossier' => ['nullable', 'string', 'max:255'],
            'ref' => ['nullable', 'string', 'max:255'],
            'date_lancement' => ['nullable', 'date'],
            'nom_dossier' => ['nullable', 'string', 'max:500'],
        ]);

        $this->reattachOfferCategorySuffix($data, $dossier);
        $dossier->update($data);

        return redirect()->route('etiquettes.interne.form', $dossier)->with('success', 'Informations enregistrées. Vous pouvez générer l\'étiquette ci-dessous.');
    }

    public function externeFormUpdate(Request $request, Dossier $dossier)
    {
        $data = $request->validate([
            'prmp_titre' => ['nullable', 'string', 'max:255'],
            'prmp_nom' => ['nullable', 'string', 'max:255'],
            'prmp_telephone' => ['nullable', 'string', 'max:100'],
            'prmp_email' => ['nullable', 'string', 'max:255'],
            'institution_nom' => ['nullable', 'string', 'max:255'],
            'secretariat_adresse' => ['nullable', 'string', 'max:2000'],
            'reference_dossier' => ['nullable', 'string', 'max:255'],
            'date_lancement' => ['nullable', 'date'],
            'ref' => ['nullable', 'string', 'max:255'],
            'nom_dossier' => ['nullable', 'string', 'max:500'],
            'lots' => ['nullable', 'string', 'max:255'],
            'titre_lot' => ['nullable', 'string', 'max:500'],
        ]);

        $this->reattachOfferCategorySuffix($data, $dossier);
        $dossier->update($data);

        return redirect()->route('etiquettes.externe.form', $dossier)->with('success', 'Informations enregistrées. Vous pouvez générer l\'étiquette ci-dessous.');
    }

    /**
     * Les formulaires d'etiquette (interne/externe) n'affichent et ne
     * modifient jamais le suffixe " — Offre technique/financiere/
     * administrative" ajoute par ailleurs au nom du dossier (voir
     * OfferCategory) : il sert a distinguer les dossiers dupliques par
     * categorie d'offre pour un meme marche et ne doit jamais etre perdu en
     * enregistrant ce formulaire. On le reapplique ici a partir du nom
     * encore present en base avant mise a jour.
     */
    private function reattachOfferCategorySuffix(array &$data, Dossier $dossier): void
    {
        if (!array_key_exists('nom_dossier', $data) || $data['nom_dossier'] === null) {
            return;
        }

        $suffix = OfferCategory::extractSuffix($dossier->nom_dossier);
        if ($suffix === null) {
            return;
        }

        $data['nom_dossier'] = rtrim($data['nom_dossier']) . $suffix;
    }
}
