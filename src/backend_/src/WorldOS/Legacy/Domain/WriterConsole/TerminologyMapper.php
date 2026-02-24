<?php

namespace WorldOS\Legacy\Domain\WriterConsole;

/**
 * Terminology Mapper
 * 
 * Translates technical simulation terms into writer-friendly concepts.
 * 
 * Philosophy (ADR-1000):
 * - Simulator: Technical, precise, mathematical
 * - Writer Console: Evocative, clear, creative
 */
class TerminologyMapper
{
    /**
     * Map archetype key to display name
     */
    public function mapArchetype(string $key): string
    {
        return ucwords(str_replace('_', ' ', $key));
    }

    /**
     * Map archetype weight to "World Mood"
     * 
     * 0.0 - 1.0 -> Evocative description
     */
    public function mapWeightToMood(string $archetypeKey, float $weight): string
    {
        // Example: 'silence'
        if ($archetypeKey === 'silence') {
            return match(true) {
                $weight > 0.8 => 'Sự im lặng đinh tai', // Deafening Silence
                $weight > 0.6 => 'Thầm lặng', // Hushed
                $weight > 0.4 => 'Yên tĩnh', // Quiet
                $weight > 0.2 => 'Thì thầm', // Murmuring
                default => 'Ồn ào', // Cacophonous
            };
        }

        // Generic fallback
        return match(true) {
            $weight > 0.8 => 'Áp đảo', // Overwhelming
            $weight > 0.6 => 'Thống trị', // Dominant
            $weight > 0.4 => 'Hiện diện', // Present
            $weight > 0.2 => 'Mờ nhạt', // Fading
            default => 'Vắng bóng', // Absent
        };
    }

    /**
     * Map drift to "Atmosphere Shift"
     */
    public function mapDriftDescription(float $delta): string
    {
        if (abs($delta) < 0.05) return 'Ổn định'; // Stable
        
        return $delta > 0 
            ? 'Cường độ tăng' // Rising Intensity
            : 'Ảnh hưởng giảm'; // Fading Influence
    }

    /**
     * Map collapse to "Story Beat"
     */
    public function mapCollapseToBeat(array $context): string
    {
        $archetype = $context['dominant_archetype'] ?? 'không xác định';
        return "Sự sụp đổ của {$this->mapArchetype($archetype)}"; // The Fall of ...
    }

    /**
     * Get writer-friendly tooltips
     */
    public function getTooltip(string $term): string
    {
        return match($term) {
            'archetype' => 'Một chủ đề hoặc lực lượng cơ bản định hình thế giới này',
            'weight' => 'Mức độ ảnh hưởng của chủ đề này đến các sự kiện',
            'drift' => 'Chủ đề đang thay đổi như thế nào theo thời gian',
            'legitimacy' => 'Mức độ chấp nhận trật tự hiện tại của người dân',
            'collapse' => 'Sự sụp đổ thảm khốc của trật tự xã hội hiện tại',
            default => '',
        };
    }
}
