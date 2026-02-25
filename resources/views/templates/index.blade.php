@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
        <h3>Modèles</h3>
        <a href="{{ route('templates.create') }}" class="btn btn-primary">Nouveau modèle</a>
    </div>

    <div style="background:white;padding:16px;border-radius:8px">
        <table class="modern-table">
            <thead><tr><th>Nom</th><th>Type</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach($templates as $t)
                <tr>
                    <td>{{ $t->nom }}</td>
                    <td>{{ $t->type }}</td>
                    <td>
                        <a href="{{ route('templates.edit', $t) }}" class="action-btn btn-view">✎</a>
                        <form action="{{ route('templates.destroy', $t) }}" method="POST" style="display:inline">@csrf
                            <button class="action-btn" type="submit">🗑</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
