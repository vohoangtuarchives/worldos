<?php

declare(strict_types=1);

namespace WorldOS\Society\Culture\ValueObject;

/**
 * CulturalVector: The "Soul" of a civilization.
 * Defines the expressive and identity-driven dimensions (0.0 to 1.0).
 */
readonly class CulturalVector
{
    public function __construct(
        public float $aestheticDensity = 0.5,    // Mức độ biểu tượng hóa & nghệ thuật
        public float $intellectualCuriosity = 0.5, // Khả năng sinh tri thức & sáng tạo
        public float $traditionRigidity = 0.5,     // Mức độ bám truyền thống & kháng lại thay đổi
        public float $individualExpression = 0.5,  // Mức độ cá nhân hóa & phân hóa nội bộ
    ) {
    }

    public static function default(): self
    {
        return new self();
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (float) ($data['aesthetic_density'] ?? 0.5),
            (float) ($data['intellectual_curiosity'] ?? 0.5),
            (float) ($data['tradition_rigidity'] ?? 0.5),
            (float) ($data['individual_expression'] ?? 0.5),
        );
    }

    public function toArray(): array
    {
        return [
            'aesthetic_density' => $this->aestheticDensity,
            'intellectual_curiosity' => $this->intellectualCuriosity,
            'tradition_rigidity' => $this->traditionRigidity,
            'individual_expression' => $this->individualExpression,
        ];
    }

    /**
     * Mutate culture towards a specific direction or with random noise.
     */
    public function mutate(float $variance = 0.02, ?array $noise = null): self
    {
        $n = $noise ?? [
            mt_rand(-100, 100) / 100,
            mt_rand(-100, 100) / 100,
            mt_rand(-100, 100) / 100,
            mt_rand(-100, 100) / 100,
        ];

        return new self(
            $this->clamp($this->aestheticDensity + ($n[0] ?? 0) * $variance),
            $this->clamp($this->intellectualCuriosity + ($n[1] ?? 0) * $variance),
            $this->clamp($this->traditionRigidity + ($n[2] ?? 0) * $variance),
            $this->clamp($this->individualExpression + ($n[3] ?? 0) * $variance),
        );
    }

    private function clamp(float $value): float
    {
        return max(0.0, min(1.0, $value));
    }
}
