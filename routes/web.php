<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KbrController;
use App\Http\Controllers\VacancyController;
use App\Http\Controllers\InfoController;

Route::get('/', fn() => redirect('/kbr'));

// Til almashtirish
Route::get('/lang/{locale}', function (string $locale) {
    $allowed = ['uz', 'ru', 'en'];
    if (in_array($locale, $allowed)) {
        session(['locale' => $locale]);
    }
    return redirect()->back()->withInput();
})->name('lang.switch');

// Kesh yangilash
Route::get('/refresh', function (App\Services\PythonAnalyticsService $svc) {
    $svc->clearCache();
    return redirect('/kbr')->with('success', __('app.cache_refresh'));
});

// Info sahifasi
Route::get('/info', [InfoController::class, 'index'])->name('info');

// Keis 1: KBR
Route::prefix('kbr')->group(function () {
    Route::get('/',            [KbrController::class, 'index']);
    Route::get('/locations',   [KbrController::class, 'locations']);
    Route::get('/types',       [KbrController::class, 'types']);
    Route::get('/roi',         [KbrController::class, 'roi']);
    Route::get('/hypotheses',  [KbrController::class, 'hypotheses']);
});

// Keis 2: Vakansiyalar
Route::prefix('vacancy')->group(function () {
    Route::get('/',            [VacancyController::class, 'index']);
    Route::get('/cities',      [VacancyController::class, 'cities']);
    Route::get('/employers',   [VacancyController::class, 'employers']);
    Route::get('/salary',      [VacancyController::class, 'salary']);
});

// Chart.js uchun API
Route::prefix('api')->group(function () {
    Route::get('/kbr/{chart}',     [KbrController::class, 'chartData']);
    Route::get('/vacancy/{chart}', [VacancyController::class, 'chartData']);
});
