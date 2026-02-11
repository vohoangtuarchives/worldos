<?php

namespace App\Http\Controllers\Admin\Historian;

use App\Http\Controllers\Controller;
use App\Domains\Saga\Saga;
use App\Domains\Historian\SagaAnalyzer;

class SagaController extends Controller
{
    private SagaAnalyzer $analyzer;

    public function __construct(SagaAnalyzer $analyzer)
    {
        $this->analyzer = $analyzer;
    }

    public function index()
    {
        $sagas = Saga::orderBy('created_at', 'desc')->paginate(20);
        return view('admin.historian.sagas.index', compact('sagas'));
    }

    public function show(Saga $saga)
    {
        $analysis = null;
        if ($saga->isComplete()) {
            $analysis = $this->analyzer->analyze($saga);
        }

        return view('admin.historian.sagas.show', compact('saga', 'analysis'));
    }
}
