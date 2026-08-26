<?php

namespace App\Http\Controllers;

use App\Models\Dossier;
use App\Models\TypeMarche;
use App\Models\Signataire;
use App\Models\Entreprise;
use App\Models\TypeDossier;
use App\Models\TypeDocument;
use App\Models\DossierDocument;
use App\Models\ChampDocument;
use App\Models\ValeurDocument;
use App\Models\DocumentFichier;
use App\Models\Bordereau;
use App\Models\BordereauLigne;
use App\Models\ChiffreAffaire;
use App\Models\Utilisateur;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class DossierController extends Controller
{
    /**
     * Afficher le dashboard home avec les dossiers
     */
    public function dashboardHome()
    {
        $dossiers = Dossier::all();
        return view('home', compact('dossiers'));
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
            "Formulaire PER",
            "Liste du personnel affecté à l'exécution du marché",
            "Chiffre d'affaires annuel moyen des activités de services",
            "Attestation de non-exclusion de la commande publique",
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
        $numericKeys = ['prix_unitaire', 'quantite', 'cout_benin'];

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

        return view('dossiers.create.step3', compact('categorie', 'typeDossierId', 'entreprises'));
    }

    /**
     * Étape 3b : Créer une nouvelle entreprise
     */
    public function storeEntreprise(Request $request)
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'sigle' => ['nullable', 'string', 'max:50'],
            'adresse' => ['nullable', 'string'],
            'adresse_officielle' => ['nullable', 'string'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'pays' => ['nullable', 'string', 'max:255'],
            'ifu' => ['nullable', 'string', 'max:100'],
            'registre_path' => ['nullable', 'string', 'max:255'],
            'responsable' => ['nullable', 'string', 'max:255'],
            'fonction_responsable' => ['nullable', 'string', 'max:255'],
            'annee_enregistrement' => ['nullable', 'digits:4'],
            'logo' => ['nullable', 'file', 'image', 'max:5120'],
        ]);

        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            $path = $request->file('logo')->store('entreprises/logos', 'public');
            $data['logo'] = $path;
        }

        $entreprise = Entreprise::create([
            'nom' => $data['nom'],
            'sigle' => $data['sigle'] ?? null,
            'adresse' => $data['adresse'] ?? null,
            'adresse_officielle' => $data['adresse_officielle'] ?? null,
            'telephone' => $data['telephone'] ?? null,
            'email' => $data['email'] ?? null,
            'pays' => $data['pays'] ?? null,
            'ifu' => $data['ifu'] ?? null,
            'registre_path' => $data['registre_path'] ?? null,
            'logo' => $data['logo'] ?? null,
            'responsable' => $data['responsable'] ?? null,
            'fonction_responsable' => $data['fonction_responsable'] ?? null,
            'annee_enregistrement' => $data['annee_enregistrement'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'entreprise' => $entreprise
        ]);
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

        foreach ($orderedSelected as $index => $doc) {
            $dossierDocument = DossierDocument::firstOrNew([
                'dossier_id' => $dossier->id,
                'type_document_id' => $doc->id,
            ]);

            $dossierDocument->ordre = $index + 1;
            if (! $dossierDocument->exists) {
                $dossierDocument->statut = 'vide';
            }
            $dossierDocument->save();

            if (in_array($doc->nom, $autoCompleteNames, true)) {
                $dossierDocument->update(['statut' => 'complete']);
            }

            if (trim($doc->nom) === "RCCM") {
                $this->attachEntrepriseRegistreIfMissing($dossier, $dossierDocument);
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
            $dossierDocument->ordre = $currentIndex + 1;
            if (! $dossierDocument->exists) {
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
                    'marches.*.nom' => ['nullable','string','max:255'],
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

                // Créer des enregistrements de chiffre d'affaires pour le dossier à partir des années sélectionnées.
                // Le rendu final du PDF principal utilise ces chiffres à partir de $dossier->chiffresAffaires.
                $selectedEntries = \App\Models\ChiffreAffaire::whereNull('dossier_id')
                    ->whereIn('annee', $values['years'])
                    ->orderBy('annee')
                    ->get();

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
                }
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
            }

            if (trim($currentDocument->nom) === 'Formulaire EXP – 4.2 a) Expérience spécifique de fournitures/services') {
                $values = $request->validate([
                    'numero_marche' => ['nullable','array'],
                    'numero_marche.*' => ['nullable','string','max:255'],
                    'identification' => ['nullable','array'],
                    'identification.*' => ['nullable','string','max:4000'],
                    'date_attribution' => ['nullable','array'],
                    'date_attribution.*' => ['nullable','string','max:255'],
                    'date_achevement' => ['nullable','array'],
                    'date_achevement.*' => ['nullable','string','max:255'],
                    'role' => ['nullable','array'],
                    'role.*' => ['nullable','string','max:255'],
                    'montant_total' => ['nullable','array'],
                    'montant_total.*' => ['nullable','string','max:255'],
                    'participation' => ['nullable','array'],
                    'participation.*' => ['nullable','string','max:50'],
                    'montant_part' => ['nullable','array'],
                    'montant_part.*' => ['nullable','string','max:255'],
                    'monnaie' => ['nullable','array'],
                    'monnaie.*' => ['nullable','string','max:20'],
                    'autorite_nom' => ['nullable','array'],
                    'autorite_nom.*' => ['nullable','string','max:4000'],
                    'autorite_adresse' => ['nullable','array'],
                    'autorite_adresse.*' => ['nullable','string','max:4000'],
                    'autorite_telephone' => ['nullable','array'],
                    'autorite_telephone.*' => ['nullable','string','max:255'],
                    'autorite_email' => ['nullable','array'],
                    'autorite_email.*' => ['nullable','string','max:255'],
                    'nom_signataire' => ['nullable','string','max:255'],
                    'fonction_signataire' => ['nullable','string','max:255'],
                ]);

                // Filter out fully empty rows
                if (!empty($values['identification']) && is_array($values['identification'])) {
                    $filtered = [];
                    $max = max(
                        count($values['numero_marche'] ?? []),
                        count($values['identification'] ?? []),
                        count($values['date_attribution'] ?? []),
                        count($values['date_achevement'] ?? []),
                        count($values['role'] ?? []),
                        count($values['montant_total'] ?? []),
                        count($values['participation'] ?? []),
                        count($values['monnaie'] ?? []),
                        count($values['autorite_nom'] ?? [])
                    );

                    $outNumero = [];
                    $outIdent = [];
                    $outDateAttr = [];
                    $outDateAch = [];
                    $outRole = [];
                    $outMontant = [];
                    $outPart = [];
                    $outMontPart = [];
                    $outMonnaie = [];
                    $outAutoriteNom = [];
                    $outAutoriteAdresse = [];
                    $outAutoriteTel = [];
                    $outAutoriteEmail = [];

                    for ($i = 0; $i < $max; $i++) {
                        $num = isset($values['numero_marche'][$i]) ? trim((string)$values['numero_marche'][$i]) : '';
                        $ident = isset($values['identification'][$i]) ? trim((string)$values['identification'][$i]) : '';
                        $attr = isset($values['date_attribution'][$i]) ? trim((string)$values['date_attribution'][$i]) : '';
                        $ach = isset($values['date_achevement'][$i]) ? trim((string)$values['date_achevement'][$i]) : '';
                        $role = isset($values['role'][$i]) ? trim((string)$values['role'][$i]) : '';
                        $mont = isset($values['montant_total'][$i]) ? trim((string)$values['montant_total'][$i]) : '';
                        $part = isset($values['participation'][$i]) ? trim((string)$values['participation'][$i]) : '';
                        $mon = isset($values['monnaie'][$i]) ? trim((string)$values['monnaie'][$i]) : '';
                        $an = isset($values['autorite_nom'][$i]) ? trim((string)$values['autorite_nom'][$i]) : '';
                        $aa = isset($values['autorite_adresse'][$i]) ? trim((string)$values['autorite_adresse'][$i]) : '';
                        $at = isset($values['autorite_telephone'][$i]) ? trim((string)$values['autorite_telephone'][$i]) : '';
                        $ae = isset($values['autorite_email'][$i]) ? trim((string)$values['autorite_email'][$i]) : '';

                        if ($num !== '' || $ident !== '' || $attr !== '' || $ach !== '' || $role !== '' || $mont !== '' || $part !== '' || $an !== '') {
                            $outNumero[] = $num;
                            $outIdent[] = $ident;
                            $outDateAttr[] = $attr;
                            $outDateAch[] = $ach;
                            $outRole[] = $role;
                            $outMontant[] = $mont;
                            $outPart[] = $part;
                            $outMontPart[] = isset($values['montant_part'][$i]) ? trim((string)$values['montant_part'][$i]) : '';
                            $outMonnaie[] = $mon;
                            $outAutoriteNom[] = $an;
                            $outAutoriteAdresse[] = $aa;
                            $outAutoriteTel[] = $at;
                            $outAutoriteEmail[] = $ae;
                        }
                    }

                    $values['numero_marche'] = $outNumero;
                    $values['identification'] = $outIdent;
                    $values['date_attribution'] = $outDateAttr;
                    $values['date_achevement'] = $outDateAch;
                    $values['role'] = $outRole;
                    $values['montant_total'] = $outMontant;
                    $values['participation'] = $outPart;
                    $values['montant_part'] = $outMontPart ?? [];
                    $values['monnaie'] = $outMonnaie;
                    $values['autorite_nom'] = $outAutoriteNom;
                    $values['autorite_adresse'] = $outAutoriteAdresse;
                    $values['autorite_telephone'] = $outAutoriteTel;
                    $values['autorite_email'] = $outAutoriteEmail;
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

            if (trim($currentDocument->nom) === 'Formulaire EXP – 4.2 b) (suite) Expérience spécifique de fournitures/services dans les activités principales (suite)') {
                $values = $request->validate([
                    'description_activites' => ['nullable', 'string', 'max:8000'],
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
                $hasFiles = DocumentFichier::where('dossier_document_id', $dossierDocument->id)->exists();
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

        $globalChiffres = ChiffreAffaire::whereNull('dossier_id')->orderBy('annee')->get();

        return view('dossiers.create.step6', [
            'dossier' => $dossier,
            'uploadQueue' => $uploadQueue,
            'currentDocument' => $currentDocument,
            'currentIndex' => $currentIndex,
            'totalCount' => $uploadQueue->count(),
            'selectedDocumentIds' => $selectedDocumentIds,
            'globalChiffres' => $globalChiffres,
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
            'fichier' => ['required', 'file', 'max:10240', 'mimes:csv,txt,xlsx'],
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
        })->sortBy('ordre')->values();

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

        $pdfAttachmentsExist = $documents->contains(function ($doc) {
            $fichiers = $doc->fichiers ?? collect();
            $pdfAttachments = $fichiers->filter(function ($f) {
                return strtolower(pathinfo($f->chemin_fichier, PATHINFO_EXTENSION)) === 'pdf';
            });
            $imageAttachments = $fichiers->filter(function ($f) {
                $ext = strtolower(pathinfo($f->chemin_fichier, PATHINFO_EXTENSION));
                return in_array($ext, ['png', 'jpg', 'jpeg', 'gif'], true);
            });
            $otherAttachments = $fichiers->filter(function ($f) {
                $ext = strtolower(pathinfo($f->chemin_fichier, PATHINFO_EXTENSION));
                return !in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'pdf'], true);
            });

            $docName = trim($doc->typeDocument->nom ?? '');
            return $pdfAttachments->isNotEmpty()
                && $imageAttachments->isEmpty()
                && $otherAttachments->isEmpty()
                && $doc->valeurs->isEmpty()
                && $doc->bordereau->isEmpty()
                && (empty($doc->content) || \App\Models\TypeDocument::isExp42Name($docName))
                && !in_array($docName, [
                    "Déclaration de garantie d'offre",
                    'Declaration de garantie d\'offre',
                    'Formulaire ELI – 1.1 : Formulaire de renseignements sur le candidat',
                ], true);
        });

        if (!$pdfAttachmentsExist) {
            $html = view('dossiers.pdf', compact('dossier', 'pageGardeDataUri', 'documents'))->render();

            try {
                $dompdf = new Dompdf(['isRemoteEnabled' => true]);
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();

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
            $filesToMerge[] = $coverTemp;
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
            $filesToMerge[] = $summaryTemp;
            $tempFiles[] = $summaryTemp;

            foreach ($documents as $doc) {
                $pdfAttachments = $doc->fichiers->filter(function ($f) {
                    return strtolower(pathinfo($f->chemin_fichier, PATHINFO_EXTENSION)) === 'pdf';
                });
                $imageAttachments = $doc->fichiers->filter(function ($f) {
                    $ext = strtolower(pathinfo($f->chemin_fichier, PATHINFO_EXTENSION));
                    return in_array($ext, ['png', 'jpg', 'jpeg', 'gif'], true);
                });
                $otherAttachments = $doc->fichiers->filter(function ($f) {
                    $ext = strtolower(pathinfo($f->chemin_fichier, PATHINFO_EXTENSION));
                    return !in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'pdf'], true);
                });

                $skipHtmlDocPage = $pdfAttachments->isNotEmpty()
                    && $imageAttachments->isEmpty()
                    && $otherAttachments->isEmpty()
                    && $doc->valeurs->isEmpty()
                    && $doc->bordereau->isEmpty()
                    && (empty($doc->content) || \App\Models\TypeDocument::isExp42Name(trim($doc->typeDocument->nom ?? '')))
                    && $doc->typeDocument
                    && !in_array(trim($doc->typeDocument->nom), [
                        "Déclaration de garantie d'offre",
                        'Declaration de garantie d\'offre',
                        'Formulaire ELI – 1.1 : Formulaire de renseignements sur le candidat',
                    ], true);

                // Une pièce "upload uniquement" sans aucun fichier joint ni contenu
                // n'a rien à afficher : on évite de générer une page vide pour elle.
                $hasNothingToShow = $doc->typeDocument
                    && \App\Models\TypeDocument::isUploadOnlyName($doc->typeDocument->nom, $doc->typeDocument->type_formulaire)
                    && $doc->fichiers->isEmpty()
                    && $doc->valeurs->isEmpty()
                    && $doc->bordereau->isEmpty()
                    && empty($doc->content);

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
                $filesToMerge[] = $titleTemp;
                $tempFiles[] = $titleTemp;

                if (!$skipHtmlDocPage && !$hasNothingToShow) {
                    $htmlDoc = view('dossiers.pdf', compact('dossier', 'pageGardeDataUri'))->with([
                        'documents' => collect([$doc]),
                        'renderMode' => 'doc',
                    ])->render();
                    $docOrientation = $doc->typeDocument && in_array(trim($doc->typeDocument->nom), $this->getLandscapeTableDocNames(), true)
                        ? 'landscape'
                        : 'portrait';
                    $dompdfDoc = new Dompdf(['isRemoteEnabled' => true]);
                    $dompdfDoc->loadHtml($htmlDoc);
                    $dompdfDoc->setPaper('A4', $docOrientation);
                    $dompdfDoc->render();
                    $docTemp = tempnam(sys_get_temp_dir(), 'dossier_doc_') . '.pdf';
                    file_put_contents($docTemp, $dompdfDoc->output());
                    $filesToMerge[] = $docTemp;
                    $tempFiles[] = $docTemp;
                }

                if ($skipHtmlDocPage) {
                    foreach ($pdfAttachments as $f) {
                        $path = storage_path('app/public/' . ltrim($f->chemin_fichier, '/'));
                        if (file_exists($path)) {
                            $filesToMerge[] = $path;
                        }
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

        $pageGardeDataUri = null;
        if (!empty($dossier->page_garde_path)) {
            $fullPath = storage_path('app/public/' . $dossier->page_garde_path);
            if (file_exists($fullPath)) {
                $mime = mime_content_type($fullPath) ?: 'application/octet-stream';
                $pageGardeDataUri = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fullPath));
            }
        }

        $classify = function ($doc) {
            $fichiers = $doc->fichiers ?? collect();
            return [
                'pdf' => $fichiers->filter(fn($f) => strtolower(pathinfo($f->chemin_fichier, PATHINFO_EXTENSION)) === 'pdf'),
                'image' => $fichiers->filter(fn($f) => in_array(strtolower(pathinfo($f->chemin_fichier, PATHINFO_EXTENSION)), ['png', 'jpg', 'jpeg', 'gif'], true)),
                'other' => $fichiers->filter(fn($f) => !in_array(strtolower(pathinfo($f->chemin_fichier, PATHINFO_EXTENSION)), ['png', 'jpg', 'jpeg', 'gif', 'pdf'], true)),
            ];
        };

        $excludedFromSkip = [
            "Déclaration de garantie d'offre",
            'Declaration de garantie d\'offre',
            'Formulaire ELI – 1.1 : Formulaire de renseignements sur le candidat',
        ];

        $allDocuments = $dossier->documents()->with(['typeDocument', 'fichiers', 'valeurs', 'bordereau'])->get();

        $pdfAttachmentsExist = $allDocuments->contains(function ($doc) use ($classify, $excludedFromSkip) {
            $groups = $classify($doc);
            $docName = trim(optional($doc->typeDocument)->nom ?? '');
            return $groups['pdf']->isNotEmpty()
                && $groups['image']->isEmpty()
                && $groups['other']->isEmpty()
                && $doc->valeurs->isEmpty()
                && $doc->bordereau->isEmpty()
                && (empty($doc->content) || TypeDocument::isExp42Name($docName))
                && !in_array($docName, $excludedFromSkip, true);
        });

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
            $filesToMerge[] = $titleTemp;
            $tempFiles[] = $titleTemp;

            $skipHtmlDocPage = false;
            $pdfAttachments = collect();

            if ($pdfAttachmentsExist) {
                $groups = $classify($document);
                $pdfAttachments = $groups['pdf'];
                $docName = trim(optional($document->typeDocument)->nom ?? '');

                $skipHtmlDocPage = $groups['pdf']->isNotEmpty()
                    && $groups['image']->isEmpty()
                    && $groups['other']->isEmpty()
                    && $document->valeurs->isEmpty()
                    && $document->bordereau->isEmpty()
                    && (empty($document->content) || TypeDocument::isExp42Name($docName))
                    && !in_array($docName, $excludedFromSkip, true);
            }

            $hasNothingToShow = $document->typeDocument
                && TypeDocument::isUploadOnlyName($document->typeDocument->nom, $document->typeDocument->type_formulaire)
                && $document->fichiers->isEmpty()
                && $document->valeurs->isEmpty()
                && $document->bordereau->isEmpty()
                && empty($document->content);

            if (!$skipHtmlDocPage && !$hasNothingToShow) {
                $htmlDoc = view('dossiers.pdf', compact('dossier', 'pageGardeDataUri'))->with([
                    'documents' => collect([$document]),
                    'renderMode' => 'doc',
                ])->render();
                $docOrientation = $document->typeDocument && in_array(trim($document->typeDocument->nom), $this->getLandscapeTableDocNames(), true)
                    ? 'landscape'
                    : 'portrait';
                $dompdfDoc = new Dompdf(['isRemoteEnabled' => true]);
                $dompdfDoc->loadHtml($htmlDoc);
                $dompdfDoc->setPaper('A4', $docOrientation);
                $dompdfDoc->render();
                $docTemp = tempnam(sys_get_temp_dir(), 'apercu_doc_') . '.pdf';
                file_put_contents($docTemp, $dompdfDoc->output());
                $filesToMerge[] = $docTemp;
                $tempFiles[] = $docTemp;
            }

            if ($skipHtmlDocPage) {
                foreach ($pdfAttachments as $f) {
                    $path = storage_path('app/public/' . ltrim($f->chemin_fichier, '/'));
                    if (file_exists($path)) {
                        $filesToMerge[] = $path;
                    }
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
     * Fusionne plusieurs fichiers PDF en un seul fichier de sortie en utilisant FPDI.
     * Nécessite l'installation de setasign/fpdi (+ fpdf ou tcpdf selon la configuration).
     * @param array $files Chemins absolus vers les fichiers PDF à fusionner
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

        $pdf = new \setasign\Fpdi\Fpdi();

        foreach ($files as $file) {
            if (!file_exists($file)) {
                // ignorer les fichiers manquants
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
            }
        }

        // Sauvegarder le PDF fusionné
        $pdf->Output('F', $outputFull);
    }

    /**
     * Ajoute chaque page d'un PDF au document FPDI en cours sous forme d'image
     * (via Ghostscript), pour les PDF que le parseur gratuit de FPDI ne sait pas
     * lire directement (techniques de compression non supportées).
     */
    private function appendPdfAsImages(\setasign\Fpdi\Fpdi $pdf, string $file): void
    {
        $gsPath = $this->findGhostscriptExecutable();
        if (!$gsPath) {
            return;
        }

        $prefix = uniqid('gs_merge_', true);
        $outputPattern = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $prefix . '_%03d.png';

        $cmd = '"' . str_replace('\\', '/', $gsPath) . '" -q -dNOPAUSE -dBATCH -dSAFER -sDEVICE=png16m -r150 '
            . '-sOutputFile="' . str_replace('\\', '/', $outputPattern) . '" "' . str_replace('\\', '/', $file) . '" 2>&1';
        exec($cmd, $cmdOutput, $returnCode);

        $pages = glob(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $prefix . '_*.png');
        sort($pages);

        foreach ($pages as $imagePath) {
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
        $documents = $dossier->documents()->orderBy('created_at')->orderBy('id')->get();

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
     * Afficher un dossier
     */
    public function show(Dossier $dossier)
    {
        $dossier->load(['documents.typeDocument', 'documents.fichiers', 'entreprise', 'typeDossier', 'utilisateurs']);

        $hasSharedUsers = $dossier->utilisateurs->isNotEmpty();
        if (! auth()->user()->isAdminOrDirecteur() && $hasSharedUsers && ! $dossier->utilisateurs->contains(auth()->id())) {
            abort(403, 'Accès refusé');
        }

        $this->normalizeDocumentOrders($dossier);
        $dossier->load(['documents.typeDocument', 'documents.fichiers', 'entreprise', 'typeDossier']);

        $uploadableDocs = $dossier->documents
            ->filter(fn ($doc) => $doc->typeDocument)
            ->sortBy('ordre')
            ->values();

        // Prefer a document with status 'en_cours', otherwise take the first empty document
        $inProgressIndex = $uploadableDocs->search(fn ($doc) => $doc->statut === 'en_cours');
        if ($inProgressIndex !== false) {
            $resumeIndex = $inProgressIndex;
        } else {
            $firstEmpty = $uploadableDocs->search(fn ($doc) => $doc->fichiers->isEmpty());
            $resumeIndex = $firstEmpty === false ? null : $firstEmpty;
        }

        $resumeDocumentIds = $uploadableDocs->map(fn ($doc) => $doc->type_document_id)->values()->all();

        return view('dossiers.show', compact('dossier', 'resumeIndex', 'resumeDocumentIds'));
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

        $resumeIndex = $uploadableDocs->search(fn ($doc) => $doc->fichiers->isEmpty() || $doc->statut !== 'complete');

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
        $globalChiffres = ChiffreAffaire::whereNull('dossier_id')->orderBy('annee')->get();

        return view('dossiers.create.step6', [
            'dossier' => $dossier,
            'uploadQueue' => $uploadQueue,
            'currentDocument' => $currentDocument,
            'currentIndex' => $resumeIndex,
            'totalCount' => $uploadQueue->count(),
            'selectedDocumentIds' => $selectedDocumentIds,
            'globalChiffres' => $globalChiffres,
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
            ? Dossier::with(['entreprise', 'typeDossier', 'documents'])
            : auth()->user()->dossiers()->with(['entreprise', 'typeDossier', 'documents']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nom_dossier', 'like', "%{$search}%")
                    ->orWhere('lot', 'like', "%{$search}%")
                    ->orWhere('public_prive', 'like', "%{$search}%")
                    ->orWhere('statut', 'like', "%{$search}%")
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

        // For each dossier, compute resume info (index and document type ids) so list can offer direct "Continuer"
        $resumeInfo = [];
        foreach ($dossiers as $dossier) {
            $excludedName = 'Lettre de soumission';
            $uploadable = $dossier->documents
                ->filter(fn ($doc) => $doc->typeDocument && $doc->typeDocument->nom !== $excludedName)
                ->sortBy('ordre')
                ->values();

            if ($uploadable->isEmpty()) {
                $resumeInfo[$dossier->id] = null;
                continue;
            }

            $inProgress = $uploadable->search(fn ($doc) => $doc->statut === 'en_cours');
            if ($inProgress !== false) {
                $resumeIndex = $inProgress;
            } else {
                $firstEmpty = $uploadable->search(fn ($doc) => $doc->fichiers->isEmpty());
                $resumeIndex = $firstEmpty === false ? null : $firstEmpty;
            }

            $resumeInfo[$dossier->id] = $resumeIndex === null ? null : [
                'current_index' => $resumeIndex,
                'document_type_ids' => $uploadable->pluck('type_document_id')->all(),
            ];
        }

        return view('dossiers.index', compact('dossiers', 'resumeInfo'));
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
