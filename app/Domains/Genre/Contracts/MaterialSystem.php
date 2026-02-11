<?php

namespace App\Domains\Genre\Contracts;

interface MaterialSystem
{
    /**
     * The primary energy/currency of the genre (e.g., 'Qi', 'Mana').
     */
    public function primary(): string;

    /**
     * Subtypes or variations of the material.
     * e.g., ['Heavenly Qi', 'Demonic Qi']
     */
    public function subtypes(): array;

    /**
     * Rules for converting items into the primary material.
     * e.g., ['spiritual_stone' => 'Qi', 'pill' => 'Qi']
     */
    public function conversionRules(): array;
}
