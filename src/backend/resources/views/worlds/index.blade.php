@extends('layouts.app')

@section('title', 'Worlds - WorldOS')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-globe"></i> Worlds</h1>
                <div>
                    <a href="{{ route('worlds.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create World
                    </a>
                    <button onclick="startAllWorlds()" class="btn btn-success ms-2">
                        <i class="fas fa-play"></i> Start All
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Worlds</h5>
                            <h2>{{ $totalWorlds }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">Autonomous</h5>
                            <h2>{{ $autonomousWorlds }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h5 class="card-title">Running</h5>
                            <h2 id="running-count">-</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Ticks</h5>
                            <h2 id="total-ticks">-</h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Worlds Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">World List</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="worlds-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Preset</th>
                                    <th>Tick</th>
                                    <th>Entropy</th>
                                    <th>Autonomous</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($worlds as $world)
                                <tr data-world-id="{{ $world->id() }}">
                                    <td>{{ $world->id() }}</td>
                                    <td>
                                        <a href="{{ route('worlds.show', $world->id()) }}" class="text-decoration-none">
                                            {{ $world->name() }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $world->preset() }}</span>
                                    </td>
                                    <td>
                                        <span class="tick-count">{{ $world->currentTick() }}</span>
                                    </td>
                                    <td>
                                        <div class="entropy-indicator">
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar entropy-bar" 
                                                     style="width: {{ $world->currentEntropy()->value() * 100 }}%"
                                                     data-entropy="{{ $world->currentEntropy()->value() }}">
                                                </div>
                                            </div>
                                            <small>{{ number_format($world->currentEntropy()->value() * 100, 1) }}%</small>
                                        </div>
                                    </td>
                                    <td>
                                        @if($world->isAutonomous())
                                            <span class="badge bg-success">Autonomous</span>
                                        @else
                                            <span class="badge bg-secondary">Manual</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="status-indicator" data-world-id="{{ $world->id() }}">
                                            <i class="fas fa-spinner fa-spin"></i> Loading...
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('worlds.dashboard', $world->id()) }}" 
                                               class="btn btn-sm btn-outline-primary" title="Dashboard">
                                                <i class="fas fa-tachometer-alt"></i>
                                            </a>
                                            <a href="{{ route('worlds.show', $world->id()) }}" 
                                               class="btn btn-sm btn-outline-info" title="Details">
                                                <i class="fas fa-info-circle"></i>
                                            </a>
                                            <button onclick="toggleWorld('{{ $world->id() }}')" 
                                                    class="btn btn-sm btn-outline-success" 
                                                    id="toggle-{{ $world->id() }}" title="Start/Stop">
                                                <i class="fas fa-play"></i>
                                            </button>
                                            <button onclick="tickWorld('{{ $world->id() }}')" 
                                                    class="btn btn-sm btn-outline-warning" title="Single Tick">
                                                <i class="fas fa-step-forward"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Global state
let worldStatuses = {};
let updateInterval;

// Initialize
$(document).ready(function() {
    loadWorldStatuses();
    startRealTimeUpdates();
});

// Load initial world statuses
function loadWorldStatuses() {
    $('.status-indicator').each(function() {
        const worldId = $(this).data('world-id');
        updateWorldStatus(worldId);
    });
}

// Start real-time updates
function startRealTimeUpdates() {
    updateInterval = setInterval(function() {
        updateAllWorldStatuses();
        updateGlobalStats();
    }, 5000); // Update every 5 seconds
}

// Update single world status
function updateWorldStatus(worldId) {
    $.get(`/api/worlds/${worldId}/realtime`)
        .done(function(data) {
            if (data.success) {
                worldStatuses[worldId] = data;
                updateWorldRow(worldId, data);
            }
        })
        .fail(function() {
            $(`.status-indicator[data-world-id="${worldId}"]`)
                .html('<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> Error</span>');
        });
}

// Update all world statuses
function updateAllWorldStatuses() {
    Object.keys(worldStatuses).forEach(worldId => {
        updateWorldStatus(worldId);
    });
}

// Update world row in table
function updateWorldRow(worldId, data) {
    const row = $(`tr[data-world-id="${worldId}"]`);
    
    // Update tick
    row.find('.tick-count').text(data.world.tick);
    
    // Update entropy
    const entropyPercent = data.world.entropy * 100;
    row.find('.entropy-bar')
        .css('width', entropyPercent + '%')
        .removeClass('bg-success bg-warning bg-danger')
        .addClass(getEntropyClass(data.world.entropy));
    row.find('.entropy-indicator small').text(entropyPercent.toFixed(1) + '%');
    
    // Update status indicator
    const statusEl = row.find('.status-indicator');
    const statusText = data.world.autonomous ? 
        '<span class="text-success"><i class="fas fa-play-circle"></i> Running</span>' : 
        '<span class="text-secondary"><i class="fas fa-pause-circle"></i> Stopped</span>';
    statusEl.html(statusText);
    
    // Update toggle button
    const toggleBtn = row.find('#toggle-' + worldId);
    if (data.world.autonomous) {
        toggleBtn.removeClass('btn-outline-success')
                 .addClass('btn-outline-danger')
                 .html('<i class="fas fa-stop"></i>');
    } else {
        toggleBtn.removeClass('btn-outline-danger')
                 .addClass('btn-outline-success')
                 .html('<i class="fas fa-play"></i>');
    }
}

// Get entropy class based on value
function getEntropyClass(entropy) {
    if (entropy < 0.3) return 'bg-success';
    if (entropy < 0.7) return 'bg-warning';
    return 'bg-danger';
}

// Toggle world (start/stop)
function toggleWorld(worldId) {
    const isRunning = worldStatuses[worldId]?.world?.autonomous;
    const action = isRunning ? 'stop' : 'start';
    
    $.post(`/worlds/${worldId}/${action}`)
        .done(function(data) {
            if (data.success) {
                updateWorldStatus(worldId);
                showNotification(`World ${worldId} ${action}ed successfully`, 'success');
            } else {
                showNotification(`Failed to ${action} world ${worldId}: ${data.error}`, 'error');
            }
        })
        .fail(function() {
            showNotification(`Failed to ${action} world ${worldId}`, 'error');
        });
}

// Single tick
function tickWorld(worldId) {
    $.post(`/worlds/${worldId}/tick`, {count: 1})
        .done(function(data) {
            if (data.success) {
                updateWorldStatus(worldId);
                showNotification(`World ${worldId} ticked successfully`, 'success');
            } else {
                showNotification(`Failed to tick world ${worldId}: ${data.error}`, 'error');
            }
        })
        .fail(function() {
            showNotification(`Failed to tick world ${worldId}`, 'error');
        });
}

// Start all worlds
function startAllWorlds() {
    $.post('/api/worlds/start-all')
        .done(function(data) {
            if (data.success) {
                showNotification('Started all autonomous worlds', 'success');
                loadWorldStatuses();
            } else {
                showNotification('Failed to start all worlds', 'error');
            }
        })
        .fail(function() {
            showNotification('Failed to start all worlds', 'error');
        });
}

// Update global stats
function updateGlobalStats() {
    let runningCount = 0;
    let totalTicks = 0;
    
    Object.values(worldStatuses).forEach(status => {
        if (status.world.autonomous) runningCount++;
        totalTicks += status.world.tick;
    });
    
    $('#running-count').text(runningCount);
    $('#total-ticks').text(totalTicks);
}

// Show notification
function showNotification(message, type) {
    // Simple notification - could be replaced with a proper toast library
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const notification = $(`
        <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999;">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);
    
    $('body').append(notification);
    
    setTimeout(function() {
        notification.alert('close');
    }, 3000);
}

// Cleanup on page unload
$(window).on('beforeunload', function() {
    if (updateInterval) {
        clearInterval(updateInterval);
    }
});
</script>

<style>
.entropy-indicator .progress {
    background-color: #f8f9fa;
}

.entropy-bar {
    transition: width 0.5s ease-in-out;
}

.status-indicator {
    min-width: 100px;
    display: inline-block;
}

.btn-group .btn {
    padding: 0.25rem 0.5rem;
}

.table th {
    border-top: none;
    font-weight: 600;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}
</style>
@endpush
