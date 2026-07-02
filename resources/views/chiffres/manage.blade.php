@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Chiffres d'affaires pour : {{ $dossier->nom_dossier }}</h3>

    <div class="card mt-3 p-3">
        <form action="{{ route('chiffres.store', $dossier) }}" method="POST" class="row g-2">
            @csrf
            <div class="col-md-3">
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
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-success w-100">Ajouter</button>
            </div>
        </form>

        <hr>
        <table class="table table-sm">
            <thead>
                <tr><th>Année</th><th>Montant</th><th>Monnaie</th></tr>
            </thead>
            <tbody>
                @foreach($chiffres as $c)
                    <tr>
                        <td>{{ $c->annee }}</td>
                        <td>{{ number_format($c->montant, 0, ',', ' ') }}</td>
                        <td>{{ $c->monnaie }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="alert alert-info mt-3">
            La génération de PDF de chiffre d'affaires pour ce dossier est désormais prise en charge par le PDF du dossier.
            Utilisez la fonction de génération de PDF du dossier pour obtenir ce document.
        </div>
    </div>
</div>
@endsection
