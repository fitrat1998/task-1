@extends('layouts.app')
@section('title', __('app.hyp_title'))
@section('breadcrumb')
    <a href="{{ url('/kbr') }}">KBR</a> <span>/</span> {{ __('app.nav_hypotheses') }}
@endsection
@section('page-title', __('app.hyp_title'))
@section('page-subtitle', __('app.hyp_subtitle'))

@section('content')
@php $hyps = $data['hypotheses'] ?? []; @endphp

<div class="alert alert-info animate-in">
    <span>📊</span>
    <div>
        <strong>Metodologiya:</strong> Gipotezalar <strong>Mann-Whitney U</strong> nonparametrik testi yordamida tekshirilgan.
        Ahamiyatlilik darajasi: <strong>α = 0.05</strong>. p &lt; 0.05 bo'lsa gipoteza tasdiqlanadi.
    </div>
</div>

<div class="animate-in delay-1">
@foreach($hyps as $key => $h)
<div class="hypothesis-card {{ $h['confirmed'] ? 'hyp-confirmed' : 'hyp-rejected' }}">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px">
        <span style="font-size:24px">{{ $h['confirmed'] ? '✅' : '❌' }}</span>
        <div>
            <div class="hyp-title">{{ $h['title'] }}</div>
            <span class="badge {{ $h['confirmed'] ? 'badge-success' : 'badge-danger' }}">
                {{ $h['confirmed'] ? __('app.confirmed') : __('app.rejected') }}
            </span>
        </div>
    </div>

    <div class="hyp-stats">
        @if(isset($h['p_value']))
        <div class="hyp-stat">
            <strong>{{ $h['p_value'] }}</strong>
            p-qiymat
        </div>
        @endif
        @if(isset($h['nalchik_avg']))
        <div class="hyp-stat">
            <strong>{{ number_format($h['nalchik_avg']) }} ₽</strong>
            Nalchik o'rtacha
        </div>
        @endif
        @if(isset($h['prielbrusye_avg']))
        <div class="hyp-stat">
            <strong>{{ number_format($h['prielbrusye_avg']) }} ₽</strong>
            Prielbrusye o'rtacha
        </div>
        @endif
        @if(isset($h['tyrnyauz_avg']))
        <div class="hyp-stat">
            <strong>{{ number_format($h['tyrnyauz_avg']) }} ₽</strong>
            Tyrnyauz o'rtacha
        </div>
        @endif
        @if(isset($h['diff_percent']))
        <div class="hyp-stat">
            <strong style="color:{{ $h['diff_percent'] > 0 ? 'var(--success)' : 'var(--danger)' }}">
                {{ $h['diff_percent'] > 0 ? '+' : '' }}{{ $h['diff_percent'] }}%
            </strong>
            Farq
        </div>
        @endif
        @if(isset($h['source_averages']))
        @foreach($h['source_averages'] as $src => $avg)
        <div class="hyp-stat">
            <strong>{{ number_format($avg) }} ₽</strong>
            {{ ucfirst($src) }}
        </div>
        @endforeach
        @endif
    </div>

    <div class="hyp-conclusion">
        💬 {{ $h['conclusion'] }}
    </div>
</div>
@endforeach
</div>

{{-- Visualization --}}
@php $h1 = $hyps['h1'] ?? null; @endphp
@if($h1 && isset($h1['nalchik_avg'], $h1['prielbrusye_avg']))
<div class="card chart-card animate-in delay-2">
    <div class="chart-header">
        <div>
            <div class="chart-title">📊 G1: Prielbrusye vs Nalchik Narx Solishtirmasi</div>
            <div class="chart-subtitle">O'rtacha kunlik ijara narxi</div>
        </div>
    </div>
    <canvas id="h1Chart" height="180"></canvas>
</div>
@endif

{{-- Platform comparison --}}
@php $h3 = $hyps['h3'] ?? null; @endphp
@if($h3 && isset($h3['source_averages']))
<div class="card chart-card animate-in delay-3">
    <div class="chart-header">
        <div>
            <div class="chart-title">🌐 G3: Platformalar Bo'yicha O'rtacha Narxlar</div>
            <div class="chart-subtitle">Har bir platforma o'rtacha ₽/kecha</div>
        </div>
    </div>
    <canvas id="h3Chart" height="180"></canvas>
</div>
@endif
@endsection

@push('scripts')
<script>
@php $h1 = $hyps['h1'] ?? null; $h3 = $hyps['h3'] ?? null; @endphp

@if($h1 && isset($h1['nalchik_avg'], $h1['prielbrusye_avg']))
new Chart(document.getElementById('h1Chart'), {
    type: 'bar',
    data: {
        labels: ['Nalchik', 'Prielbrusye'],
        datasets: [{
            data: [{{ $h1['nalchik_avg'] }}, {{ $h1['prielbrusye_avg'] }}],
            backgroundColor: ['rgba(239,71,111,0.7)', 'rgba(6,214,160,0.8)'],
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
            y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { callback: v => v.toLocaleString('ru-RU') + ' ₽' } }
        }
    }
});
@endif

@if($h3 && isset($h3['source_averages']))
const h3Sources = @json($h3['source_averages']);
new Chart(document.getElementById('h3Chart'), {
    type: 'bar',
    data: {
        labels: Object.keys(h3Sources).map(s => s.charAt(0).toUpperCase() + s.slice(1)),
        datasets: [{
            data: Object.values(h3Sources),
            backgroundColor: Object.keys(h3Sources).map((_, i) => `hsl(${220 + i*25}, 70%, 60%)`),
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
@endif
</script>
@endpush
