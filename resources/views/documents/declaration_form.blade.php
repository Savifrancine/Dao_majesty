@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2>Déclaration de garantie d'offre — Formulaire</h2>

    <form action="{{ route('documents.declaration.pdf') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label class="form-label">Société / Fournisseur</label>
            <input type="text" name="societe" class="form-control @error('societe') is-invalid @enderror" value="{{ old('societe') }}" required>
            @error('societe')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Date</label>
            <input type="date" name="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date', date('Y-m-d')) }}" required>
            @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Nom du déclarant</label>
            <input type="text" name="declarant" class="form-control @error('declarant') is-invalid @enderror" value="{{ old('declarant') }}" required>
            @error('declarant')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Fonction</label>
            <input type="text" name="fonction" class="form-control" value="{{ old('fonction') }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Référence (optionnel)</label>
            <input type="text" name="reference" class="form-control" value="{{ old('reference') }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Signature (image PNG/JPG, optionnel)</label>
            <input type="file" name="signature" accept="image/*" class="form-control">
        </div>

        <button class="btn btn-primary">Générer le PDF</button>
    </form>
</div>

@endsection
