<?php

namespace App\Http\Controllers;

use App\Services\PythonAnalyticsService;

class KbrController extends Controller
{
    public function __construct(protected PythonAnalyticsService $analytics) {}

    public function index()
    {
        $data = $this->analytics->runKbr();
        return view('kbr.index', compact('data'));
    }

    public function locations()
    {
        $data = $this->analytics->runKbr();
        return view('kbr.locations', compact('data'));
    }

    public function types()
    {
        $data = $this->analytics->runKbr();
        return view('kbr.types', compact('data'));
    }

    public function roi()
    {
        $data = $this->analytics->runKbr();
        return view('kbr.roi', compact('data'));
    }

    public function hypotheses()
    {
        $data = $this->analytics->runKbr();
        return view('kbr.hypotheses', compact('data'));
    }

    public function chartData(string $chart)
    {
        $data = $this->analytics->runKbr();
        return response()->json($data['charts'][$chart] ?? []);
    }
}
