@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1>Ajouter un article au catalogue</h1>
    <form action="{{ route('catalogue.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        @include('catalogue.form')

        <button type="submit" class="btn btn-success">Enregistrer</button>
        <a href="{{ route('catalogue.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection
