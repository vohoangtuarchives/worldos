<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>World Management Control Plane</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sidebar {
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
        }
        .sidebar-sticky {
            position: relative;
            top: 0;
            height: calc(100vh - 48px);
            padding-top: .5rem;
            overflow-x: hidden;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    
<header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
  <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="#">WMCP Admin</a>
</header>

<div class="container-fluid">
  <div class="row">
    <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
      <div class="position-sticky pt-3">
        <ul class="nav flex-column">
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.wmcp.dashboard') ? 'active' : '' }}" href="{{ route('admin.wmcp.dashboard') }}">
              📊 Dashboard
            </a>
          </li>
          
          <!-- CORE -->
          <li class="nav-item mt-3">
            <small class="text-muted text-uppercase px-3">Core</small>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.wmcp.worlds.*') ? 'active' : '' }}" href="{{ route('admin.wmcp.worlds.index') }}">
              🌍 Worlds
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.wmcp.timelines.*') ? 'active' : '' }}" href="{{ route('admin.wmcp.timelines.index') }}">
              ⏱️ Timelines
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.wmcp.simulation.*') ? 'active' : '' }}" href="{{ route('admin.wmcp.simulation.index') }}">
              ▶️ Simulation
            </a>
          </li>
          
          <!-- MATERIALS -->
          <li class="nav-item mt-3">
            <small class="text-muted text-uppercase px-3">Materials</small>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.materials.index') || request()->routeIs('admin.materials.show') || request()->routeIs('admin.materials.edit') ? 'active' : '' }}" href="{{ route('admin.materials.index') }}">
              🧱 Material Library
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.materials.analytics') ? 'active' : '' }}" href="{{ route('admin.materials.analytics') }}">
              📈 Material Analytics
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.materials.compatibility') ? 'active' : '' }}" href="{{ route('admin.materials.compatibility') }}">
              🔗 Compatibility Matrix
            </a>
          </li>
          
          <!-- GOVERNANCE -->
          <li class="nav-item mt-3">
            <small class="text-muted text-uppercase px-3">Governance</small>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.wmcp.primitives.*') ? 'active' : '' }}" 
               href="{{ route('admin.wmcp.primitives.index') }}">
                🔷 Primitives (WFR)
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.wmcp.seeds.*') ? 'active' : '' }}" 
               href="{{ route('admin.wmcp.seeds.index') }}">
                🌱 Seed Library
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.wmcp.governance.*') ? 'active' : '' }}" href="{{ route('admin.wmcp.governance.index') }}">
              ⚖️ AI Governance
            </a>
          </li>
          
          <!-- HISTORIAN -->
          <li class="nav-item mt-3">
            <small class="text-muted text-uppercase px-3">Historian</small>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.historian.sagas.*') ? 'active' : '' }}" href="{{ route('admin.historian.sagas.index') }}">
              📚 Saga Analysis
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.historian.patterns.*') ? 'active' : '' }}" href="{{ route('admin.historian.patterns.index') }}">
              🔍 Pattern Detection
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.historian.archetypes.*') ? 'active' : '' }}" href="{{ route('admin.historian.archetypes.index') }}">
              🎭 Archetype Heatmap
            </a>
          </li>
          
          <!-- MONITORING -->
          <li class="nav-item mt-3">
            <small class="text-muted text-uppercase px-3">Monitoring</small>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.wmcp.events.*') ? 'active' : '' }}" href="{{ route('admin.wmcp.events.index') }}">
              📜 Events
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.wmcp.ai-generations.*') ? 'active' : '' }}" href="{{ route('admin.wmcp.ai-generations.index') }}">
              🤖 AI Generations
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.wmcp.alerts.*') ? 'active' : '' }}" href="{{ route('admin.wmcp.alerts.index') }}">
              🚨 Alerts
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.wmcp.incidents.*') ? 'active' : '' }}" href="{{ route('admin.wmcp.incidents.index') }}">
              ⚠️ Incidents
            </a>
          </li>
          
          <!-- OBSERVABILITY -->
          <li class="nav-item mt-3">
            <small class="text-muted text-uppercase px-3">Observability</small>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.wmcp.health.*') ? 'active' : '' }}" 
               href="{{ route('admin.wmcp.health.index') }}">
                📊 Health History
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.wmcp.myths.*') ? 'active' : '' }}" 
               href="{{ route('admin.wmcp.myths.index') }}">
                🧠 Myths
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.wmcp.scars.*') ? 'active' : '' }}" 
               href="{{ route('admin.wmcp.scars.index') }}">
                ⚡ Scars
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.wmcp.audit.*') ? 'active' : '' }}" href="{{ route('admin.wmcp.audit.index') }}">
              📝 Audit Log
            </a>
          </li>
          
          <!-- HISTORIAN (Phase 5) -->
          <li class="nav-item mt-3">
            <small class="text-muted text-uppercase px-3">Historian Research</small>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.historian.*') ? 'active' : '' }}" href="{{ route('admin.historian.dashboard') }}">
              📜 Research Platform
            </a>
          </li>

          <!-- SIMULATION -->
          <li class="nav-item mt-3">
            <small class="text-muted text-uppercase px-3">Simulation</small>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.wmcp.simulation.*') ? 'active' : '' }}" href="{{ route('admin.wmcp.simulation.index') }}">
              ⚙️ Simulation
            </a>
          </li>
        </ul>
      </div>
    </nav>

    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 mt-4">
      @if(session('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
      @endif
      
      @yield('content')
    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
