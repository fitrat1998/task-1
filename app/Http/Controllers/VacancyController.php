<?php

namespace App\Http\Controllers;

use App\Services\PythonAnalyticsService;

class VacancyController extends Controller
{
    public function __construct(protected PythonAnalyticsService $analytics) {}

    public function index()
    {
        $data = $this->analytics->runVacancy();
        return view('vacancy.index', compact('data'));
    }

    public function cities()
    {
        $data = $this->analytics->runVacancy();
        return view('vacancy.cities', compact('data'));
    }

    public function employers()
    {
        $data = $this->analytics->runVacancy();
        return view('vacancy.employers', compact('data'));
    }

    public function salary()
    {
        $data = $this->analytics->runVacancy();
        return view('vacancy.salary', compact('data'));
    }

    public function chartData(string $chart)
    {
        $data = $this->analytics->runVacancy();
        return response()->json($data['charts'][$chart] ?? []);
    }
}
