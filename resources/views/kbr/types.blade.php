@extends('layouts.app')
@section('title', __('app.types_title'))
@section('breadcrumb')
    <a href="{{ url('/kbr') }}">KBR</a> <span>/</span> {{ __('app.nav_types') }}
@endsection
@section('page-title', __('app.types_title'))
@section('page-subtitle', __('app.types_subtitle'))

@section('content')
@php
    $types  = $data['type_stats'] ?? [];
    $heatmap = $data['heatmap'] ?? [];
    arsort($types);
@endphp

{{-- Type cards --}}
<div class="kpi-grid kpi-grid-3 animate-in delay-1" style="margin-bottom:20px">
    @foreach(array_slice($types, 0, 6) as $typeName => $t)
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px">
            <div class="card-title" style="margin-bottom:0">{{ $typeName ?: 'Noma\'lum' }}</div>
            <span class="badge badge-blue">{{ $t['count'] }} ta</span>
        </div>
        <div style="font-size:24px;font-weight:800;margin-bottom:4px">
            {{ number_format($t['avg_price']) }} <span style="font-size:13px;color:var(--text-secondary)">₽/kecha</span>
        </div>
        <div style="font-size:12px;color:var(--text-secondary);margin-bottom:10px">
            Median: {{ number_format($t['median_price']) }} ₽
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:6px">
            @foreach(array_slice($t['zones'], 0, 3, true) as $zone => $cnt)
            <span class="badge badge-accent" style="font-size:10px">{{ $zone }}: {{ $cnt }}</span>
            @endforeach
        </div>
    </div>
    @endforeach
</div>

{{-- Type bar chart --}}
<div class="section-row animate-in delay-2">
    <div class="card chart-card">
        <div class="chart-header">
            <div>
                <div class="chart-title">📊 Tur Bo'yicha O'rtacha Narxlar</div>
                <div class="chart-subtitle">₽/kecha (e'lonlar 3+ bo'lgan turlar)</div>
            </div>
        </div>
        <canvas id="typeChart" height="280"></canvas>
    </div>
    <div class="card chart-card">
        <div class="chart-header">
            <div>
                <div class="chart-title">🥧 E'lonlar Taqsimoti</div>
                <div class="chart-subtitle">Tur bo'yicha ulush (%)</div>
            </div>
        </div>
        <canvas id="typePieChart" height="280"></canvas>
    </div>
</div>

{{-- Heatmap table --}}
<div class="card animate-in delay-3">
    <div class="chart-header">
        <div>
            <div class="chart-title">🔥 Issiqlik Xaritasi: Zona × Tur</div>
            <div class="chart-subtitle">O'rtacha ijara narxi ₽/kecha. Yashil = qimmat, qizil = arzon</div>
        </div>
    </div>
    <div class="table-wrapper">
        <table class="analytics-table">
            <thead>
                <tr>
                    <th>Zona</th>
                    @foreach($heatmap['types'] ?? [] as $t)
                    <th>{{ $t ?: '—' }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($heatmap['zones'] ?? [] as $zi => $zone)
                <tr>
                    <td><strong>{{ $zone }}</strong></td>
                    @foreach($heatmap['values'][$zi] ?? [] as $val)
                    @php
                        $bgOpacity = $val ? min(($val / 15000), 0.8) : 0;
                        $color = $val ? "rgba(108,99,255,{$bgOpacity})" : 'transparent';
                    @endphp
                    <td style="background:{{ $color }};text-align:center;font-size:12px">
                        {{ $val ? number_format($val) . ' ₽' : '—' }}
                    </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
const types = @json($types);
const tKeys = Object.keys(types).slice(0, 8);
const colors = ['#6c63ff','#00b4d8','#ffd166','#06d6a0','#ef476f','#a8dadc','#ff9f43','#ee5a24'];

new Chart(document.getElementById('typeChart'), {
    type: 'bar',
    data: {
        labels: tKeys.map(k => k || 'Noma\'lum'),
        datasets: [{
            label: "O'rtacha narx",
            data: tKeys.map(k => types[k].avg_price),
            backgroundColor: colors,
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

new Chart(document.getElementById('typePieChart'), {
    type: 'doughnut',
    data: {
        labels: tKeys.map(k => k || 'Noma\'lum'),
        datasets: [{
            data: tKeys.map(k => types[k].count),
            backgroundColor: colors,
            borderWidth: 2,
            borderColor: '#0a0a1a',
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: {
            legend: { position: 'right' },
            tooltip: { callbacks: { label: ctx => `${ctx.label}: ${ctx.raw} ta (${((ctx.raw/Object.values(types).reduce((a,b)=>a+b.count,0))*100).toFixed(1)}%)` } }
        },
        cutout: '60%',
    }
});
</script>
@endpush
