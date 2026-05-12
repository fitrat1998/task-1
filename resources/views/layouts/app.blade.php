<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="KBR Investitsiya va Baholovchilar Mehnat Bozori tahlili — ma'lumotlarga asoslangan qarorlar qabul qiling">
    <title>@yield('title', 'Analitika Platformasi') — KBR & HR Tahlil</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg-primary: #0a0a1a;
            --bg-secondary: #111128;
            --bg-card: rgba(255,255,255,0.04);
            --bg-card-hover: rgba(255,255,255,0.07);
            --border: rgba(255,255,255,0.08);
            --border-glow: rgba(108,99,255,0.4);
            --accent: #6c63ff;
            --accent2: #00b4d8;
            --accent3: #ff6b6b;
            --accent4: #ffd166;
            --text-primary: #f0f0ff;
            --text-secondary: #9090b8;
            --text-muted: #5a5a7a;
            --success: #06d6a0;
            --danger: #ef476f;
            --warning: #ffd166;
            --sidebar-width: 260px;
            --header-height: 64px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0; left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--bg-secondary);
            border-right: 1px solid var(--border);
            z-index: 100;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }

        .sidebar-logo {
            padding: 24px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-icon {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
        }

        .logo-text { font-size: 15px; font-weight: 700; line-height: 1.2; }
        .logo-sub { font-size: 11px; color: var(--text-secondary); font-weight: 400; }

        .nav-section { padding: 20px 12px 8px; }
        .nav-section-title {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: var(--text-muted);
            padding: 0 8px;
            margin-bottom: 8px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 10px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            transition: all .2s ease;
            margin-bottom: 2px;
        }
        .nav-item:hover { background: var(--bg-card-hover); color: var(--text-primary); }
        .nav-item.active {
            background: linear-gradient(135deg, rgba(108,99,255,.25), rgba(0,180,216,.15));
            color: #fff;
            border: 1px solid rgba(108,99,255,.3);
        }
        .nav-item.active .nav-dot { background: var(--accent); box-shadow: 0 0 8px var(--accent); }

        .nav-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: var(--text-muted);
            flex-shrink: 0;
        }

        .nav-badge {
            margin-left: auto;
            background: var(--accent);
            color: #fff;
            font-size: 10px;
            padding: 2px 7px;
            border-radius: 20px;
            font-weight: 600;
        }

        .sidebar-footer {
            margin-top: auto;
            padding: 16px 12px;
            border-top: 1px solid var(--border);
        }

        .refresh-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 10px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text-secondary);
            font-size: 12px;
            font-family: inherit;
            cursor: pointer;
            text-decoration: none;
            transition: all .2s;
        }
        .refresh-btn:hover { border-color: var(--accent); color: var(--accent); }

        /* Main content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            padding: 0;
        }

        .page-header {
            padding: 32px 36px 0;
            position: relative;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 500px; height: 300px;
            background: radial-gradient(ellipse at top right, rgba(108,99,255,.12), transparent 70%);
            pointer-events: none;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: var(--text-muted);
            margin-bottom: 16px;
        }
        .breadcrumb a { color: var(--accent); text-decoration: none; }
        .breadcrumb span { color: var(--text-muted); }

        .page-title {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #fff 30%, var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 8px;
        }

        .page-subtitle {
            font-size: 14px;
            color: var(--text-secondary);
            margin-bottom: 32px;
        }

        .content-area { padding: 0 36px 48px; }

        /* Cards */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 24px;
            transition: border-color .2s, transform .2s;
            position: relative;
            overflow: hidden;
        }
        .card:hover { border-color: var(--border-glow); }

        .card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--accent), transparent);
            opacity: 0;
            transition: opacity .3s;
        }
        .card:hover::before { opacity: .5; }

        .card-title {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: .8px;
            margin-bottom: 12px;
        }

        .card-value {
            font-size: 32px;
            font-weight: 800;
            letter-spacing: -1px;
            color: var(--text-primary);
        }

        .card-unit { font-size: 14px; font-weight: 500; color: var(--text-secondary); margin-left: 4px; }
        .card-change { font-size: 12px; margin-top: 6px; }
        .card-change.up { color: var(--success); }
        .card-change.down { color: var(--danger); }

        /* KPI grid */
        .kpi-grid {
            display: grid;
            gap: 16px;
            margin-bottom: 24px;
        }
        .kpi-grid-4 { grid-template-columns: repeat(4, 1fr); }
        .kpi-grid-3 { grid-template-columns: repeat(3, 1fr); }
        .kpi-grid-2 { grid-template-columns: repeat(2, 1fr); }

        /* Chart card */
        .chart-card { padding: 28px; }
        .chart-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
        .chart-title { font-size: 16px; font-weight: 700; }
        .chart-subtitle { font-size: 12px; color: var(--text-muted); margin-top: 2px; }

        /* Table */
        .table-wrapper { overflow-x: auto; }
        table.analytics-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .analytics-table thead th {
            background: rgba(108,99,255,.1);
            color: var(--text-secondary);
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .8px;
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }
        .analytics-table tbody td {
            padding: 12px 16px;
            border-bottom: 1px solid rgba(255,255,255,0.04);
            color: var(--text-primary);
        }
        .analytics-table tbody tr:hover td { background: var(--bg-card-hover); }
        .analytics-table tbody tr:last-child td { border-bottom: none; }

        /* Badge */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-success { background: rgba(6,214,160,.15); color: var(--success); }
        .badge-danger  { background: rgba(239,71,111,.15); color: var(--danger); }
        .badge-warning { background: rgba(255,209,102,.15); color: var(--warning); }
        .badge-accent  { background: rgba(108,99,255,.2); color: var(--accent); }
        .badge-blue    { background: rgba(0,180,216,.15); color: var(--accent2); }

        /* Alert */
        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }
        .alert-success { background: rgba(6,214,160,.1); border: 1px solid rgba(6,214,160,.3); color: var(--success); }
        .alert-info    { background: rgba(0,180,216,.1);  border: 1px solid rgba(0,180,216,.3);  color: var(--accent2); }

        /* Hypothesis card */
        .hypothesis-card {
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 16px;
            position: relative;
            overflow: hidden;
        }
        .hyp-confirmed {
            background: rgba(6,214,160,.06);
            border: 1px solid rgba(6,214,160,.25);
        }
        .hyp-rejected {
            background: rgba(239,71,111,.06);
            border: 1px solid rgba(239,71,111,.25);
        }
        .hyp-title { font-size: 15px; font-weight: 700; margin-bottom: 12px; }
        .hyp-stats { display: flex; gap: 24px; flex-wrap: wrap; margin-bottom: 12px; }
        .hyp-stat { font-size: 12px; color: var(--text-secondary); }
        .hyp-stat strong { color: var(--text-primary); display: block; font-size: 18px; font-weight: 700; }
        .hyp-conclusion { font-size: 13px; color: var(--text-secondary); line-height: 1.6; }

        /* ROI bar */
        .roi-bar-wrap { position: relative; height: 8px; background: rgba(255,255,255,.08); border-radius: 4px; overflow: hidden; }
        .roi-bar { height: 100%; border-radius: 4px; background: linear-gradient(90deg, var(--accent), var(--accent2)); transition: width .8s ease; }

        /* Rank medal */
        .rank-medal {
            width: 32px; height: 32px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; font-weight: 800;
        }
        .rank-1 { background: linear-gradient(135deg,#ffd700,#ffaa00); color: #000; }
        .rank-2 { background: linear-gradient(135deg,#c0c0c0,#888); color: #000; }
        .rank-3 { background: linear-gradient(135deg,#cd7f32,#a0522d); color: #fff; }

        /* Recommendation card */
        .rec-card {
            display: flex; gap: 20px; align-items: flex-start;
            padding: 24px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            margin-bottom: 12px;
            transition: border-color .2s;
        }
        .rec-card:hover { border-color: var(--border-glow); }
        .rec-content { flex: 1; }
        .rec-zone { font-size: 17px; font-weight: 700; margin-bottom: 8px; }
        .rec-metrics { display: flex; gap: 24px; flex-wrap: wrap; margin-bottom: 10px; }
        .rec-metric { font-size: 12px; color: var(--text-secondary); }
        .rec-metric strong { color: var(--text-primary); font-size: 16px; font-weight: 700; display: block; }
        .rec-advice { font-size: 13px; color: var(--text-secondary); line-height: 1.6; }

        /* Section row */
        .section-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .section-row.wide { grid-template-columns: 2fr 1fr; }
        .section-row.full { grid-template-columns: 1fr; }

        /* Flash message */
        .flash { position: fixed; top: 20px; right: 20px; z-index: 9999;
            background: rgba(6,214,160,.15); border: 1px solid rgba(6,214,160,.4);
            color: var(--success); padding: 12px 20px; border-radius: 10px; font-size: 13px; }

        /* DataTables override */
        .dataTables_wrapper { color: var(--text-primary); }
        .dataTables_filter input, .dataTables_length select {
            background: var(--bg-card); border: 1px solid var(--border);
            color: var(--text-primary); border-radius: 6px; padding: 4px 8px;
        }
        .dataTables_info, .dataTables_paginate { color: var(--text-secondary) !important; }
        .paginate_button { color: var(--text-secondary) !important; border-radius: 6px !important; }
        .paginate_button.current { background: var(--accent) !important; border-color: var(--accent) !important; color: #fff !important; }

        /* Responsive */
        @media (max-width: 1100px) {
            .kpi-grid-4 { grid-template-columns: repeat(2, 1fr); }
            .section-row { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .main-content { margin-left: 0; }
            .page-header, .content-area { padding: 16px; }
        }

        /* Animation */
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .animate-in { animation: fadeInUp .4s ease both; }
        .delay-1 { animation-delay: .1s; }
        .delay-2 { animation-delay: .2s; }
        .delay-3 { animation-delay: .3s; }
        .delay-4 { animation-delay: .4s; }

        /* Color accent line on cards */
        .card-accent-green { border-top: 2px solid var(--success); }
        .card-accent-purple { border-top: 2px solid var(--accent); }
        .card-accent-blue { border-top: 2px solid var(--accent2); }
        .card-accent-orange { border-top: 2px solid var(--warning); }

        /* Lang switcher */
        .lang-switcher { display: flex; gap: 6px; margin-bottom: 10px; }
        .lang-btn {
            flex: 1; padding: 7px 4px; border-radius: 8px;
            font-size: 11px; font-weight: 700; letter-spacing: .5px;
            text-align: center; text-decoration: none; text-transform: uppercase;
            background: var(--bg-card); border: 1px solid var(--border);
            color: var(--text-secondary); transition: all .2s;
        }
        .lang-btn:hover { border-color: var(--accent); color: var(--accent); }
        .lang-btn.active { background: linear-gradient(135deg,rgba(108,99,255,.3),rgba(0,180,216,.2)); border-color: var(--accent); color: #fff; }
    </style>
    @stack('styles')
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon">📊</div>
        <div>
            <div class="logo-text">{{ __('app.logo_title') }}</div>
            <div class="logo-sub">{{ __('app.logo_sub') }}</div>
        </div>
    </div>

    <nav>
        <div class="nav-section">
            <div class="nav-section-title">{{ __('app.case1_title') }}</div>
            <a href="{{ url('/kbr') }}" class="nav-item {{ request()->is('kbr') ? 'active' : '' }}">
                <span class="nav-dot"></span> {{ __('app.nav_overview') }}
            </a>
            <a href="{{ url('/kbr/locations') }}" class="nav-item {{ request()->is('kbr/locations') ? 'active' : '' }}">
                <span class="nav-dot"></span> {{ __('app.nav_locations') }}
            </a>
            <a href="{{ url('/kbr/types') }}" class="nav-item {{ request()->is('kbr/types') ? 'active' : '' }}">
                <span class="nav-dot"></span> {{ __('app.nav_types') }}
            </a>
            <a href="{{ url('/kbr/roi') }}" class="nav-item {{ request()->is('kbr/roi') ? 'active' : '' }}">
                <span class="nav-dot"></span> {{ __('app.nav_roi') }}
            </a>
            <a href="{{ url('/kbr/hypotheses') }}" class="nav-item {{ request()->is('kbr/hypotheses') ? 'active' : '' }}">
                <span class="nav-dot"></span> {{ __('app.nav_hypotheses') }}
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">{{ __('app.case2_title') }}</div>
            <a href="{{ url('/vacancy') }}" class="nav-item {{ request()->is('vacancy') ? 'active' : '' }}">
                <span class="nav-dot"></span> {{ __('app.nav_overview') }}
            </a>
            <a href="{{ url('/vacancy/cities') }}" class="nav-item {{ request()->is('vacancy/cities') ? 'active' : '' }}">
                <span class="nav-dot"></span> {{ __('app.nav_cities') }}
            </a>
            <a href="{{ url('/vacancy/employers') }}" class="nav-item {{ request()->is('vacancy/employers') ? 'active' : '' }}">
                <span class="nav-dot"></span> {{ __('app.nav_employers') }}
            </a>
            <a href="{{ url('/vacancy/salary') }}" class="nav-item {{ request()->is('vacancy/salary') ? 'active' : '' }}">
                <span class="nav-dot"></span> {{ __('app.nav_salary') }}
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">ℹ️ Info</div>
            <a href="{{ url('/info') }}" class="nav-item {{ request()->is('info') ? 'active' : '' }}">
                <span class="nav-dot"></span> {{ __('app.nav_info') }}
            </a>
        </div>
    </nav>

    <div class="sidebar-footer">
        {{-- Til tanlash --}}
        <div class="lang-switcher">
            <a href="{{ url('/lang/uz') }}" class="lang-btn {{ app()->getLocale() === 'uz' ? 'active' : '' }}">🇺🇿 UZ</a>
            <a href="{{ url('/lang/ru') }}" class="lang-btn {{ app()->getLocale() === 'ru' ? 'active' : '' }}">🇷🇺 RU</a>
            <a href="{{ url('/lang/en') }}" class="lang-btn {{ app()->getLocale() === 'en' ? 'active' : '' }}">🇬🇧 EN</a>
        </div>
        <a href="{{ url('/refresh') }}" class="refresh-btn" onclick="return confirm('{{ __('app.cache_confirm') }}')">
            🔄 {{ __('app.cache_refresh') }}
        </a>
    </div>
</aside>

<!-- Flash -->
@if(session('success'))
<div class="flash">✅ {{ session('success') }}</div>
@endif

<!-- Main -->
<main class="main-content">
    <div class="page-header animate-in">
        <div class="breadcrumb">
            @yield('breadcrumb')
        </div>
        <h1 class="page-title">@yield('page-title')</h1>
        <p class="page-subtitle">@yield('page-subtitle')</p>
    </div>

    <div class="content-area">
        @yield('content')
    </div>
</main>

<script>
// Chart.js global defaults
Chart.defaults.color = '#9090b8';
Chart.defaults.borderColor = 'rgba(255,255,255,0.07)';
Chart.defaults.font.family = 'Inter';

function formatRub(n) {
    return new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'RUB', maximumFractionDigits: 0 }).format(n);
}
function formatNum(n) {
    return new Intl.NumberFormat('ru-RU').format(n);
}

// Data label translations (Python returns UZ, we translate here)
const DATA_LABELS = @json(__('data'));

/**
 * Translate a data label from Uzbek to current language.
 * Falls back to original if no translation found.
 */
function tdl(label) {
    return DATA_LABELS[label] ?? label;
}
</script>
@stack('scripts')
</body>
</html>
