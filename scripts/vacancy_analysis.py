#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Ko'chmas mulk baholovchilari uchun vakansiyalar bozorini tahlil qilish
"""

import sys
import json
import os
import re
import warnings
import pandas as pd
import numpy as np
from scipy import stats

warnings.filterwarnings('ignore')

BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
DATA_DIR = os.path.join(BASE_DIR, 'storage', 'app', 'data')
CACHE_DIR = os.path.join(BASE_DIR, 'storage', 'app', 'cache')

VAC_FILE = os.path.join(DATA_DIR, '!Вакансии для специалистов по оценке недвижимости_24_25.csv.csv')

# Tegishli kalit so'zlar
RELEVANT_KEYWORDS = [
    'оценщик', 'оценка недвижимости', 'оценка имущества', 'оценка стоимости',
    'ведущий оценщик', 'помощник оценщика', 'эксперт-оценщик', 'кадастровая оценка',
    'ассистент оценщика', 'оценщик недвижимости', 'оценка объектов',
    'оценка бизнеса', 'аналитик недвижимости'
]

CITY_GROUPS = {
    'Moskva': ['Москва', 'москва', 'moscow'],
    'Sankt-Peterburg': ['Санкт-Петербург', 'санкт-петербург', 'спб', 'питер'],
    'Yekaterinburg': ['Екатеринбург', 'екатеринбург'],
    'Novosibirsk': ['Новосибирск', 'новосибирск'],
    'Kazan': ['Казань', 'казань'],
    'Krasnodar': ['Краснодар', 'краснодар'],
    'Sochi': ['Сочи', 'сочи'],
    'Nizhniy Novgorod': ['Нижний Новгород', 'нижний новгород'],
}

EMPLOYER_TYPE_KEYWORDS = {
    'Baholash kompaniyasi': ['оценочная', 'оценка', 'оценщик', 'экспертиза', 'инверсия', 'юрдис', 'авангард', 'интерэкспертиза'],
    'Bank va moliya': ['банк', 'страховани', 'финанс', 'ипотек', 'ренессанс'],
    'Davlat tashkiloti': ['гбу', 'министерство', 'государственн', 'кадастровой', 'ГБУ', 'росреестр', 'фгуп'],
    'Developer': ['девелоп', 'застройщик', 'строительство', 'строительн', 'самолет', 'пик', 'жк'],
    'Boshqalar': [],
}


def load_data():
    try:
        df = pd.read_csv(VAC_FILE, encoding='utf-8-sig')
    except Exception:
        df = pd.read_csv(VAC_FILE, encoding='cp1251')

    df.columns = df.columns.str.strip()
    return df


def is_relevant(row):
    """Vakansiya baholovchiga tegishlimi?"""
    name = str(row.get('name', '')).lower()
    desc = str(row.get('description', '')).lower()
    text = name + ' ' + desc[:500]
    return any(kw.lower() in text for kw in RELEVANT_KEYWORDS)


def get_city_group(city_name):
    """Shaharni guruhga birlashtirish"""
    city = str(city_name).strip()
    for group, names in CITY_GROUPS.items():
        if any(n.lower() in city.lower() for n in names):
            return group
    return 'Boshqa mintaqalar'


def get_employer_type(employer_name):
    """Ish beruvchi turini aniqlash"""
    name = str(employer_name).lower()
    for emp_type, keywords in EMPLOYER_TYPE_KEYWORDS.items():
        if emp_type == 'Boshqalar':
            continue
        if any(kw.lower() in name for kw in keywords):
            return emp_type
    return 'Boshqalar'


def parse_experience(exp):
    """Tajriba darajasini normallashtirish"""
    e = str(exp).strip()
    if e in ['0', '']:
        return 'Talab yo\'q'
    if '1-3' in e:
        return '1–3 yil'
    if '3-6' in e:
        return '3–6 yil'
    if '6+' in e or '>6' in e:
        return '6+ yil'
    return 'Boshqa'


def analyze():
    df = load_data()

    # Faqat tegishli vakansiyalarni filtrlash
    mask = df.apply(is_relevant, axis=1)
    rel = df[mask].copy()

    if len(rel) == 0:
        # Agar hech narsa topilmasa, barcha ma'lumotlarni ishlatamiz
        rel = df.copy()

    # Maoshni tayyorlash
    rel['salary'] = pd.to_numeric(rel['salary'], errors='coerce')
    rel_salary = rel[rel['salary'].notna() & (rel['salary'] > 10000) & (rel['salary'] < 1_500_000)].copy()

    # Ustunlar mavjudligini tekshirish
    city_col = 'region_city_name' if 'region_city_name' in rel.columns else None
    emp_col = 'employer_name' if 'employer_name' in rel.columns else None
    schedule_col = 'schedule_label' if 'schedule_label' in rel.columns else None
    emp_type_col = 'employment_label' if 'employment_label' in rel.columns else None
    exp_col = 'experience' if 'experience' in rel.columns else None

    # Shahar va tur ustunlari qo'shish
    if city_col:
        rel['city_group'] = rel[city_col].apply(get_city_group)
        rel_salary['city_group'] = rel_salary[city_col].apply(get_city_group)
    else:
        rel['city_group'] = 'Noma\'lum'
        rel_salary['city_group'] = 'Noma\'lum'

    if emp_col:
        rel['employer_type'] = rel[emp_col].apply(get_employer_type)
        rel_salary['employer_type'] = rel_salary[emp_col].apply(get_employer_type)
    else:
        rel['employer_type'] = 'Noma\'lum'
        rel_salary['employer_type'] = 'Noma\'lum'

    if exp_col:
        rel['exp_level'] = rel[exp_col].apply(parse_experience)
        rel_salary['exp_level'] = rel_salary[exp_col].apply(parse_experience)

    # === UMUMIY STATISTIKA ===
    summary = {
        'total_vacancies': int(len(rel)),
        'vacancies_with_salary': int(len(rel_salary)),
        'avg_salary': round(float(rel_salary['salary'].mean()), 2) if len(rel_salary) > 0 else 0,
        'median_salary': round(float(rel_salary['salary'].median()), 2) if len(rel_salary) > 0 else 0,
        'min_salary': round(float(rel_salary['salary'].min()), 2) if len(rel_salary) > 0 else 0,
        'max_salary': round(float(rel_salary['salary'].max()), 2) if len(rel_salary) > 0 else 0,
    }

    # === SHAHARLAR BO'YICHA ===
    city_stats = {}
    if len(rel_salary) > 0:
        for city in rel_salary['city_group'].unique():
            cdf = rel_salary[rel_salary['city_group'] == city]
            if len(cdf) < 2:
                continue
            city_stats[city] = {
                'count': int(len(cdf)),
                'avg_salary': round(float(cdf['salary'].mean()), 2),
                'median_salary': round(float(cdf['salary'].median()), 2),
                'min_salary': round(float(cdf['salary'].min()), 2),
                'max_salary': round(float(cdf['salary'].max()), 2),
                'q25': round(float(cdf['salary'].quantile(0.25)), 2),
                'q75': round(float(cdf['salary'].quantile(0.75)), 2),
            }

    # === ISH BERUVCHI TURI BO'YICHA ===
    employer_stats = {}
    if len(rel_salary) > 0:
        for et in rel_salary['employer_type'].unique():
            edf = rel_salary[rel_salary['employer_type'] == et]
            if len(edf) < 2:
                continue
            employer_stats[et] = {
                'count': int(len(edf)),
                'avg_salary': round(float(edf['salary'].mean()), 2),
                'median_salary': round(float(edf['salary'].median()), 2),
                'min_salary': round(float(edf['salary'].min()), 2),
                'max_salary': round(float(edf['salary'].max()), 2),
            }

    # === JADVAL FORMATI BO'YICHA ===
    schedule_stats = {}
    if schedule_col and len(rel_salary) > 0:
        for sched in rel_salary[schedule_col].unique():
            sdf = rel_salary[rel_salary[schedule_col] == sched]
            if len(sdf) < 2:
                continue
            schedule_stats[str(sched)] = {
                'count': int(len(sdf)),
                'avg_salary': round(float(sdf['salary'].mean()), 2),
                'median_salary': round(float(sdf['salary'].median()), 2),
            }

    # === TAJRIBA BO'YICHA ===
    exp_stats = {}
    if exp_col and len(rel_salary) > 0:
        for exp in rel_salary['exp_level'].unique():
            edf = rel_salary[rel_salary['exp_level'] == exp]
            if len(edf) < 2:
                continue
            exp_stats[exp] = {
                'count': int(len(edf)),
                'avg_salary': round(float(edf['salary'].mean()), 2),
                'median_salary': round(float(edf['salary'].median()), 2),
            }

    # === GIPOTEZALAR ===
    hypotheses = {}

    # G1: Moskva > Mintaqalar
    msk = rel_salary[rel_salary['city_group'] == 'Moskva']['salary'].values
    other_cities = rel_salary[rel_salary['city_group'] != 'Moskva']['salary'].values
    if len(msk) >= 5 and len(other_cities) >= 5:
        t_stat, p_val = stats.mannwhitneyu(msk, other_cities, alternative='greater')
        diff_pct = ((np.mean(msk) - np.mean(other_cities)) / np.mean(other_cities)) * 100
        hypotheses['h1'] = {
            'title': "G1: Moskva mintaqalardagiga qaraganda 40%+ yuqori maosh to'laydi",
            'confirmed': bool(p_val < 0.05),
            'p_value': round(float(p_val), 4),
            'moscow_avg': round(float(np.mean(msk)), 2),
            'other_avg': round(float(np.mean(other_cities)), 2),
            'diff_percent': round(float(diff_pct), 1),
            'conclusion': f"Moskva o'rtacha maoshi mintaqalardan {abs(round(float(diff_pct), 1))}% {'yuqori' if diff_pct > 0 else 'past'}. Gipoteza {'TASDIQLANDI' if p_val < 0.05 else 'TASDIQLANMADI'} (p={round(float(p_val), 4)}).",
        }

    # G2: Bank > Baholash kompaniyasi
    bank = rel_salary[rel_salary['employer_type'] == 'Bank va moliya']['salary'].values
    eval_co = rel_salary[rel_salary['employer_type'] == 'Baholash kompaniyasi']['salary'].values
    if len(bank) >= 3 and len(eval_co) >= 3:
        t2, p2 = stats.mannwhitneyu(bank, eval_co, alternative='greater') if len(bank) >= 5 else (0, 0.5)
        diff2 = ((np.mean(bank) - np.mean(eval_co)) / np.mean(eval_co)) * 100 if np.mean(eval_co) > 0 else 0
        hypotheses['h2'] = {
            'title': "G2: Banklar baholash kompaniyalaridan ko'proq maosh to'laydi",
            'confirmed': bool(p2 < 0.05) if len(bank) >= 5 else None,
            'bank_avg': round(float(np.mean(bank)), 2) if len(bank) > 0 else 0,
            'eval_avg': round(float(np.mean(eval_co)), 2) if len(eval_co) > 0 else 0,
            'diff_percent': round(float(diff2), 1),
            'conclusion': f"Bank sektori o'rtacha maoshi baholash kompaniyalaridan {abs(round(float(diff2), 1))}% {'yuqori' if diff2 > 0 else 'past'}.",
        }

    # G3: Masofaviy < Ofis
    if schedule_col and len(rel_salary) > 0:
        remote = rel_salary[rel_salary[schedule_col].str.contains('Удал|удал|remote', na=False, case=False)]['salary'].values
        office = rel_salary[rel_salary[schedule_col].str.contains('Полный день|полный', na=False, case=False)]['salary'].values
        if len(remote) >= 3 and len(office) >= 3:
            diff3 = ((np.mean(remote) - np.mean(office)) / np.mean(office)) * 100
            hypotheses['h3'] = {
                'title': "G3: Masofaviy ish ofis ishidan past maoshga ega",
                'confirmed': bool(diff3 < 0),
                'remote_avg': round(float(np.mean(remote)), 2),
                'office_avg': round(float(np.mean(office)), 2),
                'diff_percent': round(float(diff3), 1),
                'conclusion': f"Masofaviy ish o'rtacha maoshi ofis ishidan {abs(round(float(diff3), 1))}% {'past' if diff3 < 0 else 'yuqori'}. Gipoteza {'TASDIQLANDI' if diff3 < 0 else 'TASDIQLANMADI'}.",
            }

    # === TOP KO'NIKMALAR ===
    top_skills = {'hard': {}, 'soft': {}}
    if 'hard_skills' in rel.columns:
        all_hard = []
        for s in rel['hard_skills'].dropna():
            skills = re.findall(r'[\w\s\-\.]+', str(s).replace('[', '').replace(']', '').replace('"', ''))
            all_hard.extend([sk.strip() for sk in skills if len(sk.strip()) > 3])
        from collections import Counter
        hard_counter = Counter(all_hard)
        top_skills['hard'] = dict(hard_counter.most_common(15))

    if 'soft_skills' in rel.columns:
        all_soft = []
        for s in rel['soft_skills'].dropna():
            skills = re.findall(r'[\w\s\-\.]+', str(s).replace('[', '').replace(']', '').replace('"', ''))
            all_soft.extend([sk.strip() for sk in skills if len(sk.strip()) > 3])
        from collections import Counter
        soft_counter = Counter(all_soft)
        top_skills['soft'] = dict(soft_counter.most_common(15))

    # === CHART DATA ===
    # Shaharlar bo'yicha bar chart
    city_chart = {
        'labels': list(city_stats.keys()),
        'avg_salaries': [city_stats[c]['avg_salary'] for c in city_stats],
        'median_salaries': [city_stats[c]['median_salary'] for c in city_stats],
        'counts': [city_stats[c]['count'] for c in city_stats],
    }

    # Ish beruvchi turi bo'yicha
    emp_chart = {
        'labels': list(employer_stats.keys()),
        'avg_salaries': [employer_stats[e]['avg_salary'] for e in employer_stats],
        'median_salaries': [employer_stats[e]['median_salary'] for e in employer_stats],
        'counts': [employer_stats[e]['count'] for e in employer_stats],
    }

    # Maosh histogrammasi
    if len(rel_salary) > 0:
        hist_counts, hist_bins = np.histogram(rel_salary['salary'], bins=25)
        salary_histogram = {
            'bins': [round(float(b)) for b in hist_bins[:-1]],
            'counts': [int(c) for c in hist_counts],
        }
    else:
        salary_histogram = {'bins': [], 'counts': []}

    # === TOP 3 TAVSIYA ===
    top_recommendations = []
    if city_stats:
        sorted_cities = sorted(city_stats.items(), key=lambda x: x[1]['median_salary'], reverse=True)
        for i, (city, stats_data) in enumerate(sorted_cities[:3]):
            best_emp = max(employer_stats.items(), key=lambda x: x[1]['median_salary'])[0] if employer_stats else 'Baholash kompaniyasi'
            top_recommendations.append({
                'rank': i + 1,
                'city': city,
                'median_salary': stats_data['median_salary'],
                'avg_salary': stats_data['avg_salary'],
                'best_employer_type': best_emp,
                'advice': f"{city} shahridagi {best_emp}da ishlash tavsiya etiladi. "
                          f"Median maosh: {stats_data['median_salary']:,.0f} rub/oy.",
            })

    result = {
        'summary': summary,
        'city_stats': city_stats,
        'employer_stats': employer_stats,
        'schedule_stats': schedule_stats,
        'exp_stats': exp_stats,
        'top_skills': top_skills,
        'hypotheses': hypotheses,
        'top_recommendations': top_recommendations,
        'charts': {
            'city_comparison': city_chart,
            'employer_comparison': emp_chart,
            'salary_histogram': salary_histogram,
        },
    }

    # JSON keshga yozish
    cache_path = os.path.join(CACHE_DIR, 'vacancy_analysis.json')
    with open(cache_path, 'w', encoding='utf-8') as f:
        json.dump(result, f, ensure_ascii=False, indent=2)

    print(json.dumps(result, ensure_ascii=False))
    return result


if __name__ == '__main__':
    analyze()
