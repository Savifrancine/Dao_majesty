@extends('layouts.app')

@section('content')
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 15px; margin-bottom: 30px;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="font-size: 36px;">👁️</div>
                    <h1 style="margin: 0; font-size: 36px; font-weight: 800; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Détails du DAO</h1>
                </div>
            </div>

            <div class="card" style="border-radius: 16px;">
                <div style="padding: 30px; background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%); border-radius: 16px 16px 0 0; border: 1px solid rgba(102, 126, 234, 0.3);">
                    <h2 style="margin: 0; font-weight: 700; color: #667eea;">{{ $dao->nom }}</h2>
                </div>

                <div class="card-body" style="padding: 30px;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; margin-bottom: 40px;">
                        <div>
                            <p style="color: #666; font-size: 13px; text-transform: uppercase; font-weight: 600; letter-spacing: 1px; margin-bottom: 10px;">🆔 ID</p>
                            <p style="margin: 0; font-size: 18px; font-weight: 600;">{{ $dao->id }}</p>
                        </div>
                        <div>
                            <p style="color: #666; font-size: 13px; text-transform: uppercase; font-weight: 600; letter-spacing: 1px; margin-bottom: 10px;">📧 Email</p>
                            <p style="margin: 0; font-size: 18px; font-weight: 600;">{{ $dao->email ?? '—' }}</p>
                        </div>
                        <div>
                            <p style="color: #666; font-size: 13px; text-transform: uppercase; font-weight: 600; letter-spacing: 1px; margin-bottom: 10px;">📞 Téléphone</p>
                            <p style="margin: 0; font-size: 18px; font-weight: 600;">{{ $dao->telephone ?? '—' }}</p>
                        </div>
                        <div>
                            <p style="color: #666; font-size: 13px; text-transform: uppercase; font-weight: 600; letter-spacing: 1px; margin-bottom: 10px;">📍 Adresse</p>
                            <p style="margin: 0; font-size: 18px; font-weight: 600;">{{ $dao->adresse ?? '—' }}</p>
                        </div>
                        <div>
                            <p style="color: #666; font-size: 13px; text-transform: uppercase; font-weight: 600; letter-spacing: 1px; margin-bottom: 10px;">🏙️ Ville</p>
                            <p style="margin: 0; font-size: 18px; font-weight: 600;">{{ $dao->ville ?? '—' }}</p>
                        </div>
                        <div>
                            <p style="color: #666; font-size: 13px; text-transform: uppercase; font-weight: 600; letter-spacing: 1px; margin-bottom: 10px;">📮 Code Postal</p>
                            <p style="margin: 0; font-size: 18px; font-weight: 600;">{{ $dao->code_postal ?? '—' }}</p>
                        </div>
                        <div>
                            <p style="color: #666; font-size: 13px; text-transform: uppercase; font-weight: 600; letter-spacing: 1px; margin-bottom: 10px;">✅ Statut</p>
                            @if ($dao->actif)
                                <span class="badge" style="background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%); color: white; padding: 8px 16px; font-size: 14px; font-weight: 600;">Actif</span>
                            @else
                                <span class="badge" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; padding: 8px 16px; font-size: 14px; font-weight: 600;">Inactif</span>
                            @endif
                        </div>
                    </div>

                    @if ($dao->description)
                        <div style="background: linear-gradient(135deg, rgba(132, 250, 176, 0.1) 0%, rgba(143, 211, 244, 0.1) 100%); padding: 20px; border-radius: 12px; border: 1px solid rgba(132, 250, 176, 0.3); margin-bottom: 30px;">
                            <p style="color: #666; font-size: 13px; text-transform: uppercase; font-weight: 600; letter-spacing: 1px; margin-bottom: 10px;">📝 Description</p>
                            <p style="margin: 0; font-size: 16px; line-height: 1.6;">{{ $dao->description }}</p>
                        </div>
                    @endif

                    <div style="background: #f5f5f5; padding: 20px; border-radius: 12px; display: flex; justify-content: space-between; margin-bottom: 30px;">
                        <div>
                            <p style="color: #666; font-size: 13px; text-transform: uppercase; font-weight: 600; letter-spacing: 1px; margin-bottom: 5px;">📅 Créé le</p>
                            <p style="margin: 0; font-size: 14px; font-weight: 500;">{{ $dao->created_at->format('d/m/Y à H:i') }}</p>
                        </div>
                        <div>
                            <p style="color: #666; font-size: 13px; text-transform: uppercase; font-weight: 600; letter-spacing: 1px; margin-bottom: 5px;">🔄 Modifié le</p>
                            <p style="margin: 0; font-size: 14px; font-weight: 500;">{{ $dao->updated_at->format('d/m/Y à H:i') }}</p>
                        </div>
                    </div>

                    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                        <a href="{{ route('daos.edit', $dao) }}" class="btn btn-warning" style="padding: 12px 24px;">✏️ Éditer</a>
                        <form action="{{ route('daos.destroy', $dao) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" style="padding: 12px 24px;" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce DAO ?')">🗑️ Supprimer</button>
                        </form>
                        <a href="{{ route('daos.index') }}" class="btn btn-secondary" style="padding: 12px 24px;">← Retour</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
