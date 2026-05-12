@extends('layouts.app')
@section('title', __('app.emp_title'))
@section('breadcrumb')
    <a href="{{ url('/vacancy') }}">{{ __('app.case2_title') }}</a> <span>/</span> {{ __('app.nav_employers') }}
@endsection
@section('page-title', __('app.emp_title'))
@section('page-subtitle', __('app.emp_subtitle'))

@section('content')
@php
    $employers = $data['employer_stats'] ?? [];
    $schedule  = $data['schedule_stats'] ?? [];
    $skills    = $data['top_skills'] ?? [];
    $dataMap   = __('data');
    arsort($employers);
@endphp

<div class="kpi-grid kpi-grid-3 animate-in delay-1" style="margin-bottom:20px">
    @foreach($employers as $type => $e)
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px">
            <div class="card-title" style="margin-bottom:0">{{ $dataMap[$type] ?? $type }}</div>
            <span class="badge badge-blue">{{ $e['count'] }}</span>
        </div>
        <div style="font-size:26px;font-weight:800;margin-bottom:4px">{{ number_format($e['median_salary']) }}<span style="font-size:13px;color:var(--text-secondary)"> ₽/oy</span></div>
        <div style="font-size:12px;color:var(--text-secondary);margin-bottom:12px">O'rtacha: {{ number_format($e['avg_salary']) }} ₽</div>
        <div class="roi-bar-wrap">
            <div class="roi-bar" style="width:{{ min(($e['median_salary'] / 100000) * 100, 100) }}%"></div>
        </div>
    </div>
    @endforeach
</div>

<div class="section-row animate-in delay-2">
    <div class="card chart-card">
        <div class="chart-header">
            <div><div class="chart-title">📊 Sektor Bo'yicha Maosh Solishtirmasi</div></div>
        </div>
        <canvas id="empBarChart" height="280"></canvas>
    </div>
    <div class="card chart-card">
        <div class="chart-header">
            <div><div class="chart-title">⏰ Ish Jadvali Bo'yicha Maosh</div></div>
        </div>
        <canvas id="scheduleChart" height="280"></canvas>
    </div>
</div>

{{-- Top Skills --}}
<div class="section-row animate-in delay-3">
    <div class="card">
        <div class="chart-header">
            <div>
                <div class="chart-title">🛠️ Eng Ko'p Talab Qilinadigan Texnik Ko'nikmalar</div>
                <div class="chart-subtitle">Hard skills (e'lonlar soniga ko'ra)</div>
            </div>
        </div>
        <canvas id="hardSkillsChart" height="250"></canvas>
    </div>
    <div class="card">
        <div class="chart-header">
            <div>
                <div class="chart-title">🤝 Ijtimoiy Ko'nikmalar (Soft Skills)</div>
                <div class="chart-subtitle">Eng ko'p tilga olingan</div>
            </div>
        </div>
        <canvas id="softSkillsChart" height="250"></canvas>
    </div>
</div>
@endsection

@push('scripts')
<script>
const employers = @json($employers);
const schedule  = @json($schedule);
const hardSkills = @json($skills['hard'] ?? []);
const softSkills = @json($skills['soft'] ?? []);
const colors = ['#6c63ff','#00b4d8','#ffd166','#06d6a0','#ef476f'];

const empKeys = Object.keys(employers).sort((a,b) => employers[b].median_salary - employers[a].median_salary);
new Chart(document.getElementById('empBarChart'), {
    type: 'bar',
    data: {
        labels: empKeys.map(k => tdl(k)),
        datasets: [
            { label: "O'rtacha", data: empKeys.map(k => employers[k].avg_salary), backgroundColor: 'rgba(108,99,255,0.65)', borderRadius: 6 },
            { label: "Median",   data: empKeys.map(k => employers[k].median_salary), backgroundColor: 'rgba(6,214,160,0.65)', borderRadius: 6 },
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } },
        scales: {
            x: { grid: { display: false } },
            y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { callback: v => (v/1000).toFixed(0) + 'K ₽' } }
        }
    }
});

const schKeys = Object.keys(schedule);
new Chart(document.getElementById('scheduleChart'), {
    type: 'bar',
    data: {
        labels: schKeys.map(k => tdl(k || "Noma'lum")),
        datasets: [{
            label: 'Median maosh',
            data: schKeys.map(k => schedule[k].median_salary || 0),
            backgroundColor: colors,
            borderRadius: 8,
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { callback: v => (v/1000).toFixed(0) + 'K ₽' } },
            y: { grid: { display: false } }
        }
    }
});

// Hard skills
const hKeys = Object.keys(hardSkills).slice(0, 12);
new Chart(document.getElementById('hardSkillsChart'), {
    type: 'bar',
    data: {
        labels: hKeys,
        datasets: [{
            data: hKeys.map(k => hardSkills[k]),
            backgroundColor: 'rgba(108,99,255,0.7)',
            borderRadius: 4,
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: 'rgba(255,255,255,0.05)' } },
            y: { grid: { display: false } }
        }
    }
});

const sKeys = Object.keys(softSkills).slice(0, 12);
new Chart(document.getElementById('softSkillsChart'), {
    type: 'bar',
    data: {
        labels: sKeys,
        datasets: [{
            data: sKeys.map(k => softSkills[k]),
            backgroundColor: 'rgba(0,180,216,0.7)',
            borderRadius: 4,
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: 'rgba(255,255,255,0.05)' } },
            y: { grid: { display: false } }
        }
    }
});
</script>
@endpush
