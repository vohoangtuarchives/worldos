/**
 * World Manager Component
 * Handles world-related operations and state management
 */

class WorldManager {
    constructor(options = {}) {
        this.api = options.api;
        this.cache = options.cache;
        this.notifications = options.notifications;
        this.worlds = new Map();
        this.updateCallbacks = new Set();
    }

    /**
     * Load all worlds
     */
    async loadWorlds() {
        try {
            const cacheKey = 'worlds:list';
            let worlds = this.cache.get(cacheKey);

            if (!worlds) {
                const response = await this.api.get('/worlds');
                worlds = response.data.data || response.data;
                this.cache.set(cacheKey, worlds, 30000); // 30 seconds
            }

            // Update internal state
            worlds.forEach(world => {
                this.worlds.set(world.id, world);
            });

            // Notify callbacks
            this.notifyUpdate('worlds:loaded', worlds);

            return worlds;

        } catch (error) {
            console.error('Failed to load worlds:', error);
            this.notifications.error('Failed to load worlds', error.message);
            throw error;
        }
    }

    /**
     * Get world by ID
     */
    async getWorld(worldId) {
        // Check cache first
        const cacheKey = `world:${worldId}`;
        let world = this.cache.get(cacheKey);

        if (!world) {
            const response = await this.api.get(`/worlds/${worldId}`);
            world = response.data;
            this.cache.set(cacheKey, world, 60000); // 1 minute
        }

        // Update internal state
        this.worlds.set(worldId, world);

        return world;
    }

    /**
     * Get world real-time data
     */
    async getWorldRealtime(worldId) {
        try {
            const response = await this.api.get(`/worlds/${worldId}/realtime`);
            const data = response.data;

            // Update world in cache
            if (data.world) {
                this.worlds.set(worldId, data.world);
                this.cache.set(`world:${worldId}`, data.world, 30000);
            }

            // Notify callbacks
            this.notifyUpdate('world:realtime', { worldId, data });

            return data;

        } catch (error) {
            console.error(`Failed to get real-time data for world ${worldId}:`, error);
            throw error;
        }
    }

    /**
     * Toggle world autonomous mode
     */
    async toggleWorld(worldId) {
        try {
            const world = this.worlds.get(worldId);
            const isAutonomous = world?.autonomous;
            const action = isAutonomous ? 'stop' : 'start';

            const response = await this.api.post(`/worlds/${worldId}/${action}`);
            
            // Update local state
            if (response.data.world) {
                this.worlds.set(worldId, response.data.world);
                this.cache.set(`world:${worldId}`, response.data.world, 60000);
            }

            // Clear worlds list cache
            this.cache.delete('worlds:list');

            // Notify callbacks
            this.notifyUpdate('world:toggled', { worldId, action, world: response.data.world });

            this.notifications.success(
                `World ${worldId} ${action}ed successfully`,
                response.data.message
            );

            return response.data;

        } catch (error) {
            console.error(`Failed to toggle world ${worldId}:`, error);
            this.notifications.error(`Failed to ${action} world ${worldId}`, error.message);
            throw error;
        }
    }

    /**
     * Execute world tick
     */
    async tickWorld(worldId, count = 1) {
        try {
            const response = await this.api.post(`/worlds/${worldId}/tick`, { count });
            
            // Update local state
            if (response.data.world) {
                this.worlds.set(worldId, response.data.world);
                this.cache.set(`world:${worldId}`, response.data.world, 60000);
            }

            // Notify callbacks
            this.notifyUpdate('world:ticked', { 
                worldId, 
                count, 
                results: response.data.results,
                world: response.data.world 
            });

            this.notifications.success(
                `World ${worldId} ticked ${count} time(s)`,
                `New tick: ${response.data.world.tick}`
            );

            return response.data;

        } catch (error) {
            console.error(`Failed to tick world ${worldId}:`, error);
            this.notifications.error(`Failed to tick world ${worldId}`, error.message);
            throw error;
        }
    }

    /**
     * Start all autonomous worlds
     */
    async startAllWorlds() {
        try {
            const response = await this.api.post('/api/worlds/start-all');
            
            // Clear cache to force refresh
            this.cache.delete('worlds:list');

            // Reload worlds to get updated state
            await this.loadWorlds();

            // Notify callbacks
            this.notifyUpdate('worlds:started-all', response.data);

            this.notifications.success(
                'All autonomous worlds started',
                response.data.message
            );

            return response.data;

        } catch (error) {
            console.error('Failed to start all worlds:', error);
            this.notifications.error('Failed to start all worlds', error.message);
            throw error;
        }
    }

    /**
     * Stop all worlds
     */
    async stopAllWorlds() {
        try {
            const response = await this.api.post('/api/worlds/stop-all');
            
            // Clear cache to force refresh
            this.cache.delete('worlds:list');

            // Reload worlds to get updated state
            await this.loadWorlds();

            // Notify callbacks
            this.notifyUpdate('worlds:stopped-all', response.data);

            this.notifications.success(
                'All worlds stopped',
                response.data.message
            );

            return response.data;

        } catch (error) {
            console.error('Failed to stop all worlds:', error);
            this.notifications.error('Failed to stop all worlds', error.message);
            throw error;
        }
    }

    /**
     * Get world status
     */
    async getWorldStatus(worldId) {
        try {
            const response = await this.api.get(`/worlds/${worldId}/status`);
            
            // Update local state
            if (response.data.world) {
                this.worlds.set(worldId, response.data.world);
                this.cache.set(`world:${worldId}`, response.data.world, 60000);
            }

            return response.data;

        } catch (error) {
            console.error(`Failed to get status for world ${worldId}:`, error);
            throw error;
        }
    }

    /**
     * Get world intelligence
     */
    async getWorldIntelligence(worldId) {
        try {
            const response = await this.api.get(`/worlds/${worldId}/intelligence`);
            
            // Notify callbacks
            this.notifyUpdate('world:intelligence', { worldId, intelligence: response.data });

            return response.data;

        } catch (error) {
            console.error(`Failed to get intelligence for world ${worldId}:`, error);
            throw error;
        }
    }

    /**
     * Get world materials
     */
    async getWorldMaterials(worldId) {
        try {
            const response = await this.api.get(`/worlds/${worldId}/materials`);
            
            // Notify callbacks
            this.notifyUpdate('world:materials', { worldId, materials: response.data });

            return response.data;

        } catch (error) {
            console.error(`Failed to get materials for world ${worldId}:`, error);
            throw error;
        }
    }

    /**
     * Update worlds list (for real-time updates)
     */
    async updateWorldsList() {
        try {
            await this.loadWorlds();
        } catch (error) {
            console.error('Failed to update worlds list:', error);
        }
    }

    /**
     * Refresh world data
     */
    async refreshWorld(worldId) {
        try {
            // Clear cache
            this.cache.delete(`world:${worldId}`);
            
            // Reload world
            return await this.getWorld(worldId);
        } catch (error) {
            console.error(`Failed to refresh world ${worldId}:`, error);
            throw error;
        }
    }

    /**
     * Get world from local cache
     */
    getLocalWorld(worldId) {
        return this.worlds.get(worldId);
    }

    /**
     * Get all worlds from local cache
     */
    getLocalWorlds() {
        return Array.from(this.worlds.values());
    }

    /**
     * Get worlds by status
     */
    getWorldsByStatus(status) {
        return Array.from(this.worlds.values()).filter(world => {
            switch (status) {
                case 'autonomous':
                    return world.autonomous;
                case 'running':
                    return world.autonomous && world.running !== false;
                case 'stopped':
                    return !world.autonomous || world.running === false;
                default:
                    return false;
            }
        });
    }

    /**
     * Get worlds by preset
     */
    getWorldsByPreset(preset) {
        return Array.from(this.worlds.values()).filter(world => world.preset === preset);
    }

    /**
     * Get worlds with high entropy
     */
    getWorldsWithHighEntropy(threshold = 0.7) {
        return Array.from(this.worlds.values()).filter(world => world.entropy > threshold);
    }

    /**
     * Get worlds with low population
     */
    getWorldsWithLowPopulation(threshold = 5) {
        return Array.from(this.worlds.values()).filter(world => 
            world.characters && world.characters.alive < threshold
        );
    }

    /**
     * Calculate global statistics
     */
    getGlobalStats() {
        const worlds = Array.from(this.worlds.values());
        
        if (worlds.length === 0) {
            return null;
        }

        const stats = {
            totalWorlds: worlds.length,
            autonomousWorlds: worlds.filter(w => w.autonomous).length,
            runningWorlds: worlds.filter(w => w.autonomous && w.running !== false).length,
            totalTicks: worlds.reduce((sum, w) => sum + (w.tick || 0), 0),
            averageEntropy: worlds.reduce((sum, w) => sum + (w.entropy || 0), 0) / worlds.length,
            totalCharacters: worlds.reduce((sum, w) => sum + (w.characters?.total || 0), 0),
            aliveCharacters: worlds.reduce((sum, w) => sum + (w.characters?.alive || 0), 0),
            deadCharacters: worlds.reduce((sum, w) => sum + (w.characters?.dead || 0), 0),
            totalMaterials: worlds.reduce((sum, w) => sum + (w.materials?.total || 0), 0),
            activeMaterials: worlds.reduce((sum, w) => sum + (w.materials?.active || 0), 0),
        };

        // Calculate percentages
        stats.autonomousPercentage = (stats.autonomousWorlds / stats.totalWorlds) * 100;
        stats.runningPercentage = (stats.runningWorlds / stats.totalWorlds) * 100;
        stats.survivalRate = stats.totalCharacters > 0 ? (stats.aliveCharacters / stats.totalCharacters) * 100 : 0;
        stats.materialHealthRate = stats.totalMaterials > 0 ? (stats.activeMaterials / stats.totalMaterials) * 100 : 0;

        return stats;
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
                console.error('Error in update callback:', error);
            }
        });
    }

    /**
     * Clear all cached data
     */
    clearCache() {
        this.worlds.clear();
        this.cache.clear();
        this.notifyUpdate('cache:cleared');
    }
}

export default WorldManager;
