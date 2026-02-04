<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ url('/') }}">{{ config('app.name', 'Laravel') }}</a>
            <div>
                @if (Route::has('login'))
                    @auth
                        <a class="btn btn-outline-primary" href="{{ url('/home') }}">Home</a>
                    @else
                        <a class="btn btn-outline-primary" href="{{ route('login') }}">Log in</a>
                        @if (Route::has('register'))
                            <a class="btn btn-primary ms-2" href="{{ route('register') }}">Register</a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>
    </nav>

    <main class="py-4">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
