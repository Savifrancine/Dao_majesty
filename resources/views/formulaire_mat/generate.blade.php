@extends('layouts.app')

@section('content')
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div style="display:flex;align-items:center;gap:15px;margin-bottom:25px;">
                <div style="font-size:36px;">📄</div>
                <div>
                    <h1 style="margin:0;font-size:34px;font-weight:800;">Générer un formulaire MAT (PDF)</h1>
                    <p style="margin:6px 0 0;color:#666;">Remplissez les informations et générez le PDF sans enregistrer.</p>
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
                @include('formulaire_mat.form', [
                    'action' => route('formulaire_mat.generate.post'),
                    'method' => 'POST',
                    'buttonLabel' => 'Générer le PDF',
                    'formulaireMat' => null,
                    'signataires' => $signataires,
                ])
            </div>
        </div>
    </div>
</div>
@endsection
