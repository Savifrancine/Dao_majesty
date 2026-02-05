<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DaoController;

Route::get('/', function () {
    return view('welcome');
});

// Auth routes using built-in controllers
Auth::routes();

// CRUD routes for Dao
Route::resource('daos', DaoController::class)->middleware('auth');

// Home page
Route::get('/home', [HomeController::class, 'index'])->name('home')->middleware('auth');
