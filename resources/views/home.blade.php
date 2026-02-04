@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dashboard</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <h5>Bienvenue!</h5>
                    @if (Auth::check())
                        <p>Vous êtes connecté en tant que <strong>{{ Auth::user()->prenom }} {{ Auth::user()->nom }}</strong></p>
                        <p>Email : {{ Auth::user()->email }}</p>
                        
                        <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-danger">Déconnexion</button>
                        </form>
                    @else
                        <p>Vous n'êtes pas connecté.</p>
                        <a href="{{ route('login') }}" class="btn btn-primary">Se connecter</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
