@extends('layouts.app')
@section('title', __('app.kbr_title'))
@section('breadcrumb')
    <a href="{{ url('/kbr') }}">KBR</a> <span>/</span> {{ __('app.nav_overview') }}
@endsection
@section('page-title', __('app.kbr_title'))
@section('page-subtitle', __('app.kbr_subtitle'))

@section('content')
@php
    $summary = $data['summary'] ?? [];
    $recs    = $data['top_recommendations'] ?? [];
    $zones   = $data['zone_stats'] ?? [];
    $charts  = $data['charts'] ?? [];
    $hyps    = $data['hypotheses'] ?? [];

    $best = collect($data['roi_table'] ?? [])->first();
@endphp

{{-- KPI Cards --}}
<div class="kpi-grid kpi-grid-4 animate-in delay-1">
    <div class="card card-accent-purple">
        <div class="card-title">Jami e'lonlar</div>
        <div class="card-value">{{ number_format($summary['total_listings'] ?? 0) }}</div>
        <div class="card-change">📌 Barcha platformalar</div>
    </div>
    <div class="card card-accent-blue">
        <div class="card-title">O'rtacha narx/kecha</div>
        <div class="card-value">{{ number_format($summary['avg_price'] ?? 0) }}<span class="card-unit">₽</span></div>
        <div class="card-change">Median: {{ number_format($summary['median_price'] ?? 0) }} ₽</div>
    </div>
    <div class="card card-accent-green">
        <div class="card-title">Eng yuqori ROI</div>
        <div class="card-value" style="color:var(--success)">{{ $best['roi_percent'] ?? 0 }}<span class="card-unit">%</span></div>
        <div class="card-change up">📍 {{ $best['display_name'] ?? '—' }}</div>
    </div>
    <div class="card card-accent-orange">
        <div class="card-title">Tez amortizatsiya</div>
        <div class="card-value" style="color:var(--warning)">{{ $best['payback_years'] ?? 0 }}<span class="card-unit">yil</span></div>
        <div class="card-change">💰 Sof daromad: {{ number_format($best['net_annual'] ?? 0) }} ₽/yil</div>
    </div>
</div>

{{-- Top Recommendations --}}
<div class="animate-in delay-2" style="margin-bottom:20px">
    <div class="card">
        <div class="chart-header">
            <div>
                <div class="chart-title">🏆 Top Tavsiyalar</div>
                <div class="chart-subtitle">ROI bo'yicha eng yaxshi 3 ta investitsiya varianti</div>
            </div>
            <a href="{{ url('/kbr/roi') }}" style="font-size:12px;color:var(--accent);text-decoration:none">Barchasini ko'rish →</a>
        </div>
        @foreach($recs as $rec)
        <div class="rec-card">
            <div class="rank-medal rank-{{ $rec['rank'] }}">{{ $rec['rank'] }}</div>
            <div class="rec-content">
                <div class="rec-zone">{{ $rec['zone'] }}</div>
                <div class="rec-metrics">
                    <div class="rec-metric">
                        <strong>{{ number_format($rec['avg_daily_price']) }} ₽</strong>
                        O'rtacha narx/kecha
                    </div>
                    <div class="rec-metric">
                        <strong style="color:var(--success)">{{ $rec['roi_percent'] }}%</strong>
                        ROI yillik
                    </div>
                    <div class="rec-metric">
                        <strong style="color:var(--warning)">{{ $rec['payback_years'] }} yil</strong>
                        Amortizatsiya
                    </div>
                    <div class="rec-metric">
                        <strong style="color:var(--accent2)">{{ number_format($rec['net_annual']) }} ₽</strong>
                        Sof yillik daromad
                    </div>
                </div>
                <div class="rec-advice">{{ $rec['advice'] }}</div>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- Charts Row --}}
<div class="section-row animate-in delay-3">
    {{-- Bar chart: avg price by zone --}}
    <div class="card chart-card">
        <div class="chart-header">
            <div>
                <div class="chart-title">📊 Lokatsiyalar Bo'yicha Narxlar</div>
                <div class="chart-subtitle">O'rtacha va median narxlar (₽/kecha)</div>
            </div>
        </div>
        <canvas id="zoneChart" height="250"></canvas>
    </div>

    {{-- ROI donut --}}
    <div class="card chart-card">
        <div class="chart-header">
            <div>
                <div class="chart-title">🎯 ROI Solishtirish</div>
                <div class="chart-subtitle">Zonalar bo'yicha yillik daromadlilik (%)</div>
            </div>
        </div>
        <canvas id="roiChart" height="250"></canvas>
    </div>
</div>

{{-- Histogram + Zones table --}}
<div class="section-row animate-in delay-4">
    <div class="card chart-card">
        <div class="chart-header">
            <div>
                <div class="chart-title">📉 Narxlar Taqsimoti</div>
                <div class="chart-subtitle">Gistogramma (barcha e'lonlar)</div>
            </div>
        </div>
        <canvas id="histChart" height="220"></canvas>
    </div>

    <div class="card">
        <div class="chart-header">
            <div>
                <div class="chart-title">🗺️ Zonalar Statistikasi</div>
            </div>
        </div>
        <table class="analytics-table">
            <thead><tr><th>Zona</th><th>E'lonlar</th><th>O'rtacha ₽</th></tr></thead>
            <tbody>
                @foreach($zones as $key => $z)
                <tr>
                    <td><strong>{{ $z['display_name'] }}</strong></td>
                    <td><span class="badge badge-accent">{{ $z['count'] }}</span></td>
                    <td>{{ number_format($z['avg_price']) }} ₽</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Gipotezalar qisqa --}}
@if(!empty($hyps))
<div class="animate-in delay-4">
    <div class="card" style="margin-bottom:0">
        <div class="chart-header">
            <div>
                <div class="chart-title">🔬 Gipoteza Natijalari</div>
                <div class="chart-subtitle">Statistik tekshiruv natijalari</div>
            </div>
            <a href="{{ url('/kbr/hypotheses') }}" style="font-size:12px;color:var(--accent);text-decoration:none">Batafsil →</a>
        </div>
        <div style="display:flex;gap:12px;flex-wrap:wrap">
            @foreach($hyps as $key => $h)
            <div style="flex:1;min-width:200px;padding:16px;background:{{ $h['confirmed'] ? 'rgba(6,214,160,.06)' : 'rgba(239,71,111,.06)' }};border:1px solid {{ $h['confirmed'] ? 'rgba(6,214,160,.25)' : 'rgba(239,71,111,.25)' }};border-radius:12px">
                <div style="font-size:11px;font-weight:700;text-transform:uppercase;color:{{ $h['confirmed'] ? 'var(--success)' : 'var(--danger)' }};margin-bottom:6px">
                    {{ $h['confirmed'] ? __('app.confirmed') : __('app.rejected') }}
                </div>
                <div style="font-size:13px;font-weight:600">{{ $h['title'] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
const zoneData = @json($charts['zones_comparison'] ?? []);
const roiTable = @json($data['roi_table'] ?? []);
const histData = @json($charts['price_histogram'] ?? []);

// Zone bar chart
new Chart(document.getElementById('zoneChart'), {
    type: 'bar',
    data: {
        labels: zoneData.labels || [],
        datasets: [
            {
                label: "O'rtacha narx",
                data: zoneData.avg_prices || [],
                backgroundColor: 'rgba(108,99,255,0.7)',
                borderColor: '#6c63ff',
                borderWidth: 1,
                borderRadius: 6,
            },
            {
                label: "Median narx",
                data: zoneData.median_prices || [],
                backgroundColor: 'rgba(0,180,216,0.5)',
                borderColor: '#00b4d8',
                borderWidth: 1,
                borderRadius: 6,
            }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } },
        scales: {
            x: { grid: { display: false } },
            y: {
                grid: { color: 'rgba(255,255,255,0.05)' },
                ticks: { callback: v => v.toLocaleString('ru-RU') + ' ₽' }
            }
        }
    }
});

// ROI horizontal bar
new Chart(document.getElementById('roiChart'), {
    type: 'bar',
    data: {
        labels: roiTable.map(r => r.display_name),
        datasets: [{
            label: 'ROI %',
            data: roiTable.map(r => r.roi_percent),
            backgroundColor: roiTable.map((r, i) => {
                const colors = ['rgba(6,214,160,0.8)', 'rgba(108,99,255,0.8)', 'rgba(0,180,216,0.7)', 'rgba(255,209,102,0.7)', 'rgba(239,71,111,0.7)', 'rgba(150,150,200,0.6)'];
                return colors[i % colors.length];
            }),
            borderRadius: 6,
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: {
                grid: { color: 'rgba(255,255,255,0.05)' },
                ticks: { callback: v => v + '%' }
            },
            y: { grid: { display: false } }
        }
    }
});

// Histogram
new Chart(document.getElementById('histChart'), {
    type: 'bar',
    data: {
        labels: (histData.bins || []).map(b => b.toLocaleString('ru-RU') + ' ₽'),
        datasets: [{
            label: "E'lonlar soni",
            data: histData.counts || [],
            backgroundColor: 'rgba(108,99,255,0.6)',
            borderRadius: 3,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false }, ticks: { maxRotation: 45, maxTicksLimit: 10 } },
            y: { grid: { color: 'rgba(255,255,255,0.05)' } }
        }
    }
});
</script>
@endpush
