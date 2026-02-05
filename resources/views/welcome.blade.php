@extends('layouts.app')

@section('content')
<div class="container" style="min-height: calc(100vh - 70px); display: flex; align-items: center;">
    <div class="row w-100">
        <div class="col-lg-8 mx-auto">
            <div class="hero-section">
                <h1 style="font-size: 42px; font-weight: 700; margin-bottom: 16px; color: #1e293b;">
                    Gérez vos dossiers en toute simplicité
                </h1>

                <p style="font-size: 18px; color: #64748b; margin-bottom: 32px; line-height: 1.8;">
                    Une plateforme complète pour organiser, stocker et partager vos documents de manière sécurisée et efficace.
                </p>

                <div style="display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 48px;">
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn btn-primary">
                            Créer un compte
                        </a>
                    @endif

                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="btn btn-secondary">
                            Se connecter
                        </a>
                    @endif
                </div>

                <div class="feature-grid">
                    <div class="feature-card">
                        <div style="font-size: 24px; margin-bottom: 12px; color: #3b82f6;">&#9679;</div>
                        <h3>Organisation</h3>
                        <p>Créez et organisez vos dossiers comme vous le souhaitez</p>
                    </div>
                    <div class="feature-card">
                        <div style="font-size: 24px; margin-bottom: 12px; color: #10b981;">&#10003;</div>
                        <h3>Sécurité</h3>
                        <p>Protégez vos données avec des contrôles d'accès avancés</p>
                    </div>
                    <div class="feature-card">
                        <div style="font-size: 24px; margin-bottom: 12px; color: #f59e0b;">&#128666;</div>
                        <h3>Rapidité</h3>
                        <p>Accédez à vos fichiers en quelques secondes</p>
                    </div>
                </div>
            </div>
        </div>
