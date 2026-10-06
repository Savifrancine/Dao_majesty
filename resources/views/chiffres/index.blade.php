@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Chiffres d'affaires</h3>

    @if(session('success'))
        <div class="alert alert-success mt-3">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger mt-3">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="card p-3 mt-3">
        <form id="addChiffreForm" method="POST" action="{{ route('chiffres.store.global') }}" class="row g-2">
            @csrf
            <div class="col-md-2">
                <label class="form-label">Année</label>
                <input type="number" name="annee" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Montant</label>
                <input type="text" name="montant" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Monnaie</label>
                <input type="text" name="monnaie" class="form-control" value="F CFA">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-success w-100">Ajouter</button>
            </div>
        </form>

        <hr>

        <div id="listArea">
            <h5>Liste des chiffres d'affaires</h5>
            <table class="table table-sm">
                <thead><tr><th>Année</th><th>Montant</th><th>Monnaie</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse($chiffres as $c)
                        <tr>
                            <td colspan="4">
                                <div class="d-flex gap-2 align-items-end">
                                    <form method="POST" action="{{ route('chiffres.update', $c->id) }}" class="d-flex gap-2 align-items-end flex-wrap flex-grow-1 mb-0">
                                        @csrf
                                        @method('PUT')
                                        <div style="width:90px;">
                                            <input type="number" name="annee" class="form-control form-control-sm" value="{{ $c->annee }}" required>
                                        </div>
                                        <div style="flex:1; min-width:140px;">
                                            <input type="text" name="montant" class="form-control form-control-sm" value="{{ $c->montant + 0 }}" required>
                                        </div>
                                        <div style="width:120px;">
                                            <input type="text" name="monnaie" class="form-control form-control-sm" value="{{ $c->monnaie }}">
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm">Modifier</button>
                                    </form>
                                    <form method="POST" action="{{ route('chiffres.destroy', $c->id) }}" class="mb-0" onsubmit="return confirm('Supprimer ce chiffre ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center">Aucun chiffre enregistré</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="alert alert-info mt-3">
                La génération de PDF de chiffre d'affaires est désormais intégrée au PDF final du dossier.
                Veuillez générer le PDF du dossier pour inclure cette pièce.
            </div>
        </div>
    </div>
</div>

@endsection
