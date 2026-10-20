<?php

namespace App\Http\Controllers;

use App\Models\Dossier;
use App\Models\TypeMarche;
use App\Models\Signataire;
use App\Models\Entreprise;
use App\Models\EntrepriseDocument;
use App\Models\EntrepriseDocumentFichier;
use App\Models\EntreprisePersonnel;
use App\Models\TypeDossier;
use App\Models\TypeDocument;
use App\Models\DossierDocument;
use App\Models\DossierChangeLog;
use App\Models\DossierVersionFinale;
use App\Models\DossierPresence;
use App\Models\ChampDocument;
use App\Models\ValeurDocument;
use App\Models\DocumentFichier;
use App\Models\Bordereau;
use App\Models\BordereauLigne;
use App\Models\ChiffreAffaire;
use App\Models\Utilisateur;
use App\Models\FormulaireExp42A;
use App\Models\FormulaireExp42B;
use App\Models\FormulaireExp42ASuite;
use App\Models\FormulaireExp42BSuite;
use App\Models\FormulairePER;
use App\Models\FormulairePerFichier;
use App\Models\FormulaireMat;
use App\Models\FicheTechniqueFichier;
use App\Models\CatalogueArticle;
use App\Support\Exp42aPdf;
use App\Support\Exp42bPdf;
use App\Support\Exp42aSuitePdf;
use App\Support\Exp42bSuitePdf;
use App\Support\FormulairePerPdf;
use App\Support\FormulaireMatPdf;
use App\Support\MarcheIdentification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class DossierController extends Controller
{
    private const STANDARD_SOMMAIRE = [
        'Formulaire ELI – 1.1 : Formulaire de renseignements sur le candidat',
        'Déclaration de garantie d\'offre',
        'Attestation d\'identification de statut',
        'Formulaire de qualification',
        'Formulaire EXP – 4.1 : Expérience générale de fournitures/services',
        'Formulaire EXP – 4.2 a) Expérience spécifique de fournitures/services',
        'Formulaire EXP – 4.2 a) (suite) Expérience spécifique de fournitures/services dans les activités principales (suite)',
        'Formulaire EXP – 4.2 b) Expérience spécifique de fournitures',
        'Formulaire EXP – 4.2 b) (suite) Expérience spécifique de fournitures/services dans les activités principales (suite)',
        'Formulaire ANT-2 : Formulaire renseignant sur les antécédents de marchés non exécutés, de litiges en instance et d\'antécédents de litiges',
        'Engagement du soumissionnaire à respecter le code d\'éthique et de déontologie',
        'Listes des Fournitures et Calendrier de livraison',
        'Description technique des fournitures/services',
        'Liste de Matériel',
        'Fiche technique de chaque article, délivrée par le fabricant',
        'Attestation de bonne fin',
        'Bon de commande et contrats',
        'Copie de l\'arrêté du Ministre de la Santé portant autorisation d\'importation, de détention et de vente des équipements médicaux',
        'RCCM',
        'Copie legalisee de l\'Identifiant Fiscal Unique (IFU)',
        'Attestation de non-faillite datant de moins de trois (03) mois',
        'Attestation d\'imposition ou de situation fiscale en cours de validite',
        'Attestation de regularite a la CNSS',
        'Attestation de non-exclusion de la commande publique',
        'Relevé d\'identité bancaire (RIB)',
        'Formulaire de divulgation des bénéficiaires effectifs',
        'Pièce d\'identité du premier responsable',
        'Déclaration de l\'autorité contractante',
        'Plan de charge',
        'Formulaire MTC/FIN – 3.5 : Marchés de fournitures/services en cours',
    ];

    /**
     * Ordre du sommaire pour une offre financière (distinct de STANDARD_SOMMAIRE,
     * utilisé pour l'offre technique) : la personne complète ensuite si besoin,
     * les pièces non listées ici restent à la fin dans leur ordre actuel.
     */
    private const STANDARD_SOMMAIRE_FINANCIERE = [
        'Listes des Fournitures et Calendrier de livraison',
        'Formulaire MTC/FIN – 3.5 : Marchés de fournitures/services en cours',
        'Lettre de soumission',
        'Bordereau prix unitaire',
        'Bordereau des prix pour les fournitures à importer',
        'Chiffre d\'affaires annuel moyen des activités de services',
        'Formulaire FIN – 3.1 Situation financière',
        'Formulaire FIN 3.3',
        'Formulaire FIN 3.4 (a) Modèle d\'attestation de capacité financière',
        'Etats financiers certifiés',
        'Cadres de sous détails des prix unitaire',
    ];

    private const EXP41_NOM = 'Formulaire EXP – 4.1 : Expérience générale de fournitures/services';
    private const EXP42A_NOM = 'Formulaire EXP – 4.2 a) Expérience spécifique de fournitures/services';
    private const EXP42B_NOM = 'Formulaire EXP – 4.2 b)  Expérience spécifique de fournitures';
    private const EXP42A_SUITE_NOM = 'Formulaire EXP – 4.2 a) (suite) Expérience spécifique de fournitures/services dans les activités principales (suite)';
    private const EXP42B_SUITE_NOM = 'Formulaire EXP – 4.2 b) (suite) Expérience spécifique de fournitures/services dans les activités principales (suite)';
    private const FORMULAIRE_PER_NOM = 'Formulaire PER';
    private const LISTE_PERSONNEL_NOM = 'Liste du personnel affecté à l\'exécution du marché';
    private const FORMULAIRE_MAT_NOM = 'Formulaire MAT';
    private const LISTE_MATERIEL_NOM = 'Liste de Matériel';
    private const FICHE_TECHNIQUE_ARTICLES_NOM = 'Fiche technique de chaque article, délivrée par le fabricant';

    /**
     * Les 3 categories d'offre reconnues pour un meme marche (detectees dans
     * le champ libre "type_offre" via un mot-cle, comme deja fait par
     * isOffreFinanciere() / offre_badge.blade.php) : sert a proposer, depuis
     * un dossier deja rempli, de creer directement les offres manquantes du
     * meme marche (ex: l'offre technique est faite, on propose de creer la
     * financiere et l'administrative).
     */
    private const OFFER_CATEGORIES = [
        'financiere' => ['keyword' => 'financ', 'label' => 'Offre financière'],
        'technique' => ['keyword' => 'techniq', 'label' => 'Offre technique'],
        'administrative' => ['keyword' => 'administrat', 'label' => 'Offre administrative'],
    ];

    /**
     * Afficher le dashboard home avec les dossiers
     */
    public function dashboardHome()
    {
        $dossiers = Dossier::with(['typeDossier', 'entreprise', 'documents.typeDocument', 'documents.fichiers'])->get();

        // Dossiers avec au moins une pièce manquante et/ou une échéance de
        // soumission proche (7 jours) ou dépassée, pour ne rien oublier
        // avant la date limite. Triés par échéance la plus proche d'abord ;
        // les dossiers sans date_soumission sont mis à la fin.
        $incompleteDossiers = $dossiers
            ->filter(function (Dossier $d) {
                if ($d->statut === 'genere') {
                    return false;
                }
                if ($d->hasIncompleteDocuments()) {
                    return true;
                }
                // diffInDays(..., false) est signe : positif si la date est dans
                // le futur, negatif si deja passee (Carbon 3 n'est plus absolu
                // par defaut) — <= 7 couvre donc a la fois "en retard" et
                // "echeance proche".
                return $d->date_soumission && now()->diffInDays($d->date_soumission, false) <= 7;
            })
            ->sortBy(fn (Dossier $d) => $d->date_soumission?->timestamp ?? PHP_INT_MAX)
            ->values();

        // Documents administratifs des entreprises qui expirent (Attestation de
        // non-exclusion : 1 an, Attestation de categorisation : 3 ans) : pour
        // chaque entreprise et chaque type, seul le renouvellement le plus
        // recent compte (les plus anciens restent en historique sans alerter).
        $expiringEntrepriseDocuments = EntrepriseDocument::whereIn('type', ['non_exclusion', 'categorisation', 'regularite_fiscale', 'cnss', 'non_faillite'])
            ->whereNotNull('date_delivrance')
            ->with('entreprise')
            ->get()
            ->groupBy(fn (EntrepriseDocument $d) => $d->entreprise_id . '-' . $d->type)
            ->map(fn ($group) => $group->sortByDesc('date_delivrance')->first())
            ->filter(fn (EntrepriseDocument $d) => $d->estExpire() || $d->expireBientot())
            ->sortBy(fn (EntrepriseDocument $d) => $d->date_expiration?->timestamp ?? PHP_INT_MAX)
            ->values();

        return view('home', compact('dossiers', 'incompleteDossiers', 'expiringEntrepriseDocuments'));
    }

    /**
     * Retourne la liste canonique des pièces de dossier supportées.
     */
    private function getDocumentPieceNames(): array
    {
        $fixedNames = [
            "Déclaration de garantie d'offre",
            "Formulaire de qualification",
            "Lettre de soumission",
            "RCCM",
            "Relevé d'identité bancaire (RIB)",
            "Formulaire de divulgation des bénéficiaires effectifs",
            "Pièce d'identité du premier responsable",
            "Déclaration de l'autorité contractante",
            "Fiche technique de chaque article, délivrée par le fabricant",
            "Copie de l'arrêté du Ministre de la Santé portant autorisation d'importation, de détention et de vente des équipements médicaux",
            "Attestation d'identification de statut",
            "Attestation de visite de site",
            "Attestation de bonne fin",
            "Bon de commande et contrats",
            "Preuves de propriété des matériels adéquats nécessaire à la bonne exécution du marché",
            "Attestation / Preuve de vente des équipements ou des pièces de recharge",
            "Attestation / Certificat de formation ou de qualifications en maintenance(sur au moins une équipements)",
            "Etats financiers certifiés",
            "Attestation de capacité financière",
            "Copie legalisee de l'Identifiant Fiscal Unique (IFU)",
            "Attestation de non-faillite datant de moins de trois (03) mois",
            "Attestation d'imposition ou de situation fiscale en cours de validite",
            "Attestation de regularite a la CNSS",
            "Formulaire ELI – 1.1 : Formulaire de renseignements sur le candidat",
            "Formulaire FIN – 3.1 Situation financière",
            "Formulaire FIN 3.3",
            "Formulaire FIN 3.4 (a) Modèle d'attestation de capacité financière",
            "Formulaire FIN 3.4 (b) Modèle de lettre de confirmation de la capacité financière",
            "Formulaire MTC/FIN – 3.5 : Marchés de fournitures/services en cours",
            "Formulaire EXP – 4.1 : Expérience générale de fournitures/services",
            "Formulaire EXP – 4.2 a) Expérience spécifique de fournitures/services",
            "Formulaire EXP – 4.2 a) (suite) Expérience spécifique de fournitures/services dans les activités principales (suite)",
            "Formulaire EXP – 4.2 b)  Expérience spécifique de fournitures",
            "Formulaire EXP – 4.2 b) (suite) Expérience spécifique de fournitures/services dans les activités principales (suite)",
            "Formulaire ANT-2 : Formulaire renseignant sur les antécédents de marchés non exécutés, de litiges en instance et d'antécédents de litiges",
            "Formulaire MAT",
            "Liste de Matériel",
            "Formulaire PER",
            "Liste du personnel affecté à l'exécution du marché",
            "Chiffre d'affaires annuel moyen des activités de services",
            "Attestation de non-exclusion de la commande publique",
            "Attestation de catégorisation",
            "Engagement du soumissionnaire à respecter le code d'éthique et de déontologie",
            "Attestation de non-condamnation pour fraude, corruption ou fausse declaration",
            "Attestation de nationalite ou document de constitution legale de l'entreprise",
            "Statuts de la societe et PV de nomination du gerant",
            "Copie du quitus fiscal",
            "Attestation de situation reguliere vis-a-vis des organismes de credit",
            "Bordereau prix unitaire",
            "Bordereau des prix pour les fournitures à importer",
            "Bordereau des prix des fournitures, déjà importées",
            "Bordereau des prix pour les fournitures fabriquées au Bénin",
            "Tableau de résumé des bordereaux de prix",
            "Bordereau des prix et calendrier d'exécution des services connexes",
            "Listes des services connexes et calendrier de réalisation",
            "Listes des Fournitures et Calendrier de livraison",
            "Cadres de sous détails des prix unitaire",
            "Programme d'activités",
            "Plan de charge",
            "Méthodes d'exécution",
            "Calendrier d'exécution",
            "Description technique des fournitures/services",
        ];

        $customNames = TypeDocument::whereIn('type_formulaire', ['libre', 'piece_jointe'])->pluck('nom')->all();

        return array_values(array_unique(array_merge($fixedNames, $customNames)));
    }

    /**
     * Documents dont le tableau du PDF comporte plus de 3 colonnes : ils doivent
     * être générés en orientation paysage pour rester lisibles.
     */
    private function getLandscapeTableDocNames(): array
    {
        return TypeDocument::landscapeTableNames();
    }

    /**
     * Nettoie les champs numériques des lignes de bordereau avant validation :
     * les utilisateurs tapent souvent les prix avec des espaces (y compris
     * insécables) ou des virgules comme séparateur de milliers/décimales
     * (ex. "17 280", "1.000.000,50"), ce que la règle "numeric" rejette telle quelle.
     */
    private function normalizeBordereauNumbers(array $sections): array
    {
        $numericKeys = ['prix_unitaire', 'quantite', 'cout_benin', 'nombre_propose'];

        foreach ($sections as &$section) {
            if (!isset($section['lignes']) || !is_array($section['lignes'])) {
                continue;
            }
            foreach ($section['lignes'] as &$ligne) {
                if (!is_array($ligne)) {
                    continue;
                }
                foreach ($numericKeys as $key) {
                    if (!isset($ligne[$key]) || !is_string($ligne[$key]) || trim($ligne[$key]) === '') {
                        continue;
                    }
                    $ligne[$key] = $this->normalizeNumericString($ligne[$key]);
                }
            }
            unset($ligne);
        }
        unset($section);

        return $sections;
    }

    private function normalizeNumericString(string $value): string
    {
        $clean = trim($value);

        // Convertit les chiffres unicode "pleine largeur" (ex: saisie via un clavier
        // asiatique) en chiffres ASCII normaux avant tout le reste.
        $clean = strtr($clean, [
            '０' => '0', '１' => '1', '２' => '2', '３' => '3', '４' => '4',
            '５' => '5', '６' => '6', '７' => '7', '８' => '8', '９' => '9',
        ]);

        // Ne garde que les chiffres, la virgule, le point et le signe moins : retire
        // les espaces (normaux ou insécables), lettres, symboles monétaires, etc.
        $clean = preg_replace('/[^0-9,\.\-]/u', '', $clean) ?? '';

        if ($clean === '') {
            return $clean;
        }

        $hasComma = str_contains($clean, ',');
        $hasDot = str_contains($clean, '.');

        if ($hasComma && $hasDot) {
            // Le séparateur le plus à droite est la décimale, l'autre les milliers
            if (strrpos($clean, ',') > strrpos($clean, '.')) {
                $clean = str_replace('.', '', $clean);
                $clean = str_replace(',', '.', $clean);
            } else {
                $clean = str_replace(',', '', $clean);
            }
        } elseif ($hasComma) {
            $clean = str_replace(',', '.', $clean);
        }

        return $clean;
    }

    /**
     * Reprend automatiquement le registre de commerce téléversé lors de la création
     * de l'entreprise comme pièce jointe pour le document RCCM du dossier, tant
     * qu'aucun fichier n'a encore été attaché (upload manuel ou suppression par l'utilisateur).
     */
    private function attachEntrepriseRegistreIfMissing(Dossier $dossier, DossierDocument $dossierDocument): void
    {
        $registrePath = $dossier->entreprise?->registre_path;

        if (!$registrePath || $dossierDocument->fichiers()->exists()) {
            return;
        }

        if (!Storage::disk('public')->exists($registrePath)) {
            return;
        }

        $extension = pathinfo($registrePath, PATHINFO_EXTENSION);
        $copyPath = 'dossiers/documents/dossier_' . $dossier->id . '_rccm_' . $dossierDocument->id . ($extension ? '.' . $extension : '');
        Storage::disk('public')->copy($registrePath, $copyPath);

        DocumentFichier::create([
            'dossier_document_id' => $dossierDocument->id,
            'chemin_fichier' => $copyPath,
            'utilisateur_id' => auth()->id(),
        ]);
    }

    private function resolveQualificationSignataire(Dossier $dossier): ?Signataire
    {
        return $dossier->signataires->firstWhere('pivot.role_signataire', 'gerant') ?? $dossier->signataires->first();
    }

    /**
     * Retourne [signatureDataUri, cachetDataUri] du signataire, ou [null, null]
     * si aucune image n'est disponible.
     */
    private function signataireImageDataUris(?Signataire $signataire): array
    {
        $signatureDataUri = null;
        $cachetDataUri = null;

        if ($signataire) {
            if (!empty($signataire->signature_path)) {
                $sigPath = storage_path('app/public/' . ltrim($signataire->signature_path, '/'));
                if (file_exists($sigPath)) {
                    $mime = mime_content_type($sigPath) ?: 'image/png';
                    $signatureDataUri = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($sigPath));
                }
            }
            if (!empty($signataire->cachet_path)) {
                $cachetPath = storage_path('app/public/' . ltrim($signataire->cachet_path, '/'));
                if (file_exists($cachetPath)) {
                    $mime = mime_content_type($cachetPath) ?: 'image/png';
                    $cachetDataUri = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($cachetPath));
                }
            }
        }

        return [$signatureDataUri, $cachetDataUri];
    }

    /**
     * (Re)génère le PDF individuel du Formulaire de qualification à partir de son
     * contenu courant, et l'attache au document du dossier.
     */
    private function regenerateQualificationPdf(Dossier $dossier, DossierDocument $qualificationDoc, array $vals): void
    {
        try {
            $signataire = $this->resolveQualificationSignataire($dossier);
            [$signatureDataUri, $cachetDataUri] = $this->signataireImageDataUris($signataire);

            $html = view('documents.formulaire_qualification_pdf', array_merge($vals, [
                'entreprise' => $dossier->entreprise,
                'signatureDataUri' => $signatureDataUri,
                'cachetDataUri' => $cachetDataUri,
            ]))->render();
            $dompdf = new Dompdf(['isRemoteEnabled' => true]);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $output = $dompdf->output();
            $filename = 'dossiers/documents/dossier_' . $dossier->id . '_qualification_' . time() . '.pdf';
            Storage::disk('public')->put($filename, $output);

            DocumentFichier::create([
                'dossier_document_id' => $qualificationDoc->id,
                'chemin_fichier' => $filename,
                'utilisateur_id' => auth()->id(),
            ]);

            $qualificationDoc->update(['statut' => 'complete']);
        } catch (\Throwable $e) {
            // ignore and continue with normal flow
        }
    }

    /**
     * Reporte automatiquement les marchés similaires saisis dans le Formulaire de
     * qualification vers le Formulaire EXP – 4.1 (Expérience générale), pour éviter
     * à l'utilisateur de ressaisir la même expérience deux fois :
     * - l'année du marché est reportée uniquement dans "Mois/année de départ"
     *   (une seule ligne par marché, "Mois/année final(e)" n'est pas dupliqué) ;
     * - "Identification du marché" reçoit "Marché n° {référence} - {nom}" ;
     * - le rôle du candidat n'est jamais renseigné par la qualification (ce champ
     *   n'existe pas dans ce formulaire) : il est laissé tel quel s'il a déjà été
     *   saisi manuellement dans EXP 4.1, vide sinon.
     * Les lignes ajoutées manuellement dans EXP 4.1 au-delà des marchés de
     * qualification sont conservées.
     */
    private function syncQualificationMarchesToExp41(Dossier $dossier, array $marches): void
    {
        $exp41Type = TypeDocument::where('nom', 'Formulaire EXP – 4.1 : Expérience générale de fournitures/services')->first();
        if (!$exp41Type) {
            return;
        }

        $exp41Doc = DossierDocument::where('dossier_id', $dossier->id)
            ->where('type_document_id', $exp41Type->id)
            ->first();
        if (!$exp41Doc) {
            return;
        }

        $marches = array_values(array_filter($marches, function ($m) {
            return !empty(trim($m['annee'] ?? '')) || !empty(trim($m['nom'] ?? '')) || !empty(trim($m['reference'] ?? ''));
        }));

        if (empty($marches)) {
            return;
        }

        $existing = [];
        if (!empty($exp41Doc->content)) {
            $decoded = json_decode($exp41Doc->content, true);
            if (is_array($decoded)) {
                $existing = $decoded;
            }
        }

        $moisDepart = $existing['mois_depart'] ?? [];
        $identification = $existing['identification'] ?? [];
        $roleCandidat = $existing['role_candidat'] ?? [];

        foreach ($marches as $i => $m) {
            $annee = trim($m['annee'] ?? '');
            $nom = trim($m['nom'] ?? '');
            $reference = trim($m['reference'] ?? '');

            $moisDepart[$i] = $annee;
            $identification[$i] = \App\Support\MarcheIdentification::format($nom, $reference);
            $roleCandidat[$i] = $roleCandidat[$i] ?? '';
        }

        $content = json_encode([
            'mois_depart' => array_values($moisDepart),
            'mois_final' => array_values($existing['mois_final'] ?? []),
            'identification' => array_values($identification),
            'role_candidat' => array_values($roleCandidat),
        ], JSON_UNESCAPED_UNICODE);

        if ($content === false) {
            return;
        }

        $exp41Doc->update([
            'content' => $content,
            'statut' => 'complete',
        ]);

        $this->syncExp42aRecords($dossier);
        $this->syncExp42bRecords($dossier);
        $this->syncExp42aSuiteRecords($dossier);
        $this->syncExp42bSuiteRecords($dossier);
    }

    /**
     * Sens inverse du report ci-dessus : à chaque sauvegarde du Formulaire EXP –
     * 4.1, reporte l'année ("Mois/année de départ") et le marché ("Identification
     * du marché", décomposé en nom/référence) vers le Formulaire de qualification,
     * pour que les deux documents restent synchronisés quel que soit celui rempli
     * en premier.
     */
    private function syncExp41ToQualificationMarches(Dossier $dossier, array $values): void
    {
        $qualificationType = TypeDocument::where('nom', 'Formulaire de qualification')->first();
        if (!$qualificationType) {
            return;
        }

        $qualificationDoc = DossierDocument::where('dossier_id', $dossier->id)
            ->where('type_document_id', $qualificationType->id)
            ->first();
        if (!$qualificationDoc) {
            return;
        }

        $moisDepart = $values['mois_depart'] ?? [];
        $identification = $values['identification'] ?? [];
        $count = max(count($moisDepart), count($identification));
        if ($count === 0) {
            return;
        }

        $marches = [];
        for ($i = 0; $i < $count; $i++) {
            $annee = trim((string) ($moisDepart[$i] ?? ''));
            $ident = trim((string) ($identification[$i] ?? ''));
            $parsed = \App\Support\MarcheIdentification::parse($ident);

            if ($annee === '' && $parsed['nom'] === '' && $parsed['reference'] === '') {
                continue;
            }

            $marches[] = ['annee' => $annee, 'nom' => $parsed['nom'], 'reference' => $parsed['reference']];
        }

        if (empty($marches)) {
            return;
        }

        $existing = [];
        if (!empty($qualificationDoc->content)) {
            $decoded = json_decode($qualificationDoc->content, true);
            if (is_array($decoded)) {
                $existing = $decoded;
            }
        }

        $existing['marches'] = $marches;
        $existing['nombre_marches'] = count($marches);

        // Si le formulaire de qualification n'a jamais encore été enregistré
        // directement, on renseigne ses champs déduits du dossier (comme le fait
        // sa propre sauvegarde) afin que le PDF individuel reste complet.
        $qualificationSignataire = $this->resolveQualificationSignataire($dossier);
        $qualificationEntreprise = $dossier->entreprise;
        $existing['societe'] = $existing['societe'] ?? (optional($qualificationEntreprise)->nom ?? '');
        $existing['date'] = $existing['date'] ?? now()->format('Y-m-d');
        $existing['declarant'] = $existing['declarant'] ?? ($qualificationSignataire
            ? trim($qualificationSignataire->nom . ' ' . ($qualificationSignataire->prenom ?? ''))
            : trim(optional($qualificationEntreprise)->responsable ?? ''));
        $existing['fonction'] = $existing['fonction'] ?? ($qualificationSignataire
            ? ($qualificationSignataire->fonction ?: (optional($qualificationEntreprise)->fonction_responsable ?? ''))
            : (optional($qualificationEntreprise)->fonction_responsable ?? ''));
        $existing['reference'] = $existing['reference'] ?? ($dossier->reference_dossier ?? $dossier->ref ?? '');

        $content = json_encode($existing, JSON_UNESCAPED_UNICODE);
        if ($content === false) {
            return;
        }

        $qualificationDoc->update(['content' => $content, 'statut' => 'complete']);

        $this->regenerateQualificationPdf($dossier, $qualificationDoc, $existing);
    }

    /**
     * Marchés à reporter vers les formulaires EXP 4.2 a)/b) : lus en priorité dans
     * l'EXP – 4.1 (texte libre "Identification du marché", analysé par
     * Exp41Marche) ; si 4.1 est absent du dossier ou vide, on se rabat sur les
     * marchés similaires déjà structurés du Formulaire de qualification, pour
     * ne pas obliger à ressaisir la même information dans les deux formulaires.
     *
     * @return array<int, array{nom:string, reference:string, autorite_nom:string, autorite_adresse:string, depart:string, final:string}>
     */
    private function marchesForExp42(Dossier $dossier): array
    {
        $exp41Doc = $dossier->documents()
            ->whereHas('typeDocument', fn ($q) => $q->where('nom', self::EXP41_NOM))
            ->first();

        if ($exp41Doc && !empty($exp41Doc->content)) {
            $decoded = json_decode($exp41Doc->content, true);
            if (is_array($decoded)) {
                $departs = $decoded['mois_depart'] ?? [];
                $finals = $decoded['mois_final'] ?? [];
                $marches = [];
                foreach ($decoded['identification'] ?? [] as $i => $ident) {
                    $ident = trim((string) $ident);
                    if ($ident === '') {
                        continue;
                    }
                    $parsed = \App\Support\Exp41Marche::parse($ident);
                    $marches[] = [
                        'nom' => $parsed['nom'] !== '' ? $parsed['nom'] : $ident,
                        'reference' => $parsed['reference'],
                        'autorite_nom' => $parsed['autorite_nom'],
                        'autorite_adresse' => $parsed['autorite_adresse'],
                        'depart' => $departs[$i] ?? '',
                        'final' => $finals[$i] ?? '',
                    ];
                }
                if (!empty($marches)) {
                    return $marches;
                }
            }
        }

        $qualificationDoc = $dossier->documents()
            ->whereHas('typeDocument', fn ($q) => $q->where('nom', 'Formulaire de qualification'))
            ->first();
        if (!$qualificationDoc || empty($qualificationDoc->content)) {
            return [];
        }

        $decoded = json_decode($qualificationDoc->content, true);
        if (!is_array($decoded) || empty($decoded['marches']) || !is_array($decoded['marches'])) {
            return [];
        }

        $marches = [];
        foreach ($decoded['marches'] as $m) {
            $nom = trim((string) ($m['nom'] ?? ''));
            $reference = trim((string) ($m['reference'] ?? ''));
            if ($nom === '' && $reference === '') {
                continue;
            }
            $marches[] = [
                'nom' => $nom,
                'reference' => $reference,
                'autorite_nom' => '',
                'autorite_adresse' => '',
                'depart' => trim((string) ($m['annee'] ?? '')),
                'final' => '',
            ];
        }

        return $marches;
    }

    /**
     * Crée ou met à jour un Formulaire EXP – 4.2 a) par marché (EXP – 4.1, ou
     * formulaire de qualification si 4.1 est vide — voir marchesForExp42).
     * Le numéro et l'identification du marché suivent toujours la source ; les
     * autres champs saisis dans le formulaire sont conservés.
     */
    private function syncExp42aRecords(Dossier $dossier): void
    {
        $hasExp42a = $dossier->documents()
            ->whereHas('typeDocument', fn ($q) => $q->where('nom', self::EXP42A_NOM))
            ->exists();
        if (!$hasExp42a) {
            return;
        }

        $marches = $this->marchesForExp42($dossier);
        if (empty($marches)) {
            return;
        }

        $dossier->loadMissing('entreprise', 'signataires');
        $entreprise = $dossier->entreprise;
        $signataire = $dossier->signataires->firstWhere('pivot.role_signataire', 'gerant') ?? $dossier->signataires->first();

        foreach ($marches as $position => $marche) {
            $prefill = [
                'identification_marche' => $marche['nom'],
                'numero_marche' => mb_substr($marche['reference'], 0, 250),
                'autorite_nom' => mb_substr($marche['autorite_nom'], 0, 250),
                'autorite_adresse' => $marche['autorite_adresse'],
                'date_attribution' => $this->monthToDate($marche['depart']),
                'date_achevement' => $this->monthToDate($marche['final']),
                'montant_total' => $this->knownMontantForMarche($dossier, $position),
            ];

            $record = FormulaireExp42A::firstOrNew(['dossier_id' => $dossier->id, 'marche_position' => $position]);

            if (!$record->exists) {
                $record->fill([
                    'formulaire_type' => 'A',
                    'utilisateur_id' => auth()->id(),
                    'entreprise_id' => $dossier->entreprise_id,
                    'signataire_id' => optional($signataire)->id,
                    'nom_candidat' => trim(optional($entreprise)->responsable ?? optional($entreprise)->nom ?? '') ?: 'Candidat non renseigné',
                    'numero_adrp' => (new FormulaireExp42A())->buildNumeroAdrp($dossier),
                    'nom_signataire' => $signataire ? trim($signataire->nom . ' ' . ($signataire->prenom ?? '')) : null,
                    'fonction_signataire' => $signataire ? trim($signataire->fonction ?? '') : null,
                ]);
            }

            $this->applyExp42Prefill($record, $prefill);
            $record->save();
        }

        FormulaireExp42A::where('dossier_id', $dossier->id)
            ->whereNotNull('marche_position')
            ->where('marche_position', '>=', count($marches))
            ->delete();
    }

    /**
     * Applique un pre-remplissage EXP 4.2 a)/b) a un enregistrement : les
     * champs d'identification (marche, autorite, dates, role, contacts...)
     * suivent toujours leur source (EXP 4.1, formulaire de qualification, ou
     * EXP 4.2 a) pour b) - une correction y est donc repercutee a chaque
     * sync, meme si le champ etait deja rempli. Seuls les montants
     * (montant_total, montant_part) restent "rempli une fois" : un montant
     * deja saisi n'est jamais ecrase, l'utilisateur garde la main dessus
     * (voir knownMontantForMarche).
     */
    private function applyExp42Prefill(Model $record, array $prefill): void
    {
        $fillOnce = ['montant_total', 'montant_part'];

        foreach ($prefill as $field => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            if (in_array($field, $fillOnce, true) && $record->{$field} !== null && $record->{$field} !== '') {
                continue;
            }
            $record->{$field} = $value;
        }
    }

    /**
     * Montant d'un marche deja enregistre une fois, n'importe ou parmi les
     * formulaires EXP 4.2 a)/b)/(suite) du meme marche (meme marche_position) :
     * reutilise cette valeur pour pre-remplir les autres, sans jamais
     * ecraser un montant deja saisi (l'utilisateur garde la main partout).
     */
    private function knownMontantForMarche(Dossier $dossier, int $position): ?string
    {
        $sources = [
            [FormulaireExp42A::class, 'montant_total'],
            [FormulaireExp42B::class, 'montant_total'],
            [FormulaireExp42ASuite::class, 'montant'],
            [FormulaireExp42BSuite::class, 'montant'],
        ];

        foreach ($sources as [$model, $field]) {
            $value = $model::where('dossier_id', $dossier->id)->where('marche_position', $position)->value($field);
            if ($value !== null && trim((string) $value) !== '') {
                return (string) $value;
            }
        }

        return null;
    }

    /**
     * "2023-05", "05/2023" ou "05-2023" → 1er jour du mois (format date SQL).
     */
    private function monthToDate(?string $value): ?string
    {
        $value = trim((string) $value);
        foreach (['Y-m', 'm/Y', 'm-Y'] as $format) {
            $date = \DateTime::createFromFormat('!' . $format, $value);
            if ($date && $date->format($format) === $value) {
                return $date->format('Y-m-01');
            }
        }

        return null;
    }

    /**
     * Chemins de fichiers temporaires : un PDF 4.2 a) par marché du dossier.
     * L'appelant doit les supprimer une fois fusionnés.
     */
    private function exp42aPdfPaths(Dossier $dossier): array
    {
        $this->syncExp42aRecords($dossier);

        $paths = [];
        FormulaireExp42A::where('dossier_id', $dossier->id)
            ->whereNotNull('marche_position')
            ->orderBy('marche_position')
            ->get()
            ->each(function (FormulaireExp42A $record) use (&$paths) {
                $path = tempnam(sys_get_temp_dir(), 'exp42a_') . '.pdf';
                file_put_contents($path, Exp42aPdf::render($record));
                $paths[] = $path;
            });

        return $paths;
    }

    private function isExp42aDoc(DossierDocument $doc): bool
    {
        return trim(optional($doc->typeDocument)->nom ?? '') === self::EXP42A_NOM;
    }

    /**
     * Crée ou met à jour un Formulaire EXP – 4.2 b) par marché (EXP – 4.1, ou
     * formulaire de qualification si 4.1 est vide — voir marchesForExp42).
     * Même logique que syncExp42aRecords, pour le formulaire B (même table,
     * distinguée par formulaire_type).
     */
    private function syncExp42bRecords(Dossier $dossier): void
    {
        $hasExp42b = $dossier->documents()
            ->whereHas('typeDocument', fn ($q) => $q->where('nom', self::EXP42B_NOM))
            ->exists();
        if (!$hasExp42b) {
            return;
        }

        $marches = $this->marchesForExp42($dossier);
        if (empty($marches)) {
            return;
        }

        $dossier->loadMissing('entreprise', 'signataires');
        $entreprise = $dossier->entreprise;
        $signataire = $dossier->signataires->firstWhere('pivot.role_signataire', 'gerant') ?? $dossier->signataires->first();

        // 4.2 a) est synchronise avant 4.2 b) (voir les appels groupes de ces deux
        // methodes) : on reprend ici ce que 4.1 ne fournit pas (role, montants,
        // telephone/email de l'autorite) depuis le formulaire a) du meme marche,
        // pour eviter de ressaisir deux fois la meme information.
        $aByPosition = FormulaireExp42A::where('dossier_id', $dossier->id)
            ->whereNotNull('marche_position')
            ->get()
            ->keyBy('marche_position');

        foreach ($marches as $position => $marche) {
            $prefill = [
                'identification_marche' => $marche['nom'],
                'numero_marche' => mb_substr($marche['reference'], 0, 250),
                'autorite_nom' => mb_substr($marche['autorite_nom'], 0, 250),
                'autorite_adresse' => $marche['autorite_adresse'],
                'date_attribution' => $this->monthToDate($marche['depart']),
                'date_achevement' => $this->monthToDate($marche['final']),
                'montant_total' => $this->knownMontantForMarche($dossier, $position),
            ];

            $aRecord = $aByPosition->get($position);
            if ($aRecord) {
                $prefill['autorite_nom'] = $prefill['autorite_nom'] !== '' ? $prefill['autorite_nom'] : mb_substr((string) $aRecord->autorite_nom, 0, 250);
                $prefill['autorite_adresse'] = $prefill['autorite_adresse'] !== '' ? $prefill['autorite_adresse'] : (string) $aRecord->autorite_adresse;
                $prefill['role_marche'] = (string) $aRecord->role_marche;
                $prefill['participation_pourcentage'] = (string) $aRecord->participation_pourcentage;
                $prefill['montant_part'] = (string) $aRecord->montant_part;
                $prefill['autorite_telephone'] = (string) $aRecord->autorite_telephone;
                $prefill['autorite_email'] = (string) $aRecord->autorite_email;
                $prefill['date_attribution'] = $prefill['date_attribution'] ?: $aRecord->date_attribution;
                $prefill['date_achevement'] = $prefill['date_achevement'] ?: $aRecord->date_achevement;
            }

            $record = FormulaireExp42B::firstOrNew(['dossier_id' => $dossier->id, 'marche_position' => $position]);

            if (!$record->exists) {
                $record->fill([
                    'formulaire_type' => 'B',
                    'utilisateur_id' => auth()->id(),
                    'entreprise_id' => $dossier->entreprise_id,
                    'signataire_id' => optional($signataire)->id,
                    'nom_candidat' => trim(optional($entreprise)->responsable ?? optional($entreprise)->nom ?? '') ?: 'Candidat non renseigné',
                    'numero_adrp' => (new FormulaireExp42B())->buildNumeroAdrp($dossier),
                    'nom_signataire' => $signataire ? trim($signataire->nom . ' ' . ($signataire->prenom ?? '')) : null,
                    'fonction_signataire' => $signataire ? trim($signataire->fonction ?? '') : null,
                ]);
            }

            $this->applyExp42Prefill($record, $prefill);
            $record->save();
        }

        FormulaireExp42B::where('dossier_id', $dossier->id)
            ->whereNotNull('marche_position')
            ->where('marche_position', '>=', count($marches))
            ->delete();
    }

    /**
     * Chemins de fichiers temporaires : un PDF 4.2 b) par marché du dossier.
     * L'appelant doit les supprimer une fois fusionnés.
     */
    private function exp42bPdfPaths(Dossier $dossier): array
    {
        $this->syncExp42bRecords($dossier);

        $paths = [];
        FormulaireExp42B::where('dossier_id', $dossier->id)
            ->whereNotNull('marche_position')
            ->orderBy('marche_position')
            ->get()
            ->each(function (FormulaireExp42B $record) use (&$paths) {
                $path = tempnam(sys_get_temp_dir(), 'exp42b_') . '.pdf';
                file_put_contents($path, Exp42bPdf::render($record));
                $paths[] = $path;
            });

        return $paths;
    }

    private function isExp42bDoc(DossierDocument $doc): bool
    {
        return trim(optional($doc->typeDocument)->nom ?? '') === self::EXP42B_NOM;
    }

    /**
     * Crée ou met à jour un Formulaire EXP – 4.2 a) (suite) par marché. Lié
     * directement au formulaire a) du même marché (numéro et nom repris de
     * là, pas analysés depuis 4.1) ; les champs propres à la suite (montant,
     * taille, complexité...) restent à la main de l'utilisateur.
     */
    private function syncExp42aSuiteRecords(Dossier $dossier): void
    {
        $hasExp42aSuite = $dossier->documents()
            ->whereHas('typeDocument', fn ($q) => $q->where('nom', self::EXP42A_SUITE_NOM))
            ->exists();
        if (!$hasExp42aSuite) {
            return;
        }

        $marches = $this->marchesForExp42($dossier);
        if (empty($marches)) {
            return;
        }

        $dossier->loadMissing('entreprise', 'signataires');
        $entreprise = $dossier->entreprise;
        $signataire = $dossier->signataires->firstWhere('pivot.role_signataire', 'gerant') ?? $dossier->signataires->first();

        $aByPosition = FormulaireExp42A::where('dossier_id', $dossier->id)
            ->whereNotNull('marche_position')
            ->get()
            ->keyBy('marche_position');

        foreach (array_keys($marches) as $position) {
            $aRecord = $aByPosition->get($position);

            $record = FormulaireExp42ASuite::firstOrNew(['dossier_id' => $dossier->id, 'marche_position' => $position]);

            if (!$record->exists) {
                $record->fill([
                    'formulaire_type' => 'A_SUITE',
                    'utilisateur_id' => auth()->id(),
                    'entreprise_id' => $dossier->entreprise_id,
                    'signataire_id' => optional($signataire)->id,
                    'nom_candidat' => trim(optional($entreprise)->responsable ?? optional($entreprise)->nom ?? '') ?: 'Candidat non renseigné',
                    'numero_adrp' => (new FormulaireExp42ASuite())->buildNumeroAdrp($dossier),
                    'nom_signataire' => $signataire ? trim($signataire->nom . ' ' . ($signataire->prenom ?? '')) : null,
                    'fonction_signataire' => $signataire ? trim($signataire->fonction ?? '') : null,
                ]);
            }

            // Le numero de marche est purement derive du formulaire a) : il suit
            // toujours sa valeur actuelle, sans etre modifiable independamment ici.
            if ($aRecord) {
                $record->numero_marche = mb_substr((string) $aRecord->numero_marche, 0, 250);
            }

            // Pre-remplissage : description = nom du marche (Identification du
            // marche de 4.2 a)), montant = montant total de 4.2 a), et des valeurs
            // par defaut usuelles pour les caracteristiques. L'utilisateur reste
            // libre de modifier : on ne touche jamais un champ deja renseigne.
            $suitePrefill = [
                'description_similitude' => $aRecord ? (string) $aRecord->identification_marche : '',
                'montant' => $this->knownMontantForMarche($dossier, $position),
                'taille_physique' => 'Etendue',
                'complexite' => 'Haute',
                'methodes_technologie' => 'Semblable',
                'autres_caracteristiques' => 'NEANT',
            ];
            foreach ($suitePrefill as $field => $value) {
                if (($record->{$field} === null || $record->{$field} === '') && $value !== '' && $value !== null) {
                    $record->{$field} = $value;
                }
            }

            $record->save();
        }

        FormulaireExp42ASuite::where('dossier_id', $dossier->id)
            ->whereNotNull('marche_position')
            ->where('marche_position', '>=', count($marches))
            ->delete();
    }

    /**
     * Chemins de fichiers temporaires : un PDF 4.2 a) (suite) par marché du dossier.
     * L'appelant doit les supprimer une fois fusionnés.
     */
    private function exp42aSuitePdfPaths(Dossier $dossier): array
    {
        $this->syncExp42aSuiteRecords($dossier);

        $paths = [];
        FormulaireExp42ASuite::where('dossier_id', $dossier->id)
            ->whereNotNull('marche_position')
            ->orderBy('marche_position')
            ->get()
            ->each(function (FormulaireExp42ASuite $record) use (&$paths) {
                $path = tempnam(sys_get_temp_dir(), 'exp42a_suite_') . '.pdf';
                file_put_contents($path, Exp42aSuitePdf::render($record));
                $paths[] = $path;
            });

        return $paths;
    }

    private function isExp42aSuiteDoc(DossierDocument $doc): bool
    {
        return trim(optional($doc->typeDocument)->nom ?? '') === self::EXP42A_SUITE_NOM;
    }

    /**
     * Crée ou met à jour un Formulaire EXP – 4.2 b) (suite) par marché. Lié
     * directement au formulaire b) du même marché, comme 4.2 a) (suite) l'est
     * à 4.2 a) : numéro, description et montant en sont repris.
     */
    private function syncExp42bSuiteRecords(Dossier $dossier): void
    {
        $hasExp42bSuite = $dossier->documents()
            ->whereHas('typeDocument', fn ($q) => $q->where('nom', self::EXP42B_SUITE_NOM))
            ->exists();
        if (!$hasExp42bSuite) {
            return;
        }

        $marches = $this->marchesForExp42($dossier);
        if (empty($marches)) {
            return;
        }

        $dossier->loadMissing('entreprise', 'signataires');
        $entreprise = $dossier->entreprise;
        $signataire = $dossier->signataires->firstWhere('pivot.role_signataire', 'gerant') ?? $dossier->signataires->first();

        $bByPosition = FormulaireExp42B::where('dossier_id', $dossier->id)
            ->whereNotNull('marche_position')
            ->get()
            ->keyBy('marche_position');

        foreach (array_keys($marches) as $position) {
            $bRecord = $bByPosition->get($position);

            $record = FormulaireExp42BSuite::firstOrNew(['dossier_id' => $dossier->id, 'marche_position' => $position]);

            if (!$record->exists) {
                $record->fill([
                    'formulaire_type' => 'B_SUITE',
                    'utilisateur_id' => auth()->id(),
                    'entreprise_id' => $dossier->entreprise_id,
                    'signataire_id' => optional($signataire)->id,
                    'nom_candidat' => trim(optional($entreprise)->responsable ?? optional($entreprise)->nom ?? '') ?: 'Candidat non renseigné',
                    'numero_adrp' => (new FormulaireExp42BSuite())->buildNumeroAdrp($dossier),
                    'nom_signataire' => $signataire ? trim($signataire->nom . ' ' . ($signataire->prenom ?? '')) : null,
                    'fonction_signataire' => $signataire ? trim($signataire->fonction ?? '') : null,
                ]);
            }

            // Le numero de marche est purement derive du formulaire b) : il suit
            // toujours sa valeur actuelle, sans etre modifiable independamment ici.
            if ($bRecord) {
                $record->numero_marche = mb_substr((string) $bRecord->numero_marche, 0, 250);
            }

            // Pre-remplissage : description = nom du marche (Identification du
            // marche de 4.2 b)), montant = montant total de 4.2 b), et des
            // valeurs par defaut usuelles. L'utilisateur reste libre de modifier :
            // on ne touche jamais un champ deja renseigne.
            $suitePrefill = [
                'description_similitude' => $bRecord ? (string) $bRecord->identification_marche : '',
                'montant' => $this->knownMontantForMarche($dossier, $position),
                'taille_physique' => 'Etendue',
                'complexite' => 'Haute',
                'methodes_technologie' => 'Semblable',
                'autres_caracteristiques' => 'NEANT',
            ];
            foreach ($suitePrefill as $field => $value) {
                if (($record->{$field} === null || $record->{$field} === '') && $value !== '' && $value !== null) {
                    $record->{$field} = $value;
                }
            }

            $record->save();
        }

        FormulaireExp42BSuite::where('dossier_id', $dossier->id)
            ->whereNotNull('marche_position')
            ->where('marche_position', '>=', count($marches))
            ->delete();
    }

    /**
     * Chemins de fichiers temporaires : un PDF 4.2 b) (suite) par marché du dossier.
     * L'appelant doit les supprimer une fois fusionnés.
     */
    private function exp42bSuitePdfPaths(Dossier $dossier): array
    {
        $this->syncExp42bSuiteRecords($dossier);

        $paths = [];
        FormulaireExp42BSuite::where('dossier_id', $dossier->id)
            ->whereNotNull('marche_position')
            ->orderBy('marche_position')
            ->get()
            ->each(function (FormulaireExp42BSuite $record) use (&$paths) {
                $path = tempnam(sys_get_temp_dir(), 'exp42b_suite_') . '.pdf';
                file_put_contents($path, Exp42bSuitePdf::render($record));
                $paths[] = $path;
            });

        return $paths;
    }

    private function isExp42bSuiteDoc(DossierDocument $doc): bool
    {
        return trim(optional($doc->typeDocument)->nom ?? '') === self::EXP42B_SUITE_NOM;
    }

    /**
     * Crée ou met à jour un Formulaire PER par ligne de la Liste du personnel
     * affecté à l'exécution du marché : poste et nom suivent toujours cette
     * liste (lien direct, non modifiables dans ce formulaire) ; le reste
     * (qualifications, employeur, expériences...) reste à la main de
     * l'utilisateur, par personne.
     */
    private function syncFormulairePerRecords(Dossier $dossier): void
    {
        $hasPer = $dossier->documents()
            ->whereHas('typeDocument', fn ($q) => $q->where('nom', self::FORMULAIRE_PER_NOM))
            ->exists();
        if (!$hasPer) {
            return;
        }

        $listeDoc = $dossier->documents()
            ->whereHas('typeDocument', fn ($q) => $q->where('nom', self::LISTE_PERSONNEL_NOM))
            ->first();
        if (!$listeDoc || empty($listeDoc->content)) {
            return;
        }

        $decoded = json_decode($listeDoc->content, true);
        if (!is_array($decoded)) {
            return;
        }

        $personnel = [];
        foreach ($decoded as $row) {
            $poste = trim((string) ($row['poste'] ?? ''));
            $nom = trim((string) ($row['nom'] ?? ''));
            if ($poste === '' && $nom === '') {
                continue;
            }
            $personnel[] = ['poste' => $poste, 'nom' => $nom];
        }
        if (empty($personnel)) {
            return;
        }

        $dossier->loadMissing('entreprise');
        $entreprise = $dossier->entreprise;
        $nomCandidat = trim(optional($entreprise)->nom ?? '') ?: 'Candidat non renseigné';

        foreach ($personnel as $position => $p) {
            $record = FormulairePER::firstOrNew(['dossier_id' => $dossier->id, 'personnel_position' => $position]);

            if (!$record->exists) {
                $record->fill([
                    'utilisateur_id' => auth()->id(),
                    'nom_candidat' => $nomCandidat,
                ]);
            } else {
                // Corrige les enregistrements crees avant que ce champ ne
                // remonte le nom de l'entreprise (une version precedente y
                // mettait a tort le responsable de l'entreprise) : on ne
                // touche que les valeurs vides ou encore egales a l'ancien
                // responsable, jamais une valeur deja personnalisee par
                // l'utilisateur dans le Formulaire PER.
                $currentNomCandidat = trim((string) ($record->nom_candidat ?? ''));
                $responsable = trim((string) (optional($entreprise)->responsable ?? ''));
                if ($currentNomCandidat === '' || ($responsable !== '' && $currentNomCandidat === $responsable)) {
                    $record->nom_candidat = $nomCandidat;
                }
            }

            // Poste et nom sont purement derives de la liste du personnel : ils
            // suivent toujours sa valeur actuelle, sans etre modifiables ici.
            $record->poste = mb_substr($p['poste'], 0, 250);
            $record->nom_personnel = mb_substr($p['nom'], 0, 250);
            $record->save();

            // Si cette personne correspond a EXACTEMENT un CV enregistre dans
            // "Les personnels de l'entreprise" (match par nom), on l'applique
            // automatiquement - fill-empty-only (nom de l'employeur, emploi
            // tenu, qualifications, coordonnees, experiences, pieces jointes...
            // tout ce que le CV connait deja). En cas de plusieurs CV pour ce
            // nom, rien n'est applique automatiquement : la personne choisit
            // elle-meme via le menu deroulant du Formulaire PER (ambigu sinon).
            $matches = $this->findEntreprisePersonnelsByNom($entreprise, $p['nom']);
            if ($matches->count() === 1) {
                $this->applyEntreprisePersonnelToFormulairePer($record, $matches->first(), false);
            }

            // Repli generique, seulement si toujours vide (aucun CV ne
            // fournissait l'info) : Nom de l'employeur <- Nom de la liste du
            // personnel, Emploi tenu <- Designation du poste de la liste.
            if ($record->nom_employeur === null || $record->nom_employeur === '') {
                $record->nom_employeur = mb_substr($p['nom'], 0, 250);
            }
            if ($record->emploi_tenu === null || $record->emploi_tenu === '') {
                $record->emploi_tenu = mb_substr($p['poste'], 0, 250);
            }
            $record->save();
        }

        FormulairePER::where('dossier_id', $dossier->id)
            ->whereNotNull('personnel_position')
            ->where('personnel_position', '>=', count($personnel))
            ->delete();
    }

    /**
     * Tous les CV de "Les personnels de l'entreprise" dont le nom correspond
     * (normalise : casse/accents/ponctuation ignores) - une meme personne
     * pouvant avoir plusieurs CV distincts (intitules differents).
     */
    private function findEntreprisePersonnelsByNom(?Entreprise $entreprise, string $nom): \Illuminate\Support\Collection
    {
        $nom = trim($nom);
        if (!$entreprise || $nom === '') {
            return collect();
        }

        $target = $this->normalizeSommaireName($nom);

        return $entreprise->personnels()->with('fichiers')->get()
            ->filter(fn (EntreprisePersonnel $p) => $this->normalizeSommaireName(trim((string) $p->nom)) === $target)
            ->values();
    }

    /**
     * Lie un CV de l'entreprise a un Formulaire PER : copie les champs
     * (fill-empty-only par defaut, ou ecrase si $forceOverwrite - choix
     * explicite via le bouton "Appliquer"). Les pieces jointes du CV ne sont
     * PAS dupliquees : elles restent sur l'entreprise et sont affichees
     * directement partout ou ce Formulaire PER est consulte (formulaire,
     * apercu, PDF genere) via $record->cvFichiers() - voir FormulairePER.
     * Poste et nom restent toujours derives de la Liste du personnel,
     * jamais de l'entreprise.
     */
    private function applyEntreprisePersonnelToFormulairePer(FormulairePER $record, EntreprisePersonnel $entreprisePersonnel, bool $forceOverwrite): void
    {
        foreach (['nom_employeur', 'emploi_tenu', 'date_naissance', 'qualifications', 'adresse_employeur', 'telephone', 'telecopie', 'email', 'contact_personnel', 'nombre_annees_employeur', 'experiences'] as $field) {
            $value = $entreprisePersonnel->{$field};
            $valueIsEmpty = $value === null || $value === '' || (is_array($value) && empty($value));
            if ($valueIsEmpty) {
                continue;
            }

            if ($forceOverwrite) {
                $record->{$field} = $value;
                continue;
            }

            $current = $record->{$field};
            $currentIsEmpty = $current === null || $current === '' || (is_array($current) && empty($current));
            if ($currentIsEmpty) {
                $record->{$field} = $value;
            }
        }

        // Changer de CV rend les exclusions precedentes (qui visaient les
        // pieces de l'ANCIEN CV) sans objet.
        if ($forceOverwrite && $record->source_entreprise_personnel_id !== $entreprisePersonnel->id) {
            $record->excluded_cv_fichier_ids = [];
        }

        $record->source_entreprise_personnel_id = $entreprisePersonnel->id;
        $record->save();
    }

    /**
     * Chemins de fichiers temporaires : un PDF Formulaire PER par personne du
     * dossier. L'appelant doit les supprimer une fois fusionnés.
     */
    private function formulairePerPdfPaths(Dossier $dossier): array
    {
        $this->syncFormulairePerRecords($dossier);

        $paths = [];
        FormulairePER::where('dossier_id', $dossier->id)
            ->whereNotNull('personnel_position')
            ->orderBy('personnel_position')
            ->with(['fichiers', 'sourceEntreprisePersonnel.fichiers'])
            ->get()
            ->each(function (FormulairePER $record) use (&$paths) {
                $path = tempnam(sys_get_temp_dir(), 'formulaire_per_') . '.pdf';
                file_put_contents($path, FormulairePerPdf::render($record));
                $paths[] = $path;

                // Pieces jointes de cette personne : celles ajoutees
                // directement sur ce dossier, PLUS celles du CV de
                // l'entreprise lie (moins celles explicitement exclues) -
                // inserees juste apres sa page, avant de passer a la personne
                // suivante. On travaille sur des COPIES temporaires (jamais le
                // fichier original) puisque l'appelant supprime tout ce que
                // cette methode renvoie une fois la fusion terminee.
                $toutesLesPieces = $record->fichiers->concat($record->cvFichiers());
                foreach ($toutesLesPieces as $fichier) {
                    $fullPath = storage_path('app/public/' . ltrim($fichier->chemin_fichier, '/'));
                    if (!file_exists($fullPath)) {
                        continue;
                    }

                    $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
                    if ($ext === 'pdf') {
                        $tempCopy = tempnam(sys_get_temp_dir(), 'per_piece_') . '.pdf';
                        copy($fullPath, $tempCopy);
                        $paths[] = $tempCopy;
                    } elseif (in_array($ext, ['png', 'jpg', 'jpeg', 'gif'], true)) {
                        $imagePdf = $this->imageToSinglePagePdf($fullPath);
                        if ($imagePdf) {
                            $paths[] = $imagePdf;
                        }
                    }
                }
            });

        return $paths;
    }

    /**
     * Convertit une image en un PDF autonome d'une seule page (meme format
     * que l'image, pleine page) : utilise pour inserer une piece jointe
     * image dans le PDF fusionne via le meme mecanisme que les PDF generes
     * (chemin de fichier temporaire pousse dans $filesToMerge).
     */
    private function imageToSinglePagePdf(string $imagePath): ?string
    {
        $size = @getimagesize($imagePath);
        if (!$size) {
            return null;
        }

        [$widthPx, $heightPx] = $size;
        $widthMm = $widthPx / 150 * 25.4;
        $heightMm = $heightPx / 150 * 25.4;
        $orientation = $widthMm > $heightMm ? 'L' : 'P';

        $pdf = new \FPDF($orientation, 'mm', [$widthMm, $heightMm]);
        $pdf->AddPage($orientation, [$widthMm, $heightMm]);
        $pdf->Image($imagePath, 0, 0, $widthMm, $heightMm);

        $tempPath = tempnam(sys_get_temp_dir(), 'per_piece_img_') . '.pdf';
        $pdf->Output('F', $tempPath);

        return $tempPath;
    }

    private function isFormulairePerDoc(DossierDocument $doc): bool
    {
        return trim(optional($doc->typeDocument)->nom ?? '') === self::FORMULAIRE_PER_NOM;
    }

    /**
     * Crée ou met à jour un Formulaire MAT par ligne de la Liste de Matériel
     * du dossier : le type et caractéristiques du matériel suivent toujours
     * cette liste (lien direct, non modifiable dans ce formulaire) ; le
     * reste (fabricant, modèle/puissance, capacité, provenance...) reste à
     * la main de l'utilisateur, par matériel.
     */
    private function syncFormulaireMatRecords(Dossier $dossier): void
    {
        $hasMat = $dossier->documents()
            ->whereHas('typeDocument', fn ($q) => $q->where('nom', self::FORMULAIRE_MAT_NOM))
            ->exists();
        if (!$hasMat) {
            return;
        }

        $listeDoc = $dossier->documents()
            ->whereHas('typeDocument', fn ($q) => $q->where('nom', self::LISTE_MATERIEL_NOM))
            ->with('bordereau.lignes')
            ->first();
        if (!$listeDoc) {
            return;
        }

        $materiels = [];
        foreach ($listeDoc->bordereau as $bordereau) {
            foreach ($bordereau->lignes as $ligne) {
                $designation = trim((string) ($ligne->designation ?? ''));
                if ($designation === '') {
                    continue;
                }
                $materiels[] = $designation;
            }
        }
        if (empty($materiels)) {
            return;
        }

        foreach ($materiels as $position => $designation) {
            $record = FormulaireMat::firstOrNew(['dossier_id' => $dossier->id, 'materiel_position' => $position]);

            if (!$record->exists) {
                $record->fill(['utilisateur_id' => auth()->id()]);
            }

            // Le type et caracteristiques du materiel est purement derive de
            // la Liste de Materiel : il suit toujours sa valeur actuelle,
            // sans etre modifiable ici.
            $record->piece_materiel = mb_substr($designation, 0, 250);

            $record->save();
        }

        FormulaireMat::where('dossier_id', $dossier->id)
            ->whereNotNull('materiel_position')
            ->where('materiel_position', '>=', count($materiels))
            ->delete();
    }

    /**
     * Chemins de fichiers temporaires : un PDF Formulaire MAT par matériel
     * du dossier. L'appelant doit les supprimer une fois fusionnés.
     */
    private function formulaireMatPdfPaths(Dossier $dossier): array
    {
        $this->syncFormulaireMatRecords($dossier);

        $paths = [];
        FormulaireMat::where('dossier_id', $dossier->id)
            ->whereNotNull('materiel_position')
            ->orderBy('materiel_position')
            ->get()
            ->each(function (FormulaireMat $record) use (&$paths) {
                $path = tempnam(sys_get_temp_dir(), 'formulaire_mat_') . '.pdf';
                file_put_contents($path, FormulaireMatPdf::render($record));
                $paths[] = $path;
            });

        return $paths;
    }

    private function isFormulaireMatDoc(DossierDocument $doc): bool
    {
        return trim(optional($doc->typeDocument)->nom ?? '') === self::FORMULAIRE_MAT_NOM;
    }

    private function isFicheTechniqueArticlesDoc(DossierDocument $doc): bool
    {
        return trim(optional($doc->typeDocument)->nom ?? '') === self::FICHE_TECHNIQUE_ARTICLES_NOM;
    }

    /**
     * Liste des articles source pour "Fiche technique de chaque article" -
     * voir App\Support\FicheTechniqueArticles pour la logique complete (la
     * "Description technique" si remplie, sinon n'importe quel autre
     * bordereau du dossier deja rempli avec des designations). L'index dans
     * ce tableau EST le "N°" de l'article (0-based), utilise pour rattacher
     * sa fiche technique (voir FicheTechniqueFichier::article_index). Ce
     * n'est jamais l'id d'une BordereauLigne, qui n'est pas stable (ces
     * lignes sont supprimees et recreees a chaque enregistrement du
     * tableau).
     */
    private function descriptionTechniqueArticles(Dossier $dossier): array
    {
        return \App\Support\FicheTechniqueArticles::forDossier($dossier);
    }

    /**
     * Entrees du Catalogue (menu "Catalogue") dont la designation correspond
     * EXACTEMENT (une fois normalisee - insensible a la casse, aux accents et
     * a la ponctuation, voir normalizeSommaireName()) a celle fournie. Jamais
     * de rapprochement flou : en cas de doublon dans le catalogue, l'appelant
     * doit s'abstenir (voir syncFicheTechniqueFromCatalogue()).
     */
    private function findCatalogueArticlesByDesignation(string $designation): \Illuminate\Support\Collection
    {
        $target = $this->normalizeSommaireName($designation);
        if ($target === '') {
            return collect();
        }

        return CatalogueArticle::whereNotNull('designation')
            ->get()
            ->filter(fn (CatalogueArticle $c) => $this->normalizeSommaireName(trim((string) $c->designation)) === $target)
            ->values();
    }

    /**
     * Pour chaque article du tableau "Description technique" qui n'a PAS
     * encore de fiche technique/prospectus propre a ce dossier, cherche une
     * entree du Catalogue dont la designation correspond EXACTEMENT (voir
     * findCatalogueArticlesByDesignation()) et, si une seule est trouvee,
     * copie son prospectus dans une nouvelle FicheTechniqueFichier pour cet
     * article - une copie physique independante (comme pour la duplication
     * de dossier), jamais une reference live : modifier ou supprimer
     * l'entree du catalogue plus tard n'affecte pas les dossiers deja
     * remplis. Si aucune entree ou plusieurs entrees correspondent, rien
     * n'est fait : l'utilisateur televerse ou choisit lui-meme (meme logique
     * que pour les CV multiples du personnel d'entreprise).
     */
    private function syncFicheTechniqueFromCatalogue(Dossier $dossier): void
    {
        $doc = $dossier->documents()
            ->whereHas('typeDocument', fn ($q) => $q->where('nom', self::FICHE_TECHNIQUE_ARTICLES_NOM))
            ->first();
        if (!$doc) {
            return;
        }

        $articles = $this->descriptionTechniqueArticles($dossier);
        if (empty($articles)) {
            return;
        }

        $existingIndexes = FicheTechniqueFichier::where('dossier_document_id', $doc->id)
            ->pluck('article_index')
            ->all();

        $added = false;

        foreach ($articles as $index => $article) {
            if (in_array($index, $existingIndexes, true)) {
                continue;
            }

            $designation = trim((string) ($article['designation'] ?? ''));
            if ($designation === '') {
                continue;
            }

            $matches = $this->findCatalogueArticlesByDesignation($designation);
            if ($matches->count() !== 1) {
                continue;
            }

            $catalogueArticle = $matches->first();
            if (empty($catalogueArticle->chemin_prospectus) || !Storage::disk('public')->exists($catalogueArticle->chemin_prospectus)) {
                continue;
            }

            $extension = pathinfo($catalogueArticle->chemin_prospectus, PATHINFO_EXTENSION);
            $copiePath = 'dossiers/documents/catalogue_' . $catalogueArticle->id . '_' . uniqid() . ($extension ? '.' . $extension : '');
            Storage::disk('public')->copy($catalogueArticle->chemin_prospectus, $copiePath);

            FicheTechniqueFichier::create([
                'dossier_document_id' => $doc->id,
                'article_index' => $index,
                'chemin_fichier' => $copiePath,
                'nom_original' => $catalogueArticle->nom_original_prospectus,
                'utilisateur_id' => auth()->id(),
            ]);
            $added = true;
        }

        if ($added && $doc->statut !== 'complete') {
            $doc->update(['statut' => 'complete']);
        }
    }

    /**
     * Pour chaque article du tableau "Description technique" qui a une
     * fiche technique/prospectus attache, dans l'ordre des articles (pas
     * l'ordre de televersement) : une page intercalaire generee ("Article
     * N°X — designation") suivie du prospectus lui-meme (PDF fusionne tel
     * quel, image convertie en PDF 1 page). L'intercalaire est numerote
     * (numberedEntry), le prospectus brut ne l'est jamais (attachmentEntry) -
     * meme convention que le reste du dossier. Les articles sans prospectus
     * sont simplement ignores (aucune page produite pour eux).
     */
    private function ficheTechniqueArticlesEntries(DossierDocument $doc): array
    {
        $dossier = $doc->dossier ?? Dossier::find($doc->dossier_id);
        if (!$dossier) {
            return [];
        }

        $this->syncFicheTechniqueFromCatalogue($dossier);

        $articles = $this->descriptionTechniqueArticles($dossier);
        if (empty($articles)) {
            return [];
        }

        $fichiers = FicheTechniqueFichier::where('dossier_document_id', $doc->id)
            ->get()
            ->keyBy('article_index');

        $entries = [];
        foreach ($articles as $index => $article) {
            $fichier = $fichiers->get($index);
            if (!$fichier) {
                continue;
            }

            $fullPath = storage_path('app/public/' . ltrim($fichier->chemin_fichier, '/'));
            if (!file_exists($fullPath)) {
                continue;
            }

            $numero = $index + 1;
            $designation = $article['designation'] !== '' ? $article['designation'] : ('Article n°' . $numero);

            $htmlSeparateur = view('documents.partials.fiche_technique_intercalaire_pdf', [
                'numero' => $numero,
                'designation' => $designation,
            ])->render();
            $dompdfSeparateur = new Dompdf(['isRemoteEnabled' => true]);
            $dompdfSeparateur->loadHtml($htmlSeparateur);
            $dompdfSeparateur->setPaper('A4', 'portrait');
            $dompdfSeparateur->render();
            $separateurPath = tempnam(sys_get_temp_dir(), 'fiche_technique_sep_') . '.pdf';
            file_put_contents($separateurPath, $dompdfSeparateur->output());
            $entries[] = $this->numberedEntry($separateurPath);

            $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
            if ($ext === 'pdf') {
                $tempCopy = tempnam(sys_get_temp_dir(), 'fiche_technique_') . '.pdf';
                copy($fullPath, $tempCopy);
                $entries[] = $this->attachmentEntry($tempCopy, $designation);
            } elseif (in_array($ext, ['png', 'jpg', 'jpeg', 'gif'], true)) {
                $imagePdf = $this->imageToSinglePagePdf($fullPath);
                if ($imagePdf) {
                    $entries[] = $this->attachmentEntry($imagePdf, $designation);
                }
            }
        }

        return $entries;
    }

    /**
     * Numerote les pages d'un rendu Dompdf deja effectue ("Page X / Y"),
     * calcule a partir du nombre reel de pages de CE rendu, centre en bas
     * de chaque page. Utilise uniquement quand le dossier entier tient dans
     * un seul rendu Dompdf (aucune piece jointe a fusionner - voir le
     * branchement "sans fusion" de generatePDF()). Meme mecanisme que
     * resources/views/daos/correctionn.blade.php.
     */
    private function addPageNumbers(Dompdf $dompdf): void
    {
        try {
            $canvas = $dompdf->get_canvas();
            $font = $dompdf->getFontMetrics()->get_font('Helvetica', 'normal');
            $size = 9;
            $pageCount = $canvas->get_page_count();
            $sample = "Page {$pageCount} / {$pageCount}";
            $textWidth = $dompdf->getFontMetrics()->get_text_width($sample, $font, $size);
            $x = ($canvas->get_width() - $textWidth) / 2;
            $y = $canvas->get_height() - 36;
            $canvas->page_text($x, $y, 'Page {PAGE_NUM} / {PAGE_COUNT}', $font, $size, [0, 0, 0]);
        } catch (\Throwable $e) {
            // En cas d'erreur, on continue sans numérotation.
        }
    }

    /**
     * Documents administratifs de l'entreprise (RCCM, IFU, Etats financiers
     * certifiés) pouvant être référencés directement depuis cette pièce du
     * dossier, sans être re-téléversés. Retourne le "type" EntrepriseDocument
     * correspondant (rccm/ifu/etat_financier), ou null si cette pièce n'est
     * pas concernée.
     */
    private function entrepriseDocumentPickerType(DossierDocument $doc): ?string
    {
        $nom = trim(optional($doc->typeDocument)->nom ?? '');

        return match ($nom) {
            'RCCM' => 'rccm',
            "Copie legalisee de l'Identifiant Fiscal Unique (IFU)" => 'ifu',
            'Etats financiers certifiés' => 'etat_financier',
            'Attestation de non-exclusion de la commande publique' => 'non_exclusion',
            'Attestation de catégorisation' => 'categorisation',
            "Attestation d'imposition ou de situation fiscale en cours de validite" => 'regularite_fiscale',
            'Attestation de regularite a la CNSS' => 'cnss',
            'Attestation de non-faillite datant de moins de trois (03) mois' => 'non_faillite',
            "Relevé d'identité bancaire (RIB)" => 'rib',
            "Attestation d'identification de statut" => 'identification_statut',
            'Attestation de bonne fin' => 'bonne_fin',
            'Bon de commande et contrats' => 'bon_commande_contrats',
            "Copie de l'arrêté du Ministre de la Santé portant autorisation d'importation, de détention et de vente des équipements médicaux" => 'arrete_ministre_sante',
            "Pièce d'identité du premier responsable" => 'piece_identite_responsable',
            default => null,
        };
    }

    /**
     * Types de pieces dont la liste de documents entreprise correspondante
     * se renouvelle periodiquement (une seule date de delivrance "en cours"
     * a la fois) : seule la plus recente est reprise automatiquement. Les
     * autres types repetables (RIB, bonne fin, bon de commande...) n'ont pas
     * cette notion d'unicite - toutes les entrees existantes sont reprises.
     */
    private const ENTREPRISE_DOC_TYPES_RENOUVELABLES = [
        'non_exclusion', 'categorisation', 'regularite_fiscale', 'cnss', 'non_faillite',
    ];

    /**
     * Pour une piece de dossier tout juste creee (sommaire standard ou choix
     * initial du wizard) qui correspond a un type de document deja enregistre
     * sur la fiche entreprise (RCCM, IFU, attestations, RIB...), relie
     * automatiquement ce qui existe deja - pour que l'utilisateur n'ait plus
     * qu'a ajouter ce qui manque avant de generer. Les Etats financiers sont
     * volontairement exclus (trop sensibles pour etre choisis au hasard,
     * l'utilisateur doit les selectionner lui-meme). Ne touche jamais une
     * piece deja liee manuellement.
     */
    private function autoLinkEntrepriseDocument(Dossier $dossier, DossierDocument $dossierDocument): void
    {
        if (!empty($dossierDocument->linked_entreprise_document_ids)) {
            return;
        }

        $pickerType = $this->entrepriseDocumentPickerType($dossierDocument);
        if (!$pickerType || $pickerType === 'etat_financier' || !$dossier->entreprise_id) {
            return;
        }

        $query = EntrepriseDocument::where('entreprise_id', $dossier->entreprise_id)->where('type', $pickerType);

        if (in_array($pickerType, self::ENTREPRISE_DOC_TYPES_RENOUVELABLES, true)) {
            $ids = $query->orderByDesc('date_delivrance')->orderByDesc('id')->limit(1)->pluck('id')->all();
        } else {
            $ids = $query->pluck('id')->all();
        }

        if (empty($ids)) {
            return;
        }

        $dossierDocument->linked_entreprise_document_ids = $ids;
        $dossierDocument->save();
    }

    /**
     * Chemins de fichiers temporaires : les pièces de l'entreprise (RCCM,
     * IFU ou Etats financiers) référencées pour cette pièce du dossier,
     * converties si besoin (image -> PDF une page) pour être fusionnées
     * juste après la page de cette pièce. Travaille sur des COPIES
     * temporaires (jamais le fichier original) - l'appelant doit les
     * supprimer une fois la fusion terminée.
     */
    private function entrepriseDocumentPdfPaths(DossierDocument $doc): array
    {
        $paths = [];
        foreach ($doc->entrepriseFichiers() as $fichier) {
            $fullPath = storage_path('app/public/' . ltrim($fichier->chemin_fichier, '/'));
            if (!file_exists($fullPath)) {
                continue;
            }

            $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
            if ($ext === 'pdf') {
                $tempCopy = tempnam(sys_get_temp_dir(), 'entreprise_doc_') . '.pdf';
                copy($fullPath, $tempCopy);
                $paths[] = $tempCopy;
            } elseif (in_array($ext, ['png', 'jpg', 'jpeg', 'gif'], true)) {
                $imagePdf = $this->imageToSinglePagePdf($fullPath);
                if ($imagePdf) {
                    $paths[] = $imagePdf;
                }
            }
        }

        return $paths;
    }

    /**
     * Pre-remplit le Formulaire FIN - 3.1 Situation financiere du dossier a
     * partir de la section Finance de l'entreprise (les trois annees les
     * plus recentes qui y sont renseignees), pour que la personne n'ait
     * plus rien a y saisir. Ne touche jamais un document deja rempli, meme
     * partiellement : des que le contenu n'est plus vide, l'auto-remplissage
     * s'arrete definitivement pour ce dossier.
     */
    private function syncFormulaireFin31FromEntreprise(Dossier $dossier): void
    {
        $finDoc = $dossier->documents()
            ->whereHas('typeDocument', fn ($q) => $q->where('nom', 'Formulaire FIN – 3.1 Situation financière'))
            ->first();
        if (!$finDoc || !empty($finDoc->content)) {
            return;
        }

        $dossier->loadMissing('entreprise');
        $financeData = optional($dossier->entreprise)->finance_data;
        if (empty($financeData) || empty($financeData['year_labels'])) {
            return;
        }

        // Les 3 annees les plus recentes (par valeur numerique), dans
        // l'ordre chronologique croissant pour l'affichage du tableau.
        $years = [];
        foreach ($financeData['year_labels'] as $index => $label) {
            $label = trim((string) $label);
            if ($label === '' || !is_numeric($label)) {
                continue;
            }
            $years[] = ['index' => $index, 'year' => (int) $label];
        }
        if (empty($years)) {
            return;
        }

        usort($years, fn ($a, $b) => $b['year'] <=> $a['year']);
        $selected = array_slice($years, 0, 3);
        usort($selected, fn ($a, $b) => $a['year'] <=> $b['year']);

        $metrics = ['total_actif', 'total_passif', 'patrimoine_net', 'disponibilites', 'engagements', 'recettes_totales', 'benefices_avant_impots'];
        $content = ['year_labels' => []];
        foreach ($metrics as $metric) {
            $content[$metric] = [];
        }

        foreach ($selected as $s) {
            $content['year_labels'][] = (string) $s['year'];
            foreach ($metrics as $metric) {
                $content[$metric][] = (string) ($financeData[$metric][$s['index']] ?? '');
            }
        }

        $json = json_encode($content, JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            $json = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        }

        $finDoc->update(['content' => $json, 'statut' => 'complete']);

        // $finDoc est une instance distincte de celle (si deja chargee) dans
        // $dossier->documents : on invalide ce cache pour que le reste de
        // l'appelant (vue, generation PDF...) voie le contenu a jour.
        if ($dossier->relationLoaded('documents')) {
            $dossier->unsetRelation('documents');
        }
    }

    /**
     * Chiffres d'affaires proposes pour le document "Chiffre d'affaires
     * annuel moyen des activites de services" de CE dossier : derives
     * directement des "Recettes totales (RT)" renseignees dans la fiche de
     * l'ENTREPRISE de ce dossier (section Finance - memes donnees que le
     * Formulaire FIN - 3.1, voir syncFormulaireFin31FromEntreprise()
     * ci-dessus), jamais d'un pool global partage entre TOUTES les
     * entreprises (l'ancien systeme, ChiffreAffaire::whereNull('dossier_id'),
     * melangeait les chiffres de n'importe quelle entreprise, peu importe
     * celle du dossier en cours - trie du plus recent au plus ancien, pour
     * que les 3 premiers elements soient toujours "les 3 dernieres annees").
     */
    private function entrepriseChiffresAffaires(Dossier $dossier): \Illuminate\Support\Collection
    {
        $dossier->loadMissing('entreprise');
        $financeData = optional($dossier->entreprise)->finance_data ?? [];
        $yearLabels = $financeData['year_labels'] ?? [];
        $recettes = $financeData['recettes_totales'] ?? [];

        $entries = collect();
        foreach ($yearLabels as $index => $label) {
            $year = trim((string) $label);
            if (!preg_match('/^\d{4}$/', $year)) {
                continue;
            }

            $montant = trim((string) ($recettes[$index] ?? ''));
            $montant = str_replace([' ', "\u{A0}", ','], ['', '', '.'], $montant);
            if ($montant === '' || !is_numeric($montant)) {
                continue;
            }

            $entries->push((object) [
                'annee' => (int) $year,
                'montant' => (float) $montant,
                'monnaie' => 'F CFA',
            ]);
        }

        return $entries->sortByDesc('annee')->values();
    }

    /**
     * Les beneficiaires effectifs (actionnariat/controle) d'une entreprise ne
     * changent pas d'un dossier a l'autre : des qu'ils sont renseignes une
     * fois sur la fiche entreprise, ce document est pre-rempli automatiquement
     * pour chaque nouveau dossier - sans jamais ecraser un contenu deja
     * saisi (meme logique "une seule fois" que syncFormulaireFin31FromEntreprise()).
     * Le numero d'avis et le destinataire restent propres a chaque dossier
     * (deja pre-remplis depuis le dossier lui-meme dans le formulaire).
     */
    private function syncFormulaireBeneficiairesFromEntreprise(Dossier $dossier): void
    {
        $doc = $dossier->documents()
            ->whereHas('typeDocument', fn ($q) => $q->where('nom', 'Formulaire de divulgation des bénéficiaires effectifs'))
            ->first();
        if (!$doc || !empty($doc->content)) {
            return;
        }

        $dossier->loadMissing('entreprise');
        $data = optional($dossier->entreprise)->beneficiaires_effectifs_data;
        if (empty($data) || empty($data['beneficiaires'])) {
            return;
        }

        $content = [
            'option' => $data['option'] ?? 'i',
            'beneficiaires' => $data['beneficiaires'],
        ];

        $json = json_encode($content, JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            $json = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        }

        $doc->update(['content' => $json]);

        if ($dossier->relationLoaded('documents')) {
            $dossier->unsetRelation('documents');
        }
    }

    /**
     * Afficher le formulaire de création (Étape 1)
     * Choix Public/Privé
     */
    public function create()
    {
        return view('dossiers.create.step1');
    }

    /**
     * Étape 2 : Choix du type de dossier selon la catégorie
     */
    public function step2(Request $request)
    {
        $categorie = $request->validate([
            'categorie' => ['required', 'in:public,prive']
        ])['categorie'];

        // Récupérer les types pour cette catégorie
        $types = TypeDossier::where('categorie', $categorie)->get();

        return view('dossiers.create.step2', compact('categorie', 'types'));
    }

    /**
     * Étape 3 : Infos entreprise (Sélect ou Créer)
     */
    public function step3(Request $request)
    {
        $data = $request->validate([
            'categorie' => ['required', 'in:public,prive'],
            'type_dossier_id' => ['required', 'exists:types_dossiers,id']
        ]);

        $entreprises = Entreprise::all();
        $categorie = $data['categorie'];
        $typeDossierId = $data['type_dossier_id'];
        // La meme entreprise depose quasiment toujours les dossiers : on
        // pre-selectionne celle du dernier dossier cree, pour eviter de la
        // rechoisir a chaque fois.
        $defaultEntrepriseId = Dossier::latest('id')->value('entreprise_id');

        return view('dossiers.create.step3', compact('categorie', 'typeDossierId', 'entreprises', 'defaultEntrepriseId'));
    }

    /**
     * Étape 4 : Infos du dossier (Page de garde)
     */
    public function step4(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->validate([
                'categorie' => ['required', 'in:public,prive'],
                'type_dossier_id' => ['required', 'exists:types_dossiers,id'],
                'entreprise_id' => ['required', 'exists:entreprises,id']
            ]);
        } else {
            // GET request: try to read from query string, else redirect to start
            $data = $request->only(['categorie', 'type_dossier_id', 'entreprise_id']);
            if (empty($data['categorie']) || empty($data['type_dossier_id']) || empty($data['entreprise_id'])) {
                return redirect()->route('dossiers.create')->with('error', 'Veuillez démarrer la création du dossier depuis l’étape 1.');
            }
            // Validate lightly the provided query values
            $validator = \Illuminate\Support\Facades\Validator::make($data, [
                'categorie' => ['required', 'in:public,prive'],
                'type_dossier_id' => ['required', 'exists:types_dossiers,id'],
                'entreprise_id' => ['required', 'exists:entreprises,id']
            ]);
            if ($validator->fails()) {
                return redirect()->route('dossiers.create')->with('error', 'Données de création invalides.');
            }
            $data = $validator->validated();
        }

        // No select list for type_marche anymore; using free text `type_offre`
        return view('dossiers.create.step4', $data);
    }

    /**
     * Étape 5 : Sélection des documents (Sommaire)
     */
    public function step5(Request $request)
    {
        $data = $request->validate([
            'categorie' => ['required', 'in:public,prive'],
            'type_dossier_id' => ['required', 'exists:types_dossiers,id'],
            'entreprise_id' => ['required', 'exists:entreprises,id'],
            'nom_dossier' => ['required', 'string', 'max:255'],
            'objectif' => ['nullable', 'string'],
            'lot' => ['nullable', 'string'],
            // Page de garde (only fields up to année_depot)
            'titre_dossier' => ['nullable', 'string', 'max:255'],
            'type_offre' => ['nullable','string','max:255'],
            'lots' => ['nullable', 'string'],
            'titre_lot' => ['nullable','string','max:255'],
            'autres_details' => ['nullable','string'],
            'mois_depot' => ['nullable','string','max:255'],
            'annee_depot' => ['nullable','digits:4'],
            // new cover fields
            'republique' => ['nullable','string','max:255'],
            'ministere' => ['nullable','string','max:255'],
            'direction' => ['nullable','string','max:255'],
            'services_projet' => ['nullable','string','max:255'],
            'destinataires' => ['nullable','string'],
            'reference_dossier' => ['nullable','string','max:255'],
            'ref' => ['nullable','string','max:255'],
            'date_lancement' => ['nullable','date'],
            'date_soumission' => ['nullable','date'],
            'titre_lot' => ['nullable','string','max:255'],
            'autres_details' => ['nullable','string'],
            'mois_depot' => ['nullable','string','max:255'],
            'annee_depot' => ['nullable','digits:4'],
            'reference_step' => ['nullable','string','max:255'],
            'source_financement' => ['nullable','string','max:255'],
            'gestion' => ['nullable','string','max:255'],
            'imputation_budgetaire' => ['nullable','string','max:255'],
            'accord_pret' => ['nullable','string','max:255'],
        ]);

        // Un dossier ne peut pas avoir 2 fois la meme categorie d'offre
        // (technique / financiere / administrative) pour le meme marche (=
        // meme reference_dossier + meme entreprise). Contrairement au check
        // "meme reference" ci-dessous (qui n'est qu'une suggestion et peut
        // etre ignoree via force_duplicate), celui-ci est bloquant : il n'y a
        // pas de cas legitime ou on voudrait 2 offres techniques pour le
        // meme marche et la meme entreprise. Avant ce controle, seul
        // createOtherOffer() le verifiait ; la creation "manuelle" depuis
        // l'etape 4/5 ne passait pas par la, d'ou des doublons de badges
        // "offre technique" accumules sur la page Liste des dossiers.
        $newCategory = $this->offerCategoryFor($data['type_offre'] ?? null);
        $referenceDossier = trim((string) ($data['reference_dossier'] ?? ''));
        if ($newCategory && $referenceDossier !== '') {
            $existingSameCategory = Dossier::where('reference_dossier', $referenceDossier)
                ->where('entreprise_id', $data['entreprise_id'])
                ->get()
                ->first(fn ($d) => $this->offerCategoryFor($d->type_offre) === $newCategory);

            if ($existingSameCategory) {
                $label = self::OFFER_CATEGORIES[$newCategory]['label'];
                return back()->withInput()->with('error', "Une « {$label} » existe déjà pour ce marché : « {$existingSameCategory->nom_dossier} ». Un dossier ne peut pas avoir deux fois la même catégorie d'offre (technique, financière, administrative). Modifiez ce dossier existant, dupliquez-le, ou utilisez le bouton « + {$label} » depuis sa fiche.");
            }
        }

        // Si une reference identique existe deja sur un autre dossier, on
        // propose de le dupliquer plutot que de repartir de zero, sauf si
        // l'utilisateur a deja confirme vouloir continuer quand meme.
        if (! $request->boolean('force_duplicate')) {
            $existing = $this->findDossierWithSameReference($data['ref'] ?? '', $data['reference_dossier'] ?? '');

            if ($existing) {
                return back()->withInput()->with('duplicate_warning', [
                    'dossier_id' => $existing->id,
                    'nom_dossier' => $existing->nom_dossier,
                    'matched_value' => $existing->ref === trim((string) ($data['ref'] ?? '')) && trim((string) ($data['ref'] ?? '')) !== ''
                        ? $existing->ref
                        : $existing->reference_dossier,
                ]);
            }
        }

        // Créer le dossier provisoirement (statut = en_cours)
        $dossier = Dossier::create([
            'type_dossier_id' => $data['type_dossier_id'],
            'entreprise_id' => $data['entreprise_id'],
            'nom_dossier' => $data['nom_dossier'],
            'objectif' => $data['objectif'] ?? null,
            'lot' => $data['lot'] ?? null,
            // Page de garde
            'titre_dossier' => $data['titre_dossier'] ?? null,
            'type_offre' => $data['type_offre'] ?? null,
            'lots' => $data['lots'] ?? ($data['lot'] ?? null),
            'titre_lot' => $data['titre_lot'] ?? null,
            'autres_details' => $data['autres_details'] ?? null,
            'mois_depot' => $data['mois_depot'] ?? null,
            'annee_depot' => $data['annee_depot'] ?? null,
            // new cover fields
            'republique' => $data['republique'] ?? null,
            'ministere' => $data['ministere'] ?? null,
            'direction' => $data['direction'] ?? null,
            'services_projet' => $data['services_projet'] ?? null,
            'destinataires' => $data['destinataires'] ?? null,
            'reference_dossier' => $data['reference_dossier'] ?? null,
            'ref' => $data['ref'] ?? null,
            'date_lancement' => $data['date_lancement'] ?? null,
            'date_soumission' => $data['date_soumission'] ?? null,
            'titre_lot' => $data['titre_lot'] ?? null,
            'types_offres' => $data['types_offres'] ?? null,
            'autres_details' => $data['autres_details'] ?? null,
            'mois_depot' => $data['mois_depot'] ?? null,
            'annee_depot' => $data['annee_depot'] ?? null,
            'reference_step' => $data['reference_step'] ?? null,
            'source_financement' => $data['source_financement'] ?? null,
            'gestion' => $data['gestion'] ?? null,
            'imputation_budgetaire' => $data['imputation_budgetaire'] ?? null,
            'accord_pret' => $data['accord_pret'] ?? null,
            'public_prive' => $data['categorie'],
            'page_garde_path' => null,
            'statut' => 'en_cours',
        ]);

        $dossier->utilisateurs()->attach(auth()->id());
        $this->logChange($dossier, 'dossier_cree', "Dossier « {$dossier->nom_dossier} » créé.");

        // no file/page_garde handling here (removed)
        $pieceNames = $this->getDocumentPieceNames();

        $bordereauNames = [
            'Bordereau prix unitaire',
            'Bordereau des prix pour les fournitures à importer',
            'Bordereau des prix des fournitures, déjà importées',
            'Bordereau des prix pour les fournitures fabriquées au Bénin',
            'Bordereau des prix et calendrier d\'exécution des services connexes',
            'Listes des services connexes et calendrier de réalisation',
            'Listes des Fournitures et Calendrier de livraison',
            'Cadres de sous détails des prix unitaire',
            'Programme d\'activités',
            'Méthodes d\'exécution',
            'Calendrier d\'exécution',
            'Description technique des fournitures/services',
            'Liste de Matériel',
        ];

        foreach ($pieceNames as $name) {
            $type = in_array($name, $bordereauNames, true) ? 'bordereau' : 'formulaire';
            TypeDocument::firstOrCreate(['nom' => $name], ['type_formulaire' => $type]);
        }

        $documentsByName = TypeDocument::whereIn('nom', $pieceNames)->get()->keyBy('nom');
        $documents = collect($pieceNames)
            ->map(fn ($name) => $documentsByName->get($name))
            ->filter();

        $signataires = Signataire::orderBy('nom')->orderBy('prenom')->get();

        return view('dossiers.create.step5', compact('dossier', 'documents', 'signataires'));
    }

    /**
     * Étape 6 : Créer les DossierDocument et afficher le formulaire de remplissage
     */
    public function step6(Request $request, $dossierId)
    {
        $pieceNames = $this->getDocumentPieceNames();

        $dossier = Dossier::findOrFail($dossierId);
        $dossier->load(['documents.typeDocument', 'documents.bordereau.lignes', 'entreprise']);

        $sessionKeyDocs = 'step6_documents_' . $dossierId;
        $sessionKeySignataire = 'step6_signataire_' . $dossierId;

        $selectedDocumentIds = [];
        $signataireId = null;

        if ($request->isMethod('post')) {
            $data = $request->validate([
                'documents' => ['required', 'array', 'min:1'],
                'documents.*' => ['exists:types_documents,id'],
                'signataire_id' => ['nullable', 'exists:signataires,id'],
            ]);

            $selectedDocumentIds = $data['documents'];
            $signataireId = $data['signataire_id'] ?? null;

            Session::put($sessionKeyDocs, $selectedDocumentIds);
            Session::put($sessionKeySignataire, $signataireId);
        } else {
            $selectedDocumentIds = Session::get($sessionKeyDocs, []);
            $signataireId = Session::get($sessionKeySignataire, null);

            if (empty($selectedDocumentIds)) {
                return redirect()->route('dossiers.show', $dossier->id)
                    ->with('error', 'Aucun document sélectionné pour l’étape 6.');
            }

            $data = ['documents' => $selectedDocumentIds, 'signataire_id' => $signataireId];
        }

        $selectedTypes = TypeDocument::whereIn('id', $selectedDocumentIds)->get()->keyBy('id');

        $invalidSelection = $selectedTypes->contains(function ($doc) use ($pieceNames) {
            return !in_array($doc->nom, $pieceNames, true);
        });

        if ($invalidSelection) {
            return redirect()->route('dossiers.show', $dossier->id)->with('error', 'Sélection de pièces invalide.');
        }

        $autoCompleteNames = [];
        $orderedSelected = collect($selectedDocumentIds)
            ->map(fn ($id) => $selectedTypes->get($id))
            ->filter();

        $uploadQueue = $orderedSelected
            ->filter(fn ($doc) => !in_array($doc->nom, $autoCompleteNames, true))
            ->values();

        foreach ($orderedSelected as $doc) {
            $dossierDocument = DossierDocument::firstOrNew([
                'dossier_id' => $dossier->id,
                'type_document_id' => $doc->id,
            ]);

            $isNew = ! $dossierDocument->exists;
            if ($isNew) {
                $dossierDocument->ordre = (int) $dossier->documents()->max('ordre') + 1;
                $dossierDocument->statut = 'vide';
            }
            $dossierDocument->save();

            if (in_array($doc->nom, $autoCompleteNames, true)) {
                $dossierDocument->update(['statut' => 'complete']);
            }

            if (trim($doc->nom) === "RCCM") {
                $this->attachEntrepriseRegistreIfMissing($dossier, $dossierDocument);
            }

            if ($isNew) {
                $this->autoLinkEntrepriseDocument($dossier, $dossierDocument);
            }
        }

        // Recharge les documents (et leurs fichiers) créés/mis à jour ci-dessus,
        // car $dossier->documents a été chargé avant la boucle et n'en tiendrait pas compte.
        $dossier->load(['documents.typeDocument', 'documents.bordereau.lignes', 'documents.fichiers', 'entreprise']);

        if ($uploadQueue->isEmpty()) {
            $dossier->statut = 'termine';
            $dossier->save();
            Session::forget($sessionKeyDocs);
            Session::forget($sessionKeySignataire);
            return redirect()->route('dossiers.show', $dossier->id)->with('success', 'Aucune pièce à téléverser. Dossier terminé.');
        }

        $requestedIndex = (int) $request->query('current_index', $request->input('current_index', 0));
        $currentIndex = max(0, min($requestedIndex, $uploadQueue->count() - 1));

        // Lorsqu'on soumet le formulaire d'une pièce précise (ex: "Modifier" sur une pièce
        // déjà complète), current_document_id désigne sans ambiguïté le document à traiter :
        // on ne doit pas sauter les pièces déjà complètes ni court-circuiter vers "terminé",
        // sinon les données soumises pour cette pièce ne sont jamais enregistrées.
        if ($request->input('upload_step') !== '1') {
            // Skip already complete documents
            while ($currentIndex < $uploadQueue->count()) {
                $docToCheck = $uploadQueue->get($currentIndex);
                $docRec = DossierDocument::where('dossier_id', $dossier->id)
                    ->where('type_document_id', $docToCheck->id)
                    ->first();
                if ($docRec && $docRec->statut === 'complete') {
                    $currentIndex++;
                    continue;
                }
                break;
            }

            if ($currentIndex >= $uploadQueue->count()) {
                $dossier->statut = 'termine';
                $dossier->save();
                Session::forget($sessionKeyDocs);
                Session::forget($sessionKeySignataire);
                return redirect()->route('dossiers.show', $dossier->id)->with('success', 'Pièces jointes enregistrées — dossier terminé.');
            }
        }

        $currentDocument = $uploadQueue->get($currentIndex);

        if ($request->input('upload_step') === '1') {
            $currentDocumentId = $request->validate([
                'current_document_id' => ['required', 'exists:types_documents,id']
            ])['current_document_id'];

            $currentDocument = $uploadQueue->firstWhere('id', $currentDocumentId);
            if (!$currentDocument) {
                return redirect()->route('dossiers.show', $dossier->id)->with('error', 'Pièce sélectionnée invalide.');
            }

            $currentIndex = $uploadQueue->search(fn ($doc) => $doc->id === $currentDocumentId);
            if ($currentIndex === false) {
                $currentIndex = 0;
            }

            $dossierDocument = DossierDocument::firstOrNew([
                'dossier_id' => $dossier->id,
                'type_document_id' => $currentDocumentId,
            ]);
            if (! $dossierDocument->exists) {
                $dossierDocument->ordre = (int) $dossier->documents()->max('ordre') + 1;
                $dossierDocument->statut = 'vide';
            }
            $dossierDocument->save();

            if (\App\Models\TypeDocument::isReferenceLineName($currentDocument->nom) || $currentDocument->type_formulaire === 'libre') {
                $refModel = $request->input('reference_model');
                if (in_array($refModel, \App\Support\ReferenceLine::MODELS, true)) {
                    $dossierDocument->update(['reference_model' => $refModel]);
                }
            }

            if (trim($currentDocument->nom) === "Déclaration de garantie d'offre") {
                $vals = $request->validate([
                    'societe' => ['required','string','max:255'],
                    'date' => ['required','date'],
                    'reference' => ['nullable','string','max:255'],
                    'template_id' => ['nullable','exists:templates,id'],
                ]);

                // Nom du déclarant / fonction ne sont plus saisis dans le formulaire :
                // ils sont déduits automatiquement du signataire du dossier, comme pour
                // le Formulaire de qualification.
                $garantieSignataire = $this->resolveQualificationSignataire($dossier);
                $vals['declarant'] = $garantieSignataire
                    ? trim($garantieSignataire->nom . ' ' . ($garantieSignataire->prenom ?? ''))
                    : trim(optional($dossier->entreprise)->responsable ?? '');
                $vals['fonction'] = $garantieSignataire
                    ? ($garantieSignataire->fonction ?: (optional($dossier->entreprise)->fonction_responsable ?? ''))
                    : (optional($dossier->entreprise)->fonction_responsable ?? '');

                // Enregistré pour que le formulaire réaffiche les valeurs saisies quand on
                // revient modifier ce document (auparavant seul un PDF figé était généré,
                // sans qu'aucune donnée ne soit conservée pour préremplir le formulaire).
                $dossierDocument->update(['content' => json_encode($vals, JSON_UNESCAPED_UNICODE)]);

                try {
                    // On ignore le modèle sélectionné pour éviter que son contenu ne s'infiltre
                    // dans le document final. Le rendu de la déclaration de garantie d'offre
                    // reste basé sur la vue standard et inchangé.
                    $html = view('documents.declaration_pdf', array_merge($vals, ['signatureDataUri' => null, 'entreprise' => $dossier->entreprise]))->render();
                    $dompdf = new Dompdf(['isRemoteEnabled' => true]);
                    $dompdf->loadHtml($html);
                    $dompdf->setPaper('A4', 'portrait');
                    $dompdf->render();

                    $output = $dompdf->output();
                    $filename = 'dossiers/documents/dossier_' . $dossier->id . '_declaration_' . time() . '.pdf';
                    Storage::disk('public')->put($filename, $output);

                    DocumentFichier::create([
                        'dossier_document_id' => $dossierDocument->id,
                        'chemin_fichier' => $filename,
                        'utilisateur_id' => auth()->id(),
                    ]);

                    $dossierDocument->update(['statut' => 'complete']);
                } catch (\Throwable $e) {
                    // ignore and continue with normal flow
                }
            }

            if (trim($currentDocument->nom) === 'Formulaire de qualification') {
                $vals = $request->validate([
                    'nombre_marches' => ['nullable','integer','min:0','max:20'],
                    'marches' => ['nullable','array'],
                    'marches.*.annee' => ['nullable','string','max:10'],
                    'marches.*.nom' => ['nullable','string','max:1000'],
                    'marches.*.reference' => ['nullable','string','max:255'],
                ]);

                // Champs déduits automatiquement du dossier (plus de saisie manuelle
                // de la société, la date, le déclarant, la fonction ou la référence).
                $qualificationEntreprise = $dossier->entreprise;
                $qualificationSignataire = $this->resolveQualificationSignataire($dossier);
                $vals['societe'] = optional($qualificationEntreprise)->nom ?? '';
                $vals['date'] = now()->format('Y-m-d');
                $vals['declarant'] = $qualificationSignataire
                    ? trim($qualificationSignataire->nom . ' ' . ($qualificationSignataire->prenom ?? ''))
                    : trim(optional($qualificationEntreprise)->responsable ?? '');
                $vals['fonction'] = $qualificationSignataire
                    ? ($qualificationSignataire->fonction ?: (optional($qualificationEntreprise)->fonction_responsable ?? ''))
                    : (optional($qualificationEntreprise)->fonction_responsable ?? '');
                $vals['reference'] = $dossier->reference_dossier ?? $dossier->ref ?? '';

                // Enregistré pour permettre au PDF final du dossier (dossiers.pdf) de
                // ré-afficher ce contenu, de la même façon que la déclaration de garantie.
                $qualificationContent = json_encode($vals, JSON_UNESCAPED_UNICODE);
                if ($qualificationContent === false) {
                    $qualificationContent = json_encode($vals, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
                }
                $dossierDocument->update(['content' => $qualificationContent]);

                $this->regenerateQualificationPdf($dossier, $dossierDocument, $vals);
                $this->syncQualificationMarchesToExp41($dossier, $vals['marches'] ?? []);
            }

            if (trim($currentDocument->nom) === 'Tableau de résumé des bordereaux de prix') {
                $values = $request->validate([
                    'tableau_resume' => ['required', 'array'],
                    'tableau_resume.a' => ['nullable', 'numeric'],
                    'tableau_resume.b' => ['nullable', 'numeric'],
                    'tableau_resume.c' => ['nullable', 'numeric'],
                    'tableau_resume.d' => ['nullable', 'numeric'],
                    'tableau_resume.e' => ['nullable', 'numeric'],
                    'tableau_resume.f' => ['nullable', 'numeric'],
                    'tableau_resume.g' => ['nullable', 'numeric'],
                    'tableau_resume.h' => ['nullable', 'numeric'],
                    'tableau_resume.i' => ['nullable', 'numeric'],
                ]);

                $data = $values['tableau_resume'] ?? [];
                $a = isset($data['a']) && is_numeric($data['a']) ? (float) $data['a'] : 0.0;
                $d = isset($data['d']) && is_numeric($data['d']) ? (float) $data['d'] : 0.0;

                $data['a'] = $a;
                $data['b'] = round($a * 0.18, 2);
                $data['c'] = round($a + $data['b'], 2);
                $data['d'] = $d;
                $data['e'] = round($d * 0.18, 2);
                $data['f'] = round($d + $data['e'], 2);
                $data['g'] = round($a + $d, 2);
                $data['h'] = round($data['g'] * 0.18, 2);
                $data['i'] = round($data['g'] + $data['h'], 2);

                $content = json_encode($data, JSON_UNESCAPED_UNICODE);
                if ($content === false) {
                    $content = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
                }

                $dossierDocument->update([
                    'content' => $content,
                    'statut' => 'complete',
                ]);
            }

            if (trim($currentDocument->nom) === 'Chiffre d\'affaires annuel moyen des activités de services') {
                $values = $request->validate([
                    'years' => ['required', 'array', 'min:1'],
                    'years.*' => ['integer'],
                ]);

                // Créer des enregistrements de chiffre d'affaires pour le dossier à partir des années sélectionnées,
                // en reprenant les montants des Recettes totales (RT) de la fiche de CETTE entreprise (jamais du
                // pool global partagé entre entreprises - voir entrepriseChiffresAffaires()).
                // Le rendu final du PDF principal utilise ces chiffres à partir de $dossier->chiffresAffaires.
                $selectedEntries = $this->entrepriseChiffresAffaires($dossier)
                    ->whereIn('annee', $values['years'])
                    ->sortBy('annee')
                    ->values();

                \App\Models\ChiffreAffaire::where('dossier_id', $dossier->id)->delete();
                foreach ($selectedEntries as $entry) {
                    \App\Models\ChiffreAffaire::create([
                        'dossier_id' => $dossier->id,
                        'annee' => $entry->annee,
                        'montant' => $entry->montant,
                        'monnaie' => $entry->monnaie,
                    ]);
                }

                if ($selectedEntries->isNotEmpty()) {
                    $dossierDocument->update(['statut' => 'complete']);
                }
            }

            if (trim($currentDocument->nom) === 'Engagement du soumissionnaire à respecter le code d\'éthique et de déontologie') {
                $values = $request->validate([
                    'content' => ['required', 'string'],
                ]);

                $dossierDocument->update([
                    'content' => $values['content'],
                    'statut' => 'complete',
                ]);
            }

            if ($currentDocument->type_formulaire === 'libre') {
                $values = $request->validate([
                    'texte' => ['nullable', 'string'],
                    'tableaux' => ['nullable', 'array'],
                    'tableaux.*.titre' => ['nullable', 'string', 'max:255'],
                    'tableaux.*.colonnes' => ['nullable', 'array'],
                    'tableaux.*.colonnes.*' => ['nullable', 'string', 'max:255'],
                    'tableaux.*.lignes' => ['nullable', 'array'],
                    'tableaux.*.lignes.*' => ['nullable', 'array'],
                    'tableaux.*.lignes.*.*' => ['nullable', 'string', 'max:1000'],
                    'paysage' => ['nullable', 'boolean'],
                ]);

                $tableaux = [];
                foreach ($values['tableaux'] ?? [] as $tableau) {
                    $colonnes = array_values(array_filter($tableau['colonnes'] ?? [], fn ($c) => trim($c ?? '') !== ''));
                    if (empty($colonnes)) {
                        continue;
                    }

                    $lignes = [];
                    foreach ($tableau['lignes'] ?? [] as $ligne) {
                        $row = [];
                        for ($i = 0; $i < count($colonnes); $i++) {
                            $row[] = trim($ligne[$i] ?? '');
                        }
                        if (trim(implode('', $row)) !== '') {
                            $lignes[] = $row;
                        }
                    }

                    $tableaux[] = [
                        'titre' => trim($tableau['titre'] ?? ''),
                        'colonnes' => $colonnes,
                        'lignes' => $lignes,
                    ];
                }

                $dossierDocument->update([
                    'content' => json_encode([
                        'texte' => $values['texte'] ?? '',
                        'tableaux' => $tableaux,
                    ], JSON_UNESCAPED_UNICODE),
                    'paysage' => $request->boolean('paysage'),
                    'statut' => 'complete',
                ]);
            }

            if (trim($currentDocument->nom) === 'Liste du personnel affecté à l\'exécution du marché') {
                $personnel = $request->input('personnel', []);
                $savedPersonnel = [];

                if (is_array($personnel)) {
                    foreach ($personnel as $row) {
                        $poste = trim($row['poste'] ?? '');
                        $nom = trim($row['nom'] ?? '');
                        if ($poste === '' && $nom === '') {
                            continue;
                        }
                        $savedPersonnel[] = ['poste' => $poste, 'nom' => $nom];
                    }
                }

                if (!empty($savedPersonnel)) {
                    $dossierDocument->update([
                        'content' => json_encode($savedPersonnel, JSON_UNESCAPED_UNICODE),
                        'statut' => 'complete',
                    ]);
                    $this->syncFormulairePerRecords($dossier);
                }
            }

            if (trim($currentDocument->nom) === self::FORMULAIRE_PER_NOM) {
                $this->syncFormulairePerRecords($dossier);

                $validated = $request->validate([
                    'per' => ['nullable', 'array'],
                    'per.*.nom_candidat' => ['nullable', 'string', 'max:255'],
                    'per.*.date_naissance' => ['nullable', 'date'],
                    'per.*.qualifications' => ['nullable', 'string', 'max:4000'],
                    'per.*.nom_employeur' => ['nullable', 'string', 'max:255'],
                    'per.*.emploi_tenu' => ['nullable', 'string', 'max:255'],
                    'per.*.adresse_employeur' => ['nullable', 'string', 'max:4000'],
                    'per.*.telephone' => ['nullable', 'string', 'max:20'],
                    'per.*.telecopie' => ['nullable', 'string', 'max:20'],
                    'per.*.email' => ['nullable', 'email', 'max:255'],
                    'per.*.contact_personnel' => ['nullable', 'string', 'max:255'],
                    'per.*.nombre_annees_employeur' => ['nullable', 'integer', 'min:0'],
                    'per.*.lieu_signature' => ['nullable', 'string', 'max:255'],
                    'per.*.experiences' => ['nullable', 'string'],
                    'per.*.delete_fichier_ids' => ['nullable', 'array'],
                    'per.*.delete_fichier_ids.*' => ['integer'],
                    'per.*.exclude_cv_fichier_ids' => ['nullable', 'array'],
                    'per.*.exclude_cv_fichier_ids.*' => ['integer'],
                    'per_fichiers' => ['nullable', 'array'],
                ]);

                foreach ($validated['per'] ?? [] as $id => $data) {
                    $deleteFichierIds = $data['delete_fichier_ids'] ?? [];
                    unset($data['delete_fichier_ids']);

                    // Pieces du CV de l'entreprise que l'utilisateur ne veut
                    // pas inclure pour CE dossier (ne supprime rien cote
                    // entreprise, juste exclu de ce Formulaire PER).
                    $data['excluded_cv_fichier_ids'] = array_map('intval', $data['exclude_cv_fichier_ids'] ?? []);
                    unset($data['exclude_cv_fichier_ids']);

                    if (array_key_exists('experiences', $data)) {
                        $decodedExp = json_decode((string) $data['experiences'], true);
                        $data['experiences'] = is_array($decodedExp) ? $decodedExp : [];
                    }

                    $formulairePer = FormulairePER::where('dossier_id', $dossier->id)
                        ->whereNotNull('personnel_position')
                        ->whereKey($id)
                        ->first();

                    if (!$formulairePer) {
                        continue;
                    }

                    $formulairePer->update($data);

                    foreach ($deleteFichierIds as $fichierId) {
                        $fichier = FormulairePerFichier::where('formulaire_per_id', $formulairePer->id)->find($fichierId);
                        if ($fichier) {
                            Storage::disk('public')->delete($fichier->chemin_fichier);
                            $fichier->delete();
                        }
                    }

                    foreach ($request->file("per_fichiers.$id", []) as $uploaded) {
                        if (!$uploaded) {
                            continue;
                        }
                        $chemin = $uploaded->store('formulaire_per/pieces', 'public');
                        FormulairePerFichier::create([
                            'formulaire_per_id' => $formulairePer->id,
                            'chemin_fichier' => $chemin,
                            'nom_original' => $uploaded->getClientOriginalName(),
                            'utilisateur_id' => auth()->id(),
                        ]);
                    }
                }

                $dossierDocument->update(['statut' => 'complete']);
            }

            if (trim($currentDocument->nom) === 'Formulaire de divulgation des bénéficiaires effectifs') {
                $vals = $request->validate([
                    'numero_avis' => ['nullable', 'string', 'max:255'],
                    'destinataire' => ['nullable', 'string', 'max:255'],
                    'option' => ['required', 'in:i,ii'],
                    'beneficiaires' => ['nullable', 'array'],
                    'beneficiaires.*.identite' => ['nullable', 'string', 'max:1000'],
                    'beneficiaires.*.action_25' => ['nullable', 'in:Oui,Non'],
                    'beneficiaires.*.vote_25' => ['nullable', 'in:Oui,Non'],
                    'beneficiaires.*.pouvoir_nomination' => ['nullable', 'in:Oui,Non'],
                ]);

                $beneficiaires = [];
                foreach ($vals['beneficiaires'] ?? [] as $row) {
                    $identite = trim($row['identite'] ?? '');
                    if ($identite === '') {
                        continue;
                    }
                    $beneficiaires[] = [
                        'identite' => $identite,
                        'action_25' => $row['action_25'] ?? '',
                        'vote_25' => $row['vote_25'] ?? '',
                        'pouvoir_nomination' => $row['pouvoir_nomination'] ?? '',
                    ];
                }
                $vals['beneficiaires'] = $beneficiaires;

                $dossierDocument->update([
                    'content' => json_encode($vals, JSON_UNESCAPED_UNICODE),
                    'statut' => 'complete',
                ]);
            }

            if (trim($currentDocument->nom) === 'Formulaire ELI – 1.1 : Formulaire de renseignements sur le candidat') {
                $rules = [
                    'use_existing_info' => ['required', 'in:yes,no'],
                ];

                if ($request->input('use_existing_info') === 'no') {
                    $rules = array_merge($rules, [
                        'nom_candidat' => ['required', 'string', 'max:255'],
                        'groupement_membres' => ['nullable', 'string'],
                        'pays_candidat' => ['required', 'string', 'max:255'],
                        'identification_nationale' => ['nullable', 'string', 'max:255'],
                        'annee_enregistrement' => ['nullable', 'digits:4'],
                        'adresse_officielle' => ['nullable', 'string'],
                        'nom_representant' => ['required', 'string', 'max:255'],
                        'fonction_representant' => ['nullable', 'string', 'max:255'],
                        'adresse_representant' => ['nullable', 'string'],
                        'telephone_representant' => ['nullable', 'string', 'max:255'],
                        'email_representant' => ['nullable', 'email', 'max:255'],
                    ]);
                }

                $values = $request->validate($rules);

                if ($values['use_existing_info'] === 'yes') {
                    $entreprise = $dossier->entreprise;
                    $pdfData = [
                        'nom_candidat' => $entreprise->nom,
                        'groupement_membres' => '',
                        'pays_candidat' => $entreprise->pays ?? '',
                        'identification_nationale' => $entreprise->rccm ?? '',
                        'annee_enregistrement' => $entreprise->annee_enregistrement ?? '',
                        'adresse_officielle' => $entreprise->adresse_officielle ?? $entreprise->adresse ?? '',
                        'nom_representant' => $entreprise->responsable ?? '',
                        'fonction_representant' => $entreprise->fonction_responsable ?? '',
                        'adresse_representant' => '',
                        'telephone_representant' => $entreprise->telephone ?? '',
                        'email_representant' => $entreprise->email ?? '',
                    ];
                } else {
                    $pdfData = [
                        'nom_candidat' => $values['nom_candidat'],
                        'groupement_membres' => $values['groupement_membres'] ?? '',
                        'pays_candidat' => $values['pays_candidat'],
                        'identification_nationale' => $values['identification_nationale'] ?? '',
                        'annee_enregistrement' => $values['annee_enregistrement'] ?? '',
                        'adresse_officielle' => $values['adresse_officielle'] ?? '',
                        'nom_representant' => $values['nom_representant'],
                        'fonction_representant' => $values['fonction_representant'] ?? '',
                        'adresse_representant' => $values['adresse_representant'] ?? '',
                        'telephone_representant' => $values['telephone_representant'] ?? '',
                        'email_representant' => $values['email_representant'] ?? '',
                    ];
                }

                try {
                    $pdfData['dossier'] = $dossier;
                    $pdfData['entreprise'] = $dossier->entreprise;
                    $pdfData['drp_number'] = $dossier->ref ?? 'S_DLCSSA_' . str_pad($dossier->id, 6, '0', STR_PAD_LEFT);

                    $html = view('documents.formulaire_renseignements_candidat_pdf', $pdfData)->render();
                    $dompdf = new Dompdf(['isRemoteEnabled' => true]);
                    $dompdf->loadHtml($html);
                    $dompdf->setPaper('A4', 'portrait');
                    $dompdf->render();

                    $output = $dompdf->output();
                    $filename = 'dossiers/documents/dossier_' . $dossier->id . '_formulaire_candidat_' . time() . '.pdf';
                    Storage::disk('public')->put($filename, $output);

                    DocumentFichier::create([
                        'dossier_document_id' => $dossierDocument->id,
                        'chemin_fichier' => $filename,
                        'utilisateur_id' => auth()->id(),
                    ]);

                    $dossierDocument->update(['statut' => 'complete']);
                } catch (\Throwable $e) {
                    // ignore and continue with normal flow
                }
            }

            if (trim($currentDocument->nom) === 'Formulaire FIN – 3.1 Situation financière') {
                $values = $request->validate([
                    'year_labels' => ['nullable', 'array'],
                    'year_labels.*' => ['nullable', 'string', 'max:255'],
                    'total_actif' => ['nullable', 'array'],
                    'total_actif.*' => ['nullable', 'string', 'max:255'],
                    'total_passif' => ['nullable', 'array'],
                    'total_passif.*' => ['nullable', 'string', 'max:255'],
                    'patrimoine_net' => ['nullable', 'array'],
                    'patrimoine_net.*' => ['nullable', 'string', 'max:255'],
                    'disponibilites' => ['nullable', 'array'],
                    'disponibilites.*' => ['nullable', 'string', 'max:255'],
                    'engagements' => ['nullable', 'array'],
                    'engagements.*' => ['nullable', 'string', 'max:255'],
                    'recettes_totales' => ['nullable', 'array'],
                    'recettes_totales.*' => ['nullable', 'string', 'max:255'],
                    'benefices_avant_impots' => ['nullable', 'array'],
                    'benefices_avant_impots.*' => ['nullable', 'string', 'max:255'],
                    'commentaire_complementaire' => ['nullable', 'string', 'max:4000'],
                ]);

                $content = json_encode($values, JSON_UNESCAPED_UNICODE);
                if ($content === false) {
                    $content = json_encode($values, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
                }

                $dossierDocument->update([
                    'content' => $content,
                    'statut' => 'complete',
                ]);
            }

            if (trim($currentDocument->nom) === 'Formulaire FIN 3.3') {
                $values = $request->validate([
                    'source_financement' => ['nullable', 'array'],
                    'source_financement.*' => ['nullable', 'string', 'max:1000'],
                    'montant_fcfa' => ['nullable', 'array'],
                    'montant_fcfa.*' => ['nullable', 'string', 'max:255'],
                ]);

                $content = json_encode($values, JSON_UNESCAPED_UNICODE);
                if ($content === false) {
                    $content = json_encode($values, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
                }

                // Remove rows that are fully empty (both source and montant empty)
                if (!empty($values['source_financement']) && is_array($values['source_financement'])) {
                    $filteredSources = [];
                    $filteredMontants = [];
                    $max = max(count($values['source_financement']), count($values['montant_fcfa'] ?? []));
                    for ($i = 0; $i < $max; $i++) {
                        $s = isset($values['source_financement'][$i]) ? trim((string)$values['source_financement'][$i]) : '';
                        $m = isset($values['montant_fcfa'][$i]) ? trim((string)$values['montant_fcfa'][$i]) : '';
                        if ($s !== '' || $m !== '') {
                            $filteredSources[] = $s;
                            $filteredMontants[] = $m;
                        }
                    }
                    $values['source_financement'] = $filteredSources;
                    $values['montant_fcfa'] = $filteredMontants;
                }

                $dossierDocument->update([
                    'content' => $content,
                    'statut' => 'complete',
                ]);
            }

            if (trim($currentDocument->nom) === 'Formulaire MTC/FIN – 3.5 : Marchés de fournitures/services en cours') {
                $values = $request->validate([
                    'intitule' => ['nullable', 'array'],
                    'intitule.*' => ['nullable', 'string', 'max:2000'],
                    'autorite_contact' => ['nullable', 'array'],
                    'autorite_contact.*' => ['nullable', 'string', 'max:2000'],
                    'valeur_restante' => ['nullable', 'array'],
                    'valeur_restante.*' => ['nullable', 'string', 'max:255'],
                    'date_achevement' => ['nullable', 'array'],
                    'date_achevement.*' => ['nullable', 'string', 'max:255'],
                    'montant_mensuel' => ['nullable', 'array'],
                    'montant_mensuel.*' => ['nullable', 'string', 'max:255'],
                ]);

                // Supprimer les lignes entièrement vides
                if (!empty($values['intitule']) && is_array($values['intitule'])) {
                    $filteredIntitules = [];
                    $filteredAutorites = [];
                    $filteredValeurs = [];
                    $filteredDates = [];
                    $filteredMontants = [];
                    $max = max(
                        count($values['intitule']),
                        count($values['autorite_contact'] ?? []),
                        count($values['valeur_restante'] ?? []),
                        count($values['date_achevement'] ?? []),
                        count($values['montant_mensuel'] ?? [])
                    );
                    for ($i = 0; $i < $max; $i++) {
                        $int = isset($values['intitule'][$i]) ? trim((string)$values['intitule'][$i]) : '';
                        $aut = isset($values['autorite_contact'][$i]) ? trim((string)$values['autorite_contact'][$i]) : '';
                        $val = isset($values['valeur_restante'][$i]) ? trim((string)$values['valeur_restante'][$i]) : '';
                        $date = isset($values['date_achevement'][$i]) ? trim((string)$values['date_achevement'][$i]) : '';
                        $mont = isset($values['montant_mensuel'][$i]) ? trim((string)$values['montant_mensuel'][$i]) : '';
                        if ($int !== '' || $aut !== '' || $val !== '' || $date !== '' || $mont !== '') {
                            $filteredIntitules[] = $int;
                            $filteredAutorites[] = $aut;
                            $filteredValeurs[] = $val;
                            $filteredDates[] = $date;
                            $filteredMontants[] = $mont;
                        }
                    }
                    $values['intitule'] = $filteredIntitules;
                    $values['autorite_contact'] = $filteredAutorites;
                    $values['valeur_restante'] = $filteredValeurs;
                    $values['date_achevement'] = $filteredDates;
                    $values['montant_mensuel'] = $filteredMontants;
                }

                $content = json_encode($values, JSON_UNESCAPED_UNICODE);
                if ($content === false) {
                    $content = json_encode($values, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
                }

                $dossierDocument->update([
                    'content' => $content,
                    'statut' => 'complete',
                ]);
            }

            if (trim($currentDocument->nom) === 'Formulaire EXP – 4.1 : Expérience générale de fournitures/services') {
                $values = $request->validate([
                    'mois_depart' => ['nullable', 'array'],
                    'mois_depart.*' => ['nullable', 'string', 'max:50'],
                    'mois_final' => ['nullable', 'array'],
                    'mois_final.*' => ['nullable', 'string', 'max:50'],
                    'identification' => ['nullable', 'array'],
                    'identification.*' => ['nullable', 'string', 'max:4000'],
                    'role_candidat' => ['nullable', 'array'],
                    'role_candidat.*' => ['nullable', 'string', 'max:1000'],
                ]);

                // Filter out fully empty rows
                if (!empty($values['identification']) && is_array($values['identification'])) {
                    $filteredDepart = [];
                    $filteredFinal = [];
                    $filteredIdent = [];
                    $filteredRole = [];
                    $max = max(
                        count($values['mois_depart'] ?? []),
                        count($values['mois_final'] ?? []),
                        count($values['identification'] ?? []),
                        count($values['role_candidat'] ?? [])
                    );
                    for ($i = 0; $i < $max; $i++) {
                        $d = isset($values['mois_depart'][$i]) ? trim((string)$values['mois_depart'][$i]) : '';
                        $f = isset($values['mois_final'][$i]) ? trim((string)$values['mois_final'][$i]) : '';
                        $ident = isset($values['identification'][$i]) ? trim((string)$values['identification'][$i]) : '';
                        $role = isset($values['role_candidat'][$i]) ? trim((string)$values['role_candidat'][$i]) : '';
                        if ($d !== '' || $f !== '' || $ident !== '' || $role !== '') {
                            $filteredDepart[] = $d;
                            $filteredFinal[] = $f;
                            $filteredIdent[] = $ident;
                            $filteredRole[] = $role;
                        }
                    }
                    $values['mois_depart'] = $filteredDepart;
                    $values['mois_final'] = $filteredFinal;
                    $values['identification'] = $filteredIdent;
                    $values['role_candidat'] = $filteredRole;
                }

                $content = json_encode($values, JSON_UNESCAPED_UNICODE);
                if ($content === false) {
                    $content = json_encode($values, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
                }

                $dossierDocument->update([
                    'content' => $content,
                    'statut' => 'complete',
                ]);

                $this->syncExp41ToQualificationMarches($dossier, $values);
                $this->syncExp42aRecords($dossier);
                $this->syncExp42bRecords($dossier);
                $this->syncExp42aSuiteRecords($dossier);
                $this->syncExp42bSuiteRecords($dossier);
            }

            if (trim($currentDocument->nom) === self::EXP42A_NOM) {
                $this->syncExp42aRecords($dossier);

                $validated = $request->validate([
                    'exp42a' => ['nullable', 'array'],
                    'exp42a.*.numero_marche' => ['nullable', 'string', 'max:255'],
                    'exp42a.*.identification_marche' => ['nullable', 'string', 'max:4000'],
                    'exp42a.*.date_attribution' => ['nullable', 'date_format:Y-m'],
                    'exp42a.*.date_achevement' => ['nullable', 'date_format:Y-m'],
                    'exp42a.*.role_marche' => ['nullable', 'string', 'max:255'],
                    'exp42a.*.montant_total' => ['nullable', 'string', 'max:255'],
                    'exp42a.*.participation_pourcentage' => ['nullable', 'string', 'max:50'],
                    'exp42a.*.montant_part' => ['nullable', 'string', 'max:255'],
                    'exp42a.*.autorite_nom' => ['nullable', 'string', 'max:4000'],
                    'exp42a.*.autorite_adresse' => ['nullable', 'string', 'max:4000'],
                    'exp42a.*.autorite_telephone' => ['nullable', 'string', 'max:255'],
                    'exp42a.*.autorite_email' => ['nullable', 'string', 'max:255'],
                ]);

                foreach ($validated['exp42a'] ?? [] as $id => $data) {
                    foreach (['date_attribution', 'date_achevement'] as $field) {
                        $data[$field] = !empty($data[$field]) ? $data[$field] . '-01' : null;
                    }

                    FormulaireExp42A::where('dossier_id', $dossier->id)
                        ->whereNotNull('marche_position')
                        ->whereKey($id)
                        ->first()?->update($data);
                }

                $dossierDocument->update(['statut' => 'complete']);
            }

            if (trim($currentDocument->nom) === self::EXP42B_NOM) {
                $this->syncExp42bRecords($dossier);

                $validated = $request->validate([
                    'exp42b' => ['nullable', 'array'],
                    'exp42b.*.numero_marche' => ['nullable', 'string', 'max:255'],
                    'exp42b.*.identification_marche' => ['nullable', 'string', 'max:4000'],
                    'exp42b.*.date_attribution' => ['nullable', 'date_format:Y-m'],
                    'exp42b.*.date_achevement' => ['nullable', 'date_format:Y-m'],
                    'exp42b.*.role_marche' => ['nullable', 'string', 'max:255'],
                    'exp42b.*.montant_total' => ['nullable', 'string', 'max:255'],
                    'exp42b.*.participation_pourcentage' => ['nullable', 'string', 'max:50'],
                    'exp42b.*.montant_part' => ['nullable', 'string', 'max:255'],
                    'exp42b.*.autorite_nom' => ['nullable', 'string', 'max:4000'],
                    'exp42b.*.autorite_adresse' => ['nullable', 'string', 'max:4000'],
                    'exp42b.*.autorite_telephone' => ['nullable', 'string', 'max:255'],
                    'exp42b.*.autorite_email' => ['nullable', 'string', 'max:255'],
                ]);

                foreach ($validated['exp42b'] ?? [] as $id => $data) {
                    foreach (['date_attribution', 'date_achevement'] as $field) {
                        $data[$field] = !empty($data[$field]) ? $data[$field] . '-01' : null;
                    }

                    FormulaireExp42B::where('dossier_id', $dossier->id)
                        ->whereNotNull('marche_position')
                        ->whereKey($id)
                        ->first()?->update($data);
                }

                $dossierDocument->update(['statut' => 'complete']);
            }

            if (trim($currentDocument->nom) === self::EXP42A_SUITE_NOM) {
                $this->syncExp42aSuiteRecords($dossier);

                $validated = $request->validate([
                    'exp42a_suite' => ['nullable', 'array'],
                    'exp42a_suite.*.description_similitude' => ['nullable', 'string', 'max:4000'],
                    'exp42a_suite.*.montant' => ['nullable', 'string', 'max:255'],
                    'exp42a_suite.*.taille_physique' => ['nullable', 'string', 'max:255'],
                    'exp42a_suite.*.complexite' => ['nullable', 'string', 'max:255'],
                    'exp42a_suite.*.methodes_technologie' => ['nullable', 'string', 'max:255'],
                    'exp42a_suite.*.autres_caracteristiques' => ['nullable', 'string', 'max:255'],
                ]);

                foreach ($validated['exp42a_suite'] ?? [] as $id => $data) {
                    FormulaireExp42ASuite::where('dossier_id', $dossier->id)
                        ->whereNotNull('marche_position')
                        ->whereKey($id)
                        ->first()?->update($data);
                }

                $dossierDocument->update(['statut' => 'complete']);
            }

            if (trim($currentDocument->nom) === self::EXP42B_SUITE_NOM) {
                $this->syncExp42bSuiteRecords($dossier);

                $validated = $request->validate([
                    'exp42b_suite' => ['nullable', 'array'],
                    'exp42b_suite.*.description_similitude' => ['nullable', 'string', 'max:4000'],
                    'exp42b_suite.*.montant' => ['nullable', 'string', 'max:255'],
                    'exp42b_suite.*.taille_physique' => ['nullable', 'string', 'max:255'],
                    'exp42b_suite.*.complexite' => ['nullable', 'string', 'max:255'],
                    'exp42b_suite.*.methodes_technologie' => ['nullable', 'string', 'max:255'],
                    'exp42b_suite.*.autres_caracteristiques' => ['nullable', 'string', 'max:255'],
                ]);

                foreach ($validated['exp42b_suite'] ?? [] as $id => $data) {
                    FormulaireExp42BSuite::where('dossier_id', $dossier->id)
                        ->whereNotNull('marche_position')
                        ->whereKey($id)
                        ->first()?->update($data);
                }

                $dossierDocument->update(['statut' => 'complete']);
            }

            if (trim($currentDocument->nom) === 'Formulaire ANT-2 : Formulaire renseignant sur les antécédents de marchés non exécutés, de litiges en instance et d\'antécédents de litiges') {
                $values = $request->validate([
                    'marche_non_execute_since_year' => ['nullable', 'string', 'max:50'],
                    'marches_non_execute_since_year' => ['nullable', 'string', 'max:50'],
                    'marches_non_executes' => ['nullable', 'array'],
                    'marches_non_executes.*.annee' => ['nullable', 'string', 'max:50'],
                    'marches_non_executes.*.fraction' => ['nullable', 'string', 'max:255'],
                    'marches_non_executes.*.identification' => ['nullable', 'string', 'max:2000'],
                    'marches_non_executes.*.montant_fcfa' => ['nullable', 'string', 'max:255'],
                    'litiges_since_year' => ['nullable', 'string', 'max:50'],
                    'litiges_en_instance_rows' => ['nullable', 'array'],
                    'litiges_en_instance_rows.*.annee' => ['nullable', 'string', 'max:50'],
                    'litiges_en_instance_rows.*.montant_reclamation' => ['nullable', 'string', 'max:255'],
                    'litiges_en_instance_rows.*.identification_marche' => ['nullable', 'string', 'max:2000'],
                    'litiges_en_instance_rows.*.montant_total' => ['nullable', 'string', 'max:255'],
                    'antecedents_litiges' => ['nullable', 'string', 'max:4000'],
                    'autres_details' => ['nullable', 'string', 'max:4000'],
                ]);

                if (!empty($values['marches_non_executes']) && is_array($values['marches_non_executes'])) {
                    $values['marches_non_executes'] = array_values(array_filter($values['marches_non_executes'], function ($row) {
                        return !empty(trim($row['annee'] ?? '')) || !empty(trim($row['fraction'] ?? '')) || !empty(trim($row['identification'] ?? '')) || !empty(trim($row['montant_fcfa'] ?? ''));
                    }));
                }

                if (!empty($values['litiges_en_instance_rows']) && is_array($values['litiges_en_instance_rows'])) {
                    $values['litiges_en_instance_rows'] = array_values(array_filter($values['litiges_en_instance_rows'], function ($row) {
                        return !empty(trim($row['annee'] ?? '')) || !empty(trim($row['montant_reclamation'] ?? '')) || !empty(trim($row['identification_marche'] ?? '')) || !empty(trim($row['montant_total'] ?? ''));
                    }));
                }

                $content = json_encode($values, JSON_UNESCAPED_UNICODE);
                if ($content === false) {
                    $content = json_encode($values, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
                }

                $dossierDocument->update([
                    'content' => $content,
                    'statut' => 'complete',
                ]);
            }

            if (trim($currentDocument->nom) === self::FICHE_TECHNIQUE_ARTICLES_NOM) {
                $articleCount = count($this->descriptionTechniqueArticles($dossier));

                if ($request->filled('delete_fiche_technique_indexes')) {
                    foreach ($request->input('delete_fiche_technique_indexes', []) as $indexToDelete) {
                        $existing = FicheTechniqueFichier::where('dossier_document_id', $dossierDocument->id)
                            ->where('article_index', (int) $indexToDelete)
                            ->first();
                        if ($existing) {
                            Storage::disk('public')->delete($existing->chemin_fichier);
                            $existing->delete();
                        }
                    }
                }

                if ($request->hasFile('fiche_technique_fichiers')) {
                    foreach ($request->file('fiche_technique_fichiers') as $articleIndex => $fichier) {
                        $articleIndex = (int) $articleIndex;
                        if (!$fichier || $articleIndex < 0 || $articleIndex >= $articleCount) {
                            continue;
                        }

                        $existing = FicheTechniqueFichier::where('dossier_document_id', $dossierDocument->id)
                            ->where('article_index', $articleIndex)
                            ->first();
                        if ($existing) {
                            Storage::disk('public')->delete($existing->chemin_fichier);
                            $existing->delete();
                        }

                        $chemin = $fichier->store('dossiers/documents', 'public');
                        FicheTechniqueFichier::create([
                            'dossier_document_id' => $dossierDocument->id,
                            'article_index' => $articleIndex,
                            'chemin_fichier' => $chemin,
                            'nom_original' => $fichier->getClientOriginalName(),
                            'utilisateur_id' => auth()->id(),
                        ]);
                    }
                }

                $hasAnyFicheTechnique = FicheTechniqueFichier::where('dossier_document_id', $dossierDocument->id)->exists();
                $dossierDocument->update(['statut' => $hasAnyFicheTechnique ? 'complete' : 'vide']);
            }

            // Handle FIN 3.4 (a) - Attestation de capacité financière
            if (trim($currentDocument->nom) === 'Formulaire FIN 3.4 (a) Modèle d\'attestation de capacité financière') {
                // Files are handled separately below, mark as in progress until files processed
                $dossierDocument->update(['statut' => 'en_cours']);
            }

            // Handle FIN 3.4 (b) - Lettre de confirmation
            if (trim($currentDocument->nom) === 'Formulaire FIN 3.4 (b) Modèle de lettre de confirmation de la capacité financière') {
                // Files are handled separately below, mark as in progress until files processed
                $dossierDocument->update(['statut' => 'en_cours']);
            }

                if ($currentDocument->type_formulaire === 'bordereau' || trim($currentDocument->nom) === 'Bordereau prix unitaire' || trim($currentDocument->nom) === 'Bordereau des prix pour les fournitures à importer' || trim($currentDocument->nom) === 'Programme d\'activités' || trim($currentDocument->nom) === 'Méthodes d\'exécution' || trim($currentDocument->nom) === 'Calendrier d\'exécution' || trim($currentDocument->nom) === 'Description technique des fournitures/services') {
                $isProgramme = trim($currentDocument->nom) === "Programme d'activités";
                $isMethodes = trim($currentDocument->nom) === "Méthodes d'exécution";
                $isCalendrier = trim($currentDocument->nom) === "Calendrier d'exécution";
                $isDescriptionTechnique = trim($currentDocument->nom) === "Description technique des fournitures/services";
                $isBordereauFournitures = trim($currentDocument->nom) === "Bordereau des prix pour les fournitures à importer";
                $isBordereauFournituresBenin = trim($currentDocument->nom) === "Bordereau des prix pour les fournitures fabriquées au Bénin";
                $isBordereauFournituresDejaImportees = trim($currentDocument->nom) === "Bordereau des prix des fournitures, déjà importées";
                $isBordereauPrixCalendrier = trim($currentDocument->nom) === "Bordereau des prix et calendrier d'exécution des services connexes";
                $isListesServicesConnexes = trim($currentDocument->nom) === "Listes des services connexes et calendrier de réalisation";
                $isListesFournituersLivraison = trim($currentDocument->nom) === "Listes des Fournitures et Calendrier de livraison";
                $isCadresSousDetailsPrix = trim($currentDocument->nom) === "Cadres de sous détails des prix unitaire";
                $isListeMateriel = trim($currentDocument->nom) === "Liste de Matériel";
                $rules = [
                    'sections' => ['required', 'array', 'min:1'],
                    'sections.*.titre' => ['nullable', 'string', 'max:255'],
                    'sections.*.designation_label' => ['nullable', 'string', 'max:255'],
                    'sections.*.lignes' => ['required', 'array', 'min:1'],
                    'sections.*.lignes.*.designation' => ['nullable', 'string', 'max:1000'],
                    'sections.*.lignes.*.prix_unitaire' => ['nullable', 'numeric'],
                ];

                if ($isCadresSousDetailsPrix) {
                    $rules = array_merge($rules, [
                        'sections.*.lignes.*.unite' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.total_materiel' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.location_amort' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.matiere_frais' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.main_oeuvre' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.deborse_sec' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.coef_c1' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.coef_k' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.prix_vente_htva' => ['nullable', 'string', 'max:255'],
                    ]);
                }

                if ($isProgramme) {
                    $rules = array_merge($rules, [
                        'sections.*.lignes.*.unite_physique' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.quantite' => ['nullable', 'integer', 'min:1'],
                        'sections.*.lignes.*.site' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.date_prestation' => ['nullable', 'string', 'max:255'],
                    ]);
                }

                if ($isMethodes || $isCalendrier || $isDescriptionTechnique) {
                    $rules = array_merge($rules, [
                        'sections.*.lignes.*.date_prestation' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.quantite' => ['nullable', 'numeric'],
                    ]);
                }

                if ($isDescriptionTechnique) {
                    $rules = array_merge($rules, [
                        'sections.*.lignes.*.specifications_techniques' => ['nullable', 'string', 'max:2000'],
                        'sections.*.lignes.*.specifications_obligatoires' => ['nullable', 'string', 'max:2000'],
                        'sections.*.lignes.*.specifications_proposees' => ['nullable', 'string', 'max:2000'],
                    ]);
                }

                if ($isBordereauFournitures) {
                    $rules = array_merge($rules, [
                        'sections.*.lignes.*.quantite' => ['nullable', 'numeric'],
                        'sections.*.lignes.*.date_prestation' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.cout_benin' => ['nullable', 'numeric'],
                    ]);
                }

                if ($isBordereauFournituresBenin) {
                    $rules = array_merge($rules, [
                        'sections.*.lignes.*.quantite' => ['nullable', 'numeric'],
                        'sections.*.lignes.*.date_prestation' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.transport' => ['nullable', 'numeric'],
                        'sections.*.lignes.*.cout_main_oeuvre_locale' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.taxe_vente' => ['nullable', 'numeric'],
                        'variante' => ['nullable', 'string', 'max:255'],
                    ]);
                }

                if ($isBordereauFournituresDejaImportees) {
                    $rules = array_merge($rules, [
                        'sections.*.lignes.*.quantite' => ['nullable', 'numeric'],
                        'sections.*.lignes.*.date_prestation' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.site' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.droits_douane' => ['nullable', 'numeric'],
                        'sections.*.lignes.*.transport' => ['nullable', 'numeric'],
                        'sections.*.lignes.*.taxe_vente' => ['nullable', 'numeric'],
                        'variante' => ['nullable', 'string', 'max:255'],
                    ]);
                }

                if ($isBordereauPrixCalendrier) {
                    $rules = array_merge($rules, [
                        'sections.*.lignes.*.quantite' => ['nullable', 'numeric'],
                        'sections.*.lignes.*.date_prestation' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.frequence' => ['nullable', 'string', 'max:255'],
                    ]);
                }

                if ($isListesServicesConnexes) {
                    $rules = array_merge($rules, [
                        'sections.*.lignes.*.quantite' => ['nullable', 'numeric'],
                        'sections.*.lignes.*.unite_physique' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.site' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.date_prestation_plus_tot' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.date_prestation_plus_tard' => ['nullable', 'string', 'max:255'],
                    ]);
                }

                if ($isListesFournituersLivraison) {
                    $rules = array_merge($rules, [
                        'sections.*.lignes.*.quantite' => ['nullable', 'numeric'],
                        'sections.*.lignes.*.unite_physique' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.site' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.date_livraison_plus_tot' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.date_livraison_plus_tard' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.date_livraison_offerte' => ['nullable', 'string', 'max:255'],
                    ]);
                }

                if ($isListeMateriel) {
                    $rules = array_merge($rules, [
                        'sections.*.lignes.*.quantite' => ['nullable', 'numeric'],
                        'sections.*.lignes.*.nombre_propose' => ['nullable', 'numeric'],
                    ]);
                }

                if ($request->has('sections')) {
                    $request->merge(['sections' => $this->normalizeBordereauNumbers($request->input('sections', []))]);
                }

                $values = $request->validate($rules);

                $oldBordereaux = Bordereau::where('dossier_document_id', $dossierDocument->id)->get();
                $oldBordereaux->each(function ($old) {
                    $old->lignes()->delete();
                });
                Bordereau::where('dossier_document_id', $dossierDocument->id)->delete();

                $validLineCreated = false;
                foreach ($values['sections'] as $section) {
                    $bordereau = Bordereau::create([
                        'dossier_document_id' => $dossierDocument->id,
                        'titre' => trim($section['titre'] ?? '') !== '' ? $section['titre'] : $currentDocument->nom,
                        'designation_label' => trim($section['designation_label'] ?? '') !== '' ? $section['designation_label'] : null,
                    ]);

                    foreach ($section['lignes'] as $ligne) {
                        $designation = trim($ligne['designation'] ?? '');
                        $price = !empty($ligne['prix_unitaire']) ? (float) $ligne['prix_unitaire'] : 0;

                        if ($isBordereauFournitures) {
                            $datePrestation = trim($ligne['date_prestation'] ?? '');
                            $quantite = !empty($ligne['quantite']) ? (float) $ligne['quantite'] : 0;
                            $coutBenin = !empty($ligne['cout_benin']) ? (float) $ligne['cout_benin'] : 0;
                            $montant = $price * max(1, $quantite);

                            if ($designation === '' && $price <= 0 && $datePrestation === '') {
                                continue;
                            }

                            BordereauLigne::create([
                                'bordereau_id' => $bordereau->id,
                                'designation' => $designation,
                                'quantite' => $quantite,
                                'prix_unitaire' => $price,
                                'montant' => $montant,
                                'date_prestation' => $datePrestation,
                                'cout_benin' => $coutBenin,
                            ]);
                            $validLineCreated = true;
                            continue;
                        }

                        if ($isBordereauFournituresBenin) {
                            $datePrestation = trim($ligne['date_prestation'] ?? '');
                            $quantite = !empty($ligne['quantite']) ? (float) $ligne['quantite'] : 0;
                            $transport = !empty($ligne['transport']) ? (float) $ligne['transport'] : 0;
                            $coutMainOeuvreLocale = trim($ligne['cout_main_oeuvre_locale'] ?? '');
                            $taxeVente = !empty($ligne['taxe_vente']) ? (float) $ligne['taxe_vente'] : 0;
                            $montant = $price * max(1, $quantite);

                            if ($designation === '' && $price <= 0 && $datePrestation === '') {
                                continue;
                            }

                            BordereauLigne::create([
                                'bordereau_id' => $bordereau->id,
                                'designation' => $designation,
                                'quantite' => $quantite,
                                'prix_unitaire' => $price,
                                'montant' => $montant,
                                'date_prestation' => $datePrestation,
                                'transport' => $transport,
                                'cout_main_oeuvre_locale' => $coutMainOeuvreLocale,
                                'taxe_vente' => $taxeVente,
                            ]);
                            $validLineCreated = true;
                            continue;
                        }

                        if ($isBordereauFournituresDejaImportees) {
                            $datePrestation = trim($ligne['date_prestation'] ?? '');
                            $site = trim($ligne['site'] ?? '');
                            $quantite = !empty($ligne['quantite']) ? (float) $ligne['quantite'] : 0;
                            $droitsDouane = !empty($ligne['droits_douane']) ? (float) $ligne['droits_douane'] : 0;
                            $transport = !empty($ligne['transport']) ? (float) $ligne['transport'] : 0;
                            $taxeVente = !empty($ligne['taxe_vente']) ? (float) $ligne['taxe_vente'] : 0;
                            $prixNet = $price - $droitsDouane;
                            $montant = $prixNet * max(1, $quantite);

                            if ($designation === '' && $price <= 0 && $datePrestation === '') {
                                continue;
                            }

                            BordereauLigne::create([
                                'bordereau_id' => $bordereau->id,
                                'designation' => $designation,
                                'site' => $site,
                                'quantite' => $quantite,
                                'prix_unitaire' => $price,
                                'droits_douane' => $droitsDouane,
                                'montant' => $montant,
                                'date_prestation' => $datePrestation,
                                'transport' => $transport,
                                'taxe_vente' => $taxeVente,
                            ]);
                            $validLineCreated = true;
                            continue;
                        }

                        if ($isBordereauPrixCalendrier) {
                            $datePrestation = trim($ligne['date_prestation'] ?? '');
                            $quantite = !empty($ligne['quantite']) ? (float) $ligne['quantite'] : 0;
                            $frequence = trim($ligne['frequence'] ?? '');
                            $montant = $price * max(1, $quantite);

                            if ($designation === '' && $price <= 0 && $datePrestation === '' && $frequence === '') {
                                continue;
                            }

                            BordereauLigne::create([
                                'bordereau_id' => $bordereau->id,
                                'designation' => $designation,
                                'quantite' => $quantite,
                                'prix_unitaire' => $price,
                                'montant' => $montant,
                                'date_prestation' => $datePrestation,
                                'frequence' => $frequence,
                            ]);
                            $validLineCreated = true;
                            continue;
                        }

                        if ($isListesServicesConnexes) {
                            $quantite = !empty($ligne['quantite']) ? (float) $ligne['quantite'] : 0;
                            $unitePhysique = trim($ligne['unite_physique'] ?? '');
                            $site = trim($ligne['site'] ?? '');
                            $datePrestationPlusTot = trim($ligne['date_prestation_plus_tot'] ?? '');
                            $datePrestationPlusTard = trim($ligne['date_prestation_plus_tard'] ?? '');
                            $datePrestation = trim(implode(' / ', array_filter([$datePrestationPlusTot, $datePrestationPlusTard], function ($value) {
                                return $value !== '';
                            })));

                            if ($designation === '' && $quantite <= 0 && $unitePhysique === '' && $site === '' && $datePrestation === '') {
                                continue;
                            }

                            BordereauLigne::create([
                                'bordereau_id' => $bordereau->id,
                                'designation' => $designation,
                                'quantite' => $quantite,
                                'unite_physique' => $unitePhysique,
                                'prix_unitaire' => 0,
                                'montant' => 0,
                                'site' => $site,
                                'date_prestation' => $datePrestation,
                            ]);
                            $validLineCreated = true;
                            continue;
                        }

                        if ($isListesFournituersLivraison) {
                            $quantite = !empty($ligne['quantite']) ? (float) $ligne['quantite'] : 0;
                            $unitePhysique = trim($ligne['unite_physique'] ?? '');
                            $site = trim($ligne['site'] ?? '');
                            $dateLivraisonPlusTot = trim($ligne['date_livraison_plus_tot'] ?? '');
                            $dateLivraisonPlusTard = trim($ligne['date_livraison_plus_tard'] ?? '');
                            $dateLivraisonOfferte = trim($ligne['date_livraison_offerte'] ?? '');
                            $datePrestation = trim(implode(' / ', array_filter([$dateLivraisonPlusTot, $dateLivraisonPlusTard, $dateLivraisonOfferte], function ($value) {
                                return $value !== '';
                            })));

                            if ($designation === '' && $quantite <= 0 && $unitePhysique === '' && $site === '' && $datePrestation === '') {
                                continue;
                            }

                            BordereauLigne::create([
                                'bordereau_id' => $bordereau->id,
                                'designation' => $designation,
                                'quantite' => $quantite,
                                'unite_physique' => $unitePhysique,
                                'prix_unitaire' => 0,
                                'montant' => 0,
                                'site' => $site,
                                'date_prestation' => $datePrestation,
                            ]);
                            $validLineCreated = true;
                            continue;
                        }

                        if ($isListeMateriel) {
                            $quantite = !empty($ligne['quantite']) ? (float) $ligne['quantite'] : 0;
                            $nombrePropose = !empty($ligne['nombre_propose']) ? (float) $ligne['nombre_propose'] : 0;

                            if ($designation === '' && $quantite <= 0 && $nombrePropose <= 0) {
                                continue;
                            }

                            BordereauLigne::create([
                                'bordereau_id' => $bordereau->id,
                                'designation' => $designation,
                                'quantite' => $quantite,
                                'nombre_propose' => $nombrePropose,
                                'prix_unitaire' => 0,
                                'montant' => 0,
                            ]);
                            $validLineCreated = true;
                            continue;
                        }

                        if ($isCadresSousDetailsPrix) {
                            $unite = trim($ligne['unite'] ?? '');
                            $total_materiel = trim($ligne['total_materiel'] ?? '');
                            $location_amort = trim($ligne['location_amort'] ?? '');
                            $matiere_frais = trim($ligne['matiere_frais'] ?? '');
                            $main_oeuvre = trim($ligne['main_oeuvre'] ?? '');
                            $deborse_sec = trim($ligne['deborse_sec'] ?? '');
                            $coef_c1 = trim($ligne['coef_c1'] ?? '');
                            $coef_k = trim($ligne['coef_k'] ?? '');
                            $prix_vente_htva = trim($ligne['prix_vente_htva'] ?? '');

                            if ($designation === '' && $unite === '' && $total_materiel === '' && $location_amort === '' && $matiere_frais === '' && $main_oeuvre === '' && $deborse_sec === '' && $coef_c1 === '' && $coef_k === '' && $prix_vente_htva === '') {
                                continue;
                            }

                                // Log raw incoming values for debugging
                                Log::info('cadres_sous_details_prix_unitaire - ligne raw', [
                                    'dossier_document_id' => $dossierDocument->id ?? null,
                                    'designation' => $designation,
                                    'ligne_raw' => $ligne,
                                ]);

                            // Normalize numeric-ish strings (remove spaces, convert comma decimals)
                            $norm = function ($v) {
                                $v = trim($v ?? '');
                                if ($v === '') {
                                    return '';
                                }
                                $v = str_replace(["\xc2\xa0", ' ', ','], ['', '', '.'], $v);
                                return $v;
                            };

                            $total_materiel_n = $norm($total_materiel);
                            $location_amort_n = $norm($location_amort);
                            $matiere_frais_n = $norm($matiere_frais);
                            $main_oeuvre_n = $norm($main_oeuvre);
                            $deborse_sec_n = $norm($deborse_sec);
                            $coef_c1_n = $norm($coef_c1);
                            $coef_k_n = $norm($coef_k);
                            $prix_vente_htva_n = $norm($prix_vente_htva);

                            $total_materiel_value = is_numeric($total_materiel_n) ? (float) $total_materiel_n : null;
                            $location_amort_value = is_numeric($location_amort_n) ? (float) $location_amort_n : 0.0;
                            $matiere_frais_value = is_numeric($matiere_frais_n) ? (float) $matiere_frais_n : 0.0;
                            $main_oeuvre_value = is_numeric($main_oeuvre_n) ? (float) $main_oeuvre_n : 0.0;

                            if ($total_materiel_value !== null) {
                                $calculated_deborse_sec = $total_materiel_value + $location_amort_value + $matiere_frais_value + $main_oeuvre_value;
                                $calculated_coef_c1 = $total_materiel_value > 0 ? ($calculated_deborse_sec - $total_materiel_value) / $total_materiel_value : 0;
                                $calculated_coef_k = 1 + $calculated_coef_c1;

                                $deborse_sec_n = number_format($calculated_deborse_sec, 2, '.', '');
                                $coef_c1_n = number_format($calculated_coef_c1, 4, '.', '');
                                $coef_k_n = number_format($calculated_coef_k, 4, '.', '');
                            }

                            // Log normalized values
                            Log::info('cadres_sous_details_prix_unitaire - ligne normalized', [
                                'dossier_document_id' => $dossierDocument->id ?? null,
                                'matiere_frais_n' => $matiere_frais_n,
                                'total_materiel_n' => $total_materiel_n,
                                'deborse_sec_n' => $deborse_sec_n,
                                'coef_c1_n' => $coef_c1_n,
                                'coef_k_n' => $coef_k_n,
                                'prix_vente_htva_n' => $prix_vente_htva_n,
                            ]);

                            BordereauLigne::create([
                                'bordereau_id' => $bordereau->id,
                                'designation' => $designation,
                                'unite_physique' => $unite,
                                'quantite' => 1,
                                'prix_unitaire' => 0,
                                'montant' => 0,
                                'total_materiel' => $total_materiel_n,
                                'location_amort' => $location_amort_n,
                                'matiere_frais' => $matiere_frais_n,
                                'main_oeuvre' => $main_oeuvre_n,
                                'deborse_sec' => $deborse_sec_n,
                                'coef_c1' => $coef_c1_n,
                                'coef_k' => $coef_k_n,
                                'prix_vente_htva' => $prix_vente_htva_n,
                            ]);
                            $validLineCreated = true;
                            continue;
                        }

                        if (!$isProgramme && !$isMethodes && !$isCalendrier && !$isDescriptionTechnique) {
                            if ($designation === '' && $price <= 0) {
                                continue;
                            }

                            BordereauLigne::create([
                                'bordereau_id' => $bordereau->id,
                                'designation' => $designation,
                                'quantite' => 1,
                                'prix_unitaire' => $price,
                                'montant' => $price,
                            ]);
                            $validLineCreated = true;
                            continue;
                        }

                        if ($isMethodes || $isCalendrier || $isDescriptionTechnique) {
                            $datePrestation = trim($ligne['date_prestation'] ?? '');
                            $quantite = !empty($ligne['quantite']) ? (float) $ligne['quantite'] : 1;
                            $montant = $price * max(1, $quantite);

                            if ($designation === '' && $price <= 0 && $datePrestation === '') {
                                continue;
                            }

                            $lignData = [
                                'bordereau_id' => $bordereau->id,
                                'designation' => $designation,
                                'quantite' => $quantite,
                                'prix_unitaire' => $price,
                                'montant' => $montant,
                                'date_prestation' => $datePrestation,
                            ];

                            if ($isDescriptionTechnique) {
                                $lignData['specifications_techniques'] = trim($ligne['specifications_techniques'] ?? '');
                                $lignData['specifications_obligatoires'] = trim($ligne['specifications_obligatoires'] ?? '');
                                $lignData['specifications_proposees'] = trim($ligne['specifications_proposees'] ?? '');
                            }

                            BordereauLigne::create($lignData);
                            $validLineCreated = true;
                            continue;
                        }

                        // Programme d'activités (dernier cas)
                        $unitePhysique = trim($ligne['unite_physique'] ?? '');
                        $quantite = !empty($ligne['quantite']) ? (float) $ligne['quantite'] : 1;
                        $site = trim($ligne['site'] ?? '');
                        $datePrestation = trim($ligne['date_prestation'] ?? '');
                        $montant = $price * max(1, $quantite);

                        if ($designation === '' && $price <= 0 && $site === '' && $datePrestation === '') {
                            continue;
                        }

                        BordereauLigne::create([
                            'bordereau_id' => $bordereau->id,
                            'designation' => $designation,
                            'unite_physique' => $unitePhysique,
                            'quantite' => $quantite,
                            'prix_unitaire' => $price,
                            'montant' => $montant,
                            'site' => $site,
                            'date_prestation' => $datePrestation,
                        ]);
                        $validLineCreated = true;
                    }
                }

                if ($isBordereauFournituresBenin || $isBordereauFournituresDejaImportees) {
                    $dossierDocument->update([
                        'content' => json_encode([
                            'variante' => trim($values['variante'] ?? ''),
                        ], JSON_UNESCAPED_UNICODE),
                    ]);
                }

                if ($validLineCreated) {
                    $dossierDocument->update(['statut' => 'complete']);
                }

                if (trim($currentDocument->nom) === self::LISTE_MATERIEL_NOM) {
                    $this->syncFormulaireMatRecords($dossier);
                }
            }

            if (trim($currentDocument->nom) === self::FORMULAIRE_MAT_NOM) {
                $this->syncFormulaireMatRecords($dossier);

                $validated = $request->validate([
                    'mat' => ['nullable', 'array'],
                    'mat.*.fabricant' => ['nullable', 'string', 'max:255'],
                    'mat.*.modele_puissance' => ['nullable', 'string', 'max:255'],
                    'mat.*.capacite' => ['nullable', 'string', 'max:1000'],
                    'mat.*.annee_fabrication' => ['nullable', 'string', 'max:10'],
                    'mat.*.localisation' => ['nullable', 'string', 'max:255'],
                    'mat.*.engagements' => ['nullable', 'string'],
                    'mat.*.provenance' => ['nullable', 'string', 'max:50'],
                    'mat.*.nom_proprietaire' => ['nullable', 'string', 'max:255'],
                    'mat.*.adresse_proprietaire' => ['nullable', 'string', 'max:1000'],
                    'mat.*.telephone_proprietaire' => ['nullable', 'string', 'max:100'],
                    'mat.*.contact_nom_titre' => ['nullable', 'string', 'max:255'],
                    'mat.*.telecopie_proprietaire' => ['nullable', 'string', 'max:100'],
                    'mat.*.telex_proprietaire' => ['nullable', 'string', 'max:100'],
                    'mat.*.accords_details' => ['nullable', 'string'],
                    'mat.*.signataire_id' => ['nullable', 'exists:signataires,id'],
                    'mat.*.lieu_fait' => ['nullable', 'string', 'max:255'],
                    'mat.*.date_fait' => ['nullable', 'date'],
                ]);

                foreach ($validated['mat'] ?? [] as $id => $data) {
                    FormulaireMat::where('dossier_id', $dossier->id)
                        ->whereNotNull('materiel_position')
                        ->whereKey($id)
                        ->first()?->update($data);
                }

                $dossierDocument->update(['statut' => 'complete']);
            }

            if (trim($currentDocument->nom) === 'Plan de charge') {
                $values = $request->validate([
                    'plan_charge_candidat' => ['nullable', 'string', 'max:255'],
                    'plan_charge_rows' => ['nullable', 'array'],
                    'plan_charge_rows.*.nature' => ['nullable', 'string', 'max:1000'],
                    'plan_charge_rows.*.marche' => ['nullable', 'string', 'max:1000'],
                    'plan_charge_rows.*.delai' => ['nullable', 'string', 'max:255'],
                    'plan_charge_rows.*.date_demarrage' => ['nullable', 'string', 'max:255'],
                    'plan_charge_rows.*.date_fin' => ['nullable', 'string', 'max:255'],
                    'plan_charge_rows.*.taux_physique' => ['nullable', 'string', 'max:255'],
                    'plan_charge_rows.*.taux_financier' => ['nullable', 'string', 'max:255'],
                    'plan_charge_rows.*.autorite' => ['nullable', 'string', 'max:1000'],
                    'plan_charge_rows.*.observations' => ['nullable', 'string', 'max:1000'],
                ]);

                $content = json_encode($values, JSON_UNESCAPED_UNICODE);
                if ($content === false) {
                    $content = json_encode($values, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
                }

                $dossierDocument->update(['content' => $content, 'statut' => 'complete']);
            }
            if (trim($currentDocument->nom) === 'Lettre de soumission') {
                // Champs alignés sur ce que le gabarit PDF (lettre_soumission_pdf_content)
                // utilise réellement : les clauses e) à l) y sont désormais un texte légal
                // fixe (identique pour tous les dossiers, conforme au modèle DRP), donc
                // elles ne sont plus des champs de formulaire ; "point_a" ne porte plus que
                // le numéro d'addenda et "point_b" que le délai d'exécution.
                $values = $request->validate([
                    'date' => ['required', 'date'],
                    'destinataire' => ['nullable', 'string', 'max:1000'],
                    'point_a' => ['nullable', 'string', 'max:1000'],
                    'point_b' => ['nullable', 'string', 'max:1000'],
                    'montant_ht_calendrier' => ['nullable', 'string', 'max:255'],
                    'montant_ttc_calendrier' => ['nullable', 'string', 'max:255'],
                    'montant_ht_services' => ['nullable', 'string', 'max:255'],
                    'montant_ttc_services' => ['nullable', 'string', 'max:255'],
                    'montant_ht_total' => ['nullable', 'string', 'max:255'],
                    'montant_ht_lettres' => ['nullable', 'string', 'max:255'],
                    'montant_chiffres' => ['nullable', 'string', 'max:255'],
                    'montant_lettres' => ['nullable', 'string', 'max:255'],
                    'tva_valeur' => ['nullable', 'string', 'max:255'],
                    'rabais' => ['nullable', 'string', 'max:4000'],
                ]);

                $content = json_encode($values, JSON_UNESCAPED_UNICODE);
                if ($content === false) {
                    $content = json_encode($values, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
                }

                $dossierDocument->update(['content' => $content]);

                try {
                    $pdfData = array_merge($values, [
                        'entreprise' => $dossier->entreprise,
                        'dossier' => $dossier,
                    ]);

                    $html = view('documents.lettre_soumission_pdf', $pdfData)->render();
                    $dompdf = new Dompdf(['isRemoteEnabled' => true]);
                    $dompdf->loadHtml($html);
                    $dompdf->setPaper('A4', 'portrait');
                    $dompdf->render();

                    $output = $dompdf->output();
                    $filename = 'dossiers/documents/dossier_' . $dossier->id . '_lettre_soumission_' . time() . '.pdf';
                    Storage::disk('public')->put($filename, $output);

                    DocumentFichier::create([
                        'dossier_document_id' => $dossierDocument->id,
                        'chemin_fichier' => $filename,
                        'utilisateur_id' => auth()->id(),
                    ]);

                    $dossierDocument->update(['statut' => 'complete']);
                } catch (\Throwable $e) {
                    // ignore and continue with normal flow
                }
            }

            if ($request->hasFile("fichiers.$currentDocumentId")) {
                foreach ($request->file("fichiers.$currentDocumentId") as $fichier) {
                    if (!$fichier) {
                        continue;
                    }

                    $chemin = $fichier->store('dossiers/documents', 'public');

                    DocumentFichier::create([
                        'dossier_document_id' => $dossierDocument->id,
                        'chemin_fichier' => $chemin,
                        'utilisateur_id' => auth()->id(),
                    ]);
                }
            }

            try {
                $hasFiles = DocumentFichier::where('dossier_document_id', $dossierDocument->id)->exists()
                    || (!empty($dossierDocument->linked_entreprise_document_ids) && $dossierDocument->entrepriseFichiers()->isNotEmpty());
                if ($hasFiles) {
                    $dossierDocument->update(['statut' => 'complete']);
                }
            } catch (\Throwable $e) {
                // ignore
            }

            if ($request->filled('delete_file_ids')) {
                $deleteIds = $request->input('delete_file_ids', []);
                foreach ($deleteIds as $fid) {
                    $file = DocumentFichier::find($fid);
                    if ($file && $file->dossier_document_id === $dossierDocument->id) {
                        try {
                            Storage::disk('public')->delete($file->chemin_fichier);
                        } catch (\Throwable $e) {
                            // ignore deletion errors
                        }
                        $file->delete();
                    }
                }
            }

            if ($this->entrepriseDocumentPickerType($dossierDocument) !== null) {
                $excludedEntrepriseIds = array_map('intval', $request->input('exclude_entreprise_fichier_ids', []));
                $dossierDocument->excluded_entreprise_fichier_ids = $excludedEntrepriseIds;
                $dossierDocument->save();
            }

            $this->logChange(
                $dossier,
                'document_enregistre',
                "Pièce « {$currentDocument->nom} » renseignée.",
                $currentDocument->nom,
                $currentDocument->id
            );

            $nextIndex = null;
            for ($i = $currentIndex + 1; $i < $uploadQueue->count(); $i++) {
                $nextDoc = $uploadQueue->get($i);
                $nextDocRec = DossierDocument::where('dossier_id', $dossier->id)
                    ->where('type_document_id', $nextDoc->id)
                    ->first();

                if (!$nextDocRec || $nextDocRec->statut !== 'complete') {
                    $nextIndex = $i;
                    break;
                }
            }

            if ($nextIndex === null) {
                $dossier->statut = 'termine';
                $dossier->save();
                Session::forget($sessionKeyDocs);
                Session::forget($sessionKeySignataire);
                return redirect()->route('dossiers.show', $dossier->id)->with('success', 'Pièces jointes enregistrées — dossier terminé.');
            }

            return redirect()->route('dossiers.step6', ['dossierId' => $dossier->id, 'current_index' => $nextIndex]);
        }

        $this->syncExp42aRecords($dossier);
        $this->syncExp42bRecords($dossier);
        $this->syncExp42aSuiteRecords($dossier);
        $this->syncExp42bSuiteRecords($dossier);
        $this->syncFormulairePerRecords($dossier);
        $this->syncFormulaireMatRecords($dossier);
        $this->syncFormulaireFin31FromEntreprise($dossier);
        $this->syncFormulaireBeneficiairesFromEntreprise($dossier);
        $this->syncFicheTechniqueFromCatalogue($dossier);
        $entrepriseChiffres = $this->entrepriseChiffresAffaires($dossier);

        return view('dossiers.create.step6', [
            'dossier' => $dossier,
            'uploadQueue' => $uploadQueue,
            'currentDocument' => $currentDocument,
            'currentIndex' => $currentIndex,
            'totalCount' => $uploadQueue->count(),
            'selectedDocumentIds' => $selectedDocumentIds,
            'entrepriseChiffres' => $entrepriseChiffres,
        ]);
    }

    /**
     * Lit un fichier Excel/CSV envoyé depuis un formulaire step6 et retourne
     * ses lignes brutes (en-têtes + données) pour pré-remplir un tableau
     * dynamique côté client. Le candidat garde la main : rien n'est enregistré
     * ici, l'utilisateur relit/corrige avant de soumettre le formulaire.
     */
    public function importTableau(Request $request)
    {
        $request->validate([
            'fichier' => ['required', 'file', 'max:10240', 'mimes:csv,txt,xlsx,docx'],
        ]);

        try {
            $result = \App\Support\SpreadsheetImport::parse($request->file('fichier'));
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($result);
    }

    /**
     * Sauvegarder les valeurs d'un document
     */
    public function saveDocumentValues(Request $request, $dossierId)
    {
        $dossier = Dossier::findOrFail($dossierId);
        $dossierDocumentId = $request->validate(['dossier_document_id' => 'required|exists:dossier_documents,id'])['dossier_document_id'];

        $dossierDocument = DossierDocument::findOrFail($dossierDocumentId);

        // Sauvegarder les valeurs pour chaque champ
        foreach ($request->all() as $key => $value) {
            if ($key !== 'dossier_document_id' && $key !== '_token') {
                // Le nom de champ est en format: champ_{id}
                if (preg_match('/champ_(\d+)/', $key, $matches)) {
                    $champId = $matches[1];

                    ValeurDocument::updateOrCreate(
                        [
                            'dossier_document_id' => $dossierDocumentId,
                            'champ_document_id' => $champId,
                        ],
                        [
                            'valeur' => $value,
                            'utilisateur_id' => auth()->id(),
                        ]
                    );
                }
            }
        }

        $dossierDocument->update(['statut' => 'complete']);

        // Determine next index in the uploadable queue so the UI can navigate
        $excludedName = 'Lettre de soumission';
        $uploadable = $dossier->documents->filter(fn($dd) => $dd->typeDocument && $dd->typeDocument->nom !== $excludedName)->sortBy('ordre')->values();
        $currentPos = $uploadable->pluck('id')->search($dossierDocument->id);
        $nextIndex = ($currentPos === false) ? 0 : ($currentPos + 1);

        if ($nextIndex >= $uploadable->count()) {
            $dossier->statut = 'termine';
            $dossier->save();
            $redirectUrl = route('dossiers.index');
        } else {
            $redirectUrl = route('dossiers.step6', ['dossierId' => $dossier->id, 'current_index' => $nextIndex]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'redirect' => $redirectUrl]);
        }

        return redirect($redirectUrl);
    }

    /**
     * Générer le PDF du dossier final
     */
    public function generatePDF(Dossier $dossier)
    {
        @set_time_limit(300);
        @ini_set('memory_limit', '1024M');

        $this->syncFormulaireFin31FromEntreprise($dossier);
        $this->syncFormulaireBeneficiairesFromEntreprise($dossier);

        $dossier->load([
            'documents.typeDocument.champs',
            'documents.bordereau.lignes',
            'documents.valeurs',
            'documents.fichiers',
            'entreprise',
            'typeDossier',
            'signataires'
        ]);

        // Le sommaire et le PDF final doivent reprendre tous les documents sélectionnés
        // dans le dossier, sans exclure "Déclaration de garantie d'offre" ni aucun autre.
        $documents = $dossier->documents->filter(function ($doc) {
            return $doc->typeDocument !== null;
        })->sortBy([['ordre', 'asc'], ['id', 'asc']])->values();

        // Préparer le data URI de l'image/PDF de la page de garde pour l'inclure dans le HTML
        $pageGardeDataUri = null;
        if (!empty($dossier->page_garde_path)) {
            $fullPath = storage_path('app/public/' . $dossier->page_garde_path);
            if (file_exists($fullPath)) {
                $mime = mime_content_type($fullPath) ?: 'application/octet-stream';
                $content = file_get_contents($fullPath);
                $base64 = base64_encode($content);
                $pageGardeDataUri = "data:{$mime};base64,{$base64}";
            }
        }

        $needsMerge = $documents->contains(function ($doc) {
            $plan = $this->attachmentPlan($doc);
            return $plan['pdfs']->isNotEmpty() || $plan['others']->isNotEmpty() || $this->isExp42aDoc($doc) || $this->isExp42bDoc($doc) || $this->isExp42aSuiteDoc($doc) || $this->isExp42bSuiteDoc($doc) || $this->isFormulairePerDoc($doc) || $this->isFormulaireMatDoc($doc) || $this->isFicheTechniqueArticlesDoc($doc) || ($this->entrepriseDocumentPickerType($doc) !== null && !empty($doc->linked_entreprise_document_ids));
        });

        if (!$needsMerge) {
            $html = view('dossiers.pdf', compact('dossier', 'pageGardeDataUri', 'documents'))->render();

            try {
                $dompdf = new Dompdf(['isRemoteEnabled' => true]);
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                $this->addPageNumbers($dompdf);

                $output = $dompdf->output();
                $finalFilename = 'dossiers/documents/dossier_' . $dossier->id . '_base_' . time() . '.pdf';
                Storage::disk('public')->put($finalFilename, $output);
                $finalFull = storage_path('app/public/' . $finalFilename);

                try {
                    $dossier->statut = 'genere';
                    $dossier->save();
                } catch (\Throwable $e) {}

                return response($output, 200)
                    ->header('Content-Type', 'application/pdf')
                    ->header('Content-Disposition', 'inline; filename="dossier_' . $dossier->id . '.pdf"');
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Échec de la génération du PDF du dossier #' . $dossier->id . ' : ' . $e->getMessage(), ['exception' => $e]);
                return redirect()->route('dossiers.show', $dossier->id)
                    ->with('error', 'La génération du PDF a échoué : ' . $e->getMessage());
            }
        }

        if (!class_exists('\\setasign\\Fpdi\\Fpdi')) {
            return redirect()->route('dossiers.show', $dossier->id)
                ->with('error', 'La fusion des PDFs nécessite la librairie setasign/fpdi. Exécutez : composer require setasign/fpdi setasign/fpdf');
        }

        $filesToMerge = [];
        $tempFiles = [];

        try {
            $htmlCover = view('dossiers.pdf', compact('dossier', 'pageGardeDataUri', 'documents'))->with('renderMode', 'cover')->render();
            $dompdfCover = new Dompdf(['isRemoteEnabled' => true]);
            $dompdfCover->loadHtml($htmlCover);
            $dompdfCover->setPaper('A4', 'portrait');
            $dompdfCover->render();
            $coverTemp = tempnam(sys_get_temp_dir(), 'dossier_cover_') . '.pdf';
            file_put_contents($coverTemp, $dompdfCover->output());
            $filesToMerge[] = $this->numberedEntry($coverTemp);
            $tempFiles[] = $coverTemp;

            $htmlSummary = view('dossiers.pdf', compact('dossier', 'pageGardeDataUri', 'documents'))
                ->with(['renderMode' => 'summary'])
                ->render();
            $dompdfSummary = new Dompdf(['isRemoteEnabled' => true]);
            $dompdfSummary->loadHtml($htmlSummary);
            $dompdfSummary->setPaper('A4', 'portrait');
            $dompdfSummary->render();
            $summaryTemp = tempnam(sys_get_temp_dir(), 'dossier_summary_') . '.pdf';
            file_put_contents($summaryTemp, $dompdfSummary->output());
            $filesToMerge[] = $this->numberedEntry($summaryTemp);
            $tempFiles[] = $summaryTemp;

            foreach ($documents as $doc) {
                $plan = $this->attachmentPlan($doc);
                $imageAttachments = $doc->fichiers->filter(function ($f) {
                    $ext = strtolower(pathinfo($f->chemin_fichier, PATHINFO_EXTENSION));
                    return in_array($ext, ['png', 'jpg', 'jpeg', 'gif'], true);
                });

                $skipHtmlDocPage = $plan['pdfs']->isNotEmpty()
                    && $imageAttachments->isEmpty()
                    && $plan['others']->isEmpty()
                    && $doc->valeurs->isEmpty()
                    && $doc->bordereau->isEmpty()
                    && $doc->typeDocument;

                $isExp42a = $this->isExp42aDoc($doc);
                $isExp42b = $this->isExp42bDoc($doc);
                $isExp42aSuite = $this->isExp42aSuiteDoc($doc);
                $isExp42bSuite = $this->isExp42bSuiteDoc($doc);
                $isFormulairePer = $this->isFormulairePerDoc($doc);
                $isFormulaireMat = $this->isFormulaireMatDoc($doc);
                $isFicheTechniqueArticles = $this->isFicheTechniqueArticlesDoc($doc);
                if ($isExp42a || $isExp42b || $isExp42aSuite || $isExp42bSuite || $isFormulairePer || $isFormulaireMat || $isFicheTechniqueArticles) {
                    $skipHtmlDocPage = true;
                }

                // Une pièce "upload uniquement" sans aucun fichier joint ni contenu
                // n'a rien à afficher : on évite de générer une page vide pour elle.
                $hasNothingToShow = ($doc->typeDocument
                    && \App\Models\TypeDocument::isUploadOnlyName($doc->typeDocument->nom, $doc->typeDocument->type_formulaire)
                    && $doc->fichiers->isEmpty()
                    && $doc->valeurs->isEmpty()
                    && $doc->bordereau->isEmpty()
                    && empty($doc->content))
                    || ($isFicheTechniqueArticles && !FicheTechniqueFichier::where('dossier_document_id', $doc->id)->exists());

                $htmlTitle = view('dossiers.pdf', compact('dossier', 'pageGardeDataUri'))->with([
                    'documents' => collect([$doc]),
                    'renderMode' => 'title',
                ])->render();
                $dompdfTitle = new Dompdf(['isRemoteEnabled' => true]);
                $dompdfTitle->loadHtml($htmlTitle);
                $dompdfTitle->setPaper('A4', 'portrait');
                $dompdfTitle->render();
                $titleTemp = tempnam(sys_get_temp_dir(), 'dossier_title_') . '.pdf';
                file_put_contents($titleTemp, $dompdfTitle->output());
                $filesToMerge[] = $this->numberedEntry($titleTemp);
                $tempFiles[] = $titleTemp;

                if (!$skipHtmlDocPage && !$hasNothingToShow) {
                    $htmlDoc = view('dossiers.pdf', compact('dossier', 'pageGardeDataUri'))->with([
                        'documents' => collect([$doc]),
                        'renderMode' => 'doc',
                    ])->render();
                    $docOrientation = $doc->typeDocument
                        && (in_array(trim($doc->typeDocument->nom), $this->getLandscapeTableDocNames(), true)
                            || ($doc->typeDocument->type_formulaire === 'libre' && $doc->paysage))
                        ? 'landscape'
                        : 'portrait';
                    $dompdfDoc = new Dompdf(['isRemoteEnabled' => true]);
                    $dompdfDoc->loadHtml($htmlDoc);
                    $dompdfDoc->setPaper('A4', $docOrientation);
                    $dompdfDoc->render();
                    $docTemp = tempnam(sys_get_temp_dir(), 'dossier_doc_') . '.pdf';
                    file_put_contents($docTemp, $dompdfDoc->output());
                    $filesToMerge[] = $this->numberedEntry($docTemp);
                    $tempFiles[] = $docTemp;
                }

                foreach ($plan['pdfs'] as $f) {
                    $filesToMerge[] = $this->attachmentEntry(storage_path('app/public/' . ltrim($f->chemin_fichier, '/')), $doc->typeDocument->nom);
                }
                if ($plan['others']->isNotEmpty()) {
                    $filesToMerge[] = $this->placeholderEntry($doc->typeDocument->nom);
                }
                if ($isFicheTechniqueArticles) {
                    foreach ($this->ficheTechniqueArticlesEntries($doc) as $entry) {
                        $filesToMerge[] = $entry;
                        $tempFiles[] = $entry['file'];
                    }
                }
                if ($isExp42a) {
                    foreach ($this->exp42aPdfPaths($dossier) as $path) {
                        $filesToMerge[] = $this->numberedEntry($path);
                        $tempFiles[] = $path;
                    }
                }
                if ($isExp42b) {
                    foreach ($this->exp42bPdfPaths($dossier) as $path) {
                        $filesToMerge[] = $this->numberedEntry($path);
                        $tempFiles[] = $path;
                    }
                }
                if ($isExp42aSuite) {
                    foreach ($this->exp42aSuitePdfPaths($dossier) as $path) {
                        $filesToMerge[] = $this->numberedEntry($path);
                        $tempFiles[] = $path;
                    }
                }
                if ($isExp42bSuite) {
                    foreach ($this->exp42bSuitePdfPaths($dossier) as $path) {
                        $filesToMerge[] = $this->numberedEntry($path);
                        $tempFiles[] = $path;
                    }
                }
                if ($isFormulairePer) {
                    foreach ($this->formulairePerPdfPaths($dossier) as $path) {
                        $filesToMerge[] = $this->numberedEntry($path);
                        $tempFiles[] = $path;
                    }
                }
                if ($isFormulaireMat) {
                    foreach ($this->formulaireMatPdfPaths($dossier) as $path) {
                        $filesToMerge[] = $this->numberedEntry($path);
                        $tempFiles[] = $path;
                    }
                }
                if ($this->entrepriseDocumentPickerType($doc) !== null) {
                    foreach ($this->entrepriseDocumentPdfPaths($doc) as $path) {
                        $filesToMerge[] = $this->attachmentEntry($path, $doc->typeDocument->nom);
                        $tempFiles[] = $path;
                    }
                }
            }

            $finalFilename = 'dossiers/documents/dossier_' . $dossier->id . '_merged_' . time() . '.pdf';
            $finalFull = storage_path('app/public/' . $finalFilename);
            $this->mergePdfs($filesToMerge, $finalFull);

            try {
                $dossier->statut = 'genere';
                $dossier->save();
            } catch (\Throwable $e) {}

            if (file_exists($finalFull)) {
                return response()->file($finalFull, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="dossier_' . $dossier->id . '.pdf"',
                ]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Échec de la fusion du PDF du dossier #' . $dossier->id . ' : ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('dossiers.show', $dossier->id)
                ->with('error', 'La génération du PDF a échoué : ' . $e->getMessage());
        } finally {
            foreach ($tempFiles as $tempFile) {
                if (file_exists($tempFile)) {
                    @unlink($tempFile);
                }
            }
        }

        return response()->file($finalFull, ['Content-Type' => 'application/pdf']);
    }

    /**
     * Génère le PDF d'aperçu d'un seul document, en reproduisant exactement la
     * même logique (page de titre, rendu HTML ou fusion du fichier PDF joint
     * "brut") que celle utilisée pour ce même document lors de la génération
     * du PDF complet du dossier, afin que l'aperçu corresponde toujours au
     * document réel.
     */
    public function previewSingleDocumentPdf(DossierDocument $document): string
    {
        $dossier = Dossier::with(['entreprise', 'signataires', 'typeDossier'])->find($document->dossier_id);
        $this->syncFormulaireFin31FromEntreprise($dossier);
        $this->syncFormulaireBeneficiairesFromEntreprise($dossier);
        $document->refresh();

        $pageGardeDataUri = null;
        if (!empty($dossier->page_garde_path)) {
            $fullPath = storage_path('app/public/' . $dossier->page_garde_path);
            if (file_exists($fullPath)) {
                $mime = mime_content_type($fullPath) ?: 'application/octet-stream';
                $pageGardeDataUri = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fullPath));
            }
        }

        $tempFiles = [];
        $filesToMerge = [];

        try {
            $htmlTitle = view('dossiers.pdf', compact('dossier', 'pageGardeDataUri'))->with([
                'documents' => collect([$document]),
                'renderMode' => 'title',
            ])->render();
            $dompdfTitle = new Dompdf(['isRemoteEnabled' => true]);
            $dompdfTitle->loadHtml($htmlTitle);
            $dompdfTitle->setPaper('A4', 'portrait');
            $dompdfTitle->render();
            $titleTemp = tempnam(sys_get_temp_dir(), 'apercu_title_') . '.pdf';
            file_put_contents($titleTemp, $dompdfTitle->output());
            $filesToMerge[] = $this->numberedEntry($titleTemp);
            $tempFiles[] = $titleTemp;

            $plan = $this->attachmentPlan($document);
            $docName = trim(optional($document->typeDocument)->nom ?? '');
            $imageAttachments = $document->fichiers->filter(function ($f) {
                $ext = strtolower(pathinfo($f->chemin_fichier, PATHINFO_EXTENSION));
                return in_array($ext, ['png', 'jpg', 'jpeg', 'gif'], true);
            });

            $skipHtmlDocPage = $plan['pdfs']->isNotEmpty()
                && $imageAttachments->isEmpty()
                && $plan['others']->isEmpty()
                && $document->valeurs->isEmpty()
                && $document->bordereau->isEmpty();
            $isFicheTechniqueArticlesPreview = $this->isFicheTechniqueArticlesDoc($document);
            if ($this->isExp42aDoc($document) || $this->isExp42bDoc($document) || $this->isExp42aSuiteDoc($document) || $this->isExp42bSuiteDoc($document) || $this->isFormulairePerDoc($document) || $this->isFormulaireMatDoc($document) || $isFicheTechniqueArticlesPreview) {
                $skipHtmlDocPage = true;
            }

            $hasNothingToShow = ($document->typeDocument
                && TypeDocument::isUploadOnlyName($document->typeDocument->nom, $document->typeDocument->type_formulaire)
                && $document->fichiers->isEmpty()
                && $document->valeurs->isEmpty()
                && $document->bordereau->isEmpty()
                && empty($document->content))
                || ($isFicheTechniqueArticlesPreview && !FicheTechniqueFichier::where('dossier_document_id', $document->id)->exists());

            if (!$skipHtmlDocPage && !$hasNothingToShow) {
                $htmlDoc = view('dossiers.pdf', compact('dossier', 'pageGardeDataUri'))->with([
                    'documents' => collect([$document]),
                    'renderMode' => 'doc',
                ])->render();
                $docOrientation = $document->typeDocument
                    && (in_array(trim($document->typeDocument->nom), $this->getLandscapeTableDocNames(), true)
                        || ($document->typeDocument->type_formulaire === 'libre' && $document->paysage))
                    ? 'landscape'
                    : 'portrait';
                $dompdfDoc = new Dompdf(['isRemoteEnabled' => true]);
                $dompdfDoc->loadHtml($htmlDoc);
                $dompdfDoc->setPaper('A4', $docOrientation);
                $dompdfDoc->render();
                $docTemp = tempnam(sys_get_temp_dir(), 'apercu_doc_') . '.pdf';
                file_put_contents($docTemp, $dompdfDoc->output());
                $filesToMerge[] = $this->numberedEntry($docTemp);
                $tempFiles[] = $docTemp;
            }

            foreach ($plan['pdfs'] as $f) {
                $filesToMerge[] = $this->attachmentEntry(storage_path('app/public/' . ltrim($f->chemin_fichier, '/')), $docName);
            }
            if ($plan['others']->isNotEmpty()) {
                $filesToMerge[] = $this->placeholderEntry($docName);
            }
            if ($isFicheTechniqueArticlesPreview) {
                foreach ($this->ficheTechniqueArticlesEntries($document) as $entry) {
                    $filesToMerge[] = $entry;
                    $tempFiles[] = $entry['file'];
                }
            }
            if ($this->isExp42aDoc($document)) {
                foreach ($this->exp42aPdfPaths($dossier) as $path) {
                    $filesToMerge[] = $this->numberedEntry($path);
                    $tempFiles[] = $path;
                }
            }
            if ($this->isExp42bDoc($document)) {
                foreach ($this->exp42bPdfPaths($dossier) as $path) {
                    $filesToMerge[] = $this->numberedEntry($path);
                    $tempFiles[] = $path;
                }
            }
            if ($this->isExp42aSuiteDoc($document)) {
                foreach ($this->exp42aSuitePdfPaths($dossier) as $path) {
                    $filesToMerge[] = $this->numberedEntry($path);
                    $tempFiles[] = $path;
                }
            }
            if ($this->isExp42bSuiteDoc($document)) {
                foreach ($this->exp42bSuitePdfPaths($dossier) as $path) {
                    $filesToMerge[] = $this->numberedEntry($path);
                    $tempFiles[] = $path;
                }
            }
            if ($this->isFormulairePerDoc($document)) {
                foreach ($this->formulairePerPdfPaths($dossier) as $path) {
                    $filesToMerge[] = $this->numberedEntry($path);
                    $tempFiles[] = $path;
                }
            }
            if ($this->isFormulaireMatDoc($document)) {
                foreach ($this->formulaireMatPdfPaths($dossier) as $path) {
                    $filesToMerge[] = $this->numberedEntry($path);
                    $tempFiles[] = $path;
                }
            }
            if ($this->entrepriseDocumentPickerType($document) !== null) {
                foreach ($this->entrepriseDocumentPdfPaths($document) as $path) {
                    $filesToMerge[] = $this->attachmentEntry($path, $docName);
                    $tempFiles[] = $path;
                }
            }

            $outputTemp = tempnam(sys_get_temp_dir(), 'apercu_merged_') . '.pdf';
            $tempFiles[] = $outputTemp;

            $this->mergePdfs($filesToMerge, $outputTemp);

            return file_get_contents($outputTemp);
        } finally {
            foreach ($tempFiles as $t) {
                if (file_exists($t)) {
                    @unlink($t);
                }
            }
        }
    }

    /**
     * Pièce de fusion correspondant à un document GÉNÉRÉ par l'appli (page de
     * garde, sommaire, page de titre, rendu HTML d'un document, EXP 4.1/4.2,
     * Formulaire PER/MAT...) : ses pages comptent dans la numérotation finale
     * du dossier.
     */
    private function numberedEntry(string $path): array
    {
        return ['file' => $path, 'numbered' => true];
    }

    /**
     * Pièce de fusion correspondant à une PIÈCE JOINTE brute (RCCM, IFU, RIB,
     * attestation scannée...) : intégrée telle quelle dans le PDF final, mais
     * jamais numérotée, car ce n'est pas un contenu généré par l'appli.
     */
    private function attachmentEntry(string $path, ?string $label = null): array
    {
        return ['file' => $path, 'label' => $label, 'numbered' => false];
    }

    /**
     * Page "signalétique" générée pour une pièce jointe dans un format non
     * convertible : jamais numérotée, au même titre que les pièces jointes.
     */
    private function placeholderEntry(string $label): array
    {
        return ['placeholder' => true, 'label' => $label, 'numbered' => false];
    }

    /**
     * Fusionne plusieurs fichiers PDF en un seul fichier de sortie en utilisant
     * FPDI, puis numérote ("Page X / Y") uniquement les pages issues d'entrées
     * marquées "numbered" (voir numberedEntry()/attachmentEntry()/
     * placeholderEntry()) - les pièces jointes brutes du dossier (RCCM, IFU,
     * RIB, attestations...) ne sont jamais numérotées, seul le contenu généré
     * par l'appli l'est, en continu sur l'ensemble du dossier.
     * Nécessite l'installation de setasign/fpdi (+ fpdf ou tcpdf selon la configuration).
     * @param array $files Entrées à fusionner (voir les helpers *Entry() ci-dessus)
     * @param string $outputFull Chemin absolu du fichier de sortie
     * @throws \Throwable
     */
    private function mergePdfs(array $files, string $outputFull)
    {
        if (empty($files)) {
            throw new \InvalidArgumentException('Aucun fichier fourni pour la fusion.');
        }

        // Utiliser la classe FPDI si disponible
        if (!class_exists('\\setasign\\Fpdi\\Fpdi')) {
            throw new \RuntimeException('FPDI non disponible. Exécutez `composer require setasign/fpdi setasign/fpdf`');
        }

        $rawMerged = tempnam(sys_get_temp_dir(), 'dossier_raw_merge_') . '.pdf';
        try {
            $numberedFlags = $this->mergePdfsRaw($files, $rawMerged);
            $this->applyNumberingToMergedPdf($rawMerged, $numberedFlags, $outputFull);
        } finally {
            if (file_exists($rawMerged)) {
                @unlink($rawMerged);
            }
        }
    }

    /**
     * Fait la fusion brute (sans numérotation) et retourne, pour chaque page
     * du PDF final dans l'ordre, si elle provient d'une entrée "numbered" ou
     * non - utilisé ensuite par applyNumberingToMergedPdf().
     */
    private function mergePdfsRaw(array $files, string $outputFull): array
    {
        $pdf = new \setasign\Fpdi\Fpdi();
        $flags = [];

        foreach ($files as $entry) {
            $numbered = is_array($entry) ? (bool) ($entry['numbered'] ?? false) : true;
            $pagesBefore = $pdf->PageNo();

            if (is_array($entry) && !empty($entry['placeholder'])) {
                $this->addAttachmentPlaceholderPage($pdf, $entry['label']);
            } else {
                $file = is_array($entry) ? $entry['file'] : $entry;
                $label = is_array($entry) ? ($entry['label'] ?? basename($file)) : basename($file);
                if (!file_exists($file)) {
                    continue;
                }

                try {
                    $pageCount = $pdf->setSourceFile($file);
                    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                        $tplIdx = $pdf->importPage($pageNo);
                        $specs = $pdf->getTemplateSize($tplIdx);
                        $orientation = $specs['orientation'] ?? ($specs['width'] > $specs['height'] ? 'L' : 'P');
                        $pdf->AddPage($orientation, [$specs['width'], $specs['height']]);
                        $pdf->useTemplate($tplIdx);
                    }
                } catch (\Throwable $e) {
                    // Le parseur FPDI gratuit ne supporte pas toutes les techniques de
                    // compression PDF (courant pour les scans). On rabat alors sur une
                    // conversion en image de chaque page, pour pouvoir quand même
                    // inclure ce fichier dans le dossier fusionné.
                    $this->appendPdfAsImages($pdf, $file);
                    if ($pdf->PageNo() === $pagesBefore) {
                        $this->addAttachmentPlaceholderPage($pdf, $label);
                    }
                }
            }

            $added = $pdf->PageNo() - $pagesBefore;
            for ($i = 0; $i < $added; $i++) {
                $flags[] = $numbered;
            }
        }

        $pdf->Output('F', $outputFull);

        return $flags;
    }

    /**
     * Reprend un PDF déjà fusionné page par page (FPDI) et superpose
     * "Page X / Y" uniquement sur les pages marquées numérotées dans $flags,
     * X/Y étant comptés uniquement parmi ces pages (les pièces jointes ne
     * décalent jamais la numérotation).
     */
    private function applyNumberingToMergedPdf(string $rawMerged, array $flags, string $outputFull): void
    {
        $totalNumbered = count(array_filter($flags));
        if ($totalNumbered === 0) {
            copy($rawMerged, $outputFull);
            return;
        }

        $pdf = new \setasign\Fpdi\Fpdi();
        $pdf->SetAutoPageBreak(false);
        $pageCount = $pdf->setSourceFile($rawMerged);
        $seq = 0;

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $tplIdx = $pdf->importPage($pageNo);
            $specs = $pdf->getTemplateSize($tplIdx);
            $orientation = $specs['orientation'] ?? ($specs['width'] > $specs['height'] ? 'L' : 'P');
            $pdf->AddPage($orientation, [$specs['width'], $specs['height']]);
            $pdf->useTemplate($tplIdx);

            if (!empty($flags[$pageNo - 1])) {
                $seq++;
                try {
                    $text = "Page {$seq} / {$totalNumbered}";
                    $pdf->SetFont('Arial', '', 9);
                    $textWidth = $pdf->GetStringWidth($text);
                    $x = ($specs['width'] - $textWidth) / 2;
                    $y = $specs['height'] - 12;
                    // Certains tableaux longs (ex: Description technique des
                    // fournitures/services) peuvent deborder tres pres du bas
                    // de page malgre la marge reservee. Un fond blanc sous le
                    // numero garantit qu'il reste lisible meme si une ligne de
                    // tableau s'etend jusque-la, plutot que de se superposer
                    // illisiblement au texte.
                    $padding = 1.5;
                    $pdf->SetFillColor(255, 255, 255);
                    $pdf->Rect($x - $padding, $y - $padding, $textWidth + 2 * $padding, 5 + 2 * $padding, 'F');
                    $pdf->SetXY($x, $y);
                    $pdf->Cell($textWidth, 5, $text);
                } catch (\Throwable $e) {
                    // En cas d'erreur, on continue sans numérotation pour cette page.
                }
            }
        }

        $pdf->Output('F', $outputFull);
    }

    /**
     * Décrit les fichiers d'un document à fusionner : PDF intégrés tels quels
     * (sauf pour les documents générés à partir de leur contenu) et autres
     * formats, qui ne peuvent pas être convertis et seront signalés par une page.
     */
    private function attachmentPlan(DossierDocument $doc): array
    {
        $fichiers = $doc->fichiers ?? collect();
        $extension = fn ($f) => strtolower(pathinfo($f->chemin_fichier, PATHINFO_EXTENSION));
        $docName = trim(optional($doc->typeDocument)->nom ?? '');
        $content = trim((string) $doc->content);
        $contentIsBlank = $content === '' || in_array($content, ['{}', '[]', 'null'], true);
        $canMergePdfs = ($contentIsBlank || TypeDocument::isExp42Name($docName))
            && !in_array($docName, [
                "Déclaration de garantie d'offre",
                'Declaration de garantie d\'offre',
                'Formulaire ELI – 1.1 : Formulaire de renseignements sur le candidat',
            ], true);

        return [
            'pdfs' => $canMergePdfs ? $fichiers->filter(fn ($f) => $extension($f) === 'pdf')->values() : collect(),
            'others' => $fichiers->filter(fn ($f) => !in_array($extension($f), ['png', 'jpg', 'jpeg', 'gif', 'pdf'], true))->values(),
        ];
    }

    private function addAttachmentPlaceholderPage(\setasign\Fpdi\Fpdi $pdf, string $label): void
    {
        $encode = function (string $text) {
            return iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $text) ?: $text;
        };

        $pdf->AddPage('P', 'A4');
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->SetY(100);
        $pdf->MultiCell(0, 8, $encode($label), 0, 'C');
        $pdf->Ln(6);
        $pdf->SetFont('Arial', '', 11);
        $pdf->MultiCell(0, 7, $encode('Pièce jointe non intégrable dans ce PDF (format non pris en charge par le serveur). Le fichier original reste disponible dans le dossier.'), 0, 'C');
    }

    /**
     * Ajoute chaque page d'un PDF au document FPDI en cours sous forme d'image
     * (via Ghostscript), pour les PDF que le parseur gratuit de FPDI ne sait pas
     * lire directement (techniques de compression non supportées).
     */
    private function appendPdfAsImages(\setasign\Fpdi\Fpdi $pdf, string $file): void
    {
        foreach ($this->renderPdfPagesToPng($file) as $imagePath) {
            $size = @getimagesize($imagePath);
            if (!$size) {
                @unlink($imagePath);
                continue;
            }

            [$widthPx, $heightPx] = $size;
            $widthMm = $widthPx / 150 * 25.4;
            $heightMm = $heightPx / 150 * 25.4;
            $orientation = $widthMm > $heightMm ? 'L' : 'P';

            $pdf->AddPage($orientation, [$widthMm, $heightMm]);
            $pdf->Image($imagePath, 0, 0, $widthMm, $heightMm);

            @unlink($imagePath);
        }
    }

    /**
     * Convertit chaque page d'un PDF en image PNG (150 dpi) et retourne leurs chemins.
     * Imagick est essayé en premier : il ne passe pas par exec(), souvent restreint
     * en contexte web. Ghostscript sert de repli.
     * @return string[]
     */
    private function renderPdfPagesToPng(string $file): array
    {
        $prefix = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('pdfpage_', true);

        if (class_exists(\Imagick::class)) {
            try {
                $imagick = new \Imagick();
                $imagick->setResolution(150, 150);
                $imagick->readImage($file);
                $paths = [];
                foreach ($imagick as $index => $page) {
                    $path = sprintf('%s_%03d.png', $prefix, $index);
                    $page->setImageFormat('png');
                    $page->writeImage($path);
                    $paths[] = $path;
                }
                $imagick->clear();
                if ($paths) {
                    return $paths;
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Rendu PDF via Imagick impossible : ' . $e->getMessage());
            }
        }

        $gsPath = $this->findGhostscriptExecutable();
        if (!$gsPath) {
            \Illuminate\Support\Facades\Log::warning('Ghostscript introuvable, PDF joint non converti : ' . $file);
            return [];
        }

        $outputPattern = $prefix . '_%03d.png';
        $cmd = '"' . str_replace('\\', '/', $gsPath) . '" -q -dNOPAUSE -dBATCH -dSAFER -sDEVICE=png16m -r150 '
            . '-sOutputFile="' . str_replace('\\', '/', $outputPattern) . '" "' . str_replace('\\', '/', $file) . '" 2>&1';
        exec($cmd, $cmdOutput, $returnCode);
        if ($returnCode !== 0) {
            \Illuminate\Support\Facades\Log::warning('Ghostscript a échoué (code ' . $returnCode . ') : ' . implode(' ', $cmdOutput));
        }

        $pages = glob($prefix . '_*.png') ?: [];
        sort($pages);

        return $pages;
    }

    /**
     * Recherche l'exécutable Ghostscript sur le système (Windows/Linux).
     */
    private function findGhostscriptExecutable(): ?string
    {
        $commands = [
            'where gswin64c 2>nul',
            'where gswin32c 2>nul',
            'where gs 2>nul',
            'which gswin64c 2>/dev/null',
            'which gswin32c 2>/dev/null',
            'which gs 2>/dev/null',
        ];

        foreach ($commands as $command) {
            $result = trim((string) shell_exec($command));
            if ($result !== '') {
                return explode(PHP_EOL, $result)[0];
            }
        }

        $commonPaths = [
            '/usr/bin/gs',
            '/usr/local/bin/gs',
            'C:/Program Files/gs/*/bin/gswin64c.exe',
            'C:/Program Files/gs/*/bin/gswin64.exe',
            'C:/Program Files/gs/*/bin/gs.exe',
            'C:/Program Files (x86)/gs/*/bin/gswin32c.exe',
            'C:/Program Files (x86)/gs/*/bin/gswin32.exe',
            'C:/Program Files (x86)/gs/*/bin/gs.exe',
        ];

        foreach ($commonPaths as $pattern) {
            foreach (glob($pattern) as $candidate) {
                if (file_exists($candidate)) {
                    return $candidate;
                }
            }
        }

        return null;
    }

    private function normalizeDocumentOrders(Dossier $dossier): void
    {
        $documents = $dossier->documents()->orderBy('ordre')->orderBy('created_at')->orderBy('id')->get();

        if ($documents->isEmpty()) {
            return;
        }

        $orders = $documents->pluck('ordre')->filter(function ($value) {
            return is_numeric($value) && (int) $value > 0;
        })->values();

        $hasMeaningfulOrder = $documents->contains(function ($doc) {
            return is_numeric($doc->ordre) && (int) $doc->ordre > 1;
        });
        $hasDuplicateOrder = $orders->count() !== $orders->unique()->count();

        if (!$hasMeaningfulOrder || $hasDuplicateOrder) {
            foreach ($documents as $index => $document) {
                $document->update(['ordre' => $index + 1]);
            }
        }
    }

    /**
     * Classe le sommaire selon STANDARD_SOMMAIRE ; les pièces absentes de cette
     * liste restent à la fin, dans leur ordre actuel.
     */
    public function applyStandardOrder(Dossier $dossier)
    {
        $this->applySommaireStandardOrder($dossier);

        return redirect()->route('dossiers.show', $dossier->id)
            ->with('success', 'Sommaire classé selon l\'ordre standard.');
    }

    /**
     * Historique des modifications d'un dossier : qui a fait quoi et quand
     * (creation, document renseigne, piece retiree, duplication...). Non
     * bloquant par conception : une erreur d'ecriture du log ne doit jamais
     * faire echouer l'action metier elle-meme.
     */
    private function logChange(Dossier $dossier, string $action, string $resume, ?string $documentNom = null, ?int $typeDocumentId = null): void
    {
        try {
            DossierChangeLog::create([
                'dossier_id' => $dossier->id,
                'utilisateur_id' => auth()->id(),
                'type_document_id' => $typeDocumentId,
                'document_nom' => $documentNom,
                'action' => $action,
                'resume' => $resume,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Echec enregistrement historique dossier: ' . $e->getMessage());
        }
    }

    /**
     * Cherche un dossier existant partageant la meme reference (ref ou
     * reference_dossier) que celle saisie a l'etape 4. Ignore les valeurs
     * vides pour ne pas faire matcher deux dossiers sans reference.
     */
    private function findDossierWithSameReference(?string $ref, ?string $referenceDossier): ?Dossier
    {
        $ref = trim((string) $ref);
        $referenceDossier = trim((string) $referenceDossier);

        if ($ref === '' && $referenceDossier === '') {
            return null;
        }

        return Dossier::where(function ($query) use ($ref, $referenceDossier) {
                if ($ref !== '') {
                    $query->orWhere('ref', $ref);
                }
                if ($referenceDossier !== '') {
                    $query->orWhere('reference_dossier', $referenceDossier);
                }
            })
            ->latest('id')
            ->first();
    }

    /**
     * Une offre est consideree "financiere" d'apres le texte libre saisi a
     * l'etape 4 (champ type_offre) : pas de colonne dediee, donc detection
     * par simple recherche de "financ" (financiere, financier...).
     */
    private function isOffreFinanciere(Dossier $dossier): bool
    {
        return str_contains($this->normalizeSommaireName((string) $dossier->type_offre), 'financ');
    }

    /**
     * Categorie d'offre ('financiere', 'technique', 'administrative') pour un
     * texte "type_offre" libre, ou null si aucun mot-cle connu n'y est trouve.
     */
    private function offerCategoryFor(?string $typeOffre): ?string
    {
        $normalized = $this->normalizeSommaireName((string) $typeOffre);
        if ($normalized === '') {
            return null;
        }

        foreach (self::OFFER_CATEGORIES as $key => $info) {
            if (str_contains($normalized, $info['keyword'])) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Categories d'offre (financiere/technique/administrative) qui n'existent
     * pas encore pour le meme marche que ce dossier - detecte par meme
     * reference_dossier ET meme entreprise. Renvoie [] si le dossier n'a pas
     * encore de reference_dossier renseignee (impossible de grouper les
     * dossiers du meme marche sans cela).
     */
    private function missingOfferCategories(Dossier $dossier): array
    {
        $reference = trim((string) $dossier->reference_dossier);
        if ($reference === '') {
            return [];
        }

        $siblingTypeOffres = Dossier::where('reference_dossier', $dossier->reference_dossier)
            ->where('entreprise_id', $dossier->entreprise_id)
            ->pluck('type_offre');

        $present = [];
        foreach ($siblingTypeOffres as $typeOffre) {
            $category = $this->offerCategoryFor($typeOffre);
            if ($category) {
                $present[$category] = true;
            }
        }

        $missingKeys = array_diff(array_keys(self::OFFER_CATEGORIES), array_keys($present));

        $missing = [];
        foreach ($missingKeys as $key) {
            $missing[$key] = self::OFFER_CATEGORIES[$key]['label'];
        }

        return $missing;
    }

    /**
     * Renomme le dossier duplique pour la nouvelle categorie d'offre : si le
     * nom d'origine contient deja le libelle de son ancienne offre (ex: "DAO
     * Fournitures - Offre technique"), le remplace par le nouveau libelle ;
     * sinon, ajoute simplement ce libelle a la suite.
     */
    private function renameForOfferCategory(string $originalName, ?string $originalTypeOffre, string $newLabel): string
    {
        $originalCategory = $this->offerCategoryFor($originalTypeOffre);
        if ($originalCategory) {
            $originalLabel = self::OFFER_CATEGORIES[$originalCategory]['label'];
            if (stripos($originalName, $originalLabel) !== false) {
                return str_ireplace($originalLabel, $newLabel, $originalName);
            }
        }

        return trim($originalName) . ' — ' . $newLabel;
    }

    /**
     * Retire un suffixe " — <libelle de categorie>" ajoute par
     * renameForOfferCategory() (ex: "POUELLE CALAVI — Offre financière"
     * devient "POUELLE CALAVI") : utilise pour l'affichage du nom du marche
     * dans la liste des dossiers, ou la categorie est deja visible via les
     * badges de la colonne "Offres" et n'a pas besoin d'etre repetee dans le
     * nom.
     */
    private function stripOfferCategorySuffix(?string $nom): string
    {
        $nom = trim((string) $nom);

        foreach (self::OFFER_CATEGORIES as $info) {
            $suffix = ' — ' . $info['label'];
            if (str_ends_with(mb_strtolower($nom, 'UTF-8'), mb_strtolower($suffix, 'UTF-8'))) {
                return trim(mb_substr($nom, 0, mb_strlen($nom) - mb_strlen($suffix), 'UTF-8'));
            }
        }

        return $nom;
    }

    /**
     * Cree les pieces du modele de sommaire standard (technique ou
     * financiere, selon le type_offre actuel du dossier - voir
     * isOffreFinanciere()) qui manquent encore au dossier, sans jamais
     * toucher a celles deja presentes, puis reordonne l'ensemble. Meme
     * logique additive que le select "Modele de sommaire" de l'etape 5, mais
     * cote serveur (utilise par createOtherOffer()).
     */
    private function ensureStandardSommairePieces(Dossier $dossier): void
    {
        $names = $this->isOffreFinanciere($dossier) ? self::STANDARD_SOMMAIRE_FINANCIERE : self::STANDARD_SOMMAIRE;
        $typeIds = TypeDocument::whereIn('nom', $names)->pluck('id', 'nom');
        $ordre = (int) $dossier->documents()->max('ordre');

        foreach ($names as $nom) {
            $typeId = $typeIds[$nom] ?? null;
            if (!$typeId) {
                continue;
            }
            $exists = DossierDocument::where('dossier_id', $dossier->id)->where('type_document_id', $typeId)->exists();
            if ($exists) {
                continue;
            }
            $ordre++;
            $dossierDocument = DossierDocument::create([
                'dossier_id' => $dossier->id,
                'type_document_id' => $typeId,
                'ordre' => $ordre,
                'statut' => 'vide',
            ]);
            $this->autoLinkEntrepriseDocument($dossier, $dossierDocument);
        }

        $dossier->unsetRelation('documents');
        $this->applySommaireStandardOrder($dossier);
    }

    /**
     * Retire du sommaire les pieces qui appartiennent EXCLUSIVEMENT a l'autre
     * modele standard (technique si on vient de passer en financiere, ou
     * l'inverse) : utilise par createOtherOffer(), qui duplique au depart
     * TOUTES les pieces de l'offre d'origine - sans ce nettoyage, une offre
     * financiere creee depuis une offre technique garderait en plus toutes
     * les pieces techniques (Description technique, EXP 4.x, etc.), alors
     * que seules les pieces du sommaire financier par defaut doivent
     * s'appliquer. Ne touche jamais une piece commune aux deux modeles, ni
     * une piece hors de ces 2 listes (document personnalise, piece jointe
     * libre, piece administrative non couverte par les modeles...).
     */
    private function pruneSommaireForOfferCategory(Dossier $dossier): void
    {
        $targetNames = array_map(
            fn ($nom) => $this->normalizeSommaireName($nom),
            $this->isOffreFinanciere($dossier) ? self::STANDARD_SOMMAIRE_FINANCIERE : self::STANDARD_SOMMAIRE
        );
        $otherNames = array_map(
            fn ($nom) => $this->normalizeSommaireName($nom),
            $this->isOffreFinanciere($dossier) ? self::STANDARD_SOMMAIRE : self::STANDARD_SOMMAIRE_FINANCIERE
        );

        $documents = $dossier->documents()->with(['typeDocument', 'fichiers', 'bordereau.lignes'])->get();

        foreach ($documents as $document) {
            $nom = $this->normalizeSommaireName(optional($document->typeDocument)->nom ?? '');
            if ($nom === '' || !in_array($nom, $otherNames, true) || in_array($nom, $targetNames, true)) {
                continue;
            }

            foreach ($document->fichiers as $fichier) {
                if ($fichier->chemin_fichier && Storage::disk('public')->exists($fichier->chemin_fichier)) {
                    Storage::disk('public')->delete($fichier->chemin_fichier);
                }
                $fichier->delete();
            }
            $document->valeurs()->delete();
            foreach ($document->bordereau as $bordereau) {
                $bordereau->lignes()->delete();
                $bordereau->delete();
            }
            $document->delete();
        }

        $dossier->unsetRelation('documents');
    }

    /**
     * Duplique les donnees d'un dossier (hors pieces jointes) vers une
     * nouvelle instance deja persistee : entreprise, signataires,
     * utilisateurs partages, chiffres d'affaires, et le sommaire complet
     * (documents + bordereaux/lignes) avec tout contenu deja saisi. Utilise
     * par duplicate() et createOtherOffer().
     */
    private function replicateDossierData(Dossier $dossier): Dossier
    {
        $dossier->load(['documents.bordereau.lignes', 'documents.fichiers', 'signataires', 'utilisateurs', 'chiffresAffaires']);

        $copy = $dossier->replicate();
        $copy->statut = 'en_cours';
        $copy->save();

        foreach ($dossier->signataires as $signataire) {
            $copy->signataires()->attach($signataire->id, ['role_signataire' => $signataire->pivot->role_signataire]);
        }

        foreach ($dossier->utilisateurs as $utilisateur) {
            $copy->utilisateurs()->attach($utilisateur->id);
        }

        foreach ($dossier->chiffresAffaires as $chiffre) {
            $newChiffre = $chiffre->replicate();
            $newChiffre->dossier_id = $copy->id;
            $newChiffre->save();
        }

        foreach ($dossier->documents as $doc) {
            $newDoc = $doc->replicate();
            $newDoc->dossier_id = $copy->id;
            $newDoc->save();

            // Les pieces liees aux documents de l'entreprise (RCCM, IFU,
            // attestations...) sont copiees telles quelles depuis l'original
            // (replicate() copie linked_entreprise_document_ids) - mais si
            // l'original ne les avait pas lui-meme (ex: cree avant que ces
            // pieces existent sur la fiche entreprise, ou jamais rapprochees),
            // la copie ne doit pas rester vide pour autant : on retente un
            // rapprochement frais avec les documents actuels de l'entreprise,
            // comme a la creation d'un nouveau dossier. Ne touche jamais une
            // piece deja liee (cf. le garde-fou dans autoLinkEntrepriseDocument()).
            $this->autoLinkEntrepriseDocument($copy, $newDoc);

            // Les fichiers televerses directement sur cette piece (scans,
            // formulaires remplis...) sont copies physiquement - jamais
            // partages avec l'original, pour qu'une suppression ou un
            // remplacement sur l'un n'affecte jamais l'autre.
            foreach ($doc->fichiers as $fichier) {
                $newFichier = $fichier->replicate();
                $newFichier->dossier_document_id = $newDoc->id;

                if ($fichier->chemin_fichier && Storage::disk('public')->exists($fichier->chemin_fichier)) {
                    $extension = pathinfo($fichier->chemin_fichier, PATHINFO_EXTENSION);
                    $copiePath = 'dossiers/documents/dossier_' . $copy->id . '_copie_' . $newDoc->id . '_' . uniqid() . ($extension ? '.' . $extension : '');
                    Storage::disk('public')->copy($fichier->chemin_fichier, $copiePath);
                    $newFichier->chemin_fichier = $copiePath;
                }

                $newFichier->save();
            }

            foreach ($doc->bordereau as $bordereau) {
                $newBordereau = $bordereau->replicate();
                $newBordereau->dossier_document_id = $newDoc->id;
                $newBordereau->save();

                foreach ($bordereau->lignes as $ligne) {
                    $newLigne = $ligne->replicate();
                    $newLigne->bordereau_id = $newBordereau->id;
                    $newLigne->save();
                }
            }
        }

        return $copy;
    }

    /**
     * Cree, a partir de ce dossier deja rempli, l'offre d'une autre
     * categorie pour le meme marche (ex: l'offre technique est faite, on
     * cree directement l'offre financiere) : duplique toutes les donnees
     * deja saisies, bascule le type d'offre, et complete le sommaire avec les
     * pieces standard de la nouvelle categorie qui manquent encore.
     */
    public function createOtherOffer(Request $request, Dossier $dossier)
    {
        $data = $request->validate([
            'categorie_cible' => ['required', 'string', 'in:' . implode(',', array_keys(self::OFFER_CATEGORIES))],
        ]);

        // Re-verifie cote serveur que cette categorie n'existe pas deja pour
        // ce marche (meme reference_dossier + meme entreprise) : le bouton
        // n'est normalement propose que si elle manque, mais on se protege
        // ici d'un double-clic, d'une page restee ouverte ou d'un appel
        // direct de la route une fois l'offre deja creee par ailleurs.
        if (!array_key_exists($data['categorie_cible'], $this->missingOfferCategories($dossier))) {
            return redirect()->route('dossiers.show', $dossier->id)
                ->with('error', 'Cette offre existe déjà pour ce marché.');
        }

        $newLabel = self::OFFER_CATEGORIES[$data['categorie_cible']]['label'];

        $copy = $this->replicateDossierData($dossier);
        $copy->nom_dossier = $this->renameForOfferCategory($dossier->nom_dossier, $dossier->type_offre, $newLabel);
        $copy->type_offre = $newLabel;
        $copy->save();

        $this->pruneSommaireForOfferCategory($copy);
        $this->ensureStandardSommairePieces($copy);

        $this->logChange($copy, 'dossier_duplique', "« {$newLabel} » créée depuis « {$dossier->nom_dossier} » (#{$dossier->id}).");

        return redirect()->route('dossiers.show', $copy->id)
            ->with('success', "« {$copy->nom_dossier} » créé avec le sommaire {$newLabel}, en reprenant les pièces déjà téléversées communes aux deux offres. Vérifiez-les avant de générer le PDF.");
    }

    private function applySommaireStandardOrder(Dossier $dossier): void
    {
        $rankNames = $this->isOffreFinanciere($dossier) ? self::STANDARD_SOMMAIRE_FINANCIERE : self::STANDARD_SOMMAIRE;

        $rank = [];
        foreach ($rankNames as $index => $nom) {
            $rank[$this->normalizeSommaireName($nom)] = $index;
        }

        $documents = $dossier->documents()
            ->with('typeDocument')
            ->orderBy('ordre')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $known = $documents
            ->filter(fn ($doc) => isset($rank[$this->normalizeSommaireName(optional($doc->typeDocument)->nom ?? '')]))
            ->sortBy(fn ($doc) => $rank[$this->normalizeSommaireName($doc->typeDocument->nom)])
            ->values();
        $unknown = $documents
            ->reject(fn ($doc) => isset($rank[$this->normalizeSommaireName(optional($doc->typeDocument)->nom ?? '')]))
            ->values();

        foreach ($known->concat($unknown)->values() as $index => $doc) {
            if ((int) $doc->ordre !== $index + 1) {
                $doc->update(['ordre' => $index + 1]);
            }
        }
    }

    private function normalizeSommaireName(string $nom): string
    {
        $lower = mb_strtolower(trim($nom), 'UTF-8');
        $lower = strtr($lower, [
            'à' => 'a', 'â' => 'a', 'ä' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'î' => 'i', 'ï' => 'i', 'ô' => 'o', 'ö' => 'o',
            'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ç' => 'c',
        ]);

        return trim(preg_replace('/[^a-z0-9]+/', ' ', $lower));
    }

    /**
     * Enregistre l'ordre du sommaire tel que glissé-déposé : ids dans le nouvel ordre.
     */
    public function reorderDocuments(Request $request, Dossier $dossier)
    {
        $ids = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
        ])['ids'];

        $documents = $dossier->documents()->get()->keyBy('id');

        $position = 1;
        foreach ($ids as $id) {
            $document = $documents->get((int) $id);
            if (!$document) {
                continue;
            }
            if ((int) $document->ordre !== $position) {
                $document->update(['ordre' => $position]);
            }
            $position++;
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Duplique un dossier : entreprise, signataires, contributeurs et contenu
     * de chaque document (y compris les bordereaux) sont recopiés. Les pièces
     * jointes (fichiers téléversés) ne sont pas dupliquées : il faut les
     * reteléverser sur la copie. Les formulaires générés par marché/personne
     * (EXP 4.2, PER...) se regénèrent automatiquement dès l'ouverture de la
     * copie, puisqu'ils suivent le contenu copié de l'EXP 4.1/qualification/
     * liste du personnel.
     */
    public function duplicate(Dossier $dossier)
    {
        $copy = $this->replicateDossierData($dossier);
        $copy->nom_dossier = trim($dossier->nom_dossier) . ' (copie)';
        $copy->save();

        $this->logChange($copy, 'dossier_duplique', "Dossier dupliqué depuis « {$dossier->nom_dossier} » (#{$dossier->id}).");

        return redirect()->route('dossiers.show', $copy->id)
            ->with('success', "Dossier dupliqué en « {$copy->nom_dossier} », avec les pièces déjà téléversées. Vérifiez-les et remplacez celles qui ne conviennent pas avant de générer le PDF.");
    }

    /**
     * Afficher un dossier
     */
    public function show(Dossier $dossier)
    {
        $dossier->load(['documents.typeDocument', 'documents.fichiers', 'entreprise', 'typeDossier', 'utilisateurs']);

        $hasSharedUsers = $dossier->utilisateurs->isNotEmpty();
        if (! auth()->user()->isAdminOrDirecteur() && $hasSharedUsers && ! $dossier->utilisateurs->contains(auth()->id())) {
            abort(403, 'Accès refusé');
        }

        $this->applySommaireStandardOrder($dossier);
        $this->normalizeDocumentOrders($dossier);
        $dossier->load(['documents.typeDocument', 'documents.fichiers', 'entreprise', 'typeDossier']);

        $uploadableDocs = $dossier->documents
            ->filter(fn ($doc) => $doc->typeDocument)
            ->sortBy('ordre')
            ->values();

        // Prefer a document with status 'en_cours', otherwise take the first
        // not-yet-complete document. Le statut est le seul signal fiable
        // commun a tous les types de documents : $doc->fichiers (utilise
        // avant) reste TOUJOURS vide pour les documents structures qui
        // stockent leur contenu ailleurs (bordereaux, Formulaire MAT/PER,
        // EXP 4.x, Fiche technique de chaque article...), ce qui faisait
        // boucler le bouton "Continuer" sur un document deja rempli.
        $inProgressIndex = $uploadableDocs->search(fn ($doc) => $doc->statut === 'en_cours');
        if ($inProgressIndex !== false) {
            $resumeIndex = $inProgressIndex;
        } else {
            $firstIncomplete = $uploadableDocs->search(fn ($doc) => $doc->statut !== 'complete');
            $resumeIndex = $firstIncomplete === false ? null : $firstIncomplete;
        }

        $resumeDocumentIds = $uploadableDocs->map(fn ($doc) => $doc->type_document_id)->values()->all();

        $coherenceWarnings = $this->coherenceWarnings($dossier);
        $changeLogs = $dossier->changeLogs()->with('utilisateur')->limit(50)->get();
        $missingOfferCategories = $this->missingOfferCategories($dossier);
        $versionsFinales = $dossier->versionsFinales()->with('utilisateur')->get();

        return view('dossiers.show', compact('dossier', 'resumeIndex', 'resumeDocumentIds', 'coherenceWarnings', 'changeLogs', 'missingOfferCategories', 'versionsFinales'));
    }

    /**
     * Archive une version finale du dossier téléversée manuellement (document
     * modifié après téléchargement, éventuellement signé puis scanné). Le
     * fichier est conservé tel quel, sans traitement ni fusion : simple
     * archivage, avec horodatage et auteur, consultable depuis la fiche
     * dossier.
     */
    public function uploadVersionFinale(Request $request, Dossier $dossier)
    {
        $request->validate([
            'fichier' => 'required|file|max:20480',
            'commentaire' => 'nullable|string|max:255',
        ]);

        $chemin = $request->file('fichier')->store('dossiers/versions-finales', 'public');

        DossierVersionFinale::create([
            'dossier_id' => $dossier->id,
            'chemin_fichier' => $chemin,
            'nom_original' => $request->file('fichier')->getClientOriginalName(),
            'commentaire' => $request->input('commentaire'),
            'utilisateur_id' => auth()->id(),
        ]);

        return redirect()->route('dossiers.show', $dossier->id)
            ->with('success', 'Version finale archivée.');
    }

    /**
     * Retire une version finale archivée par erreur (mauvais fichier,
     * doublon...). Supprime aussi le fichier physique sur le disque public.
     */
    public function destroyVersionFinale(DossierVersionFinale $versionFinale)
    {
        $dossierId = $versionFinale->dossier_id;

        if ($versionFinale->chemin_fichier && Storage::disk('public')->exists($versionFinale->chemin_fichier)) {
            Storage::disk('public')->delete($versionFinale->chemin_fichier);
        }
        $versionFinale->delete();

        return redirect()->route('dossiers.show', $dossierId)
            ->with('success', 'Version finale supprimée.');
    }

    /**
     * Anomalies de coherence a signaler avant de generer le dossier final :
     * dates incoherentes (achevement avant attribution) et montants a zero
     * sur une piece pourtant marquee complete, sur les marches des
     * formulaires EXP 4.2 a)/b) et leurs "(suite)". Non bloquant : une
     * simple liste de messages d'avertissement.
     */
    private function coherenceWarnings(Dossier $dossier): array
    {
        $warnings = [];

        $statutByTypeName = $dossier->documents
            ->filter(fn ($dd) => $dd->typeDocument)
            ->mapWithKeys(fn ($dd) => [$dd->typeDocument->nom => $dd->statut]);

        $nomMarche = function ($record): string {
            $nom = trim((string) ($record->numero_marche ?? ''));
            if ($nom === '') {
                $nom = trim((string) ($record->identification_marche ?? ''));
            }
            return $nom !== '' ? $nom : 'entrée #' . ((int) $record->marche_position + 1);
        };

        $checkDates = function ($records, string $label) use (&$warnings, $nomMarche) {
            foreach ($records as $record) {
                if ($record->date_attribution && $record->date_achevement && $record->date_achevement->lt($record->date_attribution)) {
                    $warnings[] = "{$label} — « {$nomMarche($record)} » : la date d'achèvement ({$record->date_achevement->format('d/m/Y')}) est antérieure à la date d'attribution ({$record->date_attribution->format('d/m/Y')}).";
                }
            }
        };

        $checkMontantZero = function ($records, string $label, string $field, bool $complete) use (&$warnings, $nomMarche) {
            if (!$complete) {
                return;
            }
            foreach ($records as $record) {
                $brut = (string) ($record->{$field} ?? '');
                $montant = (float) str_replace([' ', ','], ['', '.'], $brut);
                if ($montant <= 0) {
                    $warnings[] = "{$label} — « {$nomMarche($record)} » : le montant est à 0 alors que la pièce est marquée complète.";
                }
            }
        };

        $exp42a = FormulaireExp42A::where('dossier_id', $dossier->id)->orderBy('marche_position')->get();
        $exp42b = FormulaireExp42B::where('dossier_id', $dossier->id)->orderBy('marche_position')->get();
        $exp42aSuite = FormulaireExp42ASuite::where('dossier_id', $dossier->id)->orderBy('marche_position')->get();
        $exp42bSuite = FormulaireExp42BSuite::where('dossier_id', $dossier->id)->orderBy('marche_position')->get();

        $checkDates($exp42a, 'EXP 4.2 a)');
        $checkDates($exp42b, 'EXP 4.2 b)');

        $checkMontantZero($exp42a, 'EXP 4.2 a)', 'montant_total', ($statutByTypeName[self::EXP42A_NOM] ?? null) === 'complete');
        $checkMontantZero($exp42b, 'EXP 4.2 b)', 'montant_total', ($statutByTypeName[self::EXP42B_NOM] ?? null) === 'complete');
        $checkMontantZero($exp42aSuite, 'EXP 4.2 a) (suite)', 'montant', ($statutByTypeName[self::EXP42A_SUITE_NOM] ?? null) === 'complete');
        $checkMontantZero($exp42bSuite, 'EXP 4.2 b) (suite)', 'montant', ($statutByTypeName[self::EXP42B_SUITE_NOM] ?? null) === 'complete');

        return $warnings;
    }

    /**
     * Signale que l'utilisateur courant regarde ce dossier (et, le cas
     * echeant, telle piece precise), et renvoie qui d'autre le regarde
     * aussi en ce moment - pour eviter que deux personnes ecrasent le
     * travail l'une de l'autre sans le savoir. Deux personnes sur le meme
     * dossier mais des pieces differentes ne se genent pas (distingue des
     * "memes document" qui, eux, sont le vrai risque de collision). Pas de
     * temps reel : une simple presence "vue il y a moins de 45s" suffit.
     */
    public function presencePing(Request $request, Dossier $dossier)
    {
        $userId = auth()->id();
        $typeDocumentId = $request->integer('type_document_id') ?: null;

        DossierPresence::updateOrCreate(
            ['dossier_id' => $dossier->id, 'utilisateur_id' => $userId],
            ['type_document_id' => $typeDocumentId, 'last_seen_at' => now()]
        );

        $autresPresents = DossierPresence::with('utilisateur')
            ->where('dossier_id', $dossier->id)
            ->where('utilisateur_id', '!=', $userId)
            ->where('last_seen_at', '>=', now()->subSeconds(45))
            ->get();

        $nom = fn (DossierPresence $p) => trim(($p->utilisateur->prenom ?? '') . ' ' . ($p->utilisateur->nom ?? '')) ?: 'Un autre utilisateur';

        $others = $autresPresents->map($nom)->values();

        $sameDocument = $typeDocumentId
            ? $autresPresents->filter(fn (DossierPresence $p) => $p->type_document_id === $typeDocumentId)->map($nom)->values()
            : collect();

        return response()->json(['others' => $others, 'sameDocument' => $sameDocument]);
    }

    /**
     * Applique explicitement un CV de l'entreprise sur un Formulaire PER du
     * dossier (bouton "Appliquer" quand plusieurs CV correspondent au meme
     * nom) : contrairement a la synchronisation automatique, ceci ecrase les
     * champs deja renseignes - c'est un choix delibere de l'utilisateur.
     */
    public function applyEntreprisePersonnelCv(Request $request, Dossier $dossier)
    {
        $data = $request->validate([
            'formulaire_per_id' => ['required', 'integer'],
            'entreprise_personnel_id' => ['required', 'integer'],
        ]);

        $record = FormulairePER::where('dossier_id', $dossier->id)
            ->whereNotNull('personnel_position')
            ->whereKey($data['formulaire_per_id'])
            ->first();
        if (!$record) {
            return back()->with('error', 'Personne introuvable sur ce dossier.');
        }

        $entreprisePersonnel = EntreprisePersonnel::where('entreprise_id', $dossier->entreprise_id)
            ->with('fichiers')
            ->find($data['entreprise_personnel_id']);
        if (!$entreprisePersonnel) {
            return back()->with('error', 'CV introuvable sur la fiche entreprise.');
        }

        $this->applyEntreprisePersonnelToFormulairePer($record, $entreprisePersonnel, true);

        return back()->with('success', 'CV « ' . ($entreprisePersonnel->intitule ?: $entreprisePersonnel->nom) . ' » appliqué.');
    }

    /**
     * Choix des documents de l'entreprise (RCCM, IFU, Etats financiers)
     * référencés pour une pièce donnée du dossier : appliqué immédiatement
     * à chaque coche/décoche (case à cocher multiple, puisqu'un même
     * dossier peut avoir besoin de plusieurs Etats financiers - un par
     * année). Référence directe, jamais de copie de fichier.
     */
    public function applyEntrepriseDocumentSelection(Request $request, Dossier $dossier)
    {
        $data = $request->validate([
            'dossier_document_id' => ['required', 'integer'],
            'entreprise_document_ids' => ['nullable', 'array'],
            'entreprise_document_ids.*' => ['integer'],
        ]);

        $dossierDocument = DossierDocument::where('dossier_id', $dossier->id)
            ->whereKey($data['dossier_document_id'])
            ->first();
        if (!$dossierDocument) {
            return back()->with('error', 'Pièce introuvable sur ce dossier.');
        }

        $pickerType = $this->entrepriseDocumentPickerType($dossierDocument);
        if (!$pickerType) {
            return back()->with('error', 'Cette pièce ne prend pas en charge les documents de l\'entreprise.');
        }

        $validIds = EntrepriseDocument::where('entreprise_id', $dossier->entreprise_id)
            ->where('type', $pickerType)
            ->whereIn('id', $data['entreprise_document_ids'] ?? [])
            ->pluck('id')
            ->all();

        $dossierDocument->linked_entreprise_document_ids = $validIds;
        $dossierDocument->save();

        return back()->with('success', 'Documents de l\'entreprise mis à jour pour cette pièce.');
    }

    /**
     * Ajoute un nouveau document de l'entreprise (intitulé + fichiers)
     * directement depuis le choix des pièces d'un dossier (ex: une nouvelle
     * Attestation de bonne fin ou un nouveau Bon de commande qui n'était pas
     * encore enregistré sur la fiche entreprise), et le lie immédiatement à
     * cette pièce du dossier - sans repasser par la page de l'entreprise.
     * Le nouveau document reste disponible ensuite pour les autres dossiers.
     */
    public function createEntrepriseDocumentFromPicker(Request $request, Dossier $dossier)
    {
        $data = $request->validate([
            'dossier_document_id' => ['required', 'integer'],
            'intitule' => ['required', 'string', 'max:255'],
            'fichiers' => ['required', 'array', 'min:1'],
            'fichiers.*' => ['file'],
        ]);

        $dossierDocument = DossierDocument::where('dossier_id', $dossier->id)
            ->whereKey($data['dossier_document_id'])
            ->first();
        if (!$dossierDocument) {
            return back()->with('error', 'Pièce introuvable sur ce dossier.');
        }

        $pickerType = $this->entrepriseDocumentPickerType($dossierDocument);
        if (!$pickerType) {
            return back()->with('error', 'Cette pièce ne prend pas en charge les documents de l\'entreprise.');
        }

        $entrepriseDocument = EntrepriseDocument::create([
            'entreprise_id' => $dossier->entreprise_id,
            'type' => $pickerType,
            'intitule' => $data['intitule'],
        ]);

        foreach ($request->file('fichiers', []) as $uploaded) {
            if (!$uploaded) {
                continue;
            }
            $chemin = $uploaded->store('entreprise_documents/pieces', 'public');
            EntrepriseDocumentFichier::create([
                'entreprise_document_id' => $entrepriseDocument->id,
                'chemin_fichier' => $chemin,
                'nom_original' => $uploaded->getClientOriginalName(),
                'utilisateur_id' => auth()->id(),
            ]);
        }

        $linkedIds = $dossierDocument->linked_entreprise_document_ids ?? [];
        $linkedIds[] = $entrepriseDocument->id;
        $dossierDocument->linked_entreprise_document_ids = array_values(array_unique($linkedIds));
        $dossierDocument->save();

        return back()->with('success', "« {$data['intitule']} » ajouté et lié à ce dossier.");
    }

    /**
     * Reprendre la creation d'un dossier a la piece en cours
     */
    public function continueCreation(Request $request, Dossier $dossier)
    {
        $dossier->load(['documents.typeDocument', 'documents.fichiers', 'typeDossier']);
        $this->normalizeDocumentOrders($dossier);
        $dossier->load(['documents.typeDocument', 'documents.fichiers', 'typeDossier']);

        $uploadableDocs = $dossier->documents
            ->filter(fn ($doc) => $doc->typeDocument)
            ->sortBy('ordre')
            ->values();

        if ($uploadableDocs->isEmpty()) {
            return redirect()->route('dossiers.show', $dossier->id)
                ->with('error', 'Impossible de reprendre: aucune piece a televerser.');
        }

        // Le statut est le seul signal fiable commun a tous les types de
        // documents : $doc->fichiers reste TOUJOURS vide pour les documents
        // structures qui stockent leur contenu ailleurs (bordereaux,
        // Formulaire MAT/PER, EXP 4.x, Fiche technique de chaque article...),
        // ce qui faisait boucler le bouton "Continuer" sur un document deja
        // rempli (voir aussi show(), meme correction).
        $resumeIndex = $uploadableDocs->search(fn ($doc) => $doc->statut !== 'complete');

        // Allow overriding the resume index when coming from the 'Modifier' action
        if ($request->has('current_index')) {
            $override = (int) $request->query('current_index');
            if ($override >= 0 && $override < $uploadableDocs->count()) {
                $resumeIndex = $override;
            }
        }

        if ($resumeIndex === false) {
            return redirect()->route('dossiers.show', $dossier->id)
                ->with('success', 'Toutes les pieces sont deja televersees.');
        }

        $uploadQueue = $uploadableDocs->map(fn ($doc) => $doc->typeDocument)->values();
        $selectedDocumentIds = $uploadableDocs->pluck('type_document_id')->values()->all();
        $currentDocument = $uploadQueue->get($resumeIndex);
        $this->syncExp42aRecords($dossier);
        $this->syncExp42bRecords($dossier);
        $this->syncExp42aSuiteRecords($dossier);
        $this->syncExp42bSuiteRecords($dossier);
        $this->syncFormulairePerRecords($dossier);
        $this->syncFormulaireMatRecords($dossier);
        $this->syncFormulaireFin31FromEntreprise($dossier);
        $this->syncFormulaireBeneficiairesFromEntreprise($dossier);
        $this->syncFicheTechniqueFromCatalogue($dossier);
        $entrepriseChiffres = $this->entrepriseChiffresAffaires($dossier);

        return view('dossiers.create.step6', [
            'dossier' => $dossier,
            'uploadQueue' => $uploadQueue,
            'currentDocument' => $currentDocument,
            'currentIndex' => $resumeIndex,
            'totalCount' => $uploadQueue->count(),
            'selectedDocumentIds' => $selectedDocumentIds,
            'entrepriseChiffres' => $entrepriseChiffres,
        ]);
    }

    /**
     * Afficher la page de selection des documents pour un dossier existant
     */
    public function selectDocuments(Dossier $dossier)
    {
        $pieceNames = $this->getDocumentPieceNames();

        $bordereauNames = [
            'Bordereau prix unitaire',
            'Bordereau des prix pour les fournitures à importer',
            'Bordereau des prix des fournitures, déjà importées',
            'Bordereau des prix pour les fournitures fabriquées au Bénin',
            'Bordereau des prix et calendrier d\'exécution des services connexes',
            'Listes des services connexes et calendrier de réalisation',
            'Listes des Fournitures et Calendrier de livraison',
            'Tableau de résumé des bordereaux de prix',
            'Cadres de sous détails des prix unitaire',
            'Programme d\'activités',
            'Méthodes d\'exécution',
            'Calendrier d\'exécution',
            'Description technique des fournitures/services',
            'Liste de Matériel',
        ];

        foreach ($pieceNames as $name) {
            $type = in_array($name, $bordereauNames, true) ? 'bordereau' : 'formulaire';
            TypeDocument::firstOrCreate(['nom' => $name], ['type_formulaire' => $type]);
        }

        $documentsByName = TypeDocument::whereIn('nom', $pieceNames)->get()->keyBy('nom');
        $documents = collect($pieceNames)
            ->map(fn ($name) => $documentsByName->get($name))
            ->filter();

        $selectedTypeIds = $dossier->documents()->pluck('type_document_id')->filter()->all();
        $signataires = Signataire::orderBy('nom')->orderBy('prenom')->get();

        return view('dossiers.create.step5', compact('dossier', 'documents', 'signataires', 'selectedTypeIds'));
    }

    public function manageUsers(Dossier $dossier)
    {
        if (! auth()->user()->isAdminOrDirecteur()) {
            abort(403, 'Accès refusé');
        }

        $users = Utilisateur::orderBy('nom')->orderBy('prenom')->get();
        $selectedUsers = $dossier->utilisateurs()->pluck('utilisateur_id')->all();

        return view('dossiers.manage_users', compact('dossier', 'users', 'selectedUsers'));
    }

    public function updateUsers(Request $request, Dossier $dossier)
    {
        if (! auth()->user()->isAdminOrDirecteur()) {
            abort(403, 'Accès refusé');
        }

        $data = $request->validate([
            'utilisateurs' => ['required', 'array', 'min:1'],
            'utilisateurs.*' => ['required', 'exists:utilisateurs,id'],
        ]);

        $dossier->utilisateurs()->sync($data['utilisateurs']);

        return redirect()->route('dossiers.show', $dossier)->with('success', 'Contributeurs du dossier mis à jour.');
    }

    /**
     * Formulaire de modification des informations du dossier saisies à l'étape 4
     * (page de garde, sommaire, dates, etc.).
     */
    public function editInfos(Dossier $dossier)
    {
        return view('dossiers.edit_infos', compact('dossier'));
    }

    public function updateInfos(Request $request, Dossier $dossier)
    {
        $data = $request->validate([
            'nom_dossier' => ['required', 'string', 'max:255'],
            'titre_dossier' => ['nullable', 'string', 'max:255'],
            'republique' => ['nullable', 'string', 'max:255'],
            'ministere' => ['nullable', 'string', 'max:255'],
            'direction' => ['nullable', 'string', 'max:255'],
            'services_projet' => ['nullable', 'string', 'max:255'],
            'destinataires' => ['nullable', 'string'],
            'reference_dossier' => ['nullable', 'string', 'max:255'],
            'ref' => ['nullable', 'string', 'max:255'],
            'date_lancement' => ['nullable', 'date'],
            'date_soumission' => ['nullable', 'date'],
            'type_offre' => ['nullable', 'string', 'max:255'],
            'lots' => ['nullable', 'string'],
            'titre_lot' => ['nullable', 'string', 'max:255'],
            'autres_details' => ['nullable', 'string'],
            'mois_depot' => ['nullable', 'string', 'max:255'],
            'annee_depot' => ['nullable', 'digits:4'],
            'reference_step' => ['nullable', 'string', 'max:255'],
            'source_financement' => ['nullable', 'string', 'max:255'],
            'gestion' => ['nullable', 'string', 'max:255'],
            'imputation_budgetaire' => ['nullable', 'string', 'max:255'],
            'accord_pret' => ['nullable', 'string', 'max:255'],
        ]);

        $dossier->update($data);

        return redirect()->route('dossiers.show', $dossier)->with('success', 'Informations du dossier mises à jour.');
    }

    /**
     * Liste des dossiers
     */
    public function index(Request $request)
    {
        $search = trim($request->query('search', ''));

        $query = auth()->user()->isAdminOrDirecteur()
            ? Dossier::with(['entreprise', 'typeDossier', 'documents', 'versionsFinales'])
            : auth()->user()->dossiers()->with(['entreprise', 'typeDossier', 'documents', 'versionsFinales']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nom_dossier', 'like', "%{$search}%")
                    ->orWhere('lot', 'like', "%{$search}%")
                    ->orWhere('public_prive', 'like', "%{$search}%")
                    ->orWhere('statut', 'like', "%{$search}%")
                    ->orWhere('type_offre', 'like', "%{$search}%")
                    ->orWhereHas('entreprise', function ($q2) use ($search) {
                        $q2->where('nom', 'like', "%{$search}%")
                            ->orWhere('sigle', 'like', "%{$search}%");
                    })
                    ->orWhereHas('typeDossier', function ($q2) use ($search) {
                        $q2->where('nom', 'like', "%{$search}%");
                    });

                try {
                    $date = \Carbon\Carbon::parse($search);
                    $q->orWhereDate('created_at', $date->toDateString());
                } catch (\Exception $e) {
                    // ignore invalid date formats
                }
            });
        }

        $dossiers = $query->orderBy('created_at', 'desc')->get();

        $missingOffersByDossier = [];
        foreach ($dossiers as $dossier) {
            $missingOffersByDossier[$dossier->id] = $this->missingOfferCategories($dossier);
        }

        $dossierGroups = $this->groupDossiersByMarche($dossiers, $missingOffersByDossier);

        return view('dossiers.index', compact('dossierGroups', 'missingOffersByDossier'));
    }

    /**
     * Regroupe les dossiers d'un même marché (même reference_dossier + même
     * entreprise - meme heuristique que missingOfferCategories()) en une
     * seule "ligne" pour la liste : un marché peut avoir plusieurs offres
     * (technique/financière/administrative), chacune etant un Dossier a
     * part, mais ne doit apparaitre qu'une fois dans la liste. Un dossier
     * sans reference_dossier ne peut etre rattache a aucun autre : il forme
     * son propre groupe a lui seul.
     */
    private function groupDossiersByMarche(\Illuminate\Support\Collection $dossiers, array $missingOffersByDossier): \Illuminate\Support\Collection
    {
        $groups = collect();

        foreach ($dossiers as $dossier) {
            $reference = trim((string) $dossier->reference_dossier);
            $key = $reference !== ''
                ? 'ref:' . $reference . '|' . $dossier->entreprise_id
                : 'single:' . $dossier->id;

            if (!$groups->has($key)) {
                $groups->put($key, collect());
            }
            $groups->get($key)->push($dossier);
        }

        return $groups->map(function ($groupDossiers) use ($missingOffersByDossier) {
            $sorted = $groupDossiers->sortByDesc('created_at')->values();
            $first = $sorted->first();
            // Le nom affiche vient du dossier le plus ANCIEN du groupe (celui
            // tape par l'utilisateur a la creation), pas du plus recent : les
            // offres creees ensuite via "+ Creer une offre" ont leur nom
            // renomme avec un suffixe de categorie (ex: "... — Offre
            // financiere", voir renameForOfferCategory()), ce qui ferait
            // apparaitre ce suffixe comme "nom du dossier" du marche entier
            // si on prenait le plus recent. La categorie est deja visible
            // via les badges de la colonne "Offres" : inutile de la repeter
            // ici, donc on la retire aussi si elle trainait malgre tout.
            $title = $this->stripOfferCategorySuffix($sorted->last()->nom_dossier);

            return [
                'title' => $title,
                'lot' => $first->lot,
                'public_prive' => $first->public_prive,
                'entreprise' => $first->entreprise,
                'dossiers' => $sorted,
                'missingOffers' => $missingOffersByDossier[$first->id] ?? [],
                'representative' => $first,
                'latest_created_at' => $groupDossiers->max('created_at'),
            ];
        })->sortByDesc('latest_created_at')->values();
    }

    /**
     * Supprimer un dossier et ses relations si nécessaire.
     */
    /**
     * Supprimer un document d'un dossier
     */
    public function destroyDocument(Dossier $dossier, DossierDocument $document)
    {
        if (auth()->user()->isEmploye()) {
            abort(403, 'Suppression non autorisée pour ce rôle.');
        }

        if ($document->dossier_id !== $dossier->id) {
            return redirect()->back()->with('error', 'Document non trouvé.');
        }

        $nomDocument = optional($document->typeDocument)->nom;
        $typeDocumentId = $document->type_document_id;

        try {
            // Supprimer les fichiers associés
            foreach ($document->fichiers as $fichier) {
                if ($fichier->chemin_fichier && Storage::disk('public')->exists($fichier->chemin_fichier)) {
                    Storage::disk('public')->delete($fichier->chemin_fichier);
                }
                $fichier->delete();
            }

            // Supprimer les valeurs associées
            $document->valeurs()->delete();

            // Supprimer les bordereaux associés
            Bordereau::where('dossier_document_id', $document->id)->delete();

            // Supprimer le document
            $document->delete();

            $this->logChange($dossier, 'document_retire', "Pièce « " . ($nomDocument ?? 'Document') . " » retirée du sommaire.", $nomDocument, $typeDocumentId);

            return redirect()->route('dossiers.show', $dossier)->with('success', 'Document supprimé avec succès.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Erreur lors de la suppression du document: ' . $e->getMessage());
        }
    }

    public function destroy(Dossier $dossier)
    {
        if (auth()->user()->isEmploye()) {
            abort(403, 'Suppression non autorisée pour ce rôle.');
        }

        try {
            $dossier->delete();
            return redirect()->route('dossiers.index')->with('success', 'Dossier supprimé.');
        } catch (\Throwable $e) {
            return redirect()->route('dossiers.index')->with('error', 'Impossible de supprimer le dossier.');
        }
    }

    /**
     * Sauvegarder les chiffres d'affaires pour un dossier (API JSON)
     */
    public function saveChiffres(Request $request)
    {
        $data = $request->validate([
            'dossier_id' => 'required|exists:dossiers,id',
            'chiffres' => 'required|array|min:1',
            'chiffres.*.annee' => 'required|integer|min:1900|max:2099',
            'chiffres.*.montant' => 'required|numeric|min:0',
            'chiffres.*.monnaie' => 'required|string|max:10',
        ]);

        $dossierId = $data['dossier_id'];

        // Supprimer les anciens chiffres pour ce dossier
        \App\Models\ChiffreAffaire::where('dossier_id', $dossierId)->delete();

        // Créer les nouveaux chiffres
        foreach ($data['chiffres'] as $chiffre) {
            \App\Models\ChiffreAffaire::create([
                'dossier_id' => $dossierId,
                'annee' => $chiffre['annee'],
                'montant' => $chiffre['montant'],
                'monnaie' => $chiffre['monnaie'],
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Chiffres d\'affaires sauvegardés']);
    }
}
