<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DAO - Gestion de Dossiers</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; background: #f9f9f9; display: flex; flex-direction: column; min-height: 100vh; }
        a { text-decoration: none; color: inherit; }
        nav { position: fixed; top: 0; left: 0; right: 0; z-index: 1000; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 0 40px; display: flex; justify-content: space-between; align-items: center; height: 70px; }
        .logo { font-size: 24px; font-weight: bold; background: linear-gradient(135deg, #2563eb, #9333ea); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .nav-links { display: flex; gap: 15px; align-items: center; }
        .nav-links a { padding: 10px 20px; border-radius: 6px; font-weight: 500; transition: all 0.3s; }
        .btn-login { color: #666; }
        .btn-register { background: linear-gradient(135deg, #2563eb, #9333ea); color: white; }
        .btn-register:hover { transform: scale(1.05); box-shadow: 0 4px 12px rgba(37,99,235,0.4); }
        .hero { margin-top: 70px; padding: 60px 40px; text-align: center; min-height: 400px; display: flex; align-items: center; justify-content: center; flex: 1; }
        .hero h1 { font-size: 42px; margin-bottom: 20px; }
        .hero p { font-size: 18px; color: #666; margin-bottom: 30px; }
        .cta-buttons { display: flex; gap: 15px; justify-content: center; flex-wrap: wrap; }
        .cta-buttons a { padding: 12px 30px; border-radius: 6px; font-weight: 600; transition: all 0.3s; display: inline-block; }
        .btn-primary { background: linear-gradient(135deg, #2563eb, #9333ea); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 16px rgba(37,99,235,0.3); }
        .btn-secondary { border: 2px solid #ddd; }
        .btn-secondary:hover { border-color: #2563eb; color: #2563eb; }
        footer { background: #f9f9f9; color: #666; text-align: center; padding: 30px; font-size: 14px; border-top: 1px solid #eee; }
        @media (max-width: 768px) { nav { padding: 0 20px; } .hero { padding: 30px 20px; } .hero h1 { font-size: 28px; } .cta-buttons { flex-direction: column; } .cta-buttons a { width: 100%; } }
    </style>
</head>
<body>

    <nav>
        <div class="logo">DAO</div>
        <div class="nav-links">
            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}" style="color: #2563eb; font-weight: 600;">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn-login">Se connecter</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn-register">S'inscrire</a>
                    @endif
                @endauth
            @endif
        </div>
    </nav>

    <section class="hero">
        <div>
            <h1>Bienvenue sur <span style="background: linear-gradient(135deg, #2563eb, #9333ea); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">DAO</span></h1>
            <p>Gérez vos dossiers et documents de manière intelligente et sécurisée</p>
            <div class="cta-buttons">
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="btn-primary">Créer un compte</a>
                @endif
                @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="btn-secondary">Se connecter</a>
                @endif
            </div>
        </div>
    </section>

    <footer>
        <p>&copy; 2026 DAO - Gestion de Dossiers</p>
    </footer>
</body>
</html>
