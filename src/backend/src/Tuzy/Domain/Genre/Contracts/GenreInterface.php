<?php

namespace Tuzy\Domain\Genre\Contracts;

interface GenreInterface
{
    public function getKey(): string;
    public function getName(): string;
    public function getDescription(): string;
    
    /**
     * Get specific terminology for this genre.
     * e.g., 'energy' => 'Qi', 'school' => 'Sect'
     */
    public function getTerminology(): array;
    
    /**
     * Get unique materials for this genre.
     */
    public function getMaterials(): array;
    
    /**
     * Get narrative prompt template for AI.
     */
    public function getNarrativePrompt(): string;
}
