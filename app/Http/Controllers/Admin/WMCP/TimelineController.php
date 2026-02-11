<?php

namespace App\Http\Controllers\Admin\WMCP;

use App\Http\Controllers\Controller;
use App\Models\World;

class TimelineController extends Controller
{
    public function index()
    {
        // Fetch all worlds to build the tree
        $worlds = World::select('id', 'parent_id', 'name', 'created_at')
            ->with('clock')
            ->get();
            
        // Convert to Mermaid Graph Syntax
        $graph = "graph TD;\n";
        foreach ($worlds as $world) {
            // Node definition
            $label = "{$world->name}<br/>(ID: {$world->id})";
            $graph .= "    w{$world->id}[\"{$label}\"];\n";
            
            // Edge definition (Parent -> Child)
            if ($world->parent_id) {
                $graph .= "    w{$world->parent_id} --> w{$world->id};\n";
            }
        }
        
        // Add styling or click events if needed
        $graph .= "    classDef default fill:#f9f,stroke:#333,stroke-width:2px;\n";

        return view('admin.wmcp.timelines.index', compact('graph'));
    }
}
