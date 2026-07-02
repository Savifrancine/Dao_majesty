@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Chiffres d'affaires</h3>

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
                <thead><tr><th>Année</th><th>Montant</th><th>Monnaie</th></tr></thead>
                <tbody>
                    @forelse($chiffres as $c)
                        <tr>
                            <td>{{ $c->annee }}</td>
                            <td>{{ number_format($c->montant,0,',',' ') }}</td>
                            <td>{{ $c->monnaie }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center">Aucun chiffre enregistré</td></tr>
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
