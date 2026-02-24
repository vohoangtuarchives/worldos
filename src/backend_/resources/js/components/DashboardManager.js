/**
 * Dashboard Manager Component
 * Handles dashboard real-time updates and interactions
 */

class DashboardManager {
    constructor(options = {}) {
        this.api = options.api;
        this.ws = options.ws;
        this.chartManager = options.chartManager;
        this.refreshInterval = options.refreshInterval || 5000;
        this.currentWorldId = null;
        this.updateTimer = null;
        this.lastUpdate = null;
        this.isUpdating = false;
        this.updateCallbacks = new Set();
    }

    /**
     * Initialize dashboard for a world
     */
    async initialize(worldId) {
        this.currentWorldId = worldId;
        
        try {
            // Load initial data
            await this.loadInitialData(worldId);
            
            // Setup WebSocket if available
            if (this.ws) {
                this.setupWebSocket(worldId);
            }
            
            // Start real-time updates
            this.startRealTimeUpdates();
            
            console.log(`📊 Dashboard initialized for world ${worldId}`);
            
        } catch (error) {
            console.error(`Failed to initialize dashboard for world ${worldId}:`, error);
            throw error;
        }
    }

    /**
     * Load initial dashboard data
     */
    async loadInitialData(worldId) {
        try {
            // Load world data
            const worldData = await this.api.get(`/worlds/${worldId}/realtime`);
            this.updateDashboard(worldData.data);
            
            // Load intelligence data
            const intelligenceData = await this.api.get(`/worlds/${worldId}/intelligence`);
            this.updateIntelligence(intelligenceData.data);
            
            // Load materials data
            const materialsData = await this.api.get(`/worlds/${worldId}/materials`);
            this.updateMaterials(materialsData.data);
            
        } catch (error) {
            console.error('Failed to load initial data:', error);
            throw error;
        }
    }

    /**
     * Setup WebSocket for real-time updates
     */
    setupWebSocket(worldId) {
        const wsUrl = `${this.ws.url}/worlds/${worldId}`;
        
        this.ws.connect(wsUrl);
        
        this.ws.onMessage((data) => {
            try {
                const message = JSON.parse(data);
                this.handleWebSocketMessage(message);
            } catch (error) {
                console.error('Failed to parse WebSocket message:', error);
            }
        });
        
        this.ws.onConnect(() => {
            console.log('🔌 WebSocket connected for dashboard');
        });
        
        this.ws.onDisconnect(() => {
            console.log('🔌 WebSocket disconnected for dashboard');
            // Fallback to polling
            this.startPolling();
        });
    }

    /**
     * Handle WebSocket messages
     */
    handleWebSocketMessage(message) {
        switch (message.type) {
            case 'world:update':
                this.updateWorld(message.data);
                break;
                
            case 'tick:completed':
                this.handleTickCompleted(message.data);
                break;
                
            case 'shock:event':
                this.handleShockEvent(message.data);
                break;
                
            case 'character:death':
                this.handleCharacterDeath(message.data);
                break;
                
            case 'material:update':
                this.updateMaterials(message.data);
                break;
                
            case 'intelligence:update':
                this.updateIntelligence(message.data);
                break;
                
            default:
                console.log('Unknown WebSocket message type:', message.type);
        }
    }

    /**
     * Start real-time updates
     */
    startRealTimeUpdates() {
        if (this.updateTimer) {
            clearInterval(this.updateTimer);
        }
        
        this.updateTimer = setInterval(() => {
            if (document.visibilityState === 'visible' && !this.isUpdating) {
                this.updateDashboardData();
            }
        }, this.refreshInterval);
    }

    /**
     * Start polling fallback
     */
    startPolling() {
        if (this.updateTimer) {
            clearInterval(this.updateTimer);
        }
        
        this.updateTimer = setInterval(() => {
            if (document.visibilityState === 'visible' && !this.isUpdating) {
                this.updateDashboardData();
            }
        }, this.refreshInterval);
    }

    /**
     * Update dashboard data
     */
    async updateDashboardData() {
        if (!this.currentWorldId || this.isUpdating) {
            return;
        }
        
        this.isUpdating = true;
        
        try {
            const response = await this.api.get(`/worlds/${this.currentWorldId}/realtime`);
            this.updateDashboard(response.data);
            this.lastUpdate = new Date();
            
        } catch (error) {
            console.error('Failed to update dashboard data:', error);
        } finally {
            this.isUpdating = false;
        }
    }

    /**
     * Update dashboard UI
     */
    updateDashboard(data) {
        // Update world stats
        this.updateWorldStats(data.world);
        
        // Update character stats
        this.updateCharacterStats(data.characters);
        
        // Update material stats
        this.updateMaterialStats(data.materials);
        
        // Update charts
        if (this.chartManager) {
            this.chartManager.updateCharts(data);
        }
        
        // Update control panel
        this.updateControlPanel(data.world);
        
        // Notify callbacks
        this.notifyUpdate('dashboard:updated', data);
    }

    /**
     * Update world statistics
     */
    updateWorldStats(world) {
        // Update tick counter
        const tickElement = document.getElementById('current-tick');
        if (tickElement) {
            tickElement.textContent = world.tick;
        }
        
        // Update entropy level
        const entropyElement = document.getElementById('entropy-level');
        if (entropyElement) {
            const entropyPercent = (world.entropy * 100).toFixed(1);
            entropyElement.textContent = `${entropyPercent}%`;
            
            // Update color based on entropy level
            const entropyCard = entropyElement.closest('.card');
            entropyCard.className = `card text-white ${this.getEntropyClass(world.entropy)}`;
        }
        
        // Update autonomous status
        const toggleBtn = document.getElementById('toggle-btn');
        if (toggleBtn) {
            if (world.autonomous) {
                toggleBtn.className = 'btn btn-lg btn-danger w-100';
                toggleBtn.innerHTML = '<i class="fas fa-stop"></i> Stop Autonomous';
            } else {
                toggleBtn.className = 'btn btn-lg btn-success w-100';
                toggleBtn.innerHTML = '<i class="fas fa-play"></i> Start Autonomous';
            }
        }
    }

    /**
     * Update character statistics
     */
    updateCharacterStats(characters) {
        const aliveElement = document.getElementById('alive-characters');
        if (aliveElement) {
            aliveElement.textContent = characters.alive;
        }
        
        // Update population chart
        if (this.chartManager && this.chartManager.populationChart) {
            this.chartManager.populationChart.data.datasets[0].data = [
                characters.alive,
                characters.dead
            ];
            this.chartManager.populationChart.update('none');
        }
    }

    /**
     * Update material statistics
     */
    updateMaterialStats(materials) {
        const activeElement = document.getElementById('active-materials');
        if (activeElement) {
            activeElement.textContent = materials.active;
        }
        
        const totalElement = document.getElementById('total-materials');
        if (totalElement) {
            totalElement.textContent = materials.total;
        }
        
        const brokenElement = document.getElementById('broken-materials');
        if (brokenElement) {
            brokenElement.textContent = materials.broken;
        }
    }

    /**
     * Update intelligence data
     */
    updateIntelligence(intelligence) {
        const summaryElement = document.getElementById('intelligence-summary');
        if (summaryElement && intelligence.summary) {
            this.renderIntelligenceSummary(intelligence.summary);
        }
    }

    /**
     * Update materials data
     */
    updateMaterials(materials) {
        if (materials.statistics) {
            this.renderMaterialStats(materials.statistics);
        }
        
        if (materials.materials) {
            this.renderMaterialTypes(materials.materials);
        }
    }

    /**
     * Update control panel
     */
    updateControlPanel(world) {
        // Update speed control if needed
        const speedSlider = document.getElementById('tick-speed');
        if (speedSlider && world.tick_interval) {
            speedSlider.value = world.tick_interval;
            document.getElementById('speed-display').textContent = `${world.tick_interval}s`;
        }
    }

    /**
     * Handle tick completed event
     */
    handleTickCompleted(data) {
        // Show notification
        if (window.WorldOS && window.WorldOS.notifications) {
            window.WorldOS.notifications.success(
                'Tick completed',
                `World ${data.worldId} ticked to ${data.tick}`
            );
        }
        
        // Update charts
        if (this.chartManager) {
            this.chartManager.addEntropyPoint(data.entropy);
        }
        
        // Notify callbacks
        this.notifyUpdate('tick:completed', data);
    }

    /**
     * Handle shock event
     */
    handleShockEvent(data) {
        // Add to recent events
        this.addRecentEvent(data);
        
        // Show notification for high severity events
        if (data.severity > 0.7) {
            if (window.WorldOS && window.WorldOS.notifications) {
                window.WorldOS.notifications.warning(
                    'Shock Event',
                    `${data.type}: ${data.description}`
                );
            }
        }
        
        // Notify callbacks
        this.notifyUpdate('shock:event', data);
    }

    /**
     * Handle character death
     */
    handleCharacterDeath(data) {
        // Show notification
        if (window.WorldOS && window.WorldOS.notifications) {
            window.WorldOS.notifications.error(
                'Character Death',
                `${data.characterName} has died in ${data.location}`
            );
        }
        
        // Notify callbacks
        this.notifyUpdate('character:death', data);
    }

    /**
     * Add recent event to UI
     */
    addRecentEvent(event) {
        const eventsContainer = document.getElementById('recent-events');
        if (!eventsContainer) return;
        
        const eventElement = document.createElement('div');
        eventElement.className = `event-item ${this.getSeverityClass(event.severity)}`;
        eventElement.innerHTML = `
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong>${event.type}</strong>
                    <div class="small text-muted">${event.description}</div>
                </div>
                <div class="small text-muted">
                    ${new Date(event.timestamp).toLocaleTimeString()}
                </div>
            </div>
        `;
        
        // Add to top of list
        eventsContainer.insertBefore(eventElement, eventsContainer.firstChild);
        
        // Limit to 10 events
        while (eventsContainer.children.length > 10) {
            eventsContainer.removeChild(eventsContainer.lastChild);
        }
    }

    /**
     * Render intelligence summary
     */
    renderIntelligenceSummary(summary) {
        const container = document.getElementById('intelligence-summary');
        if (!container) return;
        
        container.innerHTML = `
            <div class="row">
                <div class="col-md-3">
                    <div class="text-center">
                        <h4 class="text-primary">${summary.total_reports || 0}</h4>
                        <p class="text-muted">Total Reports</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h4 class="text-success">${summary.reliable_reports || 0}</h4>
                        <p class="text-muted">Reliable</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h4 class="text-warning">${summary.high_urgency || 0}</h4>
                        <p class="text-muted">High Urgency</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h4 class="text-info">${summary.threats || 0} | ${summary.opportunities || 0}</h4>
                        <p class="text-muted">Threats | Opportunities</p>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Render material statistics
     */
    renderMaterialStats(stats) {
        const stableElement = document.getElementById('stable-materials');
        const damagedElement = document.getElementById('damaged-materials');
        
        if (stableElement) {
            stableElement.textContent = stats.active_instances || 0;
        }
        
        if (damagedElement) {
            damagedElement.textContent = stats.broken_instances || 0;
        }
    }

    /**
     * Render material types
     */
    renderMaterialTypes(materials) {
        const container = document.getElementById('material-types');
        if (!container) return;
        
        // Count materials by type
        const typeCounts = {};
        materials.forEach(material => {
            const type = material.material_type || 'unknown';
            typeCounts[type] = (typeCounts[type] || 0) + 1;
        });
        
        // Render badges
        container.innerHTML = '';
        Object.entries(typeCounts).forEach(([type, count]) => {
            const badge = document.createElement('span');
            badge.className = 'badge bg-secondary material-type-badge';
            badge.textContent = `${type}: ${count}`;
            container.appendChild(badge);
        });
    }

    /**
     * Get entropy class for styling
     */
    getEntropyClass(entropy) {
        if (entropy < 0.3) return 'bg-primary';
        if (entropy < 0.7) return 'bg-warning';
        return 'bg-danger';
    }

    /**
     * Get severity class for styling
     */
    getSeverityClass(severity) {
        if (severity > 0.7) return 'high-severity';
        if (severity > 0.4) return 'medium-severity';
        return 'low-severity';
    }

    /**
     * Stop dashboard updates
     */
    stop() {
        if (this.updateTimer) {
            clearInterval(this.updateTimer);
            this.updateTimer = null;
        }
        
        if (this.ws) {
            this.ws.disconnect();
        }
        
        console.log('📊 Dashboard stopped');
    }

    /**
     * Add update callback
     */
    onUpdate(callback) {
        this.updateCallbacks.add(callback);
    }

    /**
     * Remove update callback
     */
    offUpdate(callback) {
        this.updateCallbacks.delete(callback);
    }

    /**
     * Notify all callbacks
     */
    notifyUpdate(event, data) {
        this.updateCallbacks.forEach(callback => {
            try {
                callback(event, data);
            } catch (error) {
                console.error('Error in dashboard callback:', error);
            }
        });
    }
}

export default DashboardManager;
