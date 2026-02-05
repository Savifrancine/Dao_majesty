@extends('layouts.app')

@section('content')
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 30px;">
                <div style="font-size: 36px;">✨</div>
                <h1 style="margin: 0; font-size: 36px; font-weight: 800; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Créer un DAO</h1>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" style="border-radius: 12px; margin-bottom: 30px;">
                    <strong>⚠️ Erreurs !</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card" style="border-radius: 16px;">
                @include('daos.components.form', [
                    'action' => route('daos.store'),
                    'isEdit' => false,
                    'buttonLabel' => '✨ Créer',
                    'cancelUrl' => route('daos.index'),
                    'dao' => null
                ])
            </div>
        </div>
    </div>
</div>
@endsection
