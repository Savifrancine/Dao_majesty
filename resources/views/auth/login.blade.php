<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Se connecter - DAO</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; background: linear-gradient(135deg, #f0f9ff, #faf5ff); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        a { text-decoration: none; color: inherit; }
        .container { max-width: 500px; width: 100%; }
        .header { text-align: center; margin-bottom: 40px; }
        .logo { font-size: 28px; font-weight: bold; background: linear-gradient(135deg, #2563eb, #9333ea); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 20px; }
        .header h1 { font-size: 32px; color: #1a1a1a; margin-bottom: 10px; }
        .header p { color: #666; font-size: 16px; }
        .card { background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); padding: 40px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; color: #1a1a1a; }
        input[type="email"], input[type="password"] { width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; transition: all 0.3s; }
        input[type="email"]:focus, input[type="password"]:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
        .checkbox-group { display: flex; align-items: center; margin-bottom: 20px; }
        .checkbox-group input[type="checkbox"] { width: auto; margin-right: 10px; cursor: pointer; }
        .checkbox-group label { margin: 0; font-size: 14px; cursor: pointer; }
        .error { color: #dc2626; font-size: 13px; margin-top: 5px; }
        .btn { width: 100%; padding: 14px; background: linear-gradient(135deg, #2563eb, #9333ea); color: white; border: none; border-radius: 8px; font-weight: 600; font-size: 16px; cursor: pointer; transition: all 0.3s; margin-bottom: 15px; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 8px 16px rgba(37, 99, 235, 0.3); }
        .links { display: flex; justify-content: space-between; align-items: center; margin-top: 20px; font-size: 14px; }
        .links a { color: #2563eb; font-weight: 500; }
        .links a:hover { text-decoration: underline; }
        .signup-link { text-align: center; margin-top: 25px; color: #666; }
        .signup-link a { color: #2563eb; font-weight: 600; }
        .signup-link a:hover { text-decoration: underline; }
        @media (max-width: 500px) { .card { padding: 30px 20px; } .header h1 { font-size: 24px; } .links { flex-direction: column; gap: 10px; align-items: flex-start; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">DAO</div>
            <h1>Se connecter</h1>
            <p>Accédez à votre espace de gestion de dossiers</p>
        </div>

        <div class="card">
            <form method="POST" action="{{ route('login') }}">
                @csrf
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Login') }}</div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right">Email</label>
                            <div class="col-md-6">
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right">Mot de passe</label>
                            <div class="col-md-6">
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>

                <div class="form-group">
                    <label for="email">Adresse email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="vous@exemple.com">
                    @error('email')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••">
                    @error('password')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label for="remember">Se souvenir de moi</label>
                </div>
                        <div class="row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">{{ __('Login') }}</button>
                        <div class="row mb-3">
                            <div class="col-md-6 offset-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                <button type="submit" class="btn">Se connecter</button>

                <div class="links">
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}">Mot de passe oublié ?</a>
                    @endif
                </div>

                <div class="signup-link">
                    Pas encore inscrit ? <a href="{{ route('register') }}">Créer un compte</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
