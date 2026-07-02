@extends('layouts.app')

@section('content')
<div class="container-fluid py-5">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-3">
                <div class="icon-box" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-file-pdf" style="color: white; font-size: 24px;"></i>
                </div>
                <div>
                    <h1 class="mb-0" style="color: var(--text-primary); font-weight: 700;">Formulaires PER</h1>
                    <p class="mb-0 text-muted">Curriculum Vitae du Personnel Proposé</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('formulaire_per.create') }}" class="btn btn-success btn-lg">
                <i class="fas fa-plus me-2"></i>Créer un nouveau formulaire
            </a>
        </div>
    </div>

    <!-- Messages de notification -->
    @if ($message = Session::get('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ $message }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Tableau des formulaires -->
    @if($formulaires->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover" style="background: white; border-radius: 12px; overflow: hidden; box-shadow: var(--shadow-md);">
            <thead style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <tr>
                    <th style="border: none;">Candidat</th>
                    <th style="border: none;">Poste</th>
                    <th style="border: none;">Employeur</th>
                    <th style="border: none;">Email</th>
                    <th style="border: none;">Date de création</th>
                    <th style="border: none; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($formulaires as $formulaire)
                <tr style="border-bottom: 1px solid rgba(0,0,0,0.05);">
                    <td style="vertical-align: middle;">
                        <strong>{{ $formulaire->nom_candidat }}</strong>
                    </td>
                    <td style="vertical-align: middle;">{{ $formulaire->poste }}</td>
                    <td style="vertical-align: middle;">{{ $formulaire->nom_employeur }}</td>
                    <td style="vertical-align: middle;">{{ $formulaire->email }}</td>
                    <td style="vertical-align: middle;">
                        <small class="text-muted">{{ $formulaire->created_at->format('d/m/Y H:i') }}</small>
                    </td>
                    <td style="vertical-align: middle; text-align: center;">
                        <div class="btn-group btn-group-sm" role="group">
                            <a href="{{ route('formulaire_per.show', $formulaire->id) }}" class="btn btn-info" title="Voir">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('formulaire_per.edit', $formulaire->id) }}" class="btn btn-warning" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="{{ route('formulaire_per.pdf', $formulaire->id) }}" class="btn btn-primary" title="Télécharger PDF">
                                <i class="fas fa-download"></i>
                            </a>
                            <button class="btn btn-danger" onclick="confirmDelete('{{ $formulaire->id }}')" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-4">
        {{ $formulaires->links() }}
    </div>
    @else
    <div class="text-center py-5">
        <div style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;">
            <i class="fas fa-file-pdf"></i>
        </div>
        <h3 style="color: var(--text-primary);">Aucun formulaire PER</h3>
        <p class="text-muted mb-4">Vous n'avez pas encore créé de formulaire PER.</p>
        <a href="{{ route('formulaire_per.create') }}" class="btn btn-success btn-lg">
            <i class="fas fa-plus me-2"></i>Créer votre premier formulaire
        </a>
    </div>
    @endif
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmation de suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Êtes-vous sûr de vouloir supprimer ce formulaire PER ? Cette action est irréversible.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id) {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const form = document.getElementById('deleteForm');
    form.action = `{{ url('/formulaire-per') }}/${id}/destroy`;
    modal.show();
}
</script>
@endsection
