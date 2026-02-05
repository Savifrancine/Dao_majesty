@extends('layouts.app')

@section('content')
<div class="container d-flex align-items-center justify-content-center" style="min-height: calc(100vh - 70px);">
    <div class="row w-100">
        <div class="col-lg-5 col-md-7 mx-auto">
            <div class="card" style="overflow: hidden;">
                <div class="card-header" style="padding: 30px; text-align: center;">
                    <h2 style="color: #1e293b; font-size: 28px; font-weight: 700; margin: 0;">Se connecter</h2>
                </div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show">
                            <strong>Erreur !</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="email" class="form-label">Email</label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Mot de passe</label>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">
                                    Se souvenir de moi
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100" style="padding: 12px; font-size: 16px; margin-bottom: 15px;">
                            Se connecter
                        </button>

                        @if (Route::has('password.request'))
                            <div style="text-align: center;">
                                <a class="btn btn-link" href="{{ route('password.request') }}" style="color: #3b82f6; text-decoration: none; font-weight: 600;">
                                    Mot de passe oublié ?
                                </a>
                            </div>
                        @endif
                    </form>

                    <hr style="margin: 30px 0; border: none; border-top: 1px solid #e0e0e0;">

                    <div style="text-align: center;">
                        <p style="margin: 0; color: #475569; font-size: 14px;">Pas encore inscrit ?</p>
                        <a href="{{ route('register') }}" class="btn btn-secondary w-100 mt-2">
                            Créer un compte
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
