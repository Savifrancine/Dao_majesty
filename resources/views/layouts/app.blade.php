<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Inter:300,400,500,600,700" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #10b981 0%, #34d399 100%);
            --secondary-gradient: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);
            --success-gradient: linear-gradient(135deg, #22c55e 0%, #84cc16 100%);
            --dark-bg: #0f0f23;
            --card-bg: rgba(255, 255, 255, 0.05);
            --text-primary: #1a202c;
            --text-secondary: #718096;
            --border-color: rgba(0, 0, 0, 0.08);
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.04);
            --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 10px 40px rgba(0, 0, 0, 0.12);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
            padding-top: 88px;
        }

        /* Background Animation */
        body::before {
            content: '';
            position: fixed;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(102, 126, 234, 0.1) 0%, transparent 50%);
            animation: rotate 30s linear infinite;
            z-index: 0;
            pointer-events: none;
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        #app {
            position: relative;
            z-index: 1;
        }

        /* Navbar Styles */
        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
            padding: 1rem 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
            z-index: 1000;
            transition: all 0.3s ease;
            animation: slideDown 0.6s ease-out;
            overflow: hidden;
        }

        .navbar::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, rgba(16, 185, 129, 0.12), rgba(34, 197, 94, 0.18), rgba(132, 204, 22, 0.12));
            opacity: 0;
            transform: translateX(-40%);
            transition: opacity 0.4s ease;
            animation: headerGlow 6s ease-in-out infinite;
            pointer-events: none;
        }

        @keyframes headerGlow {
            0%, 100% {
                opacity: 0.35;
                transform: translateX(-40%);
            }
            50% {
                opacity: 0.6;
                transform: translateX(40%);
            }
        }

        @keyframes slideDown {
            from {
                transform: translateY(-100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .navbar.scrolled {
            box-shadow: var(--shadow-md);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--text-primary) !important;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            position: relative;
        }

        .navbar-brand::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 3px;
            background: var(--primary-gradient);
            transition: width 0.3s ease;
        }

        .navbar-brand:hover::after {
            width: 100%;
        }

        .navbar-brand img {
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
            animation: logoFloat 3s ease-in-out infinite;
        }

        @keyframes logoFloat {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-5px); }
        }

        .navbar-brand:hover img {
            transform: scale(1.1) rotate(5deg);
            box-shadow: var(--shadow-md);
        }

        .nav-link {
            color: var(--text-secondary) !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            margin: 0 0.25rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: var(--primary-gradient);
            opacity: 0.1;
            transition: left 0.3s ease;
            z-index: -1;
        }

        .nav-link:hover {
            color: #16a34a !important;
            transform: translateY(-2px);
        }

        .nav-link:hover::before {
            left: 0;
        }

        /* Dropdown Styles */
        .dropdown-toggle {
            background: var(--primary-gradient);
            color: white !important;
            padding: 0.6rem 1.5rem !important;
            border-radius: 25px;
            font-weight: 600;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
            border: none;
            position: relative;
            overflow: hidden;
        }

        .dropdown-toggle::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s ease, height 0.6s ease;
        }

        .dropdown-toggle:hover::before {
            width: 300px;
            height: 300px;
        }

        .dropdown-toggle:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .dropdown-menu {
            border: none;
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
            padding: 0.5rem;
            margin-top: 0.5rem;
            background: white;
            animation: fadeInUp 0.3s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dropdown-item {
            padding: 0.75rem 1.25rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
            color: var(--text-primary);
        }

        .dropdown-item:hover {
            background: var(--primary-gradient);
            color: white;
            transform: translateX(5px);
        }

        .nav-user-name {
            cursor: default;
            color: var(--text-primary) !important;
        }

        .nav-logout-btn {
            border-radius: 999px;
            padding: 0.4rem 1rem;
            font-weight: 600;
        }

        /* Main Content */
        main {
            animation: fadeIn 0.8s ease-out 0.3s both;
            margin-left: 260px; /* leave space for fixed sidebar */
            padding: 2rem;
        }

        /* Fixed Sidebar */
        .app-sidebar {
            position: fixed;
            left: 0;
            top: 88px; /* below navbar */
            bottom: 0;
            width: 260px;
            background: white;
            border-right: 1px solid var(--border-color);
            padding: 1rem;
            z-index: 900;
            overflow-y: auto;
            box-shadow: var(--shadow-sm);
        }

        .app-sidebar .menu { display:flex;flex-direction:column;gap:6px }
        .app-sidebar .menu-item { display:flex;align-items:center;gap:12px;padding:10px;border-radius:10px;color:var(--text-primary);text-decoration:none;font-weight:700 }
        .app-sidebar .menu-item .icon { width:36px;height:36px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;background:var(--mint);color:var(--emerald);font-weight:800 }
        .app-sidebar .menu-item:hover { background: #f8fafc; transform:translateY(-2px) }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Navbar Toggler */
        .navbar-toggler {
            border: none;
            padding: 0.5rem;
            transition: all 0.3s ease;
        }

        .navbar-toggler:focus {
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.25);
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(22, 163, 74, 1)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
            transition: all 0.3s ease;
        }

        .navbar-toggler:hover .navbar-toggler-icon {
            transform: rotate(90deg);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .navbar-collapse {
                background: rgba(255, 255, 255, 0.98);
                backdrop-filter: blur(20px);
                margin-top: 1rem;
                padding: 1rem;
                border-radius: 12px;
                box-shadow: var(--shadow-md);
                animation: fadeInUp 0.3s ease;
            }

            .nav-link {
                margin: 0.25rem 0;
            }

            .dropdown-toggle {
                width: 100%;
                text-align: center;
            }
        }

        /* Utility Classes */
        .container {
            animation: fadeIn 0.8s ease-out;
        }

        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-gradient);
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }

        /* Add subtle hover effect to navbar items */
        .navbar-nav .nav-item {
            animation: fadeIn 0.5s ease-out both;
        }

        .navbar-nav .nav-item:nth-child(1) { animation-delay: 0.1s; }
        .navbar-nav .nav-item:nth-child(2) { animation-delay: 0.2s; }
        .navbar-nav .nav-item:nth-child(3) { animation-delay: 0.3s; }
        .navbar-nav .nav-item:nth-child(4) { animation-delay: 0.4s; }

        /* Smooth page transitions */
        .py-4 {
            transition: all 0.3s ease;
        }
    </style>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="@auth{{ route('home') }}@else{{ url('/') }}@endauth">
                    <img src="{{ asset('storage/logo.jpeg') }}" alt="Logo" width="36" height="36" class="d-inline-block align-text-top me-2">
                    Majesty
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                        @auth
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('daos.index') }}">
                                    <i class="fas fa-users-cog me-1"></i>{{ __('DAOs') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('signataires.index') }}">
                                    <i class="fas fa-file-signature me-1"></i>{{ __('Signataires') }}
                                </a>
                            </li>
                        @endauth
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">
                                        <i class="fas fa-sign-in-alt me-1"></i>{{ __('Login') }}
                                    </a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">
                                        <i class="fas fa-user-plus me-1"></i>{{ __('Register') }}
                                    </a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item d-flex align-items-center">
                                <span class="nav-link nav-user-name">
                                    <i class="fas fa-user-circle me-1"></i>{{ Auth::user()->prenom }} {{ Auth::user()->nom }}
                                </span>
                                <form action="{{ route('logout') }}" method="POST" class="ms-2">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-success btn-sm nav-logout-btn">
                                        <i class="fas fa-sign-out-alt me-1"></i>{{ __('Logout') }}
                                    </button>
                                </form>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        @php
            $hideSidebar = request()->routeIs('login', 'register') || request()->is('/');
        @endphp

        @unless($hideSidebar)
        <aside class="app-sidebar">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px">
                <div style="width:44px;height:44px;border-radius:10px;background:var(--emerald);display:flex;align-items:center;justify-content:center;color:white;font-weight:800">D</div>
                <div>
                    <div style="font-weight:800;color:var(--text-primary)">DAO</div>
                    <div style="font-size:12px;color:var(--text-secondary)">Gestion dossiers</div>
                </div>
            </div>
            <div class="menu">
                <a href="{{ route('dossiers.create') }}" class="menu-item">
                    <span class="icon">✚</span>
                    <span>Nouveau dossier</span>
                </a>
                <a href="{{ route('formulaire_mat.index') }}" class="menu-item">
                    <span class="icon">📄</span>
                    <span>Formulaire MAT</span>
                </a>
                <a href="{{ route('formulaire_per.index') }}" class="menu-item">
                    <span class="icon">📋</span>
                    <span>Formulaire PER</span>
                </a>
                    <a href="{{ route('chiffres.index') }}" class="menu-item">
                        <span class="icon">📈</span>
                        <span>Chiffres d'affaires</span>
                    </a>
                <a href="{{ route('dossiers.index') }}" class="menu-item">
                    <span class="icon">📂</span>
                    <span>Listes</span>
                </a>
                @if(auth()->check() && auth()->user()->isAdminOrDirecteur())
                <a href="{{ route('utilisateurs.index') }}" class="menu-item">
                    <span class="icon">👥</span>
                    <span>Gestion des utilisateurs</span>
                </a>
                @endif
                <a href="{{ route('templates.index') }}" class="menu-item">
                    <span class="icon">📐</span>
                    <span>Modèles</span>
                </a>
                <a href="#" class="menu-item">
                    <span class="icon">⋯</span>
                    <span>Autres</span>
                </a>
            </div>
        </aside>
        @endunless

        <main class="py-4" style="{{ $hideSidebar ? 'margin-left: 0 !important;' : '' }}">
            @yield('content')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Add scroll effect to navbar
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Add ripple effect on clicks
        document.querySelectorAll('.nav-link, .dropdown-item').forEach(item => {
            item.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;

                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                ripple.classList.add('ripple');

                this.appendChild(ripple);

                setTimeout(() => ripple.remove(), 600);
            });
        });
    </script>
</body>
</html>
