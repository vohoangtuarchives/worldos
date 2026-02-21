<?php

declare(strict_types=1);

namespace Tuzy\Domain\World\Enums;

enum PowerRanking: string
{
    case NATURAL = 'NATURAL';
    case ALPHABET = 'ALPHABET';
    case CULTIVATION = 'CULTIVATION';

    public function label(): string
    {
        return match ($this) {
            self::NATURAL => 'Natural',
            self::ALPHABET => 'Alphabet',
            self::CULTIVATION => 'Cultivation',
        };
    }

    public function tiers(): array
    {
        return match ($this) {
            self::NATURAL => ['so_cap' => 'Sơ Cấp', 'trung_cap' => 'Trung Cấp', 'cao_cap' => 'Cao Cấp', 'tuyet_cap' => 'Tuyệt Cấp'],
            self::ALPHABET => ['F' => 'F', 'E' => 'E', 'D' => 'D', 'C' => 'C', 'B' => 'B', 'A' => 'A', 'S' => 'S', 'SS' => 'SS', 'SSS' => 'SSS', 'R' => 'R', 'SR' => 'SR', 'SSR' => 'SSR'],
            self::CULTIVATION => ['luyen_khi' => 'Luyện Khí', 'truc_co' => 'Trúc Cơ', 'kim_dan' => 'Kim Đan', 'nguyen_anh' => 'Nguyên Anh', 'hoa_than' => 'Hoá Thần', 'dai_thua' => 'Đại Thừa', 'dai_la' => 'Đại La', 'tien' => 'Tiên'],
        };
    }
}
