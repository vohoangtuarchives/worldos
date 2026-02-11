<?php

namespace App\Http\Controllers\WriterConsole;

use App\Http\Controllers\Controller;
use App\Domains\WriterConsole\HumanActionValidator;
use Illuminate\Http\Request;

class CanonController extends Controller
{
    private HumanActionValidator $validator;

    public function __construct(HumanActionValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Canonize an event or outcome
     */
    public function store(Request $request)
    {
        // 1. Validate
        $validation = $this->validator->validate('canonize_event', $request->all());
        
        if (!$validation->allowed) {
            return response()->json(['error' => $validation->reason], 403);
        }

        // 2. Logic to mark event as canon (not implemented in prototype yet)
        // This would interact with a CanonRepository

        return response()->json([
            'success' => true, 
            'message' => 'Event canonized. It will now serve as a fixed point for future variations.'
        ]);
    }
}
