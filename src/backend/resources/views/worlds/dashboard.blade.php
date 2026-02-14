@extends('layouts.app')

@section('title', "World Dashboard - {$world->name()}")

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-globe"></i> {{ $world->name() }}</h1>
                    <p class="text-muted mb-0">World ID: {{ $world->id() }}</p>
                </div>
                <div>
                    <span class="badge bg-{{ $world->isAutonomous() ? 'success' : 'secondary' }} fs-6">
                        {{ $world->isAutonomous() ? 'Autonomous' : 'Manual' }}
                    </span>
                    <span class="badge bg-info fs-6 ms-2">{{ $world->preset() }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Real-time Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-2x mb-2"></i>
                    <h5 class="card-title">Current Tick</h5>
                    <h2 id="current-tick">{{ $world->currentTick() }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <i class="fas fa-fire fa-2x mb-2"></i>
                    <h5 class="card-title">Entropy Level</h5>
                    <h2 id="entropy-level">{{ number_format($world->currentEntropy()->value() * 100, 1) }}%</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-2x mb-2"></i>
                    <h5 class="card-title">Alive Characters</h5>
                    <h2 id="alive-characters">-</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <i class="fas fa-box fa-2x mb-2"></i>
                    <h5 class="card-title">Active Materials</h5>
                    <h2 id="active-materials">-</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Control Panel -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-gamepad"></i> Control Panel</h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <button onclick="toggleWorld()" class="btn btn-lg btn-{{ $world->isAutonomous() ? 'danger' : 'success' }} w-100" id="toggle-btn">
                                <i class="fas fa-{{ $world->isAutonomous() ? 'stop' : 'play' }}"></i>
                                {{ $world->isAutonomous() ? 'Stop Autonomous' : 'Start Autonomous' }}
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button onclick="singleTick()" class="btn btn-lg btn-warning w-100">
                                <i class="fas fa-step-forward"></i> Single Tick
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button onclick="multiTick()" class="btn btn-lg btn-info w-100">
                                <i class="fas fa-forward"></i> Multi Tick (10)
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button onclick="refreshData()" class="btn btn-lg btn-secondary w-100">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>
                    
                    <!-- Tick Speed Control -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <label for="tick-speed" class="form-label">Tick Speed (seconds)</label>
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <input type="range" class="form-range" id="tick-speed" 
                                           min="1" max="30" value="5" id="tickSpeed">
                                </div>
                                <div class="col-md-4">
                                    <span class="badge bg-primary" id="speed-display">5s</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Entropy Chart -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-chart-line"></i> Entropy Over Time</h5>
                </div>
                <div class="card-body">
                    <canvas id="entropy-chart" height="200"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Population Chart -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-chart-pie"></i> Population Status</h5>
                </div>
                <div class="card-body">
                    <canvas id="population-chart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Events -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-bolt"></i> Recent Shock Events</h5>
                </div>
                <div class="card-body">
                    <div id="recent-events" class="event-list">
                        <div class="text-center text-muted">
                            <i class="fas fa-spinner fa-spin"></i> Loading events...
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Intelligence Summary -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-brain"></i> Intelligence Summary</h5>
                </div>
                <div class="card-body">
                    <div id="intelligence-summary">
                        <div class="text-center text-muted">
                            <i class="fas fa-spinner fa-spin"></i> Loading intelligence...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Material Status -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-boxes"></i> Material Status</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-primary" id="total-materials">-</h4>
                                <p class="text-muted">Total Materials</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-success" id="stable-materials">-</h4>
                                <p class="text-muted">Stable</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-warning" id="damaged-materials">-</h4>
                                <p class="text-muted">Damaged</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-danger" id="broken-materials">-</h4>
                                <p class="text-muted">Broken</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Material Types -->
                    <div class="mt-3">
                        <h6>Material Types</h6>
                        <div id="material-types" class="d-flex flex-wrap gap-2">
                            <!-- Will be populated by JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Global variables
let worldId = '{{ $world->id() }}';
let currentData = null;
let entropyChart = null;
let populationChart = null;
let updateInterval = null;
let entropyHistory = [];
let populationHistory = [];

// Initialize
$(document).ready(function() {
    initializeCharts();
    startRealTimeUpdates();
    setupEventListeners();
});

// Initialize charts
function initializeCharts() {
    // Entropy chart
    const entropyCtx = document.getElementById('entropy-chart').getContext('2d');
    entropyChart = new Chart(entropyCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Entropy %',
                data: [],
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });

    // Population chart
    const populationCtx = document.getElementById('population-chart').getContext('2d');
    populationChart = new Chart(populationCtx, {
        type: 'doughnut',
        data: {
            labels: ['Alive', 'Dead'],
            datasets: [{
                data: [0, 0],
                backgroundColor: [
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(255, 99, 132, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

// Start real-time updates
function startRealTimeUpdates() {
    updateData(); // Initial load
    updateInterval = setInterval(updateData, 5000); // Update every 5 seconds
}

// Update all data
function updateData() {
    $.get(`/worlds/${worldId}/realtime`)
        .done(function(data) {
            if (data.success) {
                currentData = data;
                updateDashboard(data);
                updateCharts(data);
            }
        })
        .fail(function() {
            console.error('Failed to update world data');
        });
}

// Update dashboard elements
function updateDashboard(data) {
    // Update stats
    $('#current-tick').text(data.world.tick);
    $('#entropy-level').text((data.world.entropy * 100).toFixed(1) + '%');
    $('#alive-characters').text(data.characters.alive);
    $('#active-materials').text(data.materials.active);
    
    // Update materials
    $('#total-materials').text(data.materials.total);
    $('#stable-materials').text(data.materials.total - data.materials.broken);
    $('#damaged-materials').text(data.materials.broken);
    $('#broken-materials').text(data.materials.broken);
    
    // Update toggle button
    const toggleBtn = $('#toggle-btn');
    if (data.world.autonomous) {
        toggleBtn.removeClass('btn-success').addClass('btn-danger')
               .html('<i class="fas fa-stop"></i> Stop Autonomous');
    } else {
        toggleBtn.removeClass('btn-danger').addClass('btn-success')
               .html('<i class="fas fa-play"></i> Start Autonomous');
    }
    
    // Update entropy bar color
    updateEntropyColor(data.world.entropy);
}

// Update charts
function updateCharts(data) {
    // Update entropy chart
    const now = new Date().toLocaleTimeString();
    entropyHistory.push(data.world.entropy * 100);
    
    if (entropyHistory.length > 20) {
        entropyHistory.shift();
    }
    
    entropyChart.data.labels = Array(entropyHistory.length).fill('').map((_, i) => i);
    entropyChart.data.datasets[0].data = entropyHistory;
    entropyChart.update();
    
    // Update population chart
    populationChart.data.datasets[0].data = [data.characters.alive, data.characters.dead];
    populationChart.update();
}

// Update entropy color
function updateEntropyColor(entropy) {
    const entropyLevel = $('#entropy-level').parent();
    entropyLevel.removeClass('bg-primary bg-warning bg-danger');
    
    if (entropy < 0.3) {
        entropyLevel.addClass('bg-primary');
    } else if (entropy < 0.7) {
        entropyLevel.addClass('bg-warning');
    } else {
        entropyLevel.addClass('bg-danger');
    }
}

// Setup event listeners
function setupEventListeners() {
    // Tick speed slider
    $('#tick-speed').on('input', function() {
        const speed = $(this).val();
        $('#speed-display').text(speed + 's');
    });
}

// Control functions
function toggleWorld() {
    const isAutonomous = currentData?.world?.autonomous;
    const action = isAutonomous ? 'stop' : 'start';
    
    $.post(`/worlds/${worldId}/${action}`)
        .done(function(data) {
            if (data.success) {
                showNotification(`World ${action}ed successfully`, 'success');
                updateData();
            } else {
                showNotification(`Failed to ${action} world: ${data.error}`, 'error');
            }
        })
        .fail(function() {
            showNotification(`Failed to ${action} world`, 'error');
        });
}

function singleTick() {
    $.post(`/worlds/${worldId}/tick`, {count: 1})
        .done(function(data) {
            if (data.success) {
                showNotification('Single tick completed', 'success');
                updateData();
            } else {
                showNotification('Failed to tick: ' + data.error, 'error');
            }
        })
        .fail(function() {
            showNotification('Failed to tick', 'error');
        });
}

function multiTick() {
    $.post(`/worlds/${worldId}/tick`, {count: 10})
        .done(function(data) {
            if (data.success) {
                showNotification('Multi tick completed (10 ticks)', 'success');
                updateData();
            } else {
                showNotification('Failed to tick: ' + data.error, 'error');
            }
        })
        .fail(function() {
            showNotification('Failed to tick', 'error');
        });
}

function refreshData() {
    updateData();
    showNotification('Data refreshed', 'success');
}

// Show notification
function showNotification(message, type) {
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

// Cleanup
$(window).on('beforeunload', function() {
    if (updateInterval) {
        clearInterval(updateInterval);
    }
});
</script>

<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    margin-bottom: 1rem;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.event-list {
    max-height: 300px;
    overflow-y: auto;
}

.event-item {
    padding: 0.5rem;
    border-left: 3px solid #007bff;
    margin-bottom: 0.5rem;
    background-color: #f8f9fa;
}

.event-item.high-severity {
    border-left-color: #dc3545;
}

.event-item.medium-severity {
    border-left-color: #ffc107;
}

.event-item.low-severity {
    border-left-color: #28a745;
}

.intelligence-item {
    padding: 0.5rem;
    margin-bottom: 0.5rem;
    border-radius: 0.25rem;
}

.intelligence-item.high-urgency {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
}

.intelligence-item.medium-urgency {
    background-color: #fff3cd;
    border: 1px solid #ffeaa7;
}

.intelligence-item.low-urgency {
    background-color: #d1ecf1;
    border: 1px solid #bee5eb;
}

.material-type-badge {
    font-size: 0.8rem;
}
</style>
@endpush
