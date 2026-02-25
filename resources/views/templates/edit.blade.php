@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3>Modifier le modèle</h3>
    <form action="{{ route('templates.update', $template) }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>Nom</label>
            <input type="text" name="nom" class="form-control" value="{{ $template->nom }}" required>
        </div>
        <div class="mb-3">
            <label>Type</label>
            <input type="text" name="type" class="form-control" value="{{ $template->type }}" required>
        </div>
        <div class="mb-3">
            <label>Contenu (texte)</label>
            <textarea name="content" rows="8" class="form-control">{{ $template->content }}</textarea>
        </div>
        <button class="btn btn-primary">Mettre à jour</button>
    </form>
</div>
@endsection
