<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "Dropping all related tables...\n";

// Disable foreign key checks
DB::statement('SET FOREIGN_KEY_CHECKS=0');

// Drop all tables that might reference world_states
$tables_to_drop = [
    'world_state_metrics',
    'world_state_transitions',
    'material_seeds', 
    'story_arcs',
    'world_states'
];

foreach ($tables_to_drop as $table) {
    try {
        Schema::dropIfExists($table);
        echo "Dropped: {$table}\n";
    } catch (Exception $e) {
        echo "Could not drop {$table}: " . $e->getMessage() . "\n";
    }
}

echo "Creating tables manually...\n";

// Create world_states table
DB::statement("
    CREATE TABLE world_states (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        power_axis JSON NOT NULL,
        resource_axis JSON NOT NULL,
        perception_axis JSON NOT NULL,
        author_intent JSON NOT NULL,
        structural_anchor VARCHAR(255) NOT NULL,
        resistance_factor DECIMAL(3,2) NOT NULL DEFAULT 0.15,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// Create material_seeds table
DB::statement("
    CREATE TABLE material_seeds (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        world_state_id BIGINT UNSIGNED NULL,
        seed_type ENUM('conflict', 'character', 'event', 'location') NOT NULL,
        source_axes VARCHAR(255) NOT NULL,
        content JSON NOT NULL,
        relevance_score DECIMAL(3,2) NOT NULL,
        tension_level DECIMAL(3,2) NOT NULL,
        archetype VARCHAR(255) NOT NULL,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// Create story_arcs table
DB::statement("
    CREATE TABLE story_arcs (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        world_state_id BIGINT UNSIGNED NULL,
        title VARCHAR(255) NOT NULL,
        arc_type VARCHAR(255) NOT NULL,
        source_material_seeds JSON NOT NULL,
        structure JSON NOT NULL,
        content JSON NOT NULL,
        estimated_chapters INT NOT NULL,
        tension_progression JSON NOT NULL,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// Re-enable foreign key checks
DB::statement('SET FOREIGN_KEY_CHECKS=1');

echo "All tables created successfully!\n";
