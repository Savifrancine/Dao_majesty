@extends('layouts.app')

@section('content')
<div class="container mt-5 mb-5">
    <div class="row mb-5">
        <div class="col-lg-8">
            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
                <div style="font-size: 36px;">📁</div>
                <h1 style="margin: 0; font-size: 42px; font-weight: 800; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Liste des DAOs</h1>
            </div>
        </div>
        <div class="col-lg-4 d-flex justify-content-lg-end">
            <a href="{{ route('daos.create') }}" class="btn btn-success" style="padding: 14px 30px; font-size: 16px;">
                ✨ Ajouter un DAO
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 12px; margin-bottom: 30px;">
            ✅ {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($daos->count())
        <div class="row">
            @foreach ($daos as $dao)
                <div class="col-lg-6 col-md-12 mb-4">
                    <div class="card" style="border-radius: 16px; height: 100%; display: flex; flex-direction: column;">
                        <div style="padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 16px 16px 0 0;">
                            <h5 style="color: white; margin: 0; font-weight: 700; font-size: 20px;">{{ $dao->nom }}</h5>
                        </div>
                        <div class="card-body" style="flex: 1; display: flex; flex-direction: column;">
                            <div style="margin-bottom: 20px;">
                                @if ($dao->email)
                                    <p style="margin: 8px 0;"><strong>📧 Email :</strong> {{ $dao->email }}</p>
                                @endif
                                @if ($dao->telephone)
                                    <p style="margin: 8px 0;"><strong>📞 Téléphone :</strong> {{ $dao->telephone }}</p>
                                @endif
                                @if ($dao->ville)
                                    <p style="margin: 8px 0;"><strong>🏙️ Ville :</strong> {{ $dao->ville }}</p>
                                @endif
                                <p style="margin: 8px 0;">
                                    <strong>Statut :</strong>
                                    @if ($dao->actif)
                                        <span class="badge" style="background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%); color: white;">Actif</span>
                                    @else
                                        <span class="badge" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white;">Inactif</span>
                                    @endif
                                </p>
                            </div>
                            <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: auto;">
                                <a href="{{ route('daos.show', $dao) }}" class="btn btn-info btn-sm" style="padding: 8px 16px; font-size: 12px;">👁️ Voir</a>
                                <a href="{{ route('daos.edit', $dao) }}" class="btn btn-warning btn-sm" style="padding: 8px 16px; font-size: 12px;">✏️ Éditer</a>
                                <form action="{{ route('daos.destroy', $dao) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" style="padding: 8px 16px; font-size: 12px;" onclick="return confirm('Êtes-vous sûr ?')">🗑️ Supprimer</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-5" style="display: flex; justify-content: center;">
            {{ $daos->links('pagination::bootstrap-5') }}
        </div>
    @else
        <div class="alert alert-info" style="border-radius: 12px; text-align: center; padding: 60px 30px; background: linear-gradient(135deg, rgba(79, 172, 254, 0.1) 0%, rgba(0, 242, 254, 0.1) 100%); border: 2px solid rgba(79, 172, 254, 0.3);">
            <div style="font-size: 48px; margin-bottom: 20px;">📭</div>
            <h4 style="margin-bottom: 15px;">Aucun DAO trouvé</h4>
            <p style="color: #666; margin-bottom: 20px;">Commencez par créer votre premier DAO</p>
            <a href="{{ route('daos.create') }}" class="btn btn-primary">➕ Créer le premier</a>
        </div>
    @endif
</div>
@endsection
