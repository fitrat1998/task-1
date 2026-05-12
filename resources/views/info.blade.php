@extends('layouts.app')
@section('title', __('app.nav_info'))
@section('breadcrumb')
    <a href="{{ url('/') }}">Home</a> <span>/</span> {{ __('app.nav_info') }}
@endsection
@section('page-title', 'ℹ️ ' . __('app.nav_info'))
@section('page-subtitle', app()->getLocale() === 'ru' ? 'Описание проекта, методологии и результатов' : (app()->getLocale() === 'en' ? 'Project description, methodology and results' : "Loyiha tavsifi, metodologiya va natijalar"))

@section('content')
@php $lang = app()->getLocale(); @endphp

{{-- Tech Stack --}}
<div class="kpi-grid kpi-grid-4 animate-in delay-1" style="margin-bottom:24px">
    <div class="card card-accent-purple">
        <div class="card-title">📁 {{ $lang==='ru'?'Данные':'Data' }}</div>
        <div class="card-value" style="font-size:22px">2 CSV</div>
        <div class="card-change">Avito · CIAN · Sutochno · hh.ru</div>
    </div>
    <div class="card card-accent-blue">
        <div class="card-title">🐍 Python</div>
        <div class="card-value" style="font-size:22px">Pandas</div>
        <div class="card-change">NumPy · SciPy · Mann-Whitney U</div>
    </div>
    <div class="card card-accent-green">
        <div class="card-title">🐘 Laravel</div>
        <div class="card-value" style="font-size:22px">PHP 8</div>
        <div class="card-change">{{ $lang==='ru'?'Кэш · Маршруты · API':'Cache · Routes · API' }}</div>
    </div>
    <div class="card card-accent-orange">
        <div class="card-title">📊 Chart.js</div>
        <div class="card-value" style="font-size:22px">v4.4</div>
        <div class="card-change">ApexCharts · DataTables</div>
    </div>
</div>

{{-- Architecture --}}
<div class="card animate-in delay-2" style="margin-bottom:20px">
    <div class="chart-header">
        <div>
            <div class="chart-title">⚙️ {{ $lang==='ru'?'Архитектура системы':($lang==='en'?'System Architecture':'Tizim Arxitekturasi') }}</div>
        </div>
    </div>
    <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;justify-content:center;padding:12px 0">
        @foreach([
            ['📂','CSV Fayllar','storage/app/data/','var(--accent)'],
            ['→','','','var(--text-muted)'],
            ['🐍','Python','kbr_analysis.py · vacancy_analysis.py','var(--success)'],
            ['→','','','var(--text-muted)'],
            ['💾','JSON Cache','storage/app/cache/','var(--accent2)'],
            ['→','','','var(--text-muted)'],
            ['🐘','Laravel PHP','PythonAnalyticsService','var(--warning)'],
            ['→','','','var(--text-muted)'],
            ['🌐','Web UI','Blade + Chart.js','var(--accent)'],
        ] as $step)
        @if($step[1])
        <div style="text-align:center;padding:12px 16px;background:var(--bg-card);border:1px solid var(--border);border-radius:12px;min-width:120px">
            <div style="font-size:22px;margin-bottom:4px">{{ $step[0] }}</div>
            <div style="font-size:12px;font-weight:700;color:{{ $step[3] }}">{{ $step[1] }}</div>
            <div style="font-size:10px;color:var(--text-muted);margin-top:2px">{{ $step[2] }}</div>
        </div>
        @else
        <div style="font-size:20px;color:var(--text-muted)">→</div>
        @endif
        @endforeach
    </div>
</div>

{{-- CASE 1 --}}
<div class="card animate-in delay-2" style="margin-bottom:20px;border-top:2px solid var(--accent)">
    <div class="chart-header">
        <div>
            <div class="chart-title">🏔️
                @if($lang==='ru') Кейс 1: Инвестиционный анализ посуточной аренды в КБР
                @elseif($lang==='en') Case 1: Short-Term Rental Investment Analysis in KBR
                @else Keis 1: KBR Sutkalik Ijara Investitsiya Tahlili @endif
            </div>
        </div>
        <a href="{{ url('/kbr') }}" style="font-size:12px;color:var(--accent);text-decoration:none">
            {{ $lang==='ru'?'Открыть →':($lang==='en'?'Open →':'Ochish →') }}
        </a>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
        <div>
            <div style="font-size:12px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:10px">
                @if($lang==='ru') Ситуация @elseif($lang==='en') Situation @else Vaziyat @endif
            </div>
            <p style="font-size:13px;color:var(--text-secondary);line-height:1.7">
                @if($lang==='ru')
                    Инвестор с бюджетом <strong style="color:var(--accent)">5–7 млн ₽</strong> хочет купить недвижимость в Кабардино-Балкарии для посуточной сдачи туристам. Туристический поток в КБР значительно вырос, особенно в Приэльбрусье. Нужно определить оптимальную локацию и тип объекта.
                @elseif($lang==='en')
                    An investor with a budget of <strong style="color:var(--accent)">5–7M ₽</strong> wants to purchase property in Kabardino-Balkaria for short-term tourist rentals. Tourist flow to KBR has grown significantly, especially in Prielbrusye. The goal is to find the optimal location and property type.
                @else
                    <strong style="color:var(--accent)">5–7 mln ₽</strong> byudjetli investor KBR da turistlarga sutkalik ijara uchun ko'chmas mulk sotib olmoqchi. KBR ga turistlar oqimi sezilarli o'sdi, ayniqsa Prielbrusyeda. Maqsad: optimal joy va mulk turini aniqlash.
                @endif
            </p>
        </div>
        <div>
            <div style="font-size:12px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:10px">
                @if($lang==='ru') Данные @elseif($lang==='en') Data @else Ma'lumotlar @endif
            </div>
            <div style="display:flex;flex-direction:column;gap:8px">
                @foreach([
                    ['📊','3,011 '.($lang==='ru'?'объявлений':($lang==='en'?'listings':"e'lon")),'Avito, Sutochno.ru, CIAN'],
                    ['🗺️',$lang==='ru'?'6 зон':($lang==='en'?'6 zones':'6 zona'),'Nalchik, Prielbrusye, Tyrnyauz, Baksan, Chegem'],
                    ['🏠',$lang==='ru'?'Типы жилья':($lang==='en'?'Property types':"Mulk turlari"),$lang==='ru'?'Квартира, Дом, Студия, Коттедж':($lang==='en'?'Apt, House, Studio, Cottage':'Kvartira, Dom, Studiya')],
                ] as $d)
                <div style="display:flex;gap:10px;align-items:center;padding:8px 12px;background:var(--bg-card);border-radius:8px">
                    <span style="font-size:16px">{{ $d[0] }}</span>
                    <div>
                        <div style="font-size:12px;font-weight:600">{{ $d[1] }}</div>
                        <div style="font-size:11px;color:var(--text-muted)">{{ $d[2] }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--border)">
        <div style="font-size:12px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:12px">
            @if($lang==='ru') Методология @elseif($lang==='en') Methodology @else Metodologiya @endif
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
            @foreach([
                ['💰',$lang==='ru'?'Расчёт ROI':($lang==='en'?'ROI Calc':'ROI Hisob'),'(Avg × Days × 0.85) / Price × 100%'],
                ['📈',$lang==='ru'?'Загрузка':($lang==='en'?'Occupancy':'Yuklama'),'130–230 '.$lang==='ru'?'дн/год':($lang==='en'?'days/yr':'kun/yil')],
                ['🔬',$lang==='ru'?'Гипотезы':($lang==='en'?'Hypotheses':'Gipotezalar'),'Mann-Whitney U, α=0.05'],
                ['🎯',$lang==='ru'?'Бюджеты':($lang==='en'?'Budgets':'Byudjetlar'),'5M / 6M / 7M ₽'],
            ] as $m)
            <div style="flex:1;min-width:160px;padding:12px;background:rgba(108,99,255,.07);border:1px solid rgba(108,99,255,.2);border-radius:10px">
                <div style="font-size:16px;margin-bottom:4px">{{ $m[0] }}</div>
                <div style="font-size:12px;font-weight:700">{{ $m[1] }}</div>
                <div style="font-size:11px;color:var(--text-muted);margin-top:2px">{{ $m[2] }}</div>
            </div>
            @endforeach
        </div>
    </div>

    <div style="margin-top:16px;padding:16px;background:rgba(6,214,160,.07);border:1px solid rgba(6,214,160,.25);border-radius:12px">
        <div style="font-size:12px;font-weight:700;color:var(--success);margin-bottom:8px">
            ✅ {{ $lang==='ru'?'Результат':($lang==='en'?'Result':'Natija') }}
        </div>
        <p style="font-size:13px;color:var(--text-secondary);line-height:1.6">
            @if($lang==='ru')
                Приэльбрусье показывает наибольший ROI благодаря высокому среднему чеку и загрузке 230 дней/год. Нальчик — стабильный вариант для долгосрочной стратегии. Гипотезы о превосходстве Приэльбрусья над другими зонами подтверждены статистически.
            @elseif($lang==='en')
                Prielbrusye shows the highest ROI due to high average price and 230 days/year occupancy. Nalchik is a stable option for long-term strategy. Hypotheses about Prielbrusye's superiority over other zones were statistically confirmed.
            @else
                Prielbrusye yuqori o'rtacha narx va 230 kun/yil yuklama tufayli eng yuqori ROI ko'rsatadi. Nalchik uzoq muddatli strategiya uchun barqaror variant. Prielbrusyening boshqa zonalardan ustunligi bo'yicha gipotezalar statistik jihatdan tasdiqlandi.
            @endif
        </p>
    </div>
</div>

{{-- CASE 2 --}}
<div class="card animate-in delay-3" style="margin-bottom:20px;border-top:2px solid var(--accent2)">
    <div class="chart-header">
        <div>
            <div class="chart-title">💼
                @if($lang==='ru') Кейс 2: Анализ рынка вакансий для оценщиков недвижимости
                @elseif($lang==='en') Case 2: Job Market Analysis for Real Estate Appraisers
                @else Keis 2: Ko'chmas Mulk Baholovchilari Vakansiyalari Tahlili @endif
            </div>
        </div>
        <a href="{{ url('/vacancy') }}" style="font-size:12px;color:var(--accent2);text-decoration:none">
            {{ $lang==='ru'?'Открыть →':($lang==='en'?'Open →':'Ochish →') }}
        </a>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
        <div>
            <div style="font-size:12px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:10px">
                @if($lang==='ru') Ситуация @elseif($lang==='en') Situation @else Vaziyat @endif
            </div>
            <p style="font-size:13px;color:var(--text-secondary);line-height:1.7">
                @if($lang==='ru')
                    Специалист по оценке недвижимости с опытом <strong style="color:var(--accent2)">2–3 года</strong> планирует смену работы. Рассматривает Москву, СПб, Казань, Новосибирск и удалённую работу. Нужно найти комбинацию (город + тип компании + формат) с максимальным доходом.
                @elseif($lang==='en')
                    A real estate appraiser with <strong style="color:var(--accent2)">2–3 years</strong> of experience is planning a job change. Considering Moscow, St. Petersburg, Kazan, Novosibirsk and remote work. Goal: find the best combination (city + employer type + format) for maximum income.
                @else
                    <strong style="color:var(--accent2)">2–3 yillik</strong> tajribali baholovchi yangi ish qidirmoqda. Moskva, Sankt-Peterburg, Kazan, Novosibirsk va masofaviy ish ko'rib chiqilmoqda. Maqsad: maksimal daromad uchun eng yaxshi kombinatsiyani topish.
                @endif
            </p>
        </div>
        <div>
            <div style="font-size:12px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:10px">
                @if($lang==='ru') Данные @elseif($lang==='en') Data @else Ma'lumotlar @endif
            </div>
            <div style="display:flex;flex-direction:column;gap:8px">
                @foreach([
                    ['📊','1,900+ '.($lang==='ru'?'вакансий':($lang==='en'?'vacancies':'vakansiya')),'hh.ru, SuperJob, Telegram'],
                    ['🏙️',$lang==='ru'?'8 городов':($lang==='en'?'8 cities':'8 shahar'),'Москва, СПб, Екатеринбург, Казань...'],
                    ['🏢',$lang==='ru'?'4 типа компаний':($lang==='en'?'4 employer types':'4 turdagi ish beruvchi'),$lang==='ru'?'Оценочные, Банки, Девелоперы, Государство':($lang==='en'?'Appraisal, Banks, Dev, Gov':'Baholash, Bank, Dev, Davlat')],
                ] as $d)
                <div style="display:flex;gap:10px;align-items:center;padding:8px 12px;background:var(--bg-card);border-radius:8px">
                    <span style="font-size:16px">{{ $d[0] }}</span>
                    <div>
                        <div style="font-size:12px;font-weight:600">{{ $d[1] }}</div>
                        <div style="font-size:11px;color:var(--text-muted)">{{ $d[2] }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--border)">
        <div style="font-size:12px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:12px">
            @if($lang==='ru') Гипотезы @elseif($lang==='en') Hypotheses @else Gipotezalar @endif
        </div>
        <div style="display:flex;flex-direction:column;gap:8px">
            @foreach([
                ['H1', $lang==='ru'?'Москва платит на 40%+ больше регионов':($lang==='en'?'Moscow pays 40%+ more than regions':"Moskva mintaqalardan 40%+ ko'p to'laydi")],
                ['H2', $lang==='ru'?'Банки платят больше оценочных компаний':($lang==='en'?'Banks pay more than appraisal firms':"Banklar baholash kompaniyalaridan ko'proq to'laydi")],
                ['H3', $lang==='ru'?'Удалённая работа имеет меньшую зарплату':($lang==='en'?'Remote work has lower salary':"Masofaviy ish past maoshga ega")],
            ] as $h)
            <div style="display:flex;gap:12px;align-items:center;padding:10px 14px;background:rgba(0,180,216,.06);border:1px solid rgba(0,180,216,.2);border-radius:8px">
                <span class="badge badge-blue">{{ $h[0] }}</span>
                <span style="font-size:13px;color:var(--text-secondary)">{{ $h[1] }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <div style="margin-top:16px;padding:16px;background:rgba(6,214,160,.07);border:1px solid rgba(6,214,160,.25);border-radius:12px">
        <div style="font-size:12px;font-weight:700;color:var(--success);margin-bottom:8px">
            ✅ {{ $lang==='ru'?'Результат':($lang==='en'?'Result':'Natija') }}
        </div>
        <p style="font-size:13px;color:var(--text-secondary);line-height:1.6">
            @if($lang==='ru')
                Москва обеспечивает наибольшую медианную зарплату. Банковский сектор и крупные девелоперы предлагают лучшие условия. Гипотеза о превосходстве Москвы над регионами подтверждена статистически (p &lt; 0.05).
            @elseif($lang==='en')
                Moscow provides the highest median salary. Banking sector and large developers offer the best conditions. The hypothesis about Moscow's superiority over regions was statistically confirmed (p &lt; 0.05).
            @else
                Moskva eng yuqori median maoshni ta'minlaydi. Bank sektori va yirik developerlar eng yaxshi sharoitlarni taklif qiladi. Moskvaning mintaqalardan ustunligi bo'yicha gipoteza statistik jihatdan tasdiqlandi (p &lt; 0.05).
            @endif
        </p>
    </div>
</div>

{{-- Evaluation Criteria --}}
<div class="card animate-in delay-4">
    <div class="chart-header">
        <div>
            <div class="chart-title">📋
                @if($lang==='ru') Критерии оценки
                @elseif($lang==='en') Evaluation Criteria
                @else Baholash Mezonlari @endif
            </div>
        </div>
    </div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px">
        @php
        $criteria = $lang==='ru' ? [
            ['🔬','Глубина анализа','Все факторы: цена, загрузка, покупка, сезонность'],
            ['📐','Обоснованность','Чёткая методология: выручка, доход, окупаемость'],
            ['🎯','Качество выводов','Конкретные, практические рекомендации'],
            ['📊','Визуализация','Графики, таблицы, тепловые карты'],
            ['🧪','Гипотезы','Формулировка и проверка минимум 2–3 гипотез'],
            ['💼','Структура','Логичное изложение, профессиональное оформление'],
        ] : ($lang==='en' ? [
            ['🔬','Depth of Analysis','All factors: price, occupancy, purchase, seasonality'],
            ['📐','Methodology','Clear logic: revenue, net income, payback'],
            ['🎯','Quality of Findings','Specific, practical recommendations'],
            ['📊','Visualization','Charts, tables, heatmaps'],
            ['🧪','Hypotheses','Formulating & testing minimum 2–3 hypotheses'],
            ['💼','Structure','Logical flow, professional presentation'],
        ] : [
            ['🔬',"Tahlil Chuqurligi","Barcha omillar: narx, yuklama, xarid, mavsumiylik"],
            ['📐','Asoslilik',"Aniq metodologiya: daromad, sof foyda, amortizatsiya"],
            ['🎯','Xulosalar Sifati',"Aniq, amaliy tavsiyalar"],
            ['📊','Vizualizatsiya',"Grafiklar, jadvallar, issiqlik xaritalari"],
            ['🧪','Gipotezalar',"Kamida 2–3 gipoteza shakllantiriladi va tekshiriladi"],
            ['💼','Tuzilish',"Mantiqiy bayon, professional dizayn"],
        ]);
        @endphp
        @foreach($criteria as $c)
        <div style="padding:16px;background:var(--bg-card);border:1px solid var(--border);border-radius:12px">
            <div style="font-size:20px;margin-bottom:8px">{{ $c[0] }}</div>
            <div style="font-size:13px;font-weight:700;margin-bottom:4px">{{ $c[1] }}</div>
            <div style="font-size:11px;color:var(--text-muted);line-height:1.5">{{ $c[2] }}</div>
        </div>
        @endforeach
    </div>
</div>
@endsection
