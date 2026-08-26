@extends('layouts.app')

@section('content')
<div class="container mt-5 mb-5">
    <div class="row mb-4">
        <div class="col-lg-8">
            <div style="display:flex;align-items:center;gap:15px;">
                <div style="font-size:36px;">🧾</div>
                <div>
                    <h1 style="margin:0;font-size:36px;font-weight:800;">Formulaires EXP-4.2 b) (suite)</h1>
                    <p style="margin:4px 0 0;color:#666;">Liste des formulaires EXP-4.2 b) (suite) enregistrés.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 d-flex justify-content-lg-end align-items-center">
            <a href="{{ route('formulaire_exp_4_2_b_suite.generate') }}" class="btn btn-success" style="padding:14px 26px; font-size:15px;">🖨️ Générer un formulaire (PDF)</a>
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
                        <th>Nom du candidat</th>
                        <th>N° ADRP</th>
                        <th>Autorité</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($formulaires as $formulaire)
                        <tr>
                            <td>{{ $formulaire->entreprise->nom ?? $formulaire->nom_candidat ?? '-' }}</td>
                            <td>{{ $formulaire->buildNumeroAdrp($formulaire->dossier ?? null) ?: ($formulaire->numero_adrp ?? '-') }}</td>
                            <td>{{ $formulaire->autorite_nom ?? '-' }}</td>
                            <td>
                                <a href="{{ route('formulaire_exp_4_2_b_suite.download', $formulaire) }}" class="btn btn-primary btn-sm">📥 Télécharger</a>
                                <a href="{{ route('formulaire_exp_4_2_b_suite.edit', $formulaire) }}" class="btn btn-warning btn-sm">✏️ Modifier</a>
                                <form action="{{ route('formulaire_exp_4_2_b_suite.destroy', $formulaire) }}" method="POST" style="display:inline;">
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
            <h4>Aucun Formulaire EXP-4.2 b) (suite) trouvé</h4>
            <p>Créez votre premier Formulaire EXP-4.2 b) (suite) ou générez un PDF directement.</p>
            <a href="{{ route('formulaire_exp_4_2_b_suite.generate') }}" class="btn btn-primary">Générer un formulaire (PDF)</a>
        </div>
    @endif
</div>
@endsection

