@extends('layouts.app')
@section('title', __('app.cities_title'))
@section('breadcrumb')
    <a href="{{ url('/vacancy') }}">{{ __('app.case2_title') }}</a> <span>/</span> {{ __('app.nav_cities') }}
@endsection
@section('page-title', __('app.cities_title'))
@section('page-subtitle', __('app.cities_subtitle'))

@section('content')
@php
    $cities = $data['city_stats'] ?? [];
    arsort($cities);
@endphp

<div class="kpi-grid kpi-grid-3 animate-in delay-1" style="margin-bottom:20px">
    @foreach($cities as $city => $c)
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px">
            <div class="card-title" style="margin-bottom:0">{{ $city }}</div>
            <span class="badge badge-accent">{{ $c['count'] }} vakansiya</span>
        </div>
        <div style="font-size:26px;font-weight:800;margin-bottom:4px">{{ number_format($c['median_salary']) }}<span style="font-size:13px;color:var(--text-secondary)"> ₽/oy</span></div>
        <div style="font-size:12px;color:var(--text-secondary);margin-bottom:12px">O'rtacha: {{ number_format($c['avg_salary']) }} ₽</div>
        <div class="roi-bar-wrap" style="margin-bottom:8px">
            <div class="roi-bar" style="width:{{ min(($c['median_salary'] / 100000) * 100, 100) }}%;background:linear-gradient(90deg,#6c63ff,#00b4d8)"></div>
        </div>
        <div style="display:flex;gap:12px">
            <div style="font-size:11px;color:var(--text-muted)">Min<br><strong style="color:var(--text-primary)">{{ number_format($c['min_salary']) }} ₽</strong></div>
            <div style="font-size:11px;color:var(--text-muted)">Q25<br><strong style="color:var(--text-primary)">{{ number_format($c['q25']) }} ₽</strong></div>
            <div style="font-size:11px;color:var(--text-muted)">Q75<br><strong style="color:var(--text-primary)">{{ number_format($c['q75']) }} ₽</strong></div>
            <div style="font-size:11px;color:var(--text-muted)">Max<br><strong style="color:var(--text-primary)">{{ number_format($c['max_salary']) }} ₽</strong></div>
        </div>
    </div>
    @endforeach
</div>

<div class="section-row animate-in delay-2">
    <div class="card chart-card">
        <div class="chart-header">
            <div><div class="chart-title">📊 Shaharlar Bo'yicha Maosh Diapazonlari</div></div>
        </div>
        <canvas id="cityRangeChart" height="300"></canvas>
    </div>
    <div class="card chart-card">
        <div class="chart-header">
            <div><div class="chart-title">🌍 Vakansiyalar Soni Bo'yicha</div></div>
        </div>
        <canvas id="cityCountChart" height="300"></canvas>
    </div>
</div>

<div class="card animate-in delay-3">
    <div class="chart-header">
        <div><div class="chart-title">📋 Batafsil Jadval</div></div>
    </div>
    <div class="table-wrapper">
        <table class="analytics-table" id="cityTable">
            <thead>
                <tr>
                    <th>Shahar</th>
                    <th>Vakansiyalar</th>
                    <th>O'rtacha (₽)</th>
                    <th>Median (₽)</th>
                    <th>Min (₽)</th>
                    <th>Max (₽)</th>
                    <th>Q25 (₽)</th>
                    <th>Q75 (₽)</th>
                    <th>Yillik (Median)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cities as $city => $c)
                <tr>
                    <td><strong>{{ $city }}</strong></td>
                    <td>{{ $c['count'] }}</td>
                    <td>{{ number_format($c['avg_salary']) }}</td>
                    <td><strong>{{ number_format($c['median_salary']) }}</strong></td>
                    <td>{{ number_format($c['min_salary']) }}</td>
                    <td>{{ number_format($c['max_salary']) }}</td>
                    <td>{{ number_format($c['q25']) }}</td>
                    <td>{{ number_format($c['q75']) }}</td>
                    <td><span class="badge badge-success">{{ number_format($c['median_salary'] * 12) }} ₽</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
const cities = @json($cities);
const cityKeys = Object.keys(cities).sort((a,b) => cities[b].median_salary - cities[a].median_salary);
const colors = ['#6c63ff','#00b4d8','#ffd166','#06d6a0','#ef476f','#a8dadc','#ff9f43','#ee5a24'];

new Chart(document.getElementById('cityRangeChart'), {
    type: 'bar',
    data: {
        labels: cityKeys,
        datasets: [
            { label: 'Min', data: cityKeys.map(k => cities[k].q25), backgroundColor: 'rgba(108,99,255,0.3)', borderRadius: 4, stack: 'a' },
            { label: 'Q25–Q75', data: cityKeys.map(k => cities[k].q75 - cities[k].q25), backgroundColor: colors.map(c => c + 'aa'), borderRadius: 4, stack: 'a' },
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } },
        scales: {
            x: { grid: { display: false }, stacked: true },
            y: { stacked: true, grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { callback: v => (v/1000).toFixed(0) + 'K ₽' } }
        }
    }
});

new Chart(document.getElementById('cityCountChart'), {
    type: 'doughnut',
    data: {
        labels: cityKeys,
        datasets: [{ data: cityKeys.map(k => cities[k].count), backgroundColor: colors, borderWidth: 2, borderColor: '#0a0a1a' }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'right' } },
        cutout: '55%',
    }
});

$(document).ready(() => $('#cityTable').DataTable({ order: [[2, 'desc']], pageLength: 10 }));
</script>
@endpush
