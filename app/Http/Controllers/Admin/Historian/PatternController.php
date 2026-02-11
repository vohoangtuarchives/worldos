<?php

namespace App\Http\Controllers\Admin\Historian;

use App\Http\Controllers\Controller;
use App\Domains\Saga\Saga;
use App\Domains\Historian\PatternDetector;

class PatternController extends Controller
{
    private PatternDetector $detector;

    public function __construct(PatternDetector $detector)
    {
        $this->detector = $detector;
    }

    public function index()
    {
        $sagas = Saga::where('status', Saga::STATUS_COMPLETED)->get();
        
        $patterns = [];
        if ($sagas->isNotEmpty()) {
            $patterns = $this->detector->detectAcrossSagas($sagas->pluck('id')->toArray());
        }

        return view('admin.historian.patterns.index', compact('patterns', 'sagas'));
    }
}
