<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DaoController;

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
