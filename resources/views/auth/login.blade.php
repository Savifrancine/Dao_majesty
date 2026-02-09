@extends('layouts.app')

@section('content')
<style>
    :root {
        --primary-green: #10b981;
        --dark-green: #059669;
        --light-green: #34d399;
        --emerald: #047857;
        --mint: #d1fae5;
        --green-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
        --green-glow: 0 0 30px rgba(16, 185, 129, 0.25);
    }

    html,
    body {
        height: 100%;
        margin: 0;
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 50%, #a7f3d0 100%);
        position: relative;
        overflow-x: hidden;
    }

    /* Animated Background Particles */
    body::before {
        content: '';
        position: fixed;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.08) 0%, transparent 70%);
        animation: rotateBackground 30s linear infinite;
        z-index: 0;
    }

    @keyframes rotateBackground {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Floating Particles */
    .floating-particles {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 0;
    }

    .particle {
        position: absolute;
        width: 8px;
        height: 8px;
        background: var(--primary-green);
        border-radius: 50%;
        opacity: 0.15;
        animation: floatParticle 20s infinite ease-in-out;
    }

    .particle:nth-child(1) { left: 10%; animation-delay: 0s; animation-duration: 15s; }
    .particle:nth-child(2) { left: 20%; animation-delay: 2s; animation-duration: 18s; }
    .particle:nth-child(3) { left: 30%; animation-delay: 4s; animation-duration: 12s; }
    .particle:nth-child(4) { left: 40%; animation-delay: 1s; animation-duration: 16s; }
    .particle:nth-child(5) { left: 60%; animation-delay: 3s; animation-duration: 14s; }
    .particle:nth-child(6) { left: 70%; animation-delay: 5s; animation-duration: 19s; }
    .particle:nth-child(7) { left: 80%; animation-delay: 2.5s; animation-duration: 17s; }
    .particle:nth-child(8) { left: 90%; animation-delay: 4.5s; animation-duration: 13s; }

    @keyframes floatParticle {
        0%, 100% {
            transform: translateY(100vh) scale(0);
            opacity: 0;
        }
        10% {
            opacity: 0.2;
        }
        90% {
            opacity: 0.2;
        }
        50% {
            transform: translateY(-20vh) scale(1.5);
            opacity: 0.3;
        }
    }

    .auth-wrapper {
        min-height: calc(100vh - 70px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px 14px;
        position: relative;
        z-index: 1;
    }

    /* Orbiting Decorations */
    .auth-orbit {
        position: absolute;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.15) 0%, transparent 70%);
        animation: orbit 25s linear infinite;
        pointer-events: none;
        z-index: 0;
    }

    .auth-orbit.orbit-1 {
        width: 500px;
        height: 500px;
        top: -150px;
        right: -150px;
        animation-duration: 20s;
    }

    .auth-orbit.orbit-2 {
        width: 350px;
        height: 350px;
        bottom: -100px;
        left: -100px;
        animation-direction: reverse;
        animation-duration: 28s;
        background: radial-gradient(circle, rgba(5, 150, 105, 0.12) 0%, transparent 70%);
    }

    .auth-orbit.orbit-3 {
        width: 250px;
        height: 250px;
        top: 50%;
        left: -80px;
        animation-duration: 35s;
        background: radial-gradient(circle, rgba(52, 211, 153, 0.1) 0%, transparent 70%);
    }

    @keyframes orbit {
        0% { transform: rotate(0deg) scale(1); opacity: 1; }
        50% { transform: rotate(180deg) scale(1.1); opacity: 0.8; }
        100% { transform: rotate(360deg) scale(1); opacity: 1; }
    }

    /* Main Card */
    .auth-card {
        width: 100%;
        max-width: 400px;
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(20px);
        border-radius: 22px;
        box-shadow:
            0 25px 60px rgba(16, 185, 129, 0.2),
            0 10px 30px rgba(0, 0, 0, 0.1),
            inset 0 1px 0 rgba(255, 255, 255, 0.8);
        border: 1px solid rgba(16, 185, 129, 0.2);
        overflow: hidden;
        position: relative;
        animation: cardIn 1s cubic-bezier(0.34, 1.56, 0.64, 1) both;
        z-index: 1;
    }

    @keyframes cardIn {
        from {
            opacity: 0;
            transform: translateY(50px) scale(0.9) rotateX(10deg);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1) rotateX(0deg);
        }
    }

    .auth-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
        animation: shine 3s infinite;
    }

    @keyframes shine {
        0% { left: -100%; }
        50%, 100% { left: 100%; }
    }

    /* Header */
    .auth-header {
        padding: 22px 22px 14px;
        text-align: center;
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.08), rgba(5, 150, 105, 0.03));
        border-bottom: 1px solid rgba(16, 185, 129, 0.15);
        position: relative;
        overflow: hidden;
    }

    .auth-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: var(--green-gradient);
        animation: slideWidth 2s ease-out;
    }

    @keyframes slideWidth {
        from { width: 0; }
        to { width: 100%; }
    }

    .auth-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 4px 12px;
        border-radius: 50px;
        background: rgba(16, 185, 129, 0.12);
        color: var(--emerald);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        animation: floatBadge 3s ease-in-out infinite;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.15);
    }

    @keyframes floatBadge {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-6px); }
    }

    .auth-badge::before {
        content: '🔒';
        font-size: 12px;
        animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.2); }
    }

    .auth-title {
        margin: 12px 0 6px;
        font-size: 1.55rem;
        font-weight: 800;
        color: #0f172a;
        background: linear-gradient(135deg, #047857 0%, #10b981 50%, #34d399 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        animation: slideInLeft 0.8s cubic-bezier(0.34, 1.56, 0.64, 1) both;
        letter-spacing: -0.5px;
    }

    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .auth-subtitle {
        color: #047857;
        font-weight: 500;
        font-size: 0.85rem;
        margin: 0;
        animation: fadeInUp 1s ease-out 0.3s both;
        line-height: 1.5;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(15px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Body */
    .auth-body {
        padding: 18px 22px 22px;
    }

    .auth-field {
        margin-bottom: 14px;
        animation: fadeInUp 0.6s ease-out both;
    }

    .auth-field:nth-child(1) { animation-delay: 0.4s; }
    .auth-field:nth-child(2) { animation-delay: 0.5s; }
    .auth-field:nth-child(3) { animation-delay: 0.6s; }

    .auth-label {
        font-weight: 600;
        color: #0f172a;
        margin-bottom: 6px;
        display: block;
        font-size: 0.85rem;
        transition: color 0.3s ease;
    }

    .auth-input {
        width: 100%;
        border-radius: 14px;
        border: 2px solid #e2e8f0;
        padding: 10px 12px;
        font-size: 0.9rem;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        background: #f8fafc;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.02);
    }

    .auth-input:focus {
        outline: none;
        border-color: var(--primary-green);
        box-shadow:
            0 0 0 4px rgba(16, 185, 129, 0.12),
            inset 0 2px 4px rgba(0, 0, 0, 0.02);
        background: white;
        transform: translateY(-2px);
    }

    .auth-input:hover {
        border-color: #cbd5e1;
    }

    .auth-remember {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
        font-size: 0.82rem;
        color: #475569;
        animation: fadeInUp 0.6s ease-out 0.7s both;
        flex-wrap: wrap;
        gap: 10px;
    }

    .form-check {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .form-check-input {
        cursor: pointer;
        width: 18px;
        height: 18px;
        border: 2px solid #cbd5e1;
        transition: all 0.3s ease;
    }

    .form-check-input:checked {
        background-color: var(--primary-green);
        border-color: var(--primary-green);
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
    }

    .form-check-label {
        cursor: pointer;
        user-select: none;
    }

    /* Button */
    .auth-btn {
        position: relative;
        width: 100%;
        padding: 10px 16px;
        border-radius: 50px;
        border: none;
        font-weight: 700;
        font-size: 0.85rem;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        background: var(--green-gradient);
        color: white;
        box-shadow:
            var(--green-glow),
            0 4px 15px rgba(16, 185, 129, 0.3);
        overflow: hidden;
        cursor: pointer;
        transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        animation: fadeInUp 0.6s ease-out 0.8s both;
    }

    .auth-btn::before {
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

    .auth-btn:hover::before {
        width: 400px;
        height: 400px;
    }

    .auth-btn::after {
        content: '';
        position: absolute;
        top: -50%;
        left: -30%;
        width: 50%;
        height: 200%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        transform: rotate(20deg);
        animation: shimmer 3s ease-in-out infinite;
    }

    @keyframes shimmer {
        0% { transform: translateX(-100%) rotate(20deg); }
        100% { transform: translateX(300%) rotate(20deg); }
    }

    .auth-btn:hover {
        transform: translateY(-3px) scale(1.02);
        box-shadow:
            0 15px 40px rgba(16, 185, 129, 0.4),
            0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .auth-btn:active {
        transform: translateY(-1px) scale(1);
    }

    /* Links */
    .auth-link {
        color: var(--primary-green);
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
        position: relative;
    }

    .auth-link::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 0;
        height: 2px;
        background: var(--primary-green);
        transition: width 0.3s ease;
    }

    .auth-link:hover {
        color: var(--emerald);
    }

    .auth-link:hover::after {
        width: 100%;
    }

    /* Divider */
    .auth-divider {
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(16, 185, 129, 0.3), transparent);
        margin: 18px 0;
        position: relative;
        animation: fadeIn 0.6s ease-out 0.9s both;
    }

    .auth-divider::before {
        content: 'OU';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        padding: 0 12px;
        font-size: 11px;
        font-weight: 700;
        color: var(--primary-green);
        letter-spacing: 1px;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    /* Footer */
    .auth-footer {
        text-align: center;
        color: #475569;
        font-size: 0.82rem;
        animation: fadeInUp 0.6s ease-out 1s both;
    }

    .auth-footer > div {
        margin-bottom: 8px;
        font-weight: 500;
    }

    .auth-secondary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        padding: 8px 14px;
        border-radius: 50px;
        border: 2px solid rgba(16, 185, 129, 0.3);
        background: white;
        color: var(--primary-green);
        font-weight: 700;
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        text-decoration: none;
        transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        position: relative;
        overflow: hidden;
    }

    .auth-secondary::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 0;
        height: 100%;
        background: rgba(16, 185, 129, 0.08);
        transition: width 0.4s ease;
    }

    .auth-secondary:hover::before {
        width: 100%;
    }

    .auth-secondary:hover {
        border-color: var(--primary-green);
        color: var(--emerald);
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.2);
        text-decoration: none;
    }

    /* Alert */
    .auth-alert {
        border-radius: 14px;
        border: 1px solid rgba(239, 68, 68, 0.3);
        background: linear-gradient(135deg, rgba(254, 226, 226, 0.8), rgba(254, 202, 202, 0.6));
        color: #991b1b;
        padding: 10px 12px;
        margin-bottom: 14px;
        font-size: 0.8rem;
        animation: shakeAlert 0.5s ease-out;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.1);
    }

    @keyframes shakeAlert {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
        20%, 40%, 60%, 80% { transform: translateX(5px); }
    }

    .auth-alert strong {
        display: block;
        margin-bottom: 6px;
        font-weight: 700;
    }

    .auth-alert ul {
        padding-left: 20px;
        margin: 8px 0 0;
    }

    .auth-alert li {
        margin-bottom: 4px;
    }

    /* Invalid Feedback */
    .invalid-feedback {
        display: block;
        margin-top: 6px;
        font-size: 0.78rem;
        color: #dc2626;
        animation: fadeInUp 0.3s ease-out;
    }

    .is-invalid {
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1) !important;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .auth-wrapper {
            padding: 20px 12px;
        }

        .auth-card {
            border-radius: 20px;
            max-width: 360px;
        }

        .auth-header {
            padding: 18px 18px 12px;
        }

        .auth-body {
            padding: 14px 18px 18px;
        }

        .auth-title {
            font-size: 1.35rem;
        }

        .auth-subtitle {
            font-size: 0.8rem;
        }

        .auth-btn {
            padding: 9px 14px;
        }

        .auth-remember {
            font-size: 0.8rem;
        }
    }

    @media (max-width: 480px) {
        .auth-card {
            max-width: 100%;
        }

        .auth-title {
            font-size: 1.4rem;
        }
    }
</style>


<div class="floating-particles">
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
</div>

<div class="auth-wrapper">
    <!-- Decorative Orbits -->
    <div class="auth-orbit orbit-1"></div>
    <div class="auth-orbit orbit-2"></div>
    <div class="auth-orbit orbit-3"></div>

    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-badge">Connexion sécurisée</div>
            <h1 class="auth-title">Se connecter</h1>
            <p class="auth-subtitle">Accédez à votre espace documentaire en toute sécurité.</p>
        </div>

        <div class="auth-body">
            @if ($errors->any())
                <div class="auth-alert">
                    <strong>❌ Erreur de connexion</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="auth-field">
                    <label for="email" class="auth-label">📧 Adresse e-mail</label>
                    <input
                        id="email"
                        type="email"
                        class="auth-input @error('email') is-invalid @enderror"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autocomplete="email"
                        autofocus
                        placeholder="exemple@email.com"
                    >

                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="auth-field">
                    <label for="password" class="auth-label">🔑 Mot de passe</label>
                    <input
                        id="password"
                        type="password"
                        class="auth-input @error('password') is-invalid @enderror"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="Entrez votre mot de passe"
                    >

                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="auth-remember">
                    <div class="form-check">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="remember"
                            id="remember"
                            {{ old('remember') ? 'checked' : '' }}
                        >
                        <label class="form-check-label" for="remember">
                            Se souvenir de moi
                        </label>
                    </div>

                    @if (Route::has('password.request'))
                        <a class="auth-link" href="{{ route('password.request') }}">
                            Mot de passe oublié ?
                        </a>
                    @endif
                </div>

                <button type="submit" class="auth-btn">
                    ✨ Se connecter
                </button>
            </form>

            <div class="auth-divider"></div>

            <div class="auth-footer">
                <div>Pas encore inscrit ?</div>
                <a href="{{ route('register') }}" class="auth-secondary">
                    🚀 Créer un compte
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    // Add ripple effect on button click
    document.addEventListener('DOMContentLoaded', function() {
        const buttons = document.querySelectorAll('.auth-btn, .auth-secondary');

        buttons.forEach(button => {
            button.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;

                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                ripple.style.position = 'absolute';
                ripple.style.borderRadius = '50%';
                ripple.style.background = 'rgba(255, 255, 255, 0.5)';
                ripple.style.transform = 'scale(0)';
                ripple.style.animation = 'rippleEffect 0.6s ease-out';
                ripple.style.pointerEvents = 'none';

                this.style.position = 'relative';
                this.style.overflow = 'hidden';
                this.appendChild(ripple);

                setTimeout(() => ripple.remove(), 600);
            });
        });

        // Add focus animation to inputs
        const inputs = document.querySelectorAll('.auth-input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.previousElementSibling.style.color = 'var(--primary-green)';
            });

            input.addEventListener('blur', function() {
                this.previousElementSibling.style.color = '#0f172a';
            });
        });
    });

    // Add ripple animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes rippleEffect {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
</script>
@endsection
