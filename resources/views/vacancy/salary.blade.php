@extends('layouts.app')
@section('title', __('app.sal_title'))
@section('breadcrumb')
    <a href="{{ url('/vacancy') }}">{{ __('app.case2_title') }}</a> <span>/</span> {{ __('app.nav_salary') }}
@endsection
@section('page-title', __('app.sal_title'))
@section('page-subtitle', __('app.sal_subtitle'))

@section('content')
@php
    $hyps    = $data['hypotheses'] ?? [];
    $cities  = $data['city_stats'] ?? [];
    $exp     = $data['exp_stats'] ?? [];
    $summary = $data['summary'] ?? [];
@endphp

{{-- KPI --}}
<div class="kpi-grid kpi-grid-4 animate-in delay-1">
    <div class="card card-accent-purple">
        <div class="card-title">Median Maosh</div>
        <div class="card-value">{{ number_format($summary['median_salary'] ?? 0) }}<span class="card-unit">₽</span></div>
        <div class="card-change">Barcha shaharlar bo'yicha</div>
    </div>
    <div class="card card-accent-blue">
        <div class="card-title">Moskva Median</div>
        <div class="card-value">{{ number_format($cities['Moskva']['median_salary'] ?? 0) }}<span class="card-unit">₽</span></div>
        <div class="card-change up">+{{ isset($cities['Moskva'], $summary['median_salary']) && $summary['median_salary'] > 0 ? round((($cities['Moskva']['median_salary'] - $summary['median_salary']) / $summary['median_salary']) * 100) : 0 }}% vs o'rtacha</div>
    </div>
    <div class="card card-accent-green">
        <div class="card-title">3–6 Yil Tajriba</div>
        <div class="card-value" style="color:var(--success)">{{ number_format($exp['3–6 yil']['median_salary'] ?? $exp['3-6 yil']['median_salary'] ?? 0) }}<span class="card-unit">₽</span></div>
        <div class="card-change up">Optimal tajriba darajasi</div>
    </div>
    <div class="card card-accent-orange">
        <div class="card-title">Yillik Potentsial</div>
        @php $maxCity = collect($cities)->sortByDesc('median_salary')->first(); @endphp
        <div class="card-value" style="color:var(--warning)">{{ number_format(($maxCity['median_salary'] ?? 0) * 12) }}<span class="card-unit">₽</span></div>
        <div class="card-change">Eng yuqori shahar bo'yicha</div>
    </div>
</div>

{{-- Gipotezalar --}}
<div class="animate-in delay-2" style="margin-bottom:20px">
    @foreach($hyps as $key => $h)
    @php $confirmed = $h['confirmed'] ?? null; @endphp
    <div class="hypothesis-card {{ $confirmed === true ? 'hyp-confirmed' : ($confirmed === false ? 'hyp-rejected' : '') }}" style="{{ $confirmed === null ? 'background:rgba(255,209,102,.06);border:1px solid rgba(255,209,102,.25)' : '' }}">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px">
            <span style="font-size:28px">{{ $confirmed === true ? '✅' : ($confirmed === false ? '❌' : '⚠️') }}</span>
            <div>
                <div class="hyp-title">{{ $h['title'] }}</div>
                <span class="badge {{ $confirmed === true ? 'badge-success' : ($confirmed === false ? 'badge-danger' : 'badge-warning') }}">
                    {{ $confirmed === true ? __('app.confirmed') : ($confirmed === false ? __('app.rejected') : __('app.no_data')) }}
                </span>
            </div>
        </div>
        <div class="hyp-stats">
            @if(isset($h['p_value']))
            <div class="hyp-stat"><strong>{{ $h['p_value'] }}</strong> p-qiymat</div>
            @endif
            @if(isset($h['moscow_avg']))
            <div class="hyp-stat"><strong>{{ number_format($h['moscow_avg']) }} ₽</strong> Moskva o'rtacha</div>
            @endif
            @if(isset($h['other_avg']))
            <div class="hyp-stat"><strong>{{ number_format($h['other_avg']) }} ₽</strong> Mintaqalar o'rtacha</div>
            @endif
            @if(isset($h['bank_avg']))
            <div class="hyp-stat"><strong>{{ number_format($h['bank_avg']) }} ₽</strong> Bank sektori</div>
            @endif
            @if(isset($h['eval_avg']))
            <div class="hyp-stat"><strong>{{ number_format($h['eval_avg']) }} ₽</strong> Baholash kompaniyasi</div>
            @endif
            @if(isset($h['remote_avg']))
            <div class="hyp-stat"><strong>{{ number_format($h['remote_avg']) }} ₽</strong> Masofaviy ish</div>
            @endif
            @if(isset($h['office_avg']))
            <div class="hyp-stat"><strong>{{ number_format($h['office_avg']) }} ₽</strong> Ofis ishi</div>
            @endif
            @if(isset($h['diff_percent']))
            <div class="hyp-stat">
                <strong style="color:{{ $h['diff_percent'] > 0 ? 'var(--success)' : 'var(--danger)' }}">
                    {{ $h['diff_percent'] > 0 ? '+' : '' }}{{ $h['diff_percent'] }}%
                </strong>
                Farq
            </div>
            @endif
        </div>
        <div class="hyp-conclusion">💬 {{ $h['conclusion'] }}</div>
    </div>
    @endforeach
</div>

{{-- Charts --}}
<div class="section-row animate-in delay-3">
    <div class="card chart-card">
        <div class="chart-header">
            <div><div class="chart-title">🏙️ G1: Moskva vs Mintaqalar</div></div>
        </div>
        <canvas id="moscowChart" height="250"></canvas>
    </div>
    <div class="card chart-card">
        <div class="chart-header">
            <div><div class="chart-title">📈 Tajriba vs Maosh</div></div>
        </div>
        <canvas id="expChart" height="250"></canvas>
    </div>
</div>
@endsection

@push('scripts')
<script>
const hyps   = @json($hyps);
const cities = @json($cities);
const exp    = @json($exp);

// Moskva chart
const h1 = hyps.h1 || {};
if (h1.moscow_avg && h1.other_avg) {
    new Chart(document.getElementById('moscowChart'), {
        type: 'bar',
        data: {
            labels: [tdl('Moskva') || 'Moscow', tdl('Boshqa mintaqalar')],
            datasets: [{
                data: [h1.moscow_avg, h1.other_avg],
                backgroundColor: ['rgba(6,214,160,0.8)', 'rgba(239,71,111,0.7)'],
                borderRadius: 10,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false },
                tooltip: { callbacks: { label: ctx => ctx.raw.toLocaleString('ru-RU') + ' ₽' } }
            },
            scales: {
                x: { grid: { display: false } },
                y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { callback: v => (v/1000).toFixed(0) + 'K ₽' } }
            }
        }
    });
}

// Exp chart
const expKeys = Object.keys(exp).filter(k => exp[k].median_salary > 0);
new Chart(document.getElementById('expChart'), {
    type: 'line',
    data: {
        labels: expKeys.map(k => tdl(k)),
        datasets: [{
            label: 'Median maosh',
            data: expKeys.map(k => exp[k].median_salary),
            borderColor: '#6c63ff',
            backgroundColor: 'rgba(108,99,255,0.15)',
            pointBackgroundColor: '#6c63ff',
            pointRadius: 8,
            tension: 0.3,
            fill: true,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false } },
            y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { callback: v => (v/1000).toFixed(0) + 'K ₽' } }
        }
    }
});
</script>
@endpush
