<?php

namespace App\Domains\World\Enums;

/**
 * Power Ranking Systems.
 *
 * Determines how power levels are measured and displayed in this world.
 */
enum PowerRanking: string
{
    case NATURAL = 'NATURAL';     // Sơ - Trung - Cao - Tuyệt
    case ALPHABET = 'ALPHABET';   // F - E - D - C - B - A - S - SS - SSS - R - SR - SSR
    case CULTIVATION = 'CULTIVATION'; // Luyện Khí → Trúc Cơ → Kim Đan → ...

    public function label(): string
    {
        return match($this) {
            self::NATURAL => 'Tự Nhiên (Sơ → Tuyệt)',
            self::ALPHABET => 'Alphabet (F → SSR)',
            self::CULTIVATION => 'Tu Luyện (Luyện Khí → Tiên)',
        };
    }

    /**
     * Get the ordered tiers for this ranking system.
     */
    public function tiers(): array
    {
        return match($this) {
            self::NATURAL => [
                'so_cap' => 'Sơ Cấp',
                'trung_cap' => 'Trung Cấp',
                'cao_cap' => 'Cao Cấp',
                'tuyet_cap' => 'Tuyệt Cấp',
            ],
            self::ALPHABET => [
                'F' => 'F', 'E' => 'E', 'D' => 'D', 'C' => 'C',
                'B' => 'B', 'A' => 'A', 'S' => 'S', 'SS' => 'SS',
                'SSS' => 'SSS', 'R' => 'R', 'SR' => 'SR', 'SSR' => 'SSR',
            ],
            self::CULTIVATION => [
                'luyen_khi' => 'Luyện Khí',
                'truc_co' => 'Trúc Cơ',
                'kim_dan' => 'Kim Đan',
                'nguyen_anh' => 'Nguyên Anh',
                'hoa_than' => 'Hoá Thần',
                'dai_thua' => 'Đại Thừa',
                'dai_la' => 'Đại La',
                'tien' => 'Tiên',
            ],
        };
    }
}
