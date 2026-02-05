@extends('layouts.app')

@section('content')
<div class="container" style="padding: 40px 0;">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            @if (Auth::check())
                <!-- Header du Dashboard -->
                <div style="margin-bottom: 40px;">
                    <h1 style="font-size: 32px; font-weight: 700; color: #1e293b; margin: 0 0 8px 0;">Tableau de bord</h1>
                    <p style="color: #64748b; margin: 0; font-size: 15px;">Bienvenue, {{ Auth::user()->prenom }}</p>
                </div>

                <!-- Messages d'alerte -->
                @if (session('status'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert" style="margin-bottom: 24px;">
                        {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Profil utilisateur -->
                <div class="card" style="margin-bottom: 32px;">
                    <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 16px;">
                        <h5 style="margin: 0; color: #1e293b; font-weight: 600;">Informations personnelles</h5>
                    </div>
                    <div class="card-body" style="padding: 20px;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                            <div>
                                <p style="margin: 0 0 4px 0; color: #64748b; font-size: 13px; font-weight: 600; text-transform: uppercase;">Nom complet</p>
                                <p style="margin: 0; color: #1e293b; font-size: 16px; font-weight: 500;">{{ Auth::user()->prenom }} {{ Auth::user()->nom }}</p>
                            </div>
                            <div>
                                <p style="margin: 0 0 4px 0; color: #64748b; font-size: 13px; font-weight: 600; text-transform: uppercase;">Email</p>
                                <p style="margin: 0; color: #1e293b; font-size: 16px; font-weight: 500;">{{ Auth::user()->email }}</p>
                            </div>
                            <div>
                                <p style="margin: 0 0 4px 0; color: #64748b; font-size: 13px; font-weight: 600; text-transform: uppercase;">Rôle</p>
                                <p style="margin: 0;"><span style="background: #3b82f6; color: white; padding: 4px 12px; border-radius: 6px; font-size: 13px; font-weight: 600; display: inline-block;">{{ Auth::user()->role ?? 'Utilisateur' }}</span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sections principales -->
                <div style="margin-bottom: 32px;">
                    <h5 style="color: #1e293b; font-weight: 600; margin: 0 0 16px 0; font-size: 16px;">Accès rapide</h5>
                    <div class="feature-grid">
                        <div class="feature-card">
                            <div style="width: 40px; height: 40px; background: #dbeafe; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-bottom: 12px;">
                                <span style="font-size: 20px; color: #1e40af;">📁</span>
                            </div>
                            <h6 style="color: #1e293b; font-weight: 600; margin: 0 0 8px 0;">Dossiers</h6>
                            <p style="color: #64748b; margin: 0; font-size: 14px;">Accédez et organisez vos dossiers</p>
                        </div>
                        <div class="feature-card">
                            <div style="width: 40px; height: 40px; background: #d1fae5; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-bottom: 12px;">
                                <span style="font-size: 20px; color: #065f46;">📄</span>
                            </div>
                            <h6 style="color: #1e293b; font-weight: 600; margin: 0 0 8px 0;">Documents</h6>
                            <p style="color: #64748b; margin: 0; font-size: 14px;">Gérez vos fichiers et documents</p>
                        </div>
                        <div class="feature-card">
                            <div style="width: 40px; height: 40px; background: #fef3c7; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-bottom: 12px;">
                                <span style="font-size: 20px; color: #92400e;">⚙️</span>
                            </div>
                            <h6 style="color: #1e293b; font-weight: 600; margin: 0 0 8px 0;">Paramètres</h6>
                            <p style="color: #64748b; margin: 0; font-size: 14px;">Configurez votre profil</p>
                        </div>
                    </div>
                </div>

                <!-- Actions principales -->
                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    <a href="{{ route('daos.index') }}" class="btn btn-primary">Voir les DAOs</a>
                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-danger">Déconnexion</button>
                    </form>
                </div>

            @else
                <!-- État non connecté -->
                <div style="text-align: center; padding: 60px 20px;">
                    <h1 style="font-size: 32px; font-weight: 700; color: #1e293b; margin-bottom: 16px;">Bienvenue</h1>
                    <p style="font-size: 16px; color: #64748b; margin-bottom: 32px;">Connectez-vous pour accéder à votre tableau de bord</p>
                    <a href="{{ route('login') }}" class="btn btn-primary">Se connecter</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
