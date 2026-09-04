<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DaoController;

// Some hosts won't let Apache follow the public/storage symlink (403), so
// public storage files are served through Laravel instead when needed.
Route::get('/reset-opcache-xyz', function () {
    if (function_exists('opcache_reset')) {
        opcache_reset();
        return 'OPcache reset: OK';
    }
    return 'OPcache not available';
});

Route::get('/storage/{path}', function (string $path) {
    if (request()->query('debug') === '1') {
        return response()->json([
            'path' => $path,
            'storage_path' => storage_path(),
            'disk_root' => Storage::disk('public')->path(''),
            'exists' => Storage::disk('public')->exists($path),
        ]);
    }

    abort_unless(Storage::disk('public')->exists($path), 404);

    return Storage::disk('public')->response($path);
})->where('path', '.*');

Route::get('/', function () {
    return view('welcome');
});

// Minimal auth routes (simple closures) because scaffolded Auth controllers are missing
Route::get('login', function () {
    return view('auth.login');
})->name('login');

Route::post('login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required','email'],
        'password' => ['required'],
    ]);

    // Attempt authentication with the Utilisateur model
    $user = App\Models\Utilisateur::where('email', $credentials['email'])->first();

    if ($user && password_verify($credentials['password'], $user->mot_de_passe)) {
        Auth::login($user);
        $request->session()->regenerate();
        return redirect()->intended('/home');
    }

    return back()->withErrors(['email' => 'Identifiants invalides.']);
});

Route::get('register', function () {
    return view('auth.register');
})->name('register');

Route::post('register', function (Request $request) {
    $data = $request->validate([
        'nom' => ['required','string','max:255'],
        'prenom' => ['required','string','max:255'],
        'email' => ['required','email','max:255','unique:utilisateurs,email'],
        'password' => ['required','string','min:8','confirmed'],
    ]);

    $user = App\Models\Utilisateur::create([
        'nom' => $data['nom'],
        'prenom' => $data['prenom'],
        'email' => $data['email'],
        'mot_de_passe' => bcrypt($data['password']),
        'role' => 'employe',
    ]);

    Auth::login($user);
    return redirect('/home');
});

Route::post('logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');

Route::get('/home', [App\Http\Controllers\DossierController::class, 'dashboardHome'])->name('home')->middleware('auth');

// Wizard routes for Dossier creation
Route::middleware('auth')->group(function () {
    // Dashboard dossiers
    Route::get('/dossiers', [App\Http\Controllers\DossierController::class, 'index'])->name('dossiers.index');
    Route::get('/dossiers/show/{dossier}', [App\Http\Controllers\DossierController::class, 'show'])->name('dossiers.show');
    Route::get('/dossiers/{dossier}/continuer', [App\Http\Controllers\DossierController::class, 'continueCreation'])->name('dossiers.continuer');
    Route::get('/dossiers/{dossier}/select-documents', [App\Http\Controllers\DossierController::class, 'selectDocuments'])->name('dossiers.selectDocuments');
    Route::delete('/dossiers/{dossier}/document/{document}', [App\Http\Controllers\DossierController::class, 'destroyDocument'])->name('dossiers.destroyDocument');

    // Wizard steps
    Route::get('/dossiers/create', [App\Http\Controllers\DossierController::class, 'create'])->name('dossiers.create');
    Route::post('/dossiers/step2', [App\Http\Controllers\DossierController::class, 'step2'])->name('dossiers.step2');
    Route::post('/dossiers/step3', [App\Http\Controllers\DossierController::class, 'step3'])->name('dossiers.step3');
    Route::match(['get','post'], '/dossiers/step4', [App\Http\Controllers\DossierController::class, 'step4'])->name('dossiers.step4');
    Route::post('/dossiers/step5', [App\Http\Controllers\DossierController::class, 'step5'])->name('dossiers.step5');
    Route::match(['get','post'], '/dossiers/step6/{dossierId}', [App\Http\Controllers\DossierController::class, 'step6'])->name('dossiers.step6');

    // Déclaration de garantie — formulaire et génération PDF
    Route::get('/documents/declaration', [App\Http\Controllers\DocumentController::class, 'showDeclarationForm'])->name('documents.declaration.form');
    Route::post('/documents/declaration/pdf', [App\Http\Controllers\DocumentController::class, 'generateDeclarationPDF'])->name('documents.declaration.pdf');
    Route::get('/documents/declaration/partial', [App\Http\Controllers\DocumentController::class, 'showDeclarationPartial'])->name('documents.declaration.partial');
    // Aperçu HTML des documents du dossier
    Route::get('/documents/preview/{document}', [App\Http\Controllers\DocumentController::class, 'previewDocument'])->name('documents.preview');
    // Entreprise creation
    Route::post('/dossiers/entreprise/store', [App\Http\Controllers\DossierController::class, 'storeEntreprise'])->name('dossiers.storeEntreprise');

    // Document filling
    Route::post('/dossiers/{dossierId}/documents/{documentIndex}/save', [App\Http\Controllers\DossierController::class, 'saveDocumentValues'])->name('dossiers.saveDocumentValues');
    Route::post('/dossiers/import-tableau', [App\Http\Controllers\DossierController::class, 'importTableau'])->name('dossiers.importTableau');
    Route::post('/dossiers/save-chiffres', [App\Http\Controllers\DossierController::class, 'saveChiffres'])->name('dossiers.save-chiffres');
    Route::get('/dossiers/{dossier}/pdf', [App\Http\Controllers\DossierController::class, 'generatePDF'])->name('dossiers.pdf');
    Route::post('/dossiers/{dossier}/destroy', [App\Http\Controllers\DossierController::class, 'destroy'])->name('dossiers.destroy');
    // Chiffres d'affaires (gestion et PDF)
    Route::get('/chiffres', [App\Http\Controllers\ChiffreAffaireController::class, 'index'])->name('chiffres.index');
    Route::post('/chiffres', [App\Http\Controllers\ChiffreAffaireController::class, 'storeGlobal'])->name('chiffres.store.global');
    Route::get('/chiffres/pdf/form', [App\Http\Controllers\ChiffreAffaireController::class, 'pdfFormGlobal'])->name('chiffres.pdf.form.global');
    Route::post('/chiffres/pdf', [App\Http\Controllers\ChiffreAffaireController::class, 'generatePdfGlobal'])->name('chiffres.pdf.generate.global');
    Route::get('/dossiers/{dossier}/chiffres', [App\Http\Controllers\ChiffreAffaireController::class, 'manage'])->name('chiffres.manage');
    Route::post('/dossiers/{dossier}/chiffres', [App\Http\Controllers\ChiffreAffaireController::class, 'store'])->name('chiffres.store');
    Route::get('/dossiers/{dossier}/chiffres/pdf/form', [App\Http\Controllers\ChiffreAffaireController::class, 'pdfForm'])->name('chiffres.pdf.form');
    Route::post('/dossiers/{dossier}/chiffres/pdf', [App\Http\Controllers\ChiffreAffaireController::class, 'generatePdf'])->name('chiffres.pdf.generate');

    Route::get('/dossiers/{dossier}/utilisateurs', [App\Http\Controllers\DossierController::class, 'manageUsers'])->name('dossiers.utilisateurs');
    Route::post('/dossiers/{dossier}/utilisateurs', [App\Http\Controllers\DossierController::class, 'updateUsers'])->name('dossiers.utilisateurs.update');

    Route::get('/dossiers/{dossier}/informations', [App\Http\Controllers\DossierController::class, 'editInfos'])->name('dossiers.editInfos');
    Route::post('/dossiers/{dossier}/informations', [App\Http\Controllers\DossierController::class, 'updateInfos'])->name('dossiers.updateInfos');

    // Formulaire MAT
    Route::get('/formulaire-mat', [App\Http\Controllers\FormulaireMatController::class, 'index'])->name('formulaire_mat.index');
    Route::get('/formulaire-mat/create', [App\Http\Controllers\FormulaireMatController::class, 'create'])->name('formulaire_mat.create');
    Route::get('/formulaire-mat/generate', [App\Http\Controllers\FormulaireMatController::class, 'generateForm'])->name('formulaire_mat.generate');
    Route::post('/formulaire-mat/generate', [App\Http\Controllers\FormulaireMatController::class, 'generate'])->name('formulaire_mat.generate.post');
    Route::post('/formulaire-mat', [App\Http\Controllers\FormulaireMatController::class, 'store'])->name('formulaire_mat.store');
    Route::get('/formulaire-mat/{formulaireMat}/edit', [App\Http\Controllers\FormulaireMatController::class, 'edit'])->name('formulaire_mat.edit');
    Route::put('/formulaire-mat/{formulaireMat}', [App\Http\Controllers\FormulaireMatController::class, 'update'])->name('formulaire_mat.update');
    Route::post('/formulaire-mat/{formulaireMat}/destroy', [App\Http\Controllers\FormulaireMatController::class, 'destroy'])->name('formulaire_mat.destroy');
    Route::get('/formulaire-mat/{formulaireMat}/download', [App\Http\Controllers\FormulaireMatController::class, 'download'])->name('formulaire_mat.download');

    // Formulaire EXP 4.2 a)
    Route::get('/formulaire-exp-4-2-a', [App\Http\Controllers\FormulaireExp42AController::class, 'index'])->name('formulaire_exp_4_2_a.index');
    Route::get('/formulaire-exp-4-2-a/create', [App\Http\Controllers\FormulaireExp42AController::class, 'create'])->name('formulaire_exp_4_2_a.create');
    Route::get('/formulaire-exp-4-2-a/generate', [App\Http\Controllers\FormulaireExp42AController::class, 'generateForm'])->name('formulaire_exp_4_2_a.generate');
    Route::post('/formulaire-exp-4-2-a/generate', [App\Http\Controllers\FormulaireExp42AController::class, 'generate'])->name('formulaire_exp_4_2_a.generate.post');
    Route::post('/formulaire-exp-4-2-a', [App\Http\Controllers\FormulaireExp42AController::class, 'store'])->name('formulaire_exp_4_2_a.store');
    Route::get('/formulaire-exp-4-2-a/{formulaireExp42A}/edit', [App\Http\Controllers\FormulaireExp42AController::class, 'edit'])->name('formulaire_exp_4_2_a.edit');
    Route::put('/formulaire-exp-4-2-a/{formulaireExp42A}', [App\Http\Controllers\FormulaireExp42AController::class, 'update'])->name('formulaire_exp_4_2_a.update');
    Route::post('/formulaire-exp-4-2-a/{formulaireExp42A}/destroy', [App\Http\Controllers\FormulaireExp42AController::class, 'destroy'])->name('formulaire_exp_4_2_a.destroy');
    Route::get('/formulaire-exp-4-2-a/{formulaireExp42A}/download', [App\Http\Controllers\FormulaireExp42AController::class, 'download'])->name('formulaire_exp_4_2_a.download');

    // Formulaire EXP 4.2 b)
    Route::get('/formulaire-exp-4-2-b', [App\Http\Controllers\FormulaireExp42BController::class, 'index'])->name('formulaire_exp_4_2_b.index');
    Route::get('/formulaire-exp-4-2-b/create', [App\Http\Controllers\FormulaireExp42BController::class, 'create'])->name('formulaire_exp_4_2_b.create');
    Route::get('/formulaire-exp-4-2-b/generate', [App\Http\Controllers\FormulaireExp42BController::class, 'generateForm'])->name('formulaire_exp_4_2_b.generate');
    Route::post('/formulaire-exp-4-2-b/generate', [App\Http\Controllers\FormulaireExp42BController::class, 'generate'])->name('formulaire_exp_4_2_b.generate.post');
    Route::post('/formulaire-exp-4-2-b', [App\Http\Controllers\FormulaireExp42BController::class, 'store'])->name('formulaire_exp_4_2_b.store');
    Route::get('/formulaire-exp-4-2-b/{formulaireExp42B}/edit', [App\Http\Controllers\FormulaireExp42BController::class, 'edit'])->name('formulaire_exp_4_2_b.edit');
    Route::put('/formulaire-exp-4-2-b/{formulaireExp42B}', [App\Http\Controllers\FormulaireExp42BController::class, 'update'])->name('formulaire_exp_4_2_b.update');
    Route::post('/formulaire-exp-4-2-b/{formulaireExp42B}/destroy', [App\Http\Controllers\FormulaireExp42BController::class, 'destroy'])->name('formulaire_exp_4_2_b.destroy');
    Route::get('/formulaire-exp-4-2-b/{formulaireExp42B}/download', [App\Http\Controllers\FormulaireExp42BController::class, 'download'])->name('formulaire_exp_4_2_b.download');

    // Formulaire EXP 4.2 a) (suite)
    Route::get('/formulaire-exp-4-2-a-suite', [App\Http\Controllers\FormulaireExp42ASuiteController::class, 'index'])->name('formulaire_exp_4_2_a_suite.index');
    Route::get('/formulaire-exp-4-2-a-suite/create', [App\Http\Controllers\FormulaireExp42ASuiteController::class, 'create'])->name('formulaire_exp_4_2_a_suite.create');
    Route::get('/formulaire-exp-4-2-a-suite/generate', [App\Http\Controllers\FormulaireExp42ASuiteController::class, 'generateForm'])->name('formulaire_exp_4_2_a_suite.generate');
    Route::post('/formulaire-exp-4-2-a-suite/generate', [App\Http\Controllers\FormulaireExp42ASuiteController::class, 'generate'])->name('formulaire_exp_4_2_a_suite.generate.post');
    Route::post('/formulaire-exp-4-2-a-suite', [App\Http\Controllers\FormulaireExp42ASuiteController::class, 'store'])->name('formulaire_exp_4_2_a_suite.store');
    Route::get('/formulaire-exp-4-2-a-suite/{formulaireExp42ASuite}/edit', [App\Http\Controllers\FormulaireExp42ASuiteController::class, 'edit'])->name('formulaire_exp_4_2_a_suite.edit');
    Route::put('/formulaire-exp-4-2-a-suite/{formulaireExp42ASuite}', [App\Http\Controllers\FormulaireExp42ASuiteController::class, 'update'])->name('formulaire_exp_4_2_a_suite.update');
    Route::post('/formulaire-exp-4-2-a-suite/{formulaireExp42ASuite}/destroy', [App\Http\Controllers\FormulaireExp42ASuiteController::class, 'destroy'])->name('formulaire_exp_4_2_a_suite.destroy');
    Route::get('/formulaire-exp-4-2-a-suite/{formulaireExp42ASuite}/download', [App\Http\Controllers\FormulaireExp42ASuiteController::class, 'download'])->name('formulaire_exp_4_2_a_suite.download');

    // Formulaire EXP 4.2 b) (suite)
    Route::get('/formulaire-exp-4-2-b-suite', [App\Http\Controllers\FormulaireExp42BSuiteController::class, 'index'])->name('formulaire_exp_4_2_b_suite.index');
    Route::get('/formulaire-exp-4-2-b-suite/create', [App\Http\Controllers\FormulaireExp42BSuiteController::class, 'create'])->name('formulaire_exp_4_2_b_suite.create');
    Route::get('/formulaire-exp-4-2-b-suite/generate', [App\Http\Controllers\FormulaireExp42BSuiteController::class, 'generateForm'])->name('formulaire_exp_4_2_b_suite.generate');
    Route::post('/formulaire-exp-4-2-b-suite/generate', [App\Http\Controllers\FormulaireExp42BSuiteController::class, 'generate'])->name('formulaire_exp_4_2_b_suite.generate.post');
    Route::post('/formulaire-exp-4-2-b-suite', [App\Http\Controllers\FormulaireExp42BSuiteController::class, 'store'])->name('formulaire_exp_4_2_b_suite.store');
    Route::get('/formulaire-exp-4-2-b-suite/{formulaireExp42BSuite}/edit', [App\Http\Controllers\FormulaireExp42BSuiteController::class, 'edit'])->name('formulaire_exp_4_2_b_suite.edit');
    Route::put('/formulaire-exp-4-2-b-suite/{formulaireExp42BSuite}', [App\Http\Controllers\FormulaireExp42BSuiteController::class, 'update'])->name('formulaire_exp_4_2_b_suite.update');
    Route::post('/formulaire-exp-4-2-b-suite/{formulaireExp42BSuite}/destroy', [App\Http\Controllers\FormulaireExp42BSuiteController::class, 'destroy'])->name('formulaire_exp_4_2_b_suite.destroy');
    Route::get('/formulaire-exp-4-2-b-suite/{formulaireExp42BSuite}/download', [App\Http\Controllers\FormulaireExp42BSuiteController::class, 'download'])->name('formulaire_exp_4_2_b_suite.download');

    // Formulaire PER
    Route::get('/formulaire-per', [App\Http\Controllers\FormulairePERController::class, 'index'])->name('formulaire_per.index');
    Route::get('/formulaire-per/create', [App\Http\Controllers\FormulairePERController::class, 'create'])->name('formulaire_per.create');
    Route::post('/formulaire-per', [App\Http\Controllers\FormulairePERController::class, 'store'])->name('formulaire_per.store');
    Route::get('/formulaire-per/{formulaire_per}', [App\Http\Controllers\FormulairePERController::class, 'show'])->name('formulaire_per.show');
    Route::get('/formulaire-per/{formulaire_per}/edit', [App\Http\Controllers\FormulairePERController::class, 'edit'])->name('formulaire_per.edit');
    Route::put('/formulaire-per/{formulaire_per}', [App\Http\Controllers\FormulairePERController::class, 'update'])->name('formulaire_per.update');
    Route::post('/formulaire-per/{formulaire_per}/destroy', [App\Http\Controllers\FormulairePERController::class, 'destroy'])->name('formulaire_per.destroy');
    Route::get('/formulaire-per/{formulaire_per}/pdf', [App\Http\Controllers\FormulairePERController::class, 'downloadPDF'])->name('formulaire_per.pdf');

    // Gestion des utilisateurs
    Route::get('/utilisateurs', [App\Http\Controllers\UtilisateurController::class, 'index'])->name('utilisateurs.index');
    Route::get('/utilisateurs/create', [App\Http\Controllers\UtilisateurController::class, 'create'])->name('utilisateurs.create');
    Route::post('/utilisateurs', [App\Http\Controllers\UtilisateurController::class, 'store'])->name('utilisateurs.store');
    Route::get('/utilisateurs/{utilisateur}/edit', [App\Http\Controllers\UtilisateurController::class, 'edit'])->name('utilisateurs.edit');
    Route::put('/utilisateurs/{utilisateur}', [App\Http\Controllers\UtilisateurController::class, 'update'])->name('utilisateurs.update');
    Route::delete('/utilisateurs/{utilisateur}', [App\Http\Controllers\UtilisateurController::class, 'destroy'])->name('utilisateurs.destroy');
});

    // CRUD routes pour Signataires
    Route::get('/signataires', [App\Http\Controllers\SignataireController::class, 'index'])->name('signataires.index');
    Route::get('/signataires/create', [App\Http\Controllers\SignataireController::class, 'create'])->name('signataires.create');
    Route::post('/signataires', [App\Http\Controllers\SignataireController::class, 'store'])->name('signataires.store');
    Route::get('/signataires/{signataire}/edit', [App\Http\Controllers\SignataireController::class, 'edit'])->name('signataires.edit');
    Route::put('/signataires/{signataire}', [App\Http\Controllers\SignataireController::class, 'update'])->name('signataires.update');
    Route::delete('/signataires/{signataire}', [App\Http\Controllers\SignataireController::class, 'destroy'])->name('signataires.destroy');

    // CRUD routes pour Entreprises
    Route::get('/entreprises', [App\Http\Controllers\EntrepriseController::class, 'index'])->name('entreprises.index');
    Route::get('/entreprises/create', [App\Http\Controllers\EntrepriseController::class, 'create'])->name('entreprises.create');
    Route::post('/entreprises', [App\Http\Controllers\EntrepriseController::class, 'store'])->name('entreprises.store');
    Route::get('/entreprises/{entreprise}/edit', [App\Http\Controllers\EntrepriseController::class, 'edit'])->name('entreprises.edit');
    Route::put('/entreprises/{entreprise}', [App\Http\Controllers\EntrepriseController::class, 'update'])->name('entreprises.update');
    Route::delete('/entreprises/{entreprise}', [App\Http\Controllers\EntrepriseController::class, 'destroy'])->name('entreprises.destroy');

    // CRUD routes pour les types de documents personnalisés
    Route::get('/documents-personnalises', [App\Http\Controllers\CustomDocumentTypeController::class, 'index'])->name('custom-document-types.index');
    Route::get('/documents-personnalises/create', [App\Http\Controllers\CustomDocumentTypeController::class, 'create'])->name('custom-document-types.create');
    Route::post('/documents-personnalises', [App\Http\Controllers\CustomDocumentTypeController::class, 'store'])->name('custom-document-types.store');
    Route::get('/documents-personnalises/{customDocumentType}/edit', [App\Http\Controllers\CustomDocumentTypeController::class, 'edit'])->name('custom-document-types.edit');
    Route::put('/documents-personnalises/{customDocumentType}', [App\Http\Controllers\CustomDocumentTypeController::class, 'update'])->name('custom-document-types.update');
    Route::delete('/documents-personnalises/{customDocumentType}', [App\Http\Controllers\CustomDocumentTypeController::class, 'destroy'])->name('custom-document-types.destroy');

    // Étiquettes (enveloppes interne/externe)
    Route::get('/etiquettes/enveloppe-interne', [App\Http\Controllers\EtiquetteController::class, 'interneSelect'])->name('etiquettes.interne.select');
    Route::get('/etiquettes/enveloppe-externe', [App\Http\Controllers\EtiquetteController::class, 'externeSelect'])->name('etiquettes.externe.select');
    Route::get('/etiquettes/enveloppe-interne/{dossier}/{variante}', [App\Http\Controllers\EtiquetteController::class, 'interneGenerate'])->where('variante', 'original|copie')->name('etiquettes.interne.generate');
    Route::get('/etiquettes/enveloppe-externe/{dossier}', [App\Http\Controllers\EtiquetteController::class, 'externeGenerate'])->name('etiquettes.externe.generate');
    Route::get('/etiquettes/enveloppe-interne/{dossier}/formulaire', [App\Http\Controllers\EtiquetteController::class, 'interneForm'])->name('etiquettes.interne.form');
    Route::put('/etiquettes/enveloppe-interne/{dossier}/formulaire', [App\Http\Controllers\EtiquetteController::class, 'interneFormUpdate'])->name('etiquettes.interne.form.update');
    Route::get('/etiquettes/enveloppe-externe/{dossier}/formulaire', [App\Http\Controllers\EtiquetteController::class, 'externeForm'])->name('etiquettes.externe.form');
    Route::put('/etiquettes/enveloppe-externe/{dossier}/formulaire', [App\Http\Controllers\EtiquetteController::class, 'externeFormUpdate'])->name('etiquettes.externe.form.update');

    // CRUD routes pour Dao
Route::resource('daos', App\Http\Controllers\DaoController::class)->middleware('auth');

// Lookups for page de garde (AJAX add)
Route::post('lookups/{kind}', [App\Http\Controllers\LookupController::class, 'store'])->middleware('auth')->name('lookups.store');

    // Templates CRUD
    Route::get('/templates', [App\Http\Controllers\TemplateController::class, 'index'])->name('templates.index');
    Route::get('/templates/create', [App\Http\Controllers\TemplateController::class, 'create'])->name('templates.create');
    Route::post('/templates', [App\Http\Controllers\TemplateController::class, 'store'])->name('templates.store');
    Route::get('/templates/{template}/edit', [App\Http\Controllers\TemplateController::class, 'edit'])->name('templates.edit');
    Route::post('/templates/{template}', [App\Http\Controllers\TemplateController::class, 'update'])->name('templates.update');
    Route::post('/templates/{template}/destroy', [App\Http\Controllers\TemplateController::class, 'destroy'])->name('templates.destroy');
    Route::get('/templates/{template}/json', [App\Http\Controllers\TemplateController::class, 'showJson'])->name('templates.showJson');
