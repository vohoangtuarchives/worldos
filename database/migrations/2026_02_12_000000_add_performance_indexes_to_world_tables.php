<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // World Events table optimizations
        Schema::table('world_events', function (Blueprint $table) {
            // Composite index for timeline queries
            $table->index(['timeline_id', 'chapter'], 'idx_world_events_timeline_chapter');
            
            // Index for event type queries
            $table->index(['timeline_id', 'type'], 'idx_world_events_timeline_type');
            
            // Index for time-based queries
            $table->index(['created_at'], 'idx_world_events_created_at');
            
            // Index for tick-based queries
            $table->index(['timeline_id', 'tick'], 'idx_world_events_timeline_tick');
        });

        // World Snapshots table optimizations
        if (Schema::hasTable('world_snapshots')) {
            Schema::table('world_snapshots', function (Blueprint $table) {
                // Composite index for snapshot lookups
                $table->index(['timeline_id', 'chapter'], 'idx_world_snapshots_timeline_chapter');
                
                // Index for timeline-based queries
                $table->index(['timeline_id'], 'idx_world_snapshots_timeline');
            });
        }

        // Factions table optimizations
        Schema::table('factions', function (Blueprint $table) {
            // Index for world-based faction queries
            $table->index(['world_id'], 'idx_factions_world_id');
            
            // Index for faction type queries
            $table->index(['type'], 'idx_factions_type');
            
            // Composite index for world and type queries
            $table->index(['world_id', 'type'], 'idx_factions_world_type');
        });

        // Worlds table optimizations
        Schema::table('worlds', function (Blueprint $table) {
            // Index for status queries
            $table->index(['status'], 'idx_worlds_status');
            
            // Index for health status queries
            $table->index(['health_status'], 'idx_worlds_health_status');
            
            // Index for parent_id queries (for timeline forking)
            $table->index(['parent_id'], 'idx_worlds_parent_id');
            
            // Index for tick queries
            $table->index(['tick'], 'idx_worlds_tick');
            
            // Composite index for active worlds
            $table->index(['status', 'health_status'], 'idx_worlds_status_health');
        });

        // Chronicles table optimizations
        if (Schema::hasTable('chronicles')) {
            Schema::table('chronicles', function (Blueprint $table) {
                // Index for world-based chronicle queries
                $table->index(['world_id'], 'idx_chronicles_world_id');
                
                // Index for chapter ordering
                $table->index(['world_id', 'chapter'], 'idx_chronicles_world_chapter');
            });
        }

        // Materials table optimizations
        if (Schema::hasTable('materials')) {
            Schema::table('materials', function (Blueprint $table) {
                // Index for material code lookups
                $table->index(['code'], 'idx_materials_code');
                
                // Index for ontology queries
                $table->index(['ontology'], 'idx_materials_ontology');
                
                // Index for function queries
                $table->index(['function'], 'idx_materials_function');
                
                // Composite index for ontology and function
                $table->index(['ontology', 'function'], 'idx_materials_ontology_function');
            });
        }

        // Material Instances table optimizations (if exists)
        if (Schema::hasTable('material_instances')) {
            Schema::table('material_instances', function (Blueprint $table) {
                // Index for world-based queries
                $table->index(['world_id'], 'idx_material_instances_world');
                
                // Index for material lookups
                $table->index(['material_id'], 'idx_material_instances_material');
                
                // Composite index for world and material
                $table->index(['world_id', 'material_id'], 'idx_material_instances_world_material');
                
                // Index for active instances
                $table->index(['retired_at'], 'idx_material_instances_retired');
            });
        }

        // World Event Ledger optimizations
        if (Schema::hasTable('world_event_ledger')) {
            Schema::table('world_event_ledger', function (Blueprint $table) {
                // Index for timeline queries
                $table->index(['timeline_id'], 'idx_event_ledger_timeline');
                
                // Index for event type queries
                $table->index(['event_type'], 'idx_event_ledger_type');
                
                // Composite index for timeline and type
                $table->index(['timeline_id', 'event_type'], 'idx_event_ledger_timeline_type');
                
                // Index for timestamp queries
                $table->index(['created_at'], 'idx_event_ledger_created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('world_events', function (Blueprint $table) {
            $table->dropIndex('idx_world_events_timeline_chapter');
            $table->dropIndex('idx_world_events_timeline_type');
            $table->dropIndex('idx_world_events_created_at');
            $table->dropIndex('idx_world_events_timeline_tick');
        });

        if (Schema::hasTable('world_snapshots')) {
            Schema::table('world_snapshots', function (Blueprint $table) {
                $table->dropIndex('idx_world_snapshots_timeline_chapter');
                $table->dropIndex('idx_world_snapshots_timeline');
            });
        }

        Schema::table('factions', function (Blueprint $table) {
            $table->dropIndex('idx_factions_world_id');
            $table->dropIndex('idx_factions_type');
            $table->dropIndex('idx_factions_world_type');
        });

        Schema::table('worlds', function (Blueprint $table) {
            $table->dropIndex('idx_worlds_status');
            $table->dropIndex('idx_worlds_health_status');
            $table->dropIndex('idx_worlds_parent_id');
            $table->dropIndex('idx_worlds_tick');
            $table->dropIndex('idx_worlds_status_health');
        });

        if (Schema::hasTable('chronicles')) {
            Schema::table('chronicles', function (Blueprint $table) {
                $table->dropIndex('idx_chronicles_world_id');
                $table->dropIndex('idx_chronicles_world_chapter');
            });
        }

        if (Schema::hasTable('materials')) {
            Schema::table('materials', function (Blueprint $table) {
                $table->dropIndex('idx_materials_code');
                $table->dropIndex('idx_materials_ontology');
                $table->dropIndex('idx_materials_function');
                $table->dropIndex('idx_materials_ontology_function');
            });
        }

        if (Schema::hasTable('material_instances')) {
            Schema::table('material_instances', function (Blueprint $table) {
                $table->dropIndex('idx_material_instances_world');
                $table->dropIndex('idx_material_instances_material');
                $table->dropIndex('idx_material_instances_world_material');
                $table->dropIndex('idx_material_instances_retired');
            });
        }

        if (Schema::hasTable('world_event_ledger')) {
            Schema::table('world_event_ledger', function (Blueprint $table) {
                $table->dropIndex('idx_event_ledger_timeline');
                $table->dropIndex('idx_event_ledger_type');
                $table->dropIndex('idx_event_ledger_timeline_type');
                $table->dropIndex('idx_event_ledger_created_at');
            });
        }
    }
};
