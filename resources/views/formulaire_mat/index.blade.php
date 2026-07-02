@extends('layouts.app')

@section('content')
<div class="container mt-5 mb-5">
    <div class="row mb-4">
        <div class="col-lg-8">
            <div style="display:flex;align-items:center;gap:15px;">
                <div style="font-size:36px;">📝</div>
                <div>
                    <h1 style="margin:0;font-size:36px;font-weight:800;">Formulaires MAT</h1>
                    <p style="margin:4px 0 0;color:#666;">Liste des formulaires MAT générés.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 d-flex justify-content-lg-end align-items-center">
            <a href="{{ route('formulaire_mat.generate') }}" class="btn btn-success" style="padding:14px 26px; font-size:15px;">🖨️ Générer un formulaire (PDF)</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius:12px;">
            ✅ {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($formulaires->count())
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Pièce matériel</th>
                        <th>Fabricant / Modèle</th>
                        <th>Localisation</th>
                        <th>Provenance</th>
                        <th>Créé le</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($formulaires as $formulaire)
                        <tr>
                            <td>{{ $formulaire->piece_materiel }}</td>
                            <td>{{ $formulaire->fabricant }}<br><small>{{ $formulaire->modele_puissance }}</small></td>
                            <td>{{ $formulaire->localisation ?? '-' }}</td>
                            <td>{{ $formulaire->provenance ? ucfirst(str_replace('_', ' ', $formulaire->provenance)) : '-' }}</td>
                            <td>{{ $formulaire->created_at->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('formulaire_mat.download', $formulaire) }}" class="btn btn-primary btn-sm">📥 Télécharger</a>
                                <a href="{{ route('formulaire_mat.edit', $formulaire) }}" class="btn btn-warning btn-sm">✏️ Modifier</a>
                                <form action="{{ route('formulaire_mat.destroy', $formulaire) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ce formulaire ?')">🗑️ Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4" style="display:flex;justify-content:center;">
            {{ $formulaires->links('pagination::bootstrap-5') }}
        </div>
    @else
        <div class="alert alert-info" style="border-radius:14px; padding:40px; text-align:center;">
            <h4>Aucun formulaire MAT trouvé</h4>
            <p>Créez votre premier formulaire MAT pour le gérer dans la liste.</p>
            <a href="{{ route('formulaire_mat.generate') }}" class="btn btn-primary">Générer un formulaire (PDF)</a>
        </div>
    @endif
</div>
@endsection
