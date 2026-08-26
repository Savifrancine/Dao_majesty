@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1>{{ $titre }}</h1>
    <p class="text-muted">
        @if($type === 'interne')
            Choisissez le dossier pour lequel générer l'étiquette, puis "Original" ou "Copie".
        @else
            Choisissez le dossier pour lequel générer l'étiquette (unique, sans Original/Copie).
        @endif
    </p>

    @if($dossiers->isEmpty())
        <p>Aucun dossier n'a encore été créé.</p>
    @else
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Dossier</th>
                    <th>Référence</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dossiers as $d)
                    <tr>
                        <td>{{ $d->nom_dossier ?? ('Dossier #' . $d->id) }}</td>
                        <td>{{ $d->reference_dossier ?? '-' }}</td>
                        <td>
                            <a href="{{ route('etiquettes.' . $type . '.form', $d) }}" class="btn btn-outline-primary btn-sm">Compléter / Vérifier</a>
                            @if($type === 'interne')
                                <a href="{{ route('etiquettes.interne.generate', ['dossier' => $d->id, 'variante' => 'original']) }}" class="btn btn-secondary btn-sm">Télécharger l'original</a>
                                <a href="{{ route('etiquettes.interne.generate', ['dossier' => $d->id, 'variante' => 'copie']) }}" class="btn btn-outline-secondary btn-sm">Télécharger la copie</a>
                            @else
                                <a href="{{ route('etiquettes.externe.generate', $d) }}" class="btn btn-secondary btn-sm">Télécharger l'étiquette</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
