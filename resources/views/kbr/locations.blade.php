@extends('layouts.app')
@section('title', __('app.loc_title'))
@section('breadcrumb')
    <a href="{{ url('/kbr') }}">KBR</a> <span>/</span> {{ __('app.nav_locations') }}
@endsection
@section('page-title', __('app.loc_title'))
@section('page-subtitle', __('app.loc_subtitle'))

@section('content')
@php
    $zones  = $data['zone_stats'] ?? [];
    $charts = $data['charts'] ?? [];
    $roi    = $data['roi_table'] ?? [];
@endphp

{{-- Zone cards --}}
<div class="kpi-grid kpi-grid-3 animate-in delay-1" style="margin-bottom:20px">
    @foreach($zones as $key => $z)
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px">
            <div class="card-title" style="margin-bottom:0">{{ $z['display_name'] }}</div>
            <span class="badge badge-accent">{{ $z['count'] }} ta</span>
        </div>
        <div style="font-size:26px;font-weight:800;margin-bottom:4px">{{ number_format($z['avg_price']) }} <span style="font-size:14px;color:var(--text-secondary)">₽/kecha</span></div>
        <div style="font-size:12px;color:var(--text-secondary);margin-bottom:12px">Median: {{ number_format($z['median_price']) }} ₽</div>
        <div style="display:flex;gap:12px">
            <div style="font-size:11px;color:var(--text-muted)">Min<br><strong style="color:var(--text-primary)">{{ number_format($z['min_price']) }} ₽</strong></div>
            <div style="font-size:11px;color:var(--text-muted)">Max<br><strong style="color:var(--text-primary)">{{ number_format($z['max_price']) }} ₽</strong></div>
            <div style="font-size:11px;color:var(--text-muted)">Standart<br><strong style="color:var(--text-primary)">{{ number_format($z['std_price']) }} ₽</strong></div>
        </div>
    </div>
    @endforeach
</div>

{{-- Charts --}}
<div class="section-row animate-in delay-2">
    <div class="card chart-card">
        <div class="chart-header">
            <div>
                <div class="chart-title">📊 Zona Bo'yicha O'rtacha Narxlar</div>
                <div class="chart-subtitle">₽ kechaga</div>
            </div>
        </div>
        <canvas id="zoneAvgChart" height="280"></canvas>
    </div>
    <div class="card chart-card">
        <div class="chart-header">
            <div>
                <div class="chart-title">📦 Narxlar Diapazoni (Box Plot)</div>
                <div class="chart-subtitle">25–75 percentil diapazoni</div>
            </div>
        </div>
        <canvas id="boxChart" height="280"></canvas>
    </div>
</div>

{{-- Scatter: price vs roi --}}
<div class="card chart-card animate-in delay-3" style="margin-bottom:20px">
    <div class="chart-header">
        <div>
            <div class="chart-title">🎯 Kunlik Narx vs ROI Nisbati</div>
            <div class="chart-subtitle">Eng yuqori chap-yuqori zona eng foydali</div>
        </div>
    </div>
    <canvas id="scatterChart" height="220"></canvas>
</div>

{{-- Detail table --}}
<div class="card animate-in delay-4">
    <div class="chart-header">
        <div>
            <div class="chart-title">📋 Batafsil Statistika Jadvali</div>
        </div>
    </div>
    <div class="table-wrapper">
        <table class="analytics-table" id="zonesTable">
            <thead>
                <tr>
                    <th>Zona</th>
                    <th>E'lonlar</th>
                    <th>O'rt. narx (₽)</th>
                    <th>Median (₽)</th>
                    <th>Min (₽)</th>
                    <th>Max (₽)</th>
                    <th>Q25 (₽)</th>
                    <th>Q75 (₽)</th>
                    <th>ROI (%)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($zones as $key => $z)
                @php
                    $roiEntry = collect($roi)->firstWhere('zone', $key);
                    $roiVal = $roiEntry['roi_percent'] ?? 0;
                @endphp
                <tr>
                    <td><strong>{{ $z['display_name'] }}</strong></td>
                    <td>{{ number_format($z['count']) }}</td>
                    <td>{{ number_format($z['avg_price']) }}</td>
                    <td>{{ number_format($z['median_price']) }}</td>
                    <td>{{ number_format($z['min_price']) }}</td>
                    <td>{{ number_format($z['max_price']) }}</td>
                    <td>{{ number_format($z['price_distribution']['q25']) }}</td>
                    <td>{{ number_format($z['price_distribution']['q75']) }}</td>
                    <td>
                        <span class="badge {{ $roiVal >= 20 ? 'badge-success' : ($roiVal >= 14 ? 'badge-warning' : 'badge-danger') }}">
                            {{ $roiVal }}%
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
const zones = @json($zones);
const roi = @json($roi);
const scatter = @json($data['charts']['scatter'] ?? []);

const zKeys = Object.keys(zones);
const colors = ['#6c63ff','#00b4d8','#ffd166','#06d6a0','#ef476f','#a8dadc'];

// Bar chart
new Chart(document.getElementById('zoneAvgChart'), {
    type: 'bar',
    data: {
        labels: zKeys.map(k => zones[k].display_name),
        datasets: [{
            label: "O'rtacha",
            data: zKeys.map(k => zones[k].avg_price),
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
                ticks: { callback: v => v.toLocaleString('ru-RU') + ' ₽' }
            }
        }
    }
});

// Box chart (using bar with error bars simulation)
new Chart(document.getElementById('boxChart'), {
    type: 'bar',
    data: {
        labels: zKeys.map(k => zones[k].display_name),
        datasets: [
            {
                label: 'Q25',
                data: zKeys.map(k => zones[k].price_distribution?.q25 || 0),
                backgroundColor: 'rgba(108,99,255,0.3)',
                borderRadius: 4,
                stack: 'box',
            },
            {
                label: 'Q25–Q75',
                data: zKeys.map(k => (zones[k].price_distribution?.q75 || 0) - (zones[k].price_distribution?.q25 || 0)),
                backgroundColor: colors.map(c => c + 'bb'),
                borderRadius: 4,
                stack: 'box',
            }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } },
        scales: {
            x: { grid: { display: false }, stacked: true },
            y: {
                stacked: true,
                grid: { color: 'rgba(255,255,255,0.05)' },
                ticks: { callback: v => v.toLocaleString('ru-RU') + ' ₽' }
            }
        }
    }
});

// Scatter chart
new Chart(document.getElementById('scatterChart'), {
    type: 'scatter',
    data: {
        datasets: scatter.map((s, i) => ({
            label: s.zone,
            data: [{ x: s.avg_daily_price, y: s.roi_percent }],
            backgroundColor: colors[i % colors.length],
            pointRadius: 12,
            pointHoverRadius: 15,
        }))
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'right' },
            tooltip: {
                callbacks: {
                    label: ctx => {
                        const d = ctx.raw;
                        return `${ctx.dataset.label}: ${d.x.toLocaleString('ru-RU')} ₽/kun, ROI ${d.y}%`;
                    }
                }
            }
        },
        scales: {
            x: {
                title: { display: true, text: "O'rtacha kunlik narx (₽)", color: '#9090b8' },
                grid: { color: 'rgba(255,255,255,0.05)' },
                ticks: { callback: v => v.toLocaleString('ru-RU') + ' ₽' }
            },
            y: {
                title: { display: true, text: 'ROI %', color: '#9090b8' },
                grid: { color: 'rgba(255,255,255,0.05)' },
                ticks: { callback: v => v + '%' }
            }
        }
    }
});

$(document).ready(() => $('#zonesTable').DataTable({ language: { url: '' }, pageLength: 10, order: [[2, 'desc']] }));
</script>
@endpush
