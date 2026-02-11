<?php

namespace App\Http\Controllers\Admin\Historian;

use App\Http\Controllers\Controller;
use App\Domains\Saga\Saga;
use App\Domains\Historian\PatternDetector;

class DashboardController extends Controller
{
    private PatternDetector $patternDetector;

    public function __construct(PatternDetector $patternDetector)
    {
        $this->patternDetector = $patternDetector;
    }

    public function index()
    {
        $recentSagas = Saga::orderBy('created_at', 'desc')->take(5)->get();
        $totalSagas = Saga::count();
        $completedSagas = Saga::where('status', Saga::STATUS_COMPLETED)->count();

        // Get global patterns from completed sagas
        $completedSagaIds = Saga::where('status', Saga::STATUS_COMPLETED)->pluck('id')->toArray();
        $patterns = [];
        if (!empty($completedSagaIds)) {
            $patterns = $this->patternDetector->detectAcrossSagas($completedSagaIds);
        }

        return view('admin.historian.dashboard', [
            'recentSagas' => $recentSagas,
            'stats' => [
                'total' => $totalSagas,
                'completed' => $completedSagas,
                'worlds_simulated' => \App\Models\World::count(), // Approximate
            ],
            'globalPatterns' => $patterns
        ]);
    }
}
