<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

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

Route::get('/home', function () {
    return view('home');
})->name('home')->middleware('auth');

// CRUD routes for Dao
Route::resource('daos', App\Http\Controllers\DaoController::class)->middleware('auth');

