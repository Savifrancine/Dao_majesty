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
        --green-glow: 0 0 30px rgba(16, 185, 129, 0.3);
    }

    /* Background with animated gradient */
    body {
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 50%, #a7f3d0 100%);
        position: relative;
        overflow-x: hidden;
    }

    body::before {
        content: '';
        position: fixed;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.1) 0%, transparent 70%);
        animation: rotateBackground 30s linear infinite;
        z-index: 0;
    }

    @keyframes rotateBackground {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Floating particles */
    .particles {
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
        width: 10px;
        height: 10px;
        background: var(--primary-green);
        border-radius: 50%;
        opacity: 0.2;
        animation: float 15s infinite ease-in-out;
    }

    .particle:nth-child(1) { left: 10%; animation-delay: 0s; animation-duration: 12s; }
    .particle:nth-child(2) { left: 20%; animation-delay: 2s; animation-duration: 15s; }
    .particle:nth-child(3) { left: 30%; animation-delay: 4s; animation-duration: 18s; }
    .particle:nth-child(4) { left: 70%; animation-delay: 1s; animation-duration: 14s; }
    .particle:nth-child(5) { left: 80%; animation-delay: 3s; animation-duration: 16s; }
    .particle:nth-child(6) { left: 90%; animation-delay: 5s; animation-duration: 13s; }

    @keyframes float {
        0%, 100% {
            transform: translateY(100vh) scale(1);
            opacity: 0;
        }
        10% {
            opacity: 0.3;
        }
        90% {
            opacity: 0.3;
        }
        50% {
            transform: translateY(-10vh) scale(1.5);
            opacity: 0.5;
        }
    }

    .container {
        position: relative;
        z-index: 1;
    }

    /* Hero Section */
    .hero-section {
        animation: fadeInUp 1s ease-out;
        position: relative;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(40px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .hero-section h1 {
        font-size: 3.2rem !important;
        font-weight: 800 !important;
        margin-bottom: 1.5rem !important;
        background: linear-gradient(135deg, #047857 0%, #10b981 50%, #34d399 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        line-height: 1.2;
        animation: slideInLeft 1s ease-out;
        position: relative;
    }

    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-50px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .hero-section h1::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 0;
        width: 100px;
        height: 5px;
        background: var(--green-gradient);
        border-radius: 5px;
        animation: expandWidth 1.5s ease-out 0.5s both;
    }

    @keyframes expandWidth {
        from { width: 0; }
        to { width: 100px; }
    }

    .hero-section p {
        font-size: 1.15rem !important;
        color: #047857 !important;
        margin-bottom: 2.5rem !important;
        line-height: 1.8 !important;
        animation: slideInRight 1s ease-out 0.2s both;
        font-weight: 500;
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(50px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* Buttons */
    .btn {
        padding: 1rem 2.5rem;
        font-size: 1.1rem;
        font-weight: 700;
        border-radius: 50px;
        border: none;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
        text-transform: uppercase;
        letter-spacing: 1px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .btn::before {
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

    .btn:hover::before {
        width: 400px;
        height: 400px;
    }

    .btn-primary {
        background: var(--green-gradient);
        color: white;
        animation: bounceIn 1s ease-out 0.4s both;
        box-shadow: var(--green-glow);
    }

    .btn-primary:hover {
        transform: translateY(-5px) scale(1.05);
        box-shadow: 0 10px 30px rgba(16, 185, 129, 0.4);
        color: white;
    }

    .btn-secondary {
        background: white;
        color: var(--primary-green);
        border: 3px solid var(--primary-green);
        animation: bounceIn 1s ease-out 0.6s both;
    }

    .btn-secondary:hover {
        background: var(--primary-green);
        color: white;
        transform: translateY(-5px) scale(1.05);
        box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);
    }

    @keyframes bounceIn {
        0% {
            opacity: 0;
            transform: scale(0.3);
        }
        50% {
            opacity: 1;
            transform: scale(1.05);
        }
        70% {
            transform: scale(0.9);
        }
        100% {
            opacity: 1;
            transform: scale(1);
        }
    }

    /* Feature Grid */
    .feature-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.5rem;
        margin-top: 2.5rem;
    }

    .feature-card {
        background: white;
        padding: 1.8rem;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(16, 185, 129, 0.1);
        transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
        border: 2px solid transparent;
        animation: fadeInUp 1s ease-out both;
        min-height: 260px;
    }

    .feature-card:nth-child(1) { animation-delay: 0.8s; }
    .feature-card:nth-child(2) { animation-delay: 1s; }
    .feature-card:nth-child(3) { animation-delay: 1.2s; }

    .feature-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(16, 185, 129, 0.1), transparent);
        transition: left 0.7s ease;
    }

    .feature-card:hover::before {
        left: 100%;
    }

    .feature-card:hover {
        transform: translateY(-10px) scale(1.02);
        box-shadow: 0 20px 60px rgba(16, 185, 129, 0.25);
        border-color: var(--primary-green);
    }

    .feature-card .icon-wrapper {
        width: 64px;
        height: 64px;
        background: var(--green-gradient);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin-bottom: 1.1rem;
        box-shadow: var(--green-glow);
        transition: all 0.4s ease;
        animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }

    .feature-card:hover .icon-wrapper {
        transform: rotateY(360deg) scale(1.1);
        box-shadow: 0 10px 30px rgba(16, 185, 129, 0.5);
    }

    .feature-card h3 {
        font-size: 1.3rem;
        font-weight: 700;
        color: #047857;
        margin-bottom: 0.75rem;
        transition: all 0.3s ease;
    }

    .feature-card:hover h3 {
        color: var(--primary-green);
        transform: translateX(5px);
    }

    .feature-card p {
        color: #64748b;
        font-size: 0.95rem;
        line-height: 1.55;
        margin: 0;
    }

    /* Stats/Numbers Section */
    .stats-section {
        display: flex;
        justify-content: space-around;
        margin-top: 3rem;
        animation: fadeIn 1.5s ease-out 1.4s both;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .stat-item {
        text-align: center;
    }

    .stat-number {
        font-size: 2.4rem;
        font-weight: 800;
        background: var(--green-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 0.5rem;
        animation: countUp 2s ease-out;
    }

    @keyframes countUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .stat-label {
        color: #047857;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 1px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .hero-section h1 {
            font-size: 2.5rem !important;
        }

        .hero-section p {
            font-size: 1.1rem !important;
        }

        .btn {
            width: 100%;
            margin-bottom: 1rem;
        }

        .feature-grid {
            grid-template-columns: 1fr;
        }

        .stats-section {
            flex-direction: column;
            gap: 1.5rem;
        }
    }

    /* Decorative elements */
    .decoration-circle {
        position: absolute;
        border-radius: 50%;
        background: var(--green-gradient);
        opacity: 0.1;
        z-index: -1;
        animation: float 20s infinite ease-in-out;
    }

    .decoration-circle-1 {
        width: 300px;
        height: 300px;
        top: -100px;
        right: -100px;
        animation-delay: 0s;
    }

    .decoration-circle-2 {
        width: 200px;
        height: 200px;
        bottom: -50px;
        left: -50px;
        animation-delay: 5s;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0) rotate(0deg); }
        33% { transform: translateY(-30px) rotate(120deg); }
        66% { transform: translateY(30px) rotate(240deg); }
    }
</style>

<!-- Floating Particles -->
<div class="particles">
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
</div>

<div class="container" style="min-height: calc(100vh - 70px); display: flex; align-items: center;">
    <!-- Decorative Circles -->
    <div class="decoration-circle decoration-circle-1"></div>
    <div class="decoration-circle decoration-circle-2"></div>

    <div class="row w-100">
        <div class="col-lg-10 mx-auto">
            <div class="hero-section">
                <h1>
                    Gérez vos dossiers en toute simplicité
                </h1>

                <p>
                    Une plateforme complète pour organiser, stocker et partager vos documents de manière sécurisée et efficace.
                    Simplifiez votre gestion documentaire dès aujourd'hui.
                </p>

                <div style="display: flex; gap: 1.5rem; flex-wrap: wrap; margin-bottom: 4rem;">
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn btn-primary">
                            🚀 Créer un compte
                        </a>
                    @endif

                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="btn btn-secondary">
                            🔑 Se connecter
                        </a>
                    @endif
                </div>

                <div class="feature-grid">
                    <div class="feature-card">
                        <div class="icon-wrapper">
                            📁
                        </div>
                        <h3>Organisation Intelligente</h3>
                        <p>Créez et organisez vos dossiers avec une interface intuitive. Retrouvez vos documents en un clic.</p>
                    </div>

                    <div class="feature-card">
                        <div class="icon-wrapper">
                            🔒
                        </div>
                        <h3>Sécurité Maximale</h3>
                        <p>Protégez vos données sensibles avec des contrôles d'accès avancés et un chiffrement de bout en bout.</p>
                    </div>

                    <div class="feature-card">
                        <div class="icon-wrapper">
                            ⚡
                        </div>
                        <h3>Rapidité & Performance</h3>
                        <p>Accédez à vos fichiers instantanément grâce à notre infrastructure cloud ultra-rapide.</p>
                    </div>
                </div>

                <!-- Stats Section (Optional) -->
                <div class="stats-section">
                    <div class="stat-item">
                        <div class="stat-number">10K+</div>
                        <div class="stat-label">Utilisateurs actifs</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">99.9%</div>
                        <div class="stat-label">Disponibilité</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">500K+</div>
                        <div class="stat-label">Dossiers créés</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Add interactive hover effects
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.feature-card');

        cards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.zIndex = '10';
            });

            card.addEventListener('mouseleave', function() {
                this.style.zIndex = '1';
            });

            // Parallax effect on mouse move
            card.addEventListener('mousemove', function(e) {
                const rect = this.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;

                const centerX = rect.width / 2;
                const centerY = rect.height / 2;

                const rotateX = (y - centerY) / 20;
                const rotateY = (centerX - x) / 20;

                this.style.transform = `translateY(-15px) scale(1.03) perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
            });

            card.addEventListener('mouseleave', function() {
                this.style.transform = '';
            });
        });

        // Smooth scroll for links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    });

    // Add ripple effect on button clicks
    document.querySelectorAll('.btn').forEach(button => {
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
            ripple.style.background = 'rgba(255, 255, 255, 0.6)';
            ripple.style.transform = 'scale(0)';
            ripple.style.animation = 'ripple 0.6s ease-out';
            ripple.style.pointerEvents = 'none';

            this.appendChild(ripple);

            setTimeout(() => ripple.remove(), 600);
        });
    });

    // Add ripple animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
</script>
@endsection
