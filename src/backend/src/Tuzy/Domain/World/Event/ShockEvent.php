<?php

declare(strict_types=1);

namespace Tuzy\Domain\World\Event;

readonly class ShockEvent
{
    private function __construct(
        private string $id,
        private string $type,
        private float $severity,
        private string $affectedRegion,
        private float $entropyDelta,
        private array $riskModifiers,
        private array $metadata,
    ) {
    }

    public static function create(
        string $type,
        float $severity,
        string $affectedRegion,
        float $entropyDelta,
        array $metadata = []
    ): self {
        return new self(
            uniqid('shock_', true),
            $type,
            $severity,
            $affectedRegion,
            $entropyDelta,
            self::calculateRiskModifiers($type, $severity),
            $metadata
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function severity(): float
    {
        return $this->severity;
    }

    public function affectedRegion(): string
    {
        return $this->affectedRegion;
    }

    public function entropyDelta(): float
    {
        return $this->entropyDelta;
    }

    public function riskModifiers(): array
    {
        return $this->riskModifiers;
    }

    public function metadata(): array
    {
        return $this->metadata;
    }

    public function isCatastrophic(): bool
    {
        return $this->severity >= 0.8;
    }

    public function isMajor(): bool
    {
        return $this->severity >= 0.5 && $this->severity < 0.8;
    }

    public function isMinor(): bool
    {
        return $this->severity < 0.5;
    }

    private static function calculateRiskModifiers(string $type, float $severity): array
    {
        $baseModifiers = [
            'plague' => ['injury' => 0.6, 'environmental' => 0.4, 'myth' => 0.2],
            'civil_war' => ['political' => 0.8, 'faction' => 0.7, 'resource' => 0.3],
            'magic_collapse' => ['myth' => 0.9, 'environmental' => 0.5, 'injury' => 0.3],
            'famine' => ['resource' => 0.8, 'political' => 0.4, 'injury' => 0.5],
            'invasion' => ['political' => 0.7, 'faction' => 0.6, 'injury' => 0.8],
            'earthquake' => ['environmental' => 0.9, 'injury' => 0.7, 'resource' => 0.4],
            'myth_awakening' => ['myth' => 0.8, 'environmental' => 0.3, 'political' => 0.5],
        ];

        $modifiers = $baseModifiers[$type] ?? ['injury' => 0.2, 'environmental' => 0.2, 'political' => 0.2];

        foreach ($modifiers as $key => $value) {
            $modifiers[$key] = $value * $severity;
        }

        return $modifiers;
    }

    public static function plague(float $severity, string $region): self
    {
        return self::create('plague', $severity, $region, 0.3 * $severity, [
            'contagion_rate' => $severity * 0.8,
            'mortality_rate' => $severity * 0.4,
        ]);
    }

    public static function civilWar(float $severity, string $region): self
    {
        return self::create('civil_war', $severity, $region, 0.4 * $severity, [
            'faction_count' => rand(2, 5),
            'duration_estimate' => $severity * 10,
        ]);
    }

    public static function magicCollapse(float $severity, string $region): self
    {
        return self::create('magic_collapse', $severity, $region, 0.5 * $severity, [
            'mana_void_radius' => $severity * 100,
            'recovery_time' => $severity * 50,
        ]);
    }

    public static function famine(float $severity, string $region): self
    {
        return self::create('famine', $severity, $region, 0.2 * $severity, [
            'crop_failure_rate' => $severity * 0.9,
            'starvation_risk' => $severity * 0.6,
        ]);
    }

    public static function invasion(float $severity, string $region): self
    {
        return self::create('invasion', $severity, $region, 0.4 * $severity, [
            'army_size' => $severity * 10000,
            'technology_level' => $severity,
        ]);
    }

    public static function earthquake(float $severity, string $region): self
    {
        return self::create('earthquake', $severity, $region, 0.3 * $severity, [
            'magnitude' => 5 + $severity * 3,
            'affected_area' => $severity * 1000,
        ]);
    }

    public static function mythAwakening(float $severity, string $region): self
    {
        return self::create('myth_awakening', $severity, $region, 0.6 * $severity, [
            'entity_type' => 'ancient_god',
            'power_level' => $severity,
            'influence_radius' => $severity * 500,
        ]);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'severity' => $this->severity,
            'affected_region' => $this->affectedRegion,
            'entropy_delta' => $this->entropyDelta,
            'risk_modifiers' => $this->riskModifiers,
            'metadata' => $this->metadata,
        ];
    }
}
