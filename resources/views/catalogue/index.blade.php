@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Catalogue</h1>
        <a href="{{ route('catalogue.create') }}" class="btn btn-primary">Ajouter un article</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="GET" action="{{ route('catalogue.index') }}" class="mb-3" style="max-width:420px;" id="catalogueSearchForm">
        <input type="search" name="search" id="catalogueSearchInput" class="form-control" placeholder="Rechercher par désignation, catégorie, marque..." value="{{ $search }}" autocomplete="off">
    </form>
    <script>
        (function () {
            var input = document.getElementById('catalogueSearchInput');
            var form = document.getElementById('catalogueSearchForm');
            var timer = null;

            input.addEventListener('input', function () {
                clearTimeout(timer);
                timer = setTimeout(function () { form.submit(); }, 400);
            });

            // Remet le focus (et le curseur en fin de texte) apres le
            // rechargement de page declenche par la recherche automatique,
            // pour pouvoir continuer a taper sans recliquer dans le champ.
            if (input.value) {
                input.focus();
                var end = input.value.length;
                input.setSelectionRange(end, end);
            }
        })();
    </script>

    @if($catalogueArticles->isEmpty())
        <p>Aucun article n'a encore été ajouté au catalogue.</p>
    @else
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Catégorie</th>
                    <th>Désignation / nom</th>
                    <th>Marque / Fabricant</th>
                    <th>Modèle / type</th>
                    <th>Commentaire</th>
                    <th>Prospectus</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($catalogueArticles as $article)
                    <tr>
                        <td>{{ $article->categorie ?? '-' }}</td>
                        <td>{{ $article->designation }}</td>
                        <td>{{ $article->marque_fabricant ?? '-' }}</td>
                        <td>{{ $article->marque_type ?? '-' }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($article->commentaire ?? '', 60) ?: '-' }}</td>
                        <td>
                            @if($article->chemin_prospectus)
                                <a href="{{ asset('storage/' . $article->chemin_prospectus) }}" target="_blank" rel="noopener noreferrer">{{ $article->nom_original_prospectus ?: 'Voir' }}</a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('catalogue.edit', $article) }}" class="btn btn-secondary btn-sm">Modifier</a>

                            <form action="{{ route('catalogue.destroy', $article) }}" method="POST" style="display:inline-block; margin-left:6px;" onsubmit="return confirm('Confirmer la suppression ?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $catalogueArticles->links() }}
    @endif
</div>
@endsection
