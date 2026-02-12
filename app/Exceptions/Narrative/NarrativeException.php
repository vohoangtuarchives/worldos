<?php

namespace App\Exceptions\Narrative;

use App\Exceptions\WorldOSException;

class NarrativeException extends WorldOSException
{
    public const DIALOGUE_GENERATION_FAILED = 'NARRATIVE_DIALOGUE_FAILED';
    public const CHARACTER_INCONSISTENCY = 'NARRATIVE_CHARACTER_INCONSISTENCY';
    public const SCENE_VALIDATION_FAILED = 'NARRATIVE_SCENE_VALIDATION_FAILED';
    public const TIMELINE_CONTRADICTION = 'NARRATIVE_TIMELINE_CONTRADICTION';
    public const LLM_SERVICE_ERROR = 'NARRATIVE_LLM_ERROR';
    public const CONTEXT_BUILDING_FAILED = 'NARRATIVE_CONTEXT_FAILED';

    public static function dialogueGenerationFailed(string $character, string $reason, array $context = []): self
    {
        return new self(
            "Dialogue generation failed for character '{$character}': {$reason}",
            self::DIALOGUE_GENERATION_FAILED,
            array_merge(['character' => $character, 'reason' => $reason], $context)
        );
    }

    public static function characterInconsistency(string $character, string $inconsistency, array $context = []): self
    {
        return new self(
            "Character inconsistency detected for '{$character}': {$inconsistency}",
            self::CHARACTER_INCONSISTENCY,
            array_merge(['character' => $character, 'inconsistency' => $inconsistency], $context)
        );
    }

    public static function sceneValidationFailed(string $scene, string $validationError, array $context = []): self
    {
        return new self(
            "Scene validation failed for '{$scene}': {$validationError}",
            self::SCENE_VALIDATION_FAILED,
            array_merge(['scene' => $scene, 'validation_error' => $validationError], $context)
        );
    }

    public static function timelineContradiction(string $event1, string $event2, string $contradiction, array $context = []): self
    {
        return new self(
            "Timeline contradiction between '{$event1}' and '{$event2}': {$contradiction}",
            self::TIMELINE_CONTRADICTION,
            array_merge(['event1' => $event1, 'event2' => $event2, 'contradiction' => $contradiction], $context)
        );
    }

    public static function llmServiceError(string $service, string $error, array $context = []): self
    {
        return new self(
            "LLM service error from '{$service}': {$error}",
            self::LLM_SERVICE_ERROR,
            array_merge(['service' => $service, 'error' => $error], $context)
        );
    }

    public static function contextBuildingFailed(string $contextType, string $reason, array $context = []): self
    {
        return new self(
            "Context building failed for '{$contextType}': {$reason}",
            self::CONTEXT_BUILDING_FAILED,
            array_merge(['context_type' => $contextType, 'reason' => $reason], $context)
        );
    }
}
