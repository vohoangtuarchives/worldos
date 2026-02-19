<?php

declare(strict_types=1);

namespace App\Domains\Cosmology\Services;

use App\Domains\Cosmology\ValueObjects\CosmicState;
use App\Domains\Cosmology\ValueObjects\CivilizationState;

/**
 * EpochManager — manages epoch lifecycle and style transitions.
 *
 * From RFC §6.4:
 *   Epoch { id, world_id, start_tick, end_tick, style_version_id, law_version_id }
 *   Style update → new epoch → dampened transition:
 *     effective_style(t) = lerp(old, new, α)  // α increases over N ticks
 *
 * An epoch is a period where style + law are frozen.
 * Changing style creates a new epoch with smooth transition.
 */
class EpochManager
{
    // How many ticks for a style transition to fully take effect
    private const TRANSITION_TICKS = 10;

    /** @var array<array> */
    private array $epochs = [];
    private int $epochCounter = 0;

    private ?UniverseStyleVersion $currentStyle = null;
    private ?UniverseStyleVersion $previousStyle = null;
    private int $transitionStartTick = 0;

    public function __construct(?UniverseStyleVersion $initialStyle = null)
    {
        $this->currentStyle = $initialStyle ?? UniverseStyleVersion::defaultStyle();
    }

    /**
     * Start the first epoch.
     */
    public function startFirstEpoch(int $tick, string $lawVersion = '1.0'): void
    {
        $this->epochs[] = [
            'id' => $this->epochCounter++,
            'start_tick' => $tick,
            'end_tick' => null,
            'style_version' => $this->currentStyle->toArray(),
            'law_version' => $lawVersion,
        ];
    }

    /**
     * Transition to a new style version.
     * Closes current epoch, opens new one with dampened transition.
     */
    public function transitionStyle(UniverseStyleVersion $newStyle, int $tick, string $lawVersion = '1.0'): void
    {
        // Close current epoch
        if (!empty($this->epochs)) {
            $lastIdx = count($this->epochs) - 1;
            $this->epochs[$lastIdx]['end_tick'] = $tick;
        }

        // Store old for dampening
        $this->previousStyle = $this->currentStyle;
        $this->currentStyle = $newStyle;
        $this->transitionStartTick = $tick;

        // Open new epoch
        $this->epochs[] = [
            'id' => $this->epochCounter++,
            'start_tick' => $tick,
            'end_tick' => null,
            'style_version' => $newStyle->toArray(),
            'law_version' => $lawVersion,
        ];
    }

    /**
     * Get the effective style bias at tick, accounting for dampened transition.
     *
     * effective_style(t) = lerp(old, new, α)  where α ramps from 0→1
     */
    public function effectiveStyleBias(string $currentArchetype, int $tick): array
    {
        $newBias = $this->currentStyle->styleBias($currentArchetype);

        if ($this->previousStyle === null) {
            return $newBias;
        }

        $elapsed = $tick - $this->transitionStartTick;
        if ($elapsed >= self::TRANSITION_TICKS) {
            $this->previousStyle = null; // Transition complete
            return $newBias;
        }

        $alpha = $elapsed / self::TRANSITION_TICKS;
        $oldBias = $this->previousStyle->styleBias($currentArchetype);

        $result = [];
        foreach ($newBias as $dim => $newVal) {
            $oldVal = $oldBias[$dim] ?? 0.0;
            $result[$dim] = $oldVal + $alpha * ($newVal - $oldVal);
        }

        return $result;
    }

    /**
     * Check if we're in a transition period.
     */
    public function isInTransition(int $tick): bool
    {
        if ($this->previousStyle === null) return false;
        return ($tick - $this->transitionStartTick) < self::TRANSITION_TICKS;
    }

    /**
     * Get transition progress (0.0 to 1.0).
     */
    public function transitionProgress(int $tick): float
    {
        if ($this->previousStyle === null) return 1.0;
        $elapsed = $tick - $this->transitionStartTick;
        return min(1.0, $elapsed / self::TRANSITION_TICKS);
    }

    // --- Getters ---
    public function getCurrentStyle(): UniverseStyleVersion { return $this->currentStyle; }
    public function getEpochs(): array { return $this->epochs; }
    public function getCurrentEpoch(): ?array { return empty($this->epochs) ? null : end($this->epochs); }
    public function getEpochCount(): int { return count($this->epochs); }

    public function toArray(): array
    {
        return [
            'epochs' => $this->epochs,
            'epoch_counter' => $this->epochCounter,
            'current_style' => $this->currentStyle->toArray(),
            'previous_style' => $this->previousStyle?->toArray(),
            'transition_start_tick' => $this->transitionStartTick,
        ];
    }

    public static function fromArray(array $data): self
    {
        $style = UniverseStyleVersion::fromArray($data['current_style'] ?? []);
        $mgr = new self($style);
        $mgr->epochs = $data['epochs'] ?? [];
        $mgr->epochCounter = $data['epoch_counter'] ?? 0;
        $mgr->transitionStartTick = $data['transition_start_tick'] ?? 0;

        if (!empty($data['previous_style'])) {
            $mgr->previousStyle = UniverseStyleVersion::fromArray($data['previous_style']);
        }

        return $mgr;
    }
}
