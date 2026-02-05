@extends('layouts.app')

@section('content')
<div class="container d-flex align-items-center justify-content-center" style="min-height: calc(100vh - 70px);">
    <div class="row w-100">
        <div class="col-lg-5 col-md-7 mx-auto">
            <div class="card" style="border-radius: 20px; overflow: hidden; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);">
                <div class="card-header" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border: none; padding: 30px; text-align: center;">
                    <div style="font-size: 48px; margin-bottom: 15px;">📝</div>
                    <h2 style="color: white; font-size: 28px; font-weight: 700; margin: 0;">Créer un compte</h2>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="nom" class="form-label" style="font-weight: 600;">👤 Nom</label>
                            <input id="nom" type="text" class="form-control @error('nom') is-invalid @enderror" name="nom" value="{{ old('nom') }}" required autocomplete="family-name" autofocus style="border-radius: 12px; padding: 12px 16px;">

                            @error('nom')
                                <span class="invalid-feedback" role="alert" style="display: block; margin-top: 5px;">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="prenom" class="form-label" style="font-weight: 600;">😊 Prénom</label>
                            <input id="prenom" type="text" class="form-control @error('prenom') is-invalid @enderror" name="prenom" value="{{ old('prenom') }}" required autocomplete="given-name" style="border-radius: 12px; padding: 12px 16px;">

                            @error('prenom')
                                <span class="invalid-feedback" role="alert" style="display: block; margin-top: 5px;">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="email" class="form-label" style="font-weight: 600;">📧 Email</label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" style="border-radius: 12px; padding: 12px 16px;">

                            @error('email')
                                <span class="invalid-feedback" role="alert" style="display: block; margin-top: 5px;">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label" style="font-weight: 600;">🔑 Mot de passe</label>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" style="border-radius: 12px; padding: 12px 16px;">

                            @error('password')
                                <span class="invalid-feedback" role="alert" style="display: block; margin-top: 5px;">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password-confirm" class="form-label" style="font-weight: 600;">🔒 Confirmer le mot de passe</label>
                            <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password" style="border-radius: 12px; padding: 12px 16px;">
                        </div>

                        <button type="submit" class="btn btn-primary w-100" style="padding: 14px; font-size: 16px; border-radius: 12px; margin-bottom: 15px;">
                            ✨ S'inscrire
                        </button>
                    </form>

                    <hr style="margin: 30px 0; border: none; border-top: 1px solid #e0e0e0;">

                    <div style="text-align: center;">
                        <p style="margin: 0; color: #666; font-size: 14px;">Vous avez déjà un compte ?</p>
                        <a href="{{ route('login') }}" class="btn btn-secondary w-100 mt-2" style="padding: 12px; border-radius: 12px;">
                            🔐 Se connecter
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
