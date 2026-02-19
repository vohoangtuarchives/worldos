<?php

namespace App\Domains\Genre\Contracts;

interface GenreDefinition
{
    public function key(): string;              // xianxia, wuxia…
    public function displayName(): string;

    /**
     * Define the physics/materials of this genre.
     */
    public function materials(): MaterialSystem;

    /**
     * Define how characters gain power and evolve.
     */
    public function progression(): ProgressionRule;

    /**
     * Map generic concepts to genre-specific terms.
     */
    public function vocabulary(): VocabularyMap;

    /**
     * Define possible events and their frequencies.
     */
    public function events(): EventCatalog;

    /**
     * Constraints for the World Engine and AI.
     * Used by Validator and Prompt Guard.
     */
    public function worldConstraints(): array;

    /**
     * Get the validator enforcement logic for this genre.
     */
    /**
     * Get the validator enforcement logic for this genre.
     */
    public function validator(): GenreValidator;

    /**
     * Get the narrative prompt template for AI.
     */
    public function getNarrativePrompt(): string;

    /**
     * Get style/physics bias for this genre.
     * e.g. ['entropy_decay' => 0.1, 'order_bias' => 0.8]
     */
    public function getPhysicsBias(): array;
}

