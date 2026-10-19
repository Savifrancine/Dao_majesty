@extends('layouts.app')

@section('content')
<style>
    .page-title {
        font-size: 1.8rem;
        font-weight: 700;
        letter-spacing: 0.01em;
        color: #0f172a;
    }

    .page-subtitle {
        color: #64748b;
        font-size: 0.95rem;
    }

    .table-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    .table thead th {
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .dossier-name {
        font-weight: 700;
        color: #0f172a;
    }

    .dossier-name small {
        display: block;
        color: #64748b;
        font-size: 0.8rem;
        margin-top: 2px;
        font-weight: 400;
    }

    .offre-pill-group {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .offre-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 600;
        text-decoration: none;
        border: 1px solid transparent;
        transition: filter 0.15s ease;
    }

    .offre-pill:hover {
        filter: brightness(0.95);
        text-decoration: none;
    }

    .offre-pill .statut-dot {
        font-size: 0.7rem;
    }

    .offre-pill-missing {
        background: transparent;
        border: 1px dashed #94a3b8;
        color: #475569;
    }

    .offre-pill-missing:hover {
        background: #f1f5f9;
        color: #334155;
    }

    .offre-pill-missing button {
        all: unset;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
</style>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-3">
        <div>
            <h2 class="page-title mb-1">📁 Mes dossiers</h2>
            <p class="page-subtitle mb-0">Une ligne par marché. Cliquez sur une offre (technique, financière, administrative...) pour l'ouvrir et accéder à Modifier / Dupliquer / Supprimer.</p>
        </div>
        <a href="{{ route('dossiers.create') }}" class="btn btn-success">➕ Créer un nouveau dossier</a>
    </div>

    <form method="GET" action="{{ route('dossiers.index') }}" class="mb-4">
        <div class="input-group shadow-sm rounded overflow-hidden">
            <input
                type="search"
                name="search"
                class="form-control border-0"
                placeholder="Rechercher par nom, entreprise, offre, statut ou date (JJ/MM/AAAA)"
                value="{{ request('search') }}"
                aria-label="Rechercher des dossiers"
            />
            <button class="btn btn-outline-secondary" type="submit">Recherche</button>
        </div>
    </form>

    @if(request('search'))
        <div class="mb-3 text-secondary">Résultats pour « {{ request('search') }} » : {{ $dossierGroups->count() }} marché(s)</div>
    @endif

    @if($dossierGroups->count() > 0)
        <div class="table-card table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Marché / Dossier</th>
                        <th>Offres</th>
                        <th>Document archivé</th>
                        <th>Entreprise</th>
                        <th>Créé le</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dossierGroups as $group)
                    <tr>
                        <td>
                            <div class="dossier-name">
                                {{ $group['title'] }}
                                <small>{{ $group['lot'] ?? 'Sans lot' }}</small>
                            </div>
                            @if($group['public_prive'] === 'prive')
                                <span class="badge bg-warning-subtle text-warning-emphasis">🔒 Privé</span>
                            @else
                                <span class="badge bg-info-subtle text-info-emphasis">🌐 Public</span>
                            @endif
                        </td>
                        <td>
                            <div class="offre-pill-group">
                                @foreach($group['dossiers'] as $d)
                                    @php
                                        $typeOffreLabel = trim($d->type_offre ?? '');
                                        $typeOffreLower = mb_strtolower($typeOffreLabel);
                                        if ($typeOffreLabel === '') {
                                            $icon = '📁'; $color = '#1e40af'; $bg = '#dbeafe';
                                        } elseif (str_contains($typeOffreLower, 'financ')) {
                                            $icon = '💰'; $color = '#166534'; $bg = '#dcfce7';
                                        } elseif (str_contains($typeOffreLower, 'techniq')) {
                                            $icon = '🔧'; $color = '#9a3412'; $bg = '#ffedd5';
                                        } elseif (str_contains($typeOffreLower, 'administrat')) {
                                            $icon = '📋'; $color = '#5b21b6'; $bg = '#ede9fe';
                                        } else {
                                            $icon = '📁'; $color = '#1e40af'; $bg = '#dbeafe';
                                        }
                                        $statutDot = match($d->statut) {
                                            'en_cours' => '⏳',
                                            'termine' => '✅',
                                            default => '📄',
                                        };
                                        $statutTitle = match($d->statut) {
                                            'en_cours' => 'En cours',
                                            'termine' => 'Terminé',
                                            default => 'Généré',
                                        };
                                    @endphp
                                    <a href="{{ route('dossiers.show', $d) }}"
                                       class="offre-pill"
                                       style="color:{{ $color }}; background:{{ $bg }}; text-decoration:none;"
                                       title="{{ $typeOffreLabel ?: 'Offre' }} — {{ $statutTitle }}">
                                        {{ $icon }} {{ $typeOffreLabel ?: 'Dossier' }}
                                        <span class="statut-dot">{{ $statutDot }}</span>
                                    </a>
                                @endforeach

                                @foreach(($group['missingOffers'] ?? []) as $categorieCible => $label)
                                    <span class="offre-pill offre-pill-missing">
                                        <form action="{{ route('dossiers.createOtherOffer', $group['representative']) }}" method="POST" onsubmit="return confirm('Créer « {{ $label }} » pour ce marché, à partir de « {{ $group['representative']->type_offre ?: $group['representative']->nom_dossier }} » ? Les pièces déjà téléversées seront reprises aussi : pensez à vérifier et remplacer celles qui ne conviennent pas à cette offre.');">
                                            @csrf
                                            <input type="hidden" name="categorie_cible" value="{{ $categorieCible }}">
                                            <button type="submit">+ {{ $label }}</button>
                                        </form>
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td>
                            @php
                                $dossiersAvecArchive = $group['dossiers']->filter(fn ($d) => $d->versionsFinales->isNotEmpty());
                            @endphp
                            @if($dossiersAvecArchive->isEmpty())
                                <span style="color:#94a3b8;">—</span>
                            @else
                                <div class="offre-pill-group">
                                    @foreach($dossiersAvecArchive as $d)
                                        @php
                                            $derniereVersionFinale = $d->versionsFinales->first();
                                            $typeOffreLabelArchive = trim($d->type_offre ?? '') ?: 'Dossier';
                                        @endphp
                                        <a href="{{ asset('storage/' . $derniereVersionFinale->chemin_fichier) }}"
                                           target="_blank" rel="noopener noreferrer"
                                           class="offre-pill"
                                           style="color:#334155; background:#f1f5f9; text-decoration:none;"
                                           title="{{ $typeOffreLabelArchive }} — {{ $derniereVersionFinale->nom_original ?: basename($derniereVersionFinale->chemin_fichier) }}{{ $d->versionsFinales->count() > 1 ? ' (+' . ($d->versionsFinales->count() - 1) . ' autre(s) version(s))' : '' }}">
                                            📎 {{ $typeOffreLabelArchive }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td>{{ $group['entreprise']->nom ?? '—' }}</td>
                        <td>{{ $group['latest_created_at'] ? \Illuminate\Support\Carbon::parse($group['latest_created_at'])->format('d/m/Y') : '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="alert alert-info shadow-sm">
            <p class="mb-0">📭 Vous n'avez pas encore créé de dossier.</p>
            <p class="mb-0"><a href="{{ route('dossiers.create') }}" class="alert-link">Créer un nouveau dossier</a></p>
        </div>
    @endif
</div>
@endsection
