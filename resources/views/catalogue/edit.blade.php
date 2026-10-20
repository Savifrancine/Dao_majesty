@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1>Modifier l'article du catalogue</h1>
    <form action="{{ route('catalogue.update', $catalogueArticle) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @include('catalogue.form')

        <button type="submit" class="btn btn-success">Enregistrer</button>
        <a href="{{ route('catalogue.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection
