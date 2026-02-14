@extends('layouts.writer')

@section('content')
<div class="space-y-6 h-screen flex flex-col">
    <!-- Header -->
    <div class="flex items-center justify-between pb-4 border-b border-gray-700 shrink-0">
        <div>
            <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-amber-200 to-yellow-500">
                🌳 Yggdrasil Multiverse Tree
            </h1>
            <p class="text-sm text-gray-400">Saga: {{ $saga->name }}</p>
        </div>
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2 text-xs">
                <span class="w-3 h-3 rounded-full bg-indigo-500"></span> Cosmic
                <span class="w-3 h-3 rounded-full bg-red-500 ml-2"></span> Vietnamese
                <span class="w-3 h-3 rounded-full border-2 border-amber-400 ml-2"></span> Bifurcation
            </div>
            <a href="{{ route('writer.sagas.show', $saga) }}" class="px-4 py-2 rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm font-medium transition-colors">
                ← Back to Saga
            </a>
        </div>
    </div>

    <!-- Visualization Container -->
    <div id="tree-container" class="flex-1 bg-gray-900 rounded-2xl border border-gray-800 shadow-inner relative overflow-hidden">
        <div class="absolute top-4 right-4 bg-gray-900/80 backdrop-blur p-4 rounded-xl border border-gray-800 text-xs text-gray-400 max-w-xs z-10 hidden" id="node-tooltip">
            <h3 class="font-bold text-white text-sm mb-1" id="tt-name"></h3>
            <div id="tt-details" class="space-y-1"></div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://d3js.org/d3.v7.min.js"></script>
<script>
    const treeData = @json($treeData);
    
    // Process Data into Hierarchy
    const rootNode = treeData.find(n => !n.parentId) || treeData[0];
    const stratify = d3.stratify()
        .id(d => d.id)
        .parentId(d => d.parentId);

    let root;
    try {
        root = stratify(treeData);
    } catch (e) {
        console.error("Hierarchy Error:", e);
        // Fallback for flat list or broken hierarchy
        root = d3.hierarchy({name: "Root", children: treeData});
    }

    // Dimensions
    const container = document.getElementById('tree-container');
    const width = container.clientWidth;
    const height = container.clientHeight;

    const svg = d3.select("#tree-container").append("svg")
        .attr("width", "100%")
        .attr("height", "100%")
        .call(d3.zoom().on("zoom", (event) => {
            g.attr("transform", event.transform);
        }));

    const g = svg.append("g").attr("transform", `translate(${width/2}, 50)`);

    // Tree Layout
    const treeLayout = d3.tree().size([width - 100, height - 100]);
    treeLayout(root);

    // Links
    g.selectAll(".link")
        .data(root.links())
        .enter().append("path")
        .attr("class", "link")
        .attr("d", d3.linkVertical()
            .x(d => d.x)
            .y(d => d.y))
        .attr("fill", "none")
        .attr("stroke", "#4b5563")
        .attr("stroke-width", 2)
        .attr("opacity", 0.6);

    // Nodes
    const nodes = g.selectAll(".node")
        .data(root.descendants())
        .enter().append("g")
        .attr("class", "node")
        .attr("transform", d => `translate(${d.x},${d.y})`)
        .style("cursor", "pointer")
        .on("mouseover", showTooltip)
        .on("mouseout", hideTooltip)
        .on("click", (event, d) => {
            window.location.href = `/writer/sagas/{{ $saga->id }}/worlds/${d.data.sequence}`;
        });

    // Node Circles
    nodes.append("circle")
        .attr("r", 12)
        .attr("fill", d => d.data.origin_type === 'vietnamese' ? '#ef4444' : '#6366f1')
        .attr("stroke", d => d.data.bifurcation_trigger ? '#fbbf24' : '#1f2937')
        .attr("stroke-width", d => d.data.bifurcation_trigger ? 3 : 2)
        .attr("class", "shadow-lg");

    // Icons
    nodes.append("text")
        .attr("dy", 4)
        .attr("text-anchor", "middle")
        .text(d => d.data.origin_type === 'vietnamese' ? '🇻🇳' : '🌌')
        .style("font-size", "10px");

    // Labels
    nodes.append("text")
        .attr("dy", 25)
        .attr("text-anchor", "middle")
        .text(d => d.data.name)
        .attr("fill", "#e5e7eb")
        .style("font-size", "10px")
        .style("font-weight", "500")
        .style("text-shadow", "0 1px 2px black");

    // Tooltip Logic
    const tooltip = document.getElementById('node-tooltip');
    
    function showTooltip(event, d) {
        // Highlight Node
        d3.select(this).select("circle").attr("stroke", "#fff");

        tooltip.classList.remove('hidden');
        document.getElementById('tt-name').textContent = d.data.name;
        
        const details = `
            <div class="flex justify-between"><span>Origin:</span> <span class="text-white">${d.data.origin_type}</span></div>
            <div class="flex justify-between"><span>Era:</span> <span class="text-white">${d.data.current_era}</span></div>
            <div class="flex justify-between"><span>Status:</span> <span class="text-white">${d.data.status}</span></div>
            ${d.data.bifurcation_trigger ? `<div class="mt-2 pt-2 border-t border-gray-700 text-amber-400 font-mono text-[10px]">⚡ Trigger: ${d.data.bifurcation_trigger}</div>` : ''}
        `;
        document.getElementById('tt-details').innerHTML = details;
    }

    function hideTooltip(event, d) {
        d3.select(this).select("circle")
            .attr("stroke", d => d.data.bifurcation_trigger ? '#fbbf24' : '#1f2937');
        tooltip.classList.add('hidden');
    }

</script>
@endpush
@endsection
