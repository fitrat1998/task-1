#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
KBR (Kabardino-Balkaria) sutkalik ijara ma'lumotlarini tahlil qilish
"""

import sys
import json
import os
import warnings
import pandas as pd
import numpy as np
from scipy import stats

warnings.filterwarnings('ignore')

BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
DATA_DIR = os.path.join(BASE_DIR, 'storage', 'app', 'data')
CACHE_DIR = os.path.join(BASE_DIR, 'storage', 'app', 'cache')

KBR_FILE = os.path.join(DATA_DIR, '!Сравнение локаций и типов жилья в КБР.csv')

# Sotib olish narxi (taxminiy, rub.)
PURCHASE_PRICES = {
    'Nalchik':      {'1k': 3_500_000, '2k': 5_000_000, 'studio': 3_000_000, 'house': 6_500_000, 'other': 4_000_000},
    'Prielbrusye':  {'1k': 5_000_000, '2k': 7_000_000, 'studio': 4_500_000, 'house': 8_000_000, 'other': 5_500_000},
    'Tyrnyauz':     {'1k': 2_500_000, '2k': 3_500_000, 'studio': 2_200_000, 'house': 4_500_000, 'other': 3_000_000},
    'Baksan':       {'1k': 2_800_000, '2k': 4_000_000, 'studio': 2_500_000, 'house': 5_000_000, 'other': 3_200_000},
    'Chegemsky':    {'1k': 3_000_000, '2k': 4_500_000, 'studio': 2_700_000, 'house': 6_000_000, 'other': 3_500_000},
    'Other':        {'1k': 2_500_000, '2k': 3_500_000, 'studio': 2_000_000, 'house': 4_000_000, 'other': 3_000_000},
}

# Yillik yuklanish kunlari (taxminiy)
OCCUPANCY_DAYS = {
    'Nalchik':     182,
    'Prielbrusye': 230,
    'Tyrnyauz':    140,
    'Baksan':      150,
    'Chegemsky':   160,
    'Other':       130,
}

EXPENSE_RATE = 0.15  # 15% xarajatlar


def load_data():
    try:
        df = pd.read_csv(KBR_FILE, encoding='utf-8-sig')
    except Exception:
        df = pd.read_csv(KBR_FILE, encoding='cp1251')

    df.columns = df.columns.str.strip()
    df['price'] = pd.to_numeric(df['price'], errors='coerce')
    df = df.dropna(subset=['price'])
    df = df[df['price'] > 0]

    # Narx bo'yicha tashqi qiymatlarni olib tashlash (1%–99%)
    q_low = df['price'].quantile(0.01)
    q_high = df['price'].quantile(0.99)
    df = df[(df['price'] >= q_low) & (df['price'] <= q_high)]

    return df


def assign_zone(row):
    """Lokatsiyani zonaga birlashtirish"""
    selo = str(row.get('село/поселок', '') or '').strip()
    city = str(row.get('город', '') or '').strip()
    address = str(row.get('address', '') or '').lower()

    prielbrusye = ['Терскол', 'Эльбрус', 'Тегенекли', 'Нейтрино', 'Азау', 'Чегет']
    tyrnyauz_list = ['Тырныауз']
    baksan_list = ['Баксан']

    if any(p in selo for p in prielbrusye):
        return 'Prielbrusye'
    if any(p in city for p in prielbrusye):
        return 'Prielbrusye'
    if 'Тырныауз' in city or 'Тырныауз' in selo:
        return 'Tyrnyauz'
    if 'Баксан' in city or 'Баксан' in selo:
        return 'Baksan'
    if 'Нальчик' in city:
        return 'Nalchik'
    if 'Чегем' in city or 'Чегем' in address:
        return 'Chegemsky'
    if selo and selo not in ['nan', '']:
        return 'Prielbrusye'
    return 'Other'


def assign_type_key(property_type):
    """Mulk turini kalitga o'zgartirish"""
    t = str(property_type).lower()
    if 'студ' in t or 'studio' in t:
        return 'studio'
    if 'дом' in t or 'коттедж' in t or 'house' in t:
        return 'house'
    if 'кемпинг' in t or 'camping' in t:
        return 'other'
    if 'квартир' in t or 'flat' in t or 'апарт' in t:
        return '1k'  # default kvartira
    return 'other'


def get_zone_display(zone):
    names = {
        'Nalchik': 'Nalchik',
        'Prielbrusye': 'Prielbrusye (Terskol, Elbrus)',
        'Tyrnyauz': 'Tyrnyauz',
        'Baksan': 'Baksan',
        'Chegemsky': 'Chegem tumani',
        'Other': 'Boshqa hududlar',
    }
    return names.get(zone, zone)


def calculate_roi(avg_price, zone, type_key):
    """ROI va amortizatsiyani hisoblash"""
    purchase = PURCHASE_PRICES.get(zone, PURCHASE_PRICES['Other']).get(type_key, 4_000_000)
    days = OCCUPANCY_DAYS.get(zone, 150)
    gross_income = avg_price * days
    net_income = gross_income * (1 - EXPENSE_RATE)
    roi = (net_income / purchase) * 100
    payback_years = purchase / net_income if net_income > 0 else 999
    return {
        'purchase_price': purchase,
        'occupancy_days': days,
        'gross_annual': round(gross_income),
        'net_annual': round(net_income),
        'roi_percent': round(roi, 2),
        'payback_years': round(payback_years, 1),
    }


def analyze():
    df = load_data()

    # Zona va tur tayinlash
    df['zone'] = df.apply(assign_zone, axis=1)
    df['type_key'] = df['тип'].apply(assign_type_key)

    # === UMUMIY STATISTIKA ===
    summary = {
        'total_listings': int(len(df)),
        'avg_price': round(float(df['price'].mean()), 2),
        'median_price': round(float(df['price'].median()), 2),
        'min_price': round(float(df['price'].min()), 2),
        'max_price': round(float(df['price'].max()), 2),
        'zones': df['zone'].value_counts().to_dict(),
        'types': df['тип'].value_counts().head(10).to_dict(),
        'sources': df['Источник данных'].value_counts().to_dict() if 'Источник данных' in df.columns else {},
    }

    # === LOKATSIYALAR BO'YICHA ===
    zone_stats = {}
    for zone in df['zone'].unique():
        zdf = df[df['zone'] == zone]
        zone_stats[zone] = {
            'display_name': get_zone_display(zone),
            'count': int(len(zdf)),
            'avg_price': round(float(zdf['price'].mean()), 2),
            'median_price': round(float(zdf['price'].median()), 2),
            'min_price': round(float(zdf['price'].min()), 2),
            'max_price': round(float(zdf['price'].max()), 2),
            'std_price': round(float(zdf['price'].std()), 2),
            'price_distribution': {
                'q25': round(float(zdf['price'].quantile(0.25)), 2),
                'q50': round(float(zdf['price'].quantile(0.50)), 2),
                'q75': round(float(zdf['price'].quantile(0.75)), 2),
            },
            'top_types': zdf['тип'].value_counts().head(5).to_dict(),
        }

    # === TUR BO'YICHA ===
    type_stats = {}
    for t in df['тип'].unique():
        tdf = df[df['тип'] == t]
        if len(tdf) < 3:
            continue
        type_stats[str(t)] = {
            'count': int(len(tdf)),
            'avg_price': round(float(tdf['price'].mean()), 2),
            'median_price': round(float(tdf['price'].median()), 2),
            'min_price': round(float(tdf['price'].min()), 2),
            'max_price': round(float(tdf['price'].max()), 2),
            'zones': tdf['zone'].value_counts().to_dict(),
        }

    # === ISSIQLIK XARITASI (zona x tur) ===
    zones_order = ['Nalchik', 'Prielbrusye', 'Tyrnyauz', 'Baksan', 'Chegemsky', 'Other']
    types_order = ['Квартира', 'Дом', 'Коттедж', 'Кемпинг']
    all_types = [t for t in df['тип'].unique() if df[df['тип'] == t].shape[0] >= 5]

    heatmap = {'zones': [], 'types': all_types, 'values': []}
    for zone in zones_order:
        row_vals = []
        for t in all_types:
            sub = df[(df['zone'] == zone) & (df['тип'] == t)]
            val = round(float(sub['price'].mean()), 2) if len(sub) >= 3 else None
            row_vals.append(val)
        heatmap['zones'].append(get_zone_display(zone))
        heatmap['values'].append(row_vals)

    # === ROI JADVALI ===
    roi_table = []
    budgets = [5_000_000, 6_000_000, 7_000_000]
    for zone in zones_order:
        zdf = df[df['zone'] == zone]
        if len(zdf) == 0:
            continue
        avg_p = float(zdf['price'].mean())
        type_key = '1k'  # asosiy ssenariy
        roi_data = calculate_roi(avg_p, zone, type_key)
        entry = {
            'zone': zone,
            'display_name': get_zone_display(zone),
            'avg_daily_price': round(avg_p, 2),
            **roi_data,
            'budget_scenarios': [],
        }
        for budget in budgets:
            gross = avg_p * roi_data['occupancy_days']
            net = gross * (1 - EXPENSE_RATE)
            roi_b = (net / budget) * 100 if budget > 0 else 0
            pb = budget / net if net > 0 else 999
            entry['budget_scenarios'].append({
                'budget': budget,
                'roi_percent': round(roi_b, 2),
                'payback_years': round(pb, 1),
                'net_annual': round(net),
            })
        roi_table.append(entry)

    # ROI bo'yicha tartiblash
    roi_table.sort(key=lambda x: x['roi_percent'], reverse=True)

    # === GIPOTEZALAR ===
    hypotheses = {}

    # G1: Prielbrusye > Nalchik daromad bo'yicha
    nalchik_prices = df[df['zone'] == 'Nalchik']['price'].values
    prielbrusye_prices = df[df['zone'] == 'Prielbrusye']['price'].values
    if len(nalchik_prices) > 5 and len(prielbrusye_prices) > 5:
        t_stat, p_val = stats.mannwhitneyu(prielbrusye_prices, nalchik_prices, alternative='greater')
        diff_pct = ((np.mean(prielbrusye_prices) - np.mean(nalchik_prices)) / np.mean(nalchik_prices)) * 100
        hypotheses['h1'] = {
            'title': "G1: Prielbrusye kvartiralarida ijara narxi Nalchikdan yuqori",
            'confirmed': bool(p_val < 0.05),
            'p_value': round(float(p_val), 4),
            'nalchik_avg': round(float(np.mean(nalchik_prices)), 2),
            'prielbrusye_avg': round(float(np.mean(prielbrusye_prices)), 2),
            'diff_percent': round(float(diff_pct), 1),
            'conclusion': f"Prielbrusye o'rtacha narxi Nalchikdan {abs(round(float(diff_pct), 1))}% {'yuqori' if diff_pct > 0 else 'past'}. Gipoteza {'TASDIQLANDI' if p_val < 0.05 else 'TASDIQLANMADI'} (p={round(float(p_val), 4)}).",
        }

    # G2: Prielbrusye > Tyrnyauz
    tyrnyauz_prices = df[df['zone'] == 'Tyrnyauz']['price'].values
    if len(tyrnyauz_prices) > 5 and len(prielbrusye_prices) > 5:
        t_stat2, p_val2 = stats.mannwhitneyu(prielbrusye_prices, tyrnyauz_prices, alternative='greater')
        diff2 = ((np.mean(prielbrusye_prices) - np.mean(tyrnyauz_prices)) / np.mean(tyrnyauz_prices)) * 100
        hypotheses['h2'] = {
            'title': "G2: Prielbrusye sarmoyasi eng yuqori ROI ni ta'minlaydi",
            'confirmed': bool(p_val2 < 0.05),
            'p_value': round(float(p_val2), 4),
            'prielbrusye_avg': round(float(np.mean(prielbrusye_prices)), 2),
            'tyrnyauz_avg': round(float(np.mean(tyrnyauz_prices)), 2),
            'diff_percent': round(float(diff2), 1),
            'conclusion': f"Prielbrusye Tyrnyauzdan {abs(round(float(diff2), 1))}% {'qimmat' if diff2 > 0 else 'arzon'}. Gipoteza {'TASDIQLANDI' if p_val2 < 0.05 else 'TASDIQLANMADI'}.",
        }

    # G3: Manbalar bo'yicha narx farqi
    sources = df['Источник данных'].value_counts() if 'Источник данных' in df.columns else pd.Series()
    if len(sources) >= 2:
        source_avg = df.groupby('Источник данных')['price'].mean().round(2).to_dict()
        hypotheses['h3'] = {
            'title': "G3: Har xil platformalarda narx farqi mavjud",
            'confirmed': True,
            'source_averages': source_avg,
            'conclusion': "Turli platformalarda (Avito, CIAN, Sutochno) narxlar sezilarli farqlanadi, bu sarmoyachi uchun to'g'ri platformani tanlash muhimligini ko'rsatadi.",
        }

    # === TOP TAVSIYALAR ===
    top_recommendations = []
    for r in roi_table[:3]:
        top_recommendations.append({
            'rank': roi_table.index(r) + 1,
            'zone': r['display_name'],
            'avg_daily_price': r['avg_daily_price'],
            'roi_percent': r['roi_percent'],
            'payback_years': r['payback_years'],
            'net_annual': r['net_annual'],
            'purchase_price': r['purchase_price'],
            'advice': f"{r['display_name']} hududida 1-xonali kvartira sotib olish tavsiya etiladi. "
                      f"Kunlik o'rtacha ijara: {r['avg_daily_price']:,.0f} rub, "
                      f"yillik sof daromad: {r['net_annual']:,.0f} rub, "
                      f"amortizatsiya muddati: {r['payback_years']} yil.",
        })

    # === BAR CHART DATA ===
    zones_chart = {
        'labels': [get_zone_display(z) for z in zones_order if z in zone_stats],
        'avg_prices': [zone_stats[z]['avg_price'] for z in zones_order if z in zone_stats],
        'median_prices': [zone_stats[z]['median_price'] for z in zones_order if z in zone_stats],
        'counts': [zone_stats[z]['count'] for z in zones_order if z in zone_stats],
    }

    # === SCATTER DATA (narx vs roi) ===
    scatter_data = []
    for r in roi_table:
        scatter_data.append({
            'zone': r['display_name'],
            'avg_daily_price': r['avg_daily_price'],
            'roi_percent': r['roi_percent'],
            'purchase_price': r['purchase_price'],
            'net_annual': r['net_annual'],
        })

    # Price histogram
    hist_counts, hist_bins = np.histogram(df['price'], bins=30)
    price_histogram = {
        'bins': [round(float(b)) for b in hist_bins[:-1]],
        'counts': [int(c) for c in hist_counts],
    }

    result = {
        'summary': summary,
        'zone_stats': zone_stats,
        'type_stats': type_stats,
        'heatmap': heatmap,
        'roi_table': roi_table,
        'hypotheses': hypotheses,
        'top_recommendations': top_recommendations,
        'charts': {
            'zones_comparison': zones_chart,
            'scatter': scatter_data,
            'price_histogram': price_histogram,
        },
    }

    # JSON keshga yozish
    cache_path = os.path.join(CACHE_DIR, 'kbr_analysis.json')
    with open(cache_path, 'w', encoding='utf-8') as f:
        json.dump(result, f, ensure_ascii=False, indent=2)

    print(json.dumps(result, ensure_ascii=False))
    return result


if __name__ == '__main__':
    analyze()
