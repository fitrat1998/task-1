@extends('layouts.app')
@section('title', __('app.roi_title'))
@section('breadcrumb')
    <a href="{{ url('/kbr') }}">KBR</a> <span>/</span> {{ __('app.nav_roi') }}
@endsection
@section('page-title', __('app.roi_title'))
@section('page-subtitle', __('app.roi_subtitle'))

@section('content')
@php
    $roiTable = $data['roi_table'] ?? [];
    $budgets  = [5000000, 6000000, 7000000];
@endphp

{{-- Summary KPIs --}}
<div class="kpi-grid kpi-grid-4 animate-in delay-1">
    @php $best = $roiTable[0] ?? []; $worst = end($roiTable) ?: []; @endphp
    <div class="card card-accent-green">
        <div class="card-title">Eng Yuqori ROI</div>
        <div class="card-value" style="color:var(--success)">{{ $best['roi_percent'] ?? 0 }}%</div>
        <div class="card-change up">📍 {{ $best['display_name'] ?? '—' }}</div>
    </div>
    <div class="card card-accent-blue">
        <div class="card-title">Tez Amortizatsiya</div>
        <div class="card-value" style="color:var(--accent2)">{{ $best['payback_years'] ?? 0 }}<span class="card-unit">yil</span></div>
        <div class="card-change">Yillik daromad: {{ number_format($best['net_annual'] ?? 0) }} ₽</div>
    </div>
    <div class="card card-accent-purple">
        <div class="card-title">O'rtacha ROI</div>
        @php $avgRoi = collect($roiTable)->avg('roi_percent'); @endphp
        <div class="card-value">{{ round($avgRoi, 1) }}%</div>
        <div class="card-change">Barcha zonalar bo'yicha</div>
    </div>
    <div class="card card-accent-orange">
        <div class="card-title">Xarajatlar Ulushi</div>
        <div class="card-value" style="color:var(--warning)">15%</div>
        <div class="card-change">Gross daromaddan</div>
    </div>
</div>

{{-- Methodology --}}
<div class="alert alert-info animate-in delay-2">
    <span>ℹ️</span>
    <div>
        <strong>Hisoblash metodologiyasi:</strong>
        ROI = (O'rtacha narx × Yuklama kunlari × 0.85) / Sotib olish narxi × 100%.
        Xarajatlar (kommunal, boshqaruv): 15%.
        Yuklama: Prielbrusye — 230 kun, Nalchik — 182 kun, Tyrnyauz — 140 kun.
    </div>
</div>

{{-- Main ROI table --}}
<div class="card animate-in delay-2" style="margin-bottom:20px">
    <div class="chart-header">
        <div>
            <div class="chart-title">📋 Byudjet Ssenariylar Jadvali</div>
            <div class="chart-subtitle">5 mln / 6 mln / 7 mln byudjet uchun ROI va amortizatsiya</div>
        </div>
    </div>
    <div class="table-wrapper">
        <table class="analytics-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Zona</th>
                    <th>Kundal. narx</th>
                    <th>Yukl. kunlar</th>
                    <th>Yillik brutto</th>
                    <th>Yillik sof</th>
                    <th>ROI (real)</th>
                    <th>ROI 5M</th>
                    <th>ROI 6M</th>
                    <th>ROI 7M</th>
                    <th>Amort. (real)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($roiTable as $i => $r)
                <tr>
                    <td><div class="rank-medal rank-{{ $i+1 <= 3 ? $i+1 : '' }}" style="{{ $i >= 3 ? 'background:var(--bg-card);color:var(--text-secondary);font-size:12px' : '' }}">{{ $i+1 }}</div></td>
                    <td><strong>{{ $r['display_name'] }}</strong></td>
                    <td>{{ number_format($r['avg_daily_price']) }} ₽</td>
                    <td>{{ $r['occupancy_days'] }} kun</td>
                    <td>{{ number_format($r['gross_annual']) }} ₽</td>
                    <td><strong style="color:var(--success)">{{ number_format($r['net_annual']) }} ₽</strong></td>
                    <td>
                        <span class="badge {{ $r['roi_percent'] >= 20 ? 'badge-success' : ($r['roi_percent'] >= 14 ? 'badge-warning' : 'badge-danger') }}">
                            {{ $r['roi_percent'] }}%
                        </span>
                    </td>
                    @foreach($r['budget_scenarios'] as $sc)
                    <td>
                        <span style="font-size:12px;color:{{ $sc['roi_percent'] >= 15 ? 'var(--success)' : ($sc['roi_percent'] >= 8 ? 'var(--warning)' : 'var(--danger)') }}">
                            {{ $sc['roi_percent'] }}%
                        </span>
                    </td>
                    @endforeach
                    <td>{{ $r['payback_years'] }} yil</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Charts --}}
<div class="section-row animate-in delay-3">
    <div class="card chart-card">
        <div class="chart-header">
            <div>
                <div class="chart-title">📊 ROI Solishtirma Grafigi</div>
                <div class="chart-subtitle">Zonalar bo'yicha ROI (real sotib olish narxida)</div>
            </div>
        </div>
        <canvas id="roiBarChart" height="280"></canvas>
    </div>
    <div class="card chart-card">
        <div class="chart-header">
            <div>
                <div class="chart-title">📈 Byudjet Ssenariylar</div>
                <div class="chart-subtitle">5M / 6M / 7M byudjetda ROI eng yaxshi zona</div>
            </div>
        </div>
        <canvas id="scenarioChart" height="280"></canvas>
    </div>
</div>

{{-- ROI progress bars --}}
<div class="card animate-in delay-4">
    <div class="chart-header">
        <div><div class="chart-title">🎯 ROI Vizual Ko'rsatkichi</div></div>
    </div>
    @foreach($roiTable as $r)
    <div style="margin-bottom:20px">
        <div style="display:flex;justify-content:space-between;margin-bottom:8px">
            <span style="font-size:13px;font-weight:600">{{ $r['display_name'] }}</span>
            <span style="font-size:13px;color:var(--success);font-weight:700">{{ $r['roi_percent'] }}% ROI · {{ $r['payback_years'] }} yil amortizatsiya</span>
        </div>
        <div class="roi-bar-wrap">
            <div class="roi-bar" style="width:{{ min($r['roi_percent'] * 2.5, 100) }}%"></div>
        </div>
        <div style="font-size:11px;color:var(--text-muted);margin-top:4px">
            Kunlik o'rtacha: {{ number_format($r['avg_daily_price']) }} ₽ · Yillik sof: {{ number_format($r['net_annual']) }} ₽
        </div>
    </div>
    @endforeach
</div>
@endsection

@push('scripts')
<script>
const roi = @json($roiTable);
const colors = ['#06d6a0','#6c63ff','#00b4d8','#ffd166','#ef476f','#a8dadc'];

// ROI bar
new Chart(document.getElementById('roiBarChart'), {
    type: 'bar',
    data: {
        labels: roi.map(r => r.display_name),
        datasets: [{
            label: 'ROI %',
            data: roi.map(r => r.roi_percent),
            backgroundColor: colors,
            borderRadius: 8,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false } },
            y: {
                grid: { color: 'rgba(255,255,255,0.05)' },
                ticks: { callback: v => v + '%' }
            }
        }
    }
});

// Scenario grouped bar
const budgetLabels = ['5 mln ₽', '6 mln ₽', '7 mln ₽'];
const top3 = roi.slice(0, 3);
new Chart(document.getElementById('scenarioChart'), {
    type: 'bar',
    data: {
        labels: budgetLabels,
        datasets: top3.map((r, i) => ({
            label: r.display_name,
            data: r.budget_scenarios.map(s => s.roi_percent),
            backgroundColor: colors[i],
            borderRadius: 6,
        }))
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } },
        scales: {
            x: { grid: { display: false } },
            y: {
                grid: { color: 'rgba(255,255,255,0.05)' },
                ticks: { callback: v => v + '%' }
            }
        }
    }
});
</script>
@endpush
