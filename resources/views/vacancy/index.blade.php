@extends('layouts.app')
@section('title', __('app.vac_title'))
@section('breadcrumb')
    <a href="{{ url('/vacancy') }}">{{ __('app.case2_title') }}</a> <span>/</span> {{ __('app.nav_overview') }}
@endsection
@section('page-title', __('app.vac_title'))
@section('page-subtitle', __('app.vac_subtitle'))

@section('content')
@php
    $summary   = $data['summary'] ?? [];
    $cities    = $data['city_stats'] ?? [];
    $employers = $data['employer_stats'] ?? [];
    $hyps      = $data['hypotheses'] ?? [];
    $recs      = $data['top_recommendations'] ?? [];
    $charts    = $data['charts'] ?? [];
    $exp       = $data['exp_stats'] ?? [];
@endphp

{{-- KPI --}}
<div class="kpi-grid kpi-grid-4 animate-in delay-1">
    <div class="card card-accent-purple">
        <div class="card-title">Jami vakansiyalar</div>
        <div class="card-value">{{ number_format($summary['total_vacancies'] ?? 0) }}</div>
        <div class="card-change">💼 2024–2025 yil</div>
    </div>
    <div class="card card-accent-blue">
        <div class="card-title">O'rtacha maosh</div>
        <div class="card-value">{{ number_format($summary['avg_salary'] ?? 0) }}<span class="card-unit">₽</span></div>
        <div class="card-change">Median: {{ number_format($summary['median_salary'] ?? 0) }} ₽</div>
    </div>
    <div class="card card-accent-green">
        <div class="card-title">Eng yuqori maosh</div>
        <div class="card-value" style="color:var(--success)">{{ number_format($summary['max_salary'] ?? 0) }}<span class="card-unit">₽</span></div>
        <div class="card-change up">Yillik: {{ number_format(($summary['max_salary'] ?? 0) * 12) }} ₽</div>
    </div>
    <div class="card card-accent-orange">
        <div class="card-title">Maosh ko'rsatilgan</div>
        <div class="card-value" style="color:var(--warning)">{{ number_format($summary['vacancies_with_salary'] ?? 0) }}</div>
        <div class="card-change">{{ $summary['total_vacancies'] > 0 ? round(($summary['vacancies_with_salary'] / $summary['total_vacancies']) * 100) : 0 }}% umumiydan</div>
    </div>
</div>

{{-- Top Recommendations --}}
<div class="animate-in delay-2" style="margin-bottom:20px">
    <div class="card">
        <div class="chart-header">
            <div>
                <div class="chart-title">🏆 Eng Foydali Karriera Yo'nalishlari</div>
                <div class="chart-subtitle">Median maosh bo'yicha top shaharlar</div>
            </div>
        </div>
        @foreach($recs as $rec)
        <div class="rec-card">
            <div class="rank-medal rank-{{ $rec['rank'] }}">{{ $rec['rank'] }}</div>
            <div class="rec-content">
                <div class="rec-zone">{{ $rec['city'] }}</div>
                <div class="rec-metrics">
                    <div class="rec-metric">
                        <strong style="color:var(--success)">{{ number_format($rec['median_salary']) }} ₽</strong>
                        Median maosh/oy
                    </div>
                    <div class="rec-metric">
                        <strong style="color:var(--accent2)">{{ number_format($rec['avg_salary']) }} ₽</strong>
                        O'rtacha maosh/oy
                    </div>
                    <div class="rec-metric">
                        <strong style="color:var(--warning)">{{ number_format($rec['median_salary'] * 12) }} ₽</strong>
                        Yillik daromad
                    </div>
                    <div class="rec-metric">
                        <strong>{{ $rec['best_employer_type'] }}</strong>
                        Tavsiya etilgan sektor
                    </div>
                </div>
                <div class="rec-advice">{{ $rec['advice'] }}</div>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- Charts --}}
<div class="section-row animate-in delay-3">
    <div class="card chart-card">
        <div class="chart-header">
            <div>
                <div class="chart-title">🏙️ Shaharlar Bo'yicha Maosh</div>
                <div class="chart-subtitle">O'rtacha va median maosh (₽/oy)</div>
            </div>
        </div>
        <canvas id="cityChart" height="280"></canvas>
    </div>
    <div class="card chart-card">
        <div class="chart-header">
            <div>
                <div class="chart-title">🏢 Ish Beruvchi Turi</div>
                <div class="chart-subtitle">Median maosh sektori bo'yicha</div>
            </div>
        </div>
        <canvas id="empChart" height="280"></canvas>
    </div>
</div>

<div class="section-row animate-in delay-4">
    <div class="card chart-card">
        <div class="chart-header">
            <div>
                <div class="chart-title">📉 Maosh Taqsimoti Gistogrammasi</div>
            </div>
        </div>
        <canvas id="salHistChart" height="250"></canvas>
    </div>
    <div class="card chart-card">
        <div class="chart-header">
            <div>
                <div class="chart-title">📈 Tajriba vs Maosh</div>
                <div class="chart-subtitle">Tajriba darajasiga ko'ra median maosh</div>
            </div>
        </div>
        <canvas id="expChart" height="250"></canvas>
    </div>
</div>

{{-- Gipoteza natijalari --}}
@if(!empty($hyps))
<div class="animate-in delay-4">
    <div class="card">
        <div class="chart-header">
            <div>
                <div class="chart-title">🔬 Gipotezalar Natijalari</div>
            </div>
            <a href="{{ url('/vacancy/salary') }}" style="font-size:12px;color:var(--accent);text-decoration:none">Batafsil →</a>
        </div>
        <div style="display:flex;gap:12px;flex-wrap:wrap">
            @foreach($hyps as $key => $h)
            @php $confirmed = $h['confirmed'] ?? null; @endphp
            <div style="flex:1;min-width:220px;padding:16px;background:{{ $confirmed ? 'rgba(6,214,160,.06)' : 'rgba(239,71,111,.06)' }};border:1px solid {{ $confirmed ? 'rgba(6,214,160,.25)' : 'rgba(239,71,111,.25)' }};border-radius:12px">
                <div style="font-size:11px;font-weight:700;color:{{ $confirmed ? 'var(--success)' : 'var(--danger)' }};margin-bottom:6px">
                    {{ $confirmed === true ? __('app.confirmed') : ($confirmed === false ? __('app.rejected') : __('app.no_data')) }}
                </div>
                <div style="font-size:13px;font-weight:600">{{ $h['title'] }}</div>
                @if(isset($h['conclusion']))
                <div style="font-size:11px;color:var(--text-muted);margin-top:6px">{{ $h['conclusion'] }}</div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
const cities    = @json($data['city_stats'] ?? []);
const employers = @json($data['employer_stats'] ?? []);
const hist      = @json($data['charts']['salary_histogram'] ?? []);
const expStats  = @json($data['exp_stats'] ?? []);
const colors    = ['#6c63ff','#00b4d8','#ffd166','#06d6a0','#ef476f','#a8dadc','#ff9f43','#ee5a24'];

// Sort cities by median
const cityKeys = Object.keys(cities).sort((a,b) => cities[b].median_salary - cities[a].median_salary);

new Chart(document.getElementById('cityChart'), {
    type: 'bar',
    data: {
        labels: cityKeys,
        datasets: [
            { label: "O'rtacha", data: cityKeys.map(k => cities[k].avg_salary), backgroundColor: 'rgba(108,99,255,0.7)', borderRadius: 6 },
            { label: "Median",   data: cityKeys.map(k => cities[k].median_salary), backgroundColor: 'rgba(0,180,216,0.6)', borderRadius: 6 },
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } },
        scales: {
            x: { grid: { display: false } },
            y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { callback: v => v.toLocaleString('ru-RU') + ' ₽' } }
        }
    }
});

const empKeys = Object.keys(employers).sort((a,b) => employers[b].median_salary - employers[a].median_salary);
new Chart(document.getElementById('empChart'), {
    type: 'bar',
    data: {
        labels: empKeys.map(k => tdl(k)),
        datasets: [{
            label: 'Median maosh',
            data: empKeys.map(k => employers[k].median_salary),
            backgroundColor: colors,
            borderRadius: 8,
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { callback: v => v.toLocaleString('ru-RU') + ' ₽' } },
            y: { grid: { display: false } }
        }
    }
});

new Chart(document.getElementById('salHistChart'), {
    type: 'bar',
    data: {
        labels: (hist.bins || []).map(b => (b/1000).toFixed(0) + 'K ₽'),
        datasets: [{
            label: 'Vakansiyalar soni',
            data: hist.counts || [],
            backgroundColor: 'rgba(0,180,216,0.65)',
            borderRadius: 3,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false }, ticks: { maxTicksLimit: 10 } },
            y: { grid: { color: 'rgba(255,255,255,0.05)' } }
        }
    }
});

const expKeys = Object.keys(expStats).sort((a,b) => (expStats[a].avg_salary || 0) - (expStats[b].avg_salary || 0));
new Chart(document.getElementById('expChart'), {
    type: 'bar',
    data: {
        labels: expKeys.map(k => tdl(k)),
        datasets: [{
            label: 'Median maosh',
            data: expKeys.map(k => expStats[k].median_salary || 0),
            backgroundColor: expKeys.map((_, i) => colors[i % colors.length]),
            borderRadius: 8,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false } },
            y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { callback: v => v.toLocaleString('ru-RU') + ' ₽' } }
        }
    }
});
</script>
@endpush
