<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>S'inscrire - DAO</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; background: linear-gradient(135deg, #f0f9ff, #faf5ff); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        a { text-decoration: none; color: inherit; }
        .container { max-width: 500px; width: 100%; }
        .header { text-align: center; margin-bottom: 40px; }
        .logo { font-size: 28px; font-weight: bold; background: linear-gradient(135deg, #2563eb, #9333ea); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 20px; }
        .header h1 { font-size: 32px; color: #1a1a1a; margin-bottom: 10px; }
        .header p { color: #666; font-size: 16px; }
        .card { background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); padding: 40px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; color: #1a1a1a; }
        input { width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; transition: all 0.3s; }
        input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
        .error { color: #dc2626; font-size: 13px; margin-top: 5px; }
        .btn { width: 100%; padding: 14px; background: linear-gradient(135deg, #2563eb, #9333ea); color: white; border: none; border-radius: 8px; font-weight: 600; font-size: 16px; cursor: pointer; transition: all 0.3s; margin-top: 10px; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 8px 16px rgba(37, 99, 235, 0.3); }
        .login-link { text-align: center; margin-top: 25px; color: #666; }
        .login-link a { color: #2563eb; font-weight: 600; }
        .login-link a:hover { text-decoration: underline; }
        @media (max-width: 500px) { .card { padding: 30px 20px; } .header h1 { font-size: 24px; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">DAO</div>
            <h1>Créer un compte</h1>
            <p>Rejoignez-nous et commencez à gérer vos dossiers</p>
        </div>

        <div class="card">
            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input id="nom" type="text" name="nom" value="{{ old('nom') }}" required autocomplete="off" placeholder="Votre nom">
                    @error('nom')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="prenom">Prénom</label>
                    <input id="prenom" type="text" name="prenom" value="{{ old('prenom') }}" required autocomplete="off" placeholder="Votre prénom">
                    @error('prenom')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">Adresse email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="vous@exemple.com">
                    @error('email')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input id="password" type="password" name="password" required autocomplete="new-password" placeholder="••••••••">
                    @error('password')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirmer le mot de passe</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••">
                </div>

                <button type="submit" class="btn">S'inscrire</button>

                <div class="login-link">
                    Vous avez déjà un compte ? <a href="{{ route('login') }}">Se connecter</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
