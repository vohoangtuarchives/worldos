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
        if (Schema::hasTable('world_events')) {
            Schema::table('world_events', function (Blueprint $table) {
                // Check if columns exist before adding indexes
                if (Schema::hasColumn('world_events', 'timeline_id') && Schema::hasColumn('world_events', 'chapter')) {
                    $table->index(['timeline_id', 'chapter'], 'idx_world_events_timeline_chapter');
                }
                
                if (Schema::hasColumn('world_events', 'timeline_id') && Schema::hasColumn('world_events', 'type')) {
                    $table->index(['timeline_id', 'type'], 'idx_world_events_timeline_type');
                }
                
                if (Schema::hasColumn('world_events', 'created_at')) {
                    $table->index(['created_at'], 'idx_world_events_created_at');
                }
                
                if (Schema::hasColumn('world_events', 'timeline_id') && Schema::hasColumn('world_events', 'tick')) {
                    $table->index(['timeline_id', 'tick'], 'idx_world_events_timeline_tick');
                }
            });
        }

        // World Snapshots table optimizations
        if (Schema::hasTable('world_snapshots')) {
            Schema::table('world_snapshots', function (Blueprint $table) {
                // Check if columns exist before adding indexes
                if (Schema::hasColumn('world_snapshots', 'timeline_id') && Schema::hasColumn('world_snapshots', 'chapter')) {
                    $table->index(['timeline_id', 'chapter'], 'idx_world_snapshots_timeline_chapter');
                }
                
                if (Schema::hasColumn('world_snapshots', 'timeline_id')) {
                    $table->index(['timeline_id'], 'idx_world_snapshots_timeline');
                }
            });
        }

        // Factions table optimizations
        if (Schema::hasTable('factions')) {
            Schema::table('factions', function (Blueprint $table) {
                // Check if columns exist before adding indexes
                if (Schema::hasColumn('factions', 'world_id')) {
                    $table->index(['world_id'], 'idx_factions_world_id');
                }
                
                if (Schema::hasColumn('factions', 'type')) {
                    $table->index(['type'], 'idx_factions_type');
                }
                
                if (Schema::hasColumn('factions', 'world_id') && Schema::hasColumn('factions', 'type')) {
                    $table->index(['world_id', 'type'], 'idx_factions_world_type');
                }
            });
        }

        // Worlds table optimizations
        if (Schema::hasTable('worlds')) {
            Schema::table('worlds', function (Blueprint $table) {
                // Check if columns exist before adding indexes
                if (Schema::hasColumn('worlds', 'status')) {
                    $table->index(['status'], 'idx_worlds_status');
                }
                
                if (Schema::hasColumn('worlds', 'health_status')) {
                    $table->index(['health_status'], 'idx_worlds_health_status');
                }
                
                if (Schema::hasColumn('worlds', 'parent_id')) {
                    $table->index(['parent_id'], 'idx_worlds_parent_id');
                }
                
                if (Schema::hasColumn('worlds', 'tick')) {
                    $table->index(['tick'], 'idx_worlds_tick');
                }
                
                if (Schema::hasColumn('worlds', 'status') && Schema::hasColumn('worlds', 'health_status')) {
                    $table->index(['status', 'health_status'], 'idx_worlds_status_health');
                }
            });
        }

        // Chronicles table optimizations
        if (Schema::hasTable('chronicles')) {
            Schema::table('chronicles', function (Blueprint $table) {
                // Check if columns exist before adding indexes
                if (Schema::hasColumn('chronicles', 'world_id')) {
                    $table->index(['world_id'], 'idx_chronicles_world_id');
                }
                
                if (Schema::hasColumn('chronicles', 'world_id') && Schema::hasColumn('chronicles', 'chapter')) {
                    $table->index(['world_id', 'chapter'], 'idx_chronicles_world_chapter');
                }
            });
        }

        // Materials table optimizations
        if (Schema::hasTable('materials')) {
            Schema::table('materials', function (Blueprint $table) {
                // Check if columns exist before adding indexes
                if (Schema::hasColumn('materials', 'code')) {
                    $table->index(['code'], 'idx_materials_code');
                }
                
                if (Schema::hasColumn('materials', 'ontology')) {
                    $table->index(['ontology'], 'idx_materials_ontology');
                }
                
                if (Schema::hasColumn('materials', 'function')) {
                    $table->index(['function'], 'idx_materials_function');
                }
                
                if (Schema::hasColumn('materials', 'ontology') && Schema::hasColumn('materials', 'function')) {
                    $table->index(['ontology', 'function'], 'idx_materials_ontology_function');
                }
            });
        }

        // Material Instances table optimizations (if exists)
        if (Schema::hasTable('material_instances')) {
            Schema::table('material_instances', function (Blueprint $table) {
                // Check if columns exist before adding indexes
                if (Schema::hasColumn('material_instances', 'world_id')) {
                    $table->index(['world_id'], 'idx_material_instances_world');
                }
                
                if (Schema::hasColumn('material_instances', 'material_id')) {
                    $table->index(['material_id'], 'idx_material_instances_material');
                }
                
                if (Schema::hasColumn('material_instances', 'world_id') && Schema::hasColumn('material_instances', 'material_id')) {
                    $table->index(['world_id', 'material_id'], 'idx_material_instances_world_material');
                }
                
                if (Schema::hasColumn('material_instances', 'retired_at')) {
                    $table->index(['retired_at'], 'idx_material_instances_retired');
                }
            });
        }

        // World Event Ledger optimizations
        if (Schema::hasTable('world_event_ledger')) {
            Schema::table('world_event_ledger', function (Blueprint $table) {
                // Check if columns exist before adding indexes
                if (Schema::hasColumn('world_event_ledger', 'timeline_id')) {
                    $table->index(['timeline_id'], 'idx_event_ledger_timeline');
                }
                
                if (Schema::hasColumn('world_event_ledger', 'event_type')) {
                    $table->index(['event_type'], 'idx_event_ledger_type');
                }
                
                if (Schema::hasColumn('world_event_ledger', 'timeline_id') && Schema::hasColumn('world_event_ledger', 'event_type')) {
                    $table->index(['timeline_id', 'event_type'], 'idx_event_ledger_timeline_type');
                }
                
                if (Schema::hasColumn('world_event_ledger', 'created_at')) {
                    $table->index(['created_at'], 'idx_event_ledger_created_at');
                }
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
