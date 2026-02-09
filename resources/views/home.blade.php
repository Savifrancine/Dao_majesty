@extends('layouts.app')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Space+Mono:wght@400;700&display=swap');

    :root {
        --emerald-50: #ecfdf5;
        --emerald-100: #d1fae5;
        --emerald-200: #a7f3d0;
        --emerald-300: #6ee7b7;
        --emerald-400: #34d399;
        --emerald-500: #10b981;
        --emerald-600: #059669;
        --emerald-700: #047857;
        --emerald-800: #065f46;
        --emerald-900: #064e3b;

        --slate-50: #f8fafc;
        --slate-100: #f1f5f9;
        --slate-200: #e2e8f0;
        --slate-700: #334155;
        --slate-800: #1e293b;
        --slate-900: #0f172a;

        --green-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
        --green-mesh: radial-gradient(at 20% 30%, #10b98140 0%, transparent 50%),
                      radial-gradient(at 80% 70%, #05966940 0%, transparent 50%),
                      radial-gradient(at 50% 50%, #34d39920 0%, transparent 50%);
    }

    * {
        font-family: 'Outfit', sans-serif;
    }

    body {
        background: var(--slate-50);
        position: relative;
        overflow-x: hidden;
    }

    body::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: var(--green-mesh);
        opacity: 0.4;
        z-index: 0;
        pointer-events: none;
    }

    .dashboard-container {
        position: relative;
        z-index: 1;
        padding: 2rem 0;
        animation: fadeIn 0.8s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    /* Welcome Hero */
    .welcome-hero {
        background: linear-gradient(135deg, #047857 0%, #10b981 50%, #34d399 100%);
        border-radius: 32px;
        padding: 3rem;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(16, 185, 129, 0.3);
        animation: slideDown 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-50px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .welcome-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, transparent 70%);
        border-radius: 50%;
        animation: float 20s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translate(0, 0) rotate(0deg); }
        33% { transform: translate(30px, -30px) rotate(120deg); }
        66% { transform: translate(-30px, 30px) rotate(240deg); }
    }

    .welcome-hero::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: -10%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
        border-radius: 50%;
        animation: float 15s ease-in-out infinite reverse;
    }

    .hero-content {
        position: relative;
        z-index: 1;
    }

    .hero-greeting {
        font-size: 0.95rem;
        font-weight: 600;
        color: rgba(255, 255, 255, 0.9);
        text-transform: uppercase;
        letter-spacing: 2px;
        margin-bottom: 0.5rem;
        animation: slideInLeft 0.8s ease-out 0.2s both;
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

    .hero-title {
        font-size: 3rem;
        font-weight: 900;
        color: white;
        margin-bottom: 1rem;
        line-height: 1.1;
        animation: slideInLeft 0.8s ease-out 0.3s both;
    }

    .hero-subtitle {
        font-size: 1.2rem;
        color: rgba(255, 255, 255, 0.95);
        margin-bottom: 2rem;
        font-weight: 400;
        animation: slideInLeft 0.8s ease-out 0.4s both;
    }

    .hero-actions {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        animation: slideInLeft 0.8s ease-out 0.5s both;
    }

    .btn-hero {
        padding: 1rem 2.5rem;
        border-radius: 100px;
        font-weight: 700;
        font-size: 1rem;
        border: none;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        position: relative;
        overflow: hidden;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .btn-hero-primary {
        background: white;
        color: var(--emerald-700);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    .btn-hero-primary::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        background: var(--emerald-50);
        border-radius: 50%;
        transform: translate(-50%, -50%);
        transition: width 0.6s ease, height 0.6s ease;
    }

    .btn-hero-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
    }

    .btn-hero-primary:hover::before {
        width: 400px;
        height: 400px;
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 24px;
        padding: 2rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        animation: scaleIn 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) both;
        border: 1px solid var(--slate-200);
    }

    .stat-card:nth-child(1) { animation-delay: 0.6s; }
    .stat-card:nth-child(2) { animation-delay: 0.7s; }
    .stat-card:nth-child(3) { animation-delay: 0.8s; }
    .stat-card:nth-child(4) { animation-delay: 0.9s; }

    @keyframes scaleIn {
        from {
            opacity: 0;
            transform: scale(0.8);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: var(--green-gradient);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.6s ease;
    }

    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 50px rgba(16, 185, 129, 0.2);
    }

    .stat-card:hover::before {
        transform: scaleX(1);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 16px;
        background: linear-gradient(135deg, var(--emerald-50), var(--emerald-100));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin-bottom: 1.5rem;
        transition: all 0.4s ease;
    }

    .stat-card:hover .stat-icon {
        transform: scale(1.1) rotate(5deg);
        background: var(--green-gradient);
    }

    .stat-label {
        font-size: 0.85rem;
        color: var(--slate-700);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 0.5rem;
    }

    .stat-value {
        font-size: 2.5rem;
        font-weight: 900;
        color: var(--slate-900);
        font-family: 'Space Mono', monospace;
        line-height: 1;
        margin-bottom: 0.5rem;
    }

    .stat-change {
        font-size: 0.9rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.25rem 0.75rem;
        border-radius: 100px;
        background: var(--emerald-50);
        color: var(--emerald-700);
    }

    /* Main Grid */
    .main-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    @media (max-width: 1024px) {
        .main-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Chart Card */
    .chart-card {
        background: white;
        border-radius: 24px;
        padding: 2rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        animation: slideInLeft 0.8s ease-out 1s both;
        border: 1px solid var(--slate-200);
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--slate-100);
    }

    .chart-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--slate-900);
    }

    .chart-tabs {
        display: flex;
        gap: 0.5rem;
        background: var(--slate-100);
        padding: 0.25rem;
        border-radius: 100px;
    }

    .chart-tab {
        padding: 0.5rem 1.25rem;
        border-radius: 100px;
        border: none;
        background: transparent;
        color: var(--slate-700);
        font-weight: 600;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .chart-tab.active {
        background: white;
        color: var(--emerald-700);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    /* Simple Chart Visualization */
    .chart-bars {
        display: flex;
        align-items: flex-end;
        justify-content: space-around;
        height: 300px;
        gap: 1rem;
        padding: 1rem 0;
    }

    .chart-bar {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
    }

    .bar {
        width: 100%;
        max-width: 60px;
        background: var(--green-gradient);
        border-radius: 12px 12px 0 0;
        position: relative;
        transition: all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        box-shadow: 0 -4px 20px rgba(16, 185, 129, 0.3);
        animation: growBar 1.2s ease-out both;
    }

    .chart-bar:nth-child(1) .bar { animation-delay: 1.2s; }
    .chart-bar:nth-child(2) .bar { animation-delay: 1.3s; }
    .chart-bar:nth-child(3) .bar { animation-delay: 1.4s; }
    .chart-bar:nth-child(4) .bar { animation-delay: 1.5s; }
    .chart-bar:nth-child(5) .bar { animation-delay: 1.6s; }
    .chart-bar:nth-child(6) .bar { animation-delay: 1.7s; }
    .chart-bar:nth-child(7) .bar { animation-delay: 1.8s; }

    @keyframes growBar {
        from {
            transform: scaleY(0);
            opacity: 0;
        }
        to {
            transform: scaleY(1);
            opacity: 1;
        }
    }

    .bar:hover {
        transform: scaleY(1.05);
        filter: brightness(1.1);
    }

    .bar-value {
        position: absolute;
        top: -30px;
        left: 50%;
        transform: translateX(-50%);
        font-weight: 700;
        font-size: 0.9rem;
        color: var(--slate-900);
        font-family: 'Space Mono', monospace;
    }

    .bar-label {
        font-size: 0.75rem;
        color: var(--slate-700);
        font-weight: 600;
        text-transform: uppercase;
    }

    /* Activity Card */
    .activity-card {
        background: white;
        border-radius: 24px;
        padding: 2rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        animation: slideInRight 0.8s ease-out 1s both;
        border: 1px solid var(--slate-200);
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .activity-header {
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--slate-100);
    }

    .activity-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--slate-900);
        margin-bottom: 0.5rem;
    }

    .activity-subtitle {
        font-size: 0.9rem;
        color: var(--slate-700);
    }

    .activity-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .activity-item {
        display: flex;
        gap: 1rem;
        padding: 1rem;
        border-radius: 16px;
        background: var(--slate-50);
        transition: all 0.3s ease;
        animation: fadeInUp 0.6s ease-out both;
    }

    .activity-item:nth-child(1) { animation-delay: 1.2s; }
    .activity-item:nth-child(2) { animation-delay: 1.3s; }
    .activity-item:nth-child(3) { animation-delay: 1.4s; }
    .activity-item:nth-child(4) { animation-delay: 1.5s; }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .activity-item:hover {
        background: white;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        transform: translateX(5px);
    }

    .activity-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: var(--green-gradient);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .activity-content {
        flex: 1;
    }

    .activity-name {
        font-weight: 700;
        color: var(--slate-900);
        margin-bottom: 0.25rem;
    }

    .activity-desc {
        font-size: 0.85rem;
        color: var(--slate-700);
    }

    .activity-time {
        font-size: 0.75rem;
        color: var(--slate-700);
        font-family: 'Space Mono', monospace;
    }

    /* Table Section */
    .table-section {
        background: white;
        border-radius: 24px;
        padding: 2rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        animation: slideUp 0.8s ease-out 1.2s both;
        border: 1px solid var(--slate-200);
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--slate-100);
    }

    .table-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--slate-900);
    }

    .table-filters {
        display: flex;
        gap: 0.5rem;
    }

    .filter-btn {
        padding: 0.5rem 1rem;
        border-radius: 100px;
        border: 2px solid var(--slate-200);
        background: white;
        color: var(--slate-700);
        font-weight: 600;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .filter-btn:hover,
    .filter-btn.active {
        background: var(--green-gradient);
        color: white;
        border-color: var(--emerald-600);
    }

    .modern-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 0.5rem;
    }

    .modern-table thead th {
        text-align: left;
        padding: 1rem;
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--slate-700);
        text-transform: uppercase;
        letter-spacing: 1px;
        border: none;
    }

    .modern-table tbody tr {
        background: var(--slate-50);
        transition: all 0.3s ease;
        animation: fadeInUp 0.6s ease-out both;
    }

    .modern-table tbody tr:nth-child(1) { animation-delay: 1.4s; }
    .modern-table tbody tr:nth-child(2) { animation-delay: 1.5s; }
    .modern-table tbody tr:nth-child(3) { animation-delay: 1.6s; }
    .modern-table tbody tr:nth-child(4) { animation-delay: 1.7s; }
    .modern-table tbody tr:nth-child(5) { animation-delay: 1.8s; }

    .modern-table tbody tr:hover {
        background: white;
        box-shadow: 0 4px 20px rgba(16, 185, 129, 0.15);
        transform: scale(1.01);
    }

    .modern-table tbody td {
        padding: 1.25rem 1rem;
        border: none;
        color: var(--slate-900);
    }

    .modern-table tbody tr td:first-child {
        border-radius: 12px 0 0 12px;
    }

    .modern-table tbody tr td:last-child {
        border-radius: 0 12px 12px 0;
    }

    .table-name {
        font-weight: 700;
        color: var(--slate-900);
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .name-avatar {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: var(--green-gradient);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 1rem;
    }

    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 100px;
        font-weight: 700;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-success {
        background: var(--emerald-100);
        color: var(--emerald-700);
    }

    .status-warning {
        background: #fef3c7;
        color: #92400e;
    }

    .status-info {
        background: #dbeafe;
        color: #1e40af;
    }

    .status-private {
        background: #fce7f3;
        color: #9f1239;
    }

    .action-btns {
        display: flex;
        gap: 0.5rem;
    }

    .action-btn {
        padding: 0.5rem 1rem;
        border-radius: 10px;
        border: none;
        font-weight: 600;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-view {
        background: var(--emerald-50);
        color: var(--emerald-700);
    }

    .btn-view:hover {
        background: var(--emerald-100);
        transform: translateY(-2px);
    }

    .btn-download {
        background: var(--green-gradient);
        color: white;
    }

    .btn-download:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
    }

    .empty-icon {
        font-size: 5rem;
        margin-bottom: 1.5rem;
        animation: float 3s ease-in-out infinite;
    }

    .empty-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--slate-900);
        margin-bottom: 0.5rem;
    }

    .empty-desc {
        color: var(--slate-700);
        margin-bottom: 2rem;
    }

    .empty-action {
        padding: 1rem 2rem;
        border-radius: 100px;
        background: var(--green-gradient);
        color: white;
        font-weight: 700;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .empty-action:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .hero-title {
            font-size: 2rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .chart-bars {
            height: 250px;
        }

        .modern-table {
            font-size: 0.85rem;
        }

        .action-btns {
            flex-direction: column;
        }
    }
</style>

<div class="dashboard-container container">
    <!-- Welcome Hero -->
    <div class="welcome-hero">
        <div class="hero-content">
            <div class="hero-greeting">Bonjour, {{ Auth::user()->prenom }} 👋</div>
            <h1 class="hero-title">Tableau de bord</h1>
            <p class="hero-subtitle">Gérez tous vos dossiers et suivez vos statistiques en temps réel</p>
            <div class="hero-actions">
                <a href="{{ route('dossiers.create') }}" class="btn-hero btn-hero-primary">
                    ✨ Créer un nouveau dossier
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📁</div>
            <div class="stat-label">Total Dossiers</div>
            <div class="stat-value">{{ $dossiers->count() }}</div>
            <div class="stat-change">↗ +12% ce mois</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-label">Terminés</div>
            <div class="stat-value">{{ $dossiers->where('statut', 'termine')->count() }}</div>
            <div class="stat-change">↗ +8% ce mois</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">⏳</div>
            <div class="stat-label">En cours</div>
            <div class="stat-value">{{ $dossiers->where('statut', 'en_cours')->count() }}</div>
            <div class="stat-change">↗ +5% ce mois</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📄</div>
            <div class="stat-label">Générés</div>
            <div class="stat-value">{{ $dossiers->where('statut', 'genere')->count() }}</div>
            <div class="stat-change">↗ +15% ce mois</div>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="main-grid">
        <!-- Chart Card -->
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title">📊 Activité hebdomadaire</h3>
                <div class="chart-tabs">
                    <button class="chart-tab active">7 jours</button>
                    <button class="chart-tab">30 jours</button>
                </div>
            </div>
            <div class="chart-bars">
                <div class="chart-bar">
                    <div class="bar" style="height: 45%;">
                        <span class="bar-value">12</span>
                    </div>
                    <span class="bar-label">Lun</span>
                </div>
                <div class="chart-bar">
                    <div class="bar" style="height: 65%;">
                        <span class="bar-value">18</span>
                    </div>
                    <span class="bar-label">Mar</span>
                </div>
                <div class="chart-bar">
                    <div class="bar" style="height: 55%;">
                        <span class="bar-value">15</span>
                    </div>
                    <span class="bar-label">Mer</span>
                </div>
                <div class="chart-bar">
                    <div class="bar" style="height: 80%;">
                        <span class="bar-value">22</span>
                    </div>
                    <span class="bar-label">Jeu</span>
                </div>
                <div class="chart-bar">
                    <div class="bar" style="height: 70%;">
                        <span class="bar-value">19</span>
                    </div>
                    <span class="bar-label">Ven</span>
                </div>
                <div class="chart-bar">
                    <div class="bar" style="height: 40%;">
                        <span class="bar-value">11</span>
                    </div>
                    <span class="bar-label">Sam</span>
                </div>
                <div class="chart-bar">
                    <div class="bar" style="height: 35%;">
                        <span class="bar-value">9</span>
                    </div>
                    <span class="bar-label">Dim</span>
                </div>
            </div>
        </div>

        <!-- Activity Card -->
        <div class="activity-card">
            <div class="activity-header">
                <h3 class="activity-title">🔔 Activités récentes</h3>
                <p class="activity-subtitle">Dernières actions</p>
            </div>
            <div class="activity-list">
                @php
                    $recentDossiers = $dossiers->sortByDesc('created_at')->take(4);
                @endphp
                @forelse($recentDossiers as $dossier)
                <div class="activity-item">
                    <div class="activity-icon">📁</div>
                    <div class="activity-content">
                        <div class="activity-name">{{ Str::limit($dossier->nom_dossier, 25) }}</div>
                        <div class="activity-desc">{{ ucfirst($dossier->statut) }}</div>
                    </div>
                    <div class="activity-time">{{ $dossier->created_at->diffForHumans() }}</div>
                </div>
                @empty
                <div class="activity-item">
                    <div class="activity-icon">📭</div>
                    <div class="activity-content">
                        <div class="activity-name">Aucune activité</div>
                        <div class="activity-desc">Commencez par créer un dossier</div>
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="table-section">
        <div class="table-header">
            <h3 class="table-title">📂 Tous vos dossiers</h3>
            <div class="table-filters">
                <button class="filter-btn active">Tous</button>
                <button class="filter-btn">En cours</button>
                <button class="filter-btn">Terminés</button>
            </div>
        </div>

        @if($dossiers->count() > 0)
        <div style="overflow-x: auto;">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Nom du dossier</th>
                        <th>Type</th>
                        <th>Entreprise</th>
                        <th>Statut</th>
                        <th>Visibilité</th>
                        <th>Créé le</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dossiers as $dossier)
                    <tr>
                        <td>
                            <div class="table-name">
                                <div class="name-avatar">{{ substr($dossier->nom_dossier, 0, 1) }}</div>
                                <strong>{{ $dossier->nom_dossier }}</strong>
                            </div>
                        </td>
                        <td>{{ $dossier->typeDossier->nom }}</td>
                        <td>{{ $dossier->entreprise->nom }}</td>
                        <td>
                            @if($dossier->statut === 'en_cours')
                                <span class="status-badge status-warning">⏳ En cours</span>
                            @elseif($dossier->statut === 'termine')
                                <span class="status-badge status-success">✅ Terminé</span>
                            @else
                                <span class="status-badge status-info">📄 Généré</span>
                            @endif
                        </td>
                        <td>
                            @if($dossier->public_prive === 'prive')
                                <span class="status-badge status-private">🔒 Privé</span>
                            @else
                                <span class="status-badge status-info">🌐 Public</span>
                            @endif
                        </td>
                        <td>{{ $dossier->created_at->format('d/m/Y') }}</td>
                        <td>
                            <div class="action-btns">
                                <a href="{{ route('dossiers.show', $dossier) }}" class="action-btn btn-view">👁️ Voir</a>
                                @if($dossier->statut === 'genere')
                                    <a href="{{ route('dossiers.pdf', $dossier) }}" class="action-btn btn-download">📥 PDF</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state">
            <div class="empty-icon">📭</div>
            <h4 class="empty-title">Aucun dossier pour le moment</h4>
            <p class="empty-desc">Commencez par créer votre premier dossier pour organiser vos documents</p>
            <a href="{{ route('dossiers.create') }}" class="empty-action">
                ✨ Créer mon premier dossier
            </a>
        </div>
        @endif
    </div>
</div>

<script>
    // Animate numbers on load
    document.addEventListener('DOMContentLoaded', function() {
        const statValues = document.querySelectorAll('.stat-value');

        statValues.forEach(stat => {
            const target = parseInt(stat.textContent);
            let current = 0;
            const increment = target / 50;
            const duration = 1500;
            const stepTime = duration / 50;

            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    stat.textContent = target;
                    clearInterval(timer);
                } else {
                    stat.textContent = Math.floor(current);
                }
            }, stepTime);
        });

        // Chart tabs functionality
        const chartTabs = document.querySelectorAll('.chart-tab');
        chartTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                chartTabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Filter buttons functionality
        const filterBtns = document.querySelectorAll('.filter-btn');
        const tableRows = document.querySelectorAll('.modern-table tbody tr');

        filterBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                filterBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                const filter = this.textContent.toLowerCase();

                tableRows.forEach(row => {
                    if (filter === 'tous') {
                        row.style.display = '';
                    } else if (filter === 'en cours') {
                        const hasEnCours = row.textContent.includes('En cours');
                        row.style.display = hasEnCours ? '' : 'none';
                    } else if (filter === 'terminés') {
                        const hasTermine = row.textContent.includes('Terminé');
                        row.style.display = hasTermine ? '' : 'none';
                    }
                });
            });
        });

        // Add ripple effect
        document.querySelectorAll('.btn-hero, .action-btn, .empty-action').forEach(button => {
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
                ripple.style.animation = 'ripple 0.6s ease-out';
                ripple.style.pointerEvents = 'none';

                this.style.position = 'relative';
                this.style.overflow = 'hidden';
                this.appendChild(ripple);

                setTimeout(() => ripple.remove(), 600);
            });
        });
    });

   
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
