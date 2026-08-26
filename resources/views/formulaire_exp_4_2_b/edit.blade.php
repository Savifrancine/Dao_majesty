@extends('layouts.app')

@section('content')
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div style="display:flex;align-items:center;gap:15px;margin-bottom:25px;">
                <div style="font-size:36px;">✏️</div>
                <div>
                    <h1 style="margin:0;font-size:34px;font-weight:800;">Modifier un Formulaire EXP-4.2 b)</h1>
                    <p style="margin:6px 0 0;color:#666;">Mettez à jour les informations et enregistrez le formulaire.</p>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" style="border-radius:12px;">
                    <strong>⚠️ Erreurs :</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card" style="border-radius:16px; padding:24px;">
                @include('formulaire_exp_4_2_b.form', [
                    'action' => route('formulaire_exp_4_2_b.update', $formulaireExp42B),
                    'method' => 'PUT',
                    'buttonLabel' => 'Mettre à jour le Formulaire EXP-4.2 b)',
                    'formulaireExp42B' => $formulaireExp42B,
                    'dossiers' => $dossiers ?? [],
                ])
            </div>
        </div>
    </div>
</div>
@endsection

