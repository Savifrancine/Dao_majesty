@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Signataires</h1>
        <a href="{{ route('signataires.create') }}" class="btn btn-primary">Ajouter un signataire</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($signataires->isEmpty())
        <p>Aucun signataire n'a encore été ajouté.</p>
    @else
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Fonction</th>
                </tr>
            </thead>
            <tbody>
                @foreach($signataires as $s)
                    <tr>
                        <td>{{ $s->nom }}</td>
                        <td>{{ $s->prenom ?? '-' }}</td>
                        <td>{{ $s->fonction ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection