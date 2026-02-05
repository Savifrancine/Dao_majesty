<?php

use Illuminate\Support\Facades\Route;
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
    
    // Wizard steps
    Route::get('/dossiers/create', [App\Http\Controllers\DossierController::class, 'create'])->name('dossiers.create');
    Route::post('/dossiers/step2', [App\Http\Controllers\DossierController::class, 'step2'])->name('dossiers.step2');
    Route::post('/dossiers/step3', [App\Http\Controllers\DossierController::class, 'step3'])->name('dossiers.step3');
    Route::post('/dossiers/step4', [App\Http\Controllers\DossierController::class, 'step4'])->name('dossiers.step4');
    Route::post('/dossiers/step5', [App\Http\Controllers\DossierController::class, 'step5'])->name('dossiers.step5');
    Route::post('/dossiers/step6/{dossierId}', [App\Http\Controllers\DossierController::class, 'step6'])->name('dossiers.step6');
    
    // Entreprise creation
    Route::post('/dossiers/entreprise/store', [App\Http\Controllers\DossierController::class, 'storeEntreprise'])->name('dossiers.storeEntreprise');
    
    // Document filling
    Route::post('/dossiers/{dossierId}/documents/{documentIndex}/save', [App\Http\Controllers\DossierController::class, 'saveDocumentValues'])->name('dossiers.saveDocumentValues');
    Route::get('/dossiers/{dossier}/pdf', [App\Http\Controllers\DossierController::class, 'generatePDF'])->name('dossiers.pdf');
});

// CRUD routes for Dao
Route::resource('daos', App\Http\Controllers\DaoController::class)->middleware('auth');
