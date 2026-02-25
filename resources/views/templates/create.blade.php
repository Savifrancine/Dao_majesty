@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3>Créer un modèle</h3>
    <form action="{{ route('templates.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>Nom</label>
            <input type="text" name="nom" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Type</label>
            <input type="text" name="type" class="form-control" placeholder="declaration|lettre|page_garde" required>
        </div>
        <div class="mb-3">
            <label>Contenu (texte)</label>
            <textarea name="content" rows="8" class="form-control"></textarea>
        </div>
        <button class="btn btn-primary">Enregistrer</button>
    </form>
</div>
@endsection
